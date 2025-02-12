<?php

declare(strict_types=1);

namespace Drupal\content_translation\Plugin\Action;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\content_translation\Plugin\Action\Derivative\ContentEntityTranslatableActionDeriver;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Content Entity Translate action.
 */
#[Action(
  id: 'entity:translate_action',
  action_label: new TranslatableMarkup('Translate'),
  deriver: ContentEntityTranslatableActionDeriver::class
)]
class TranslateAction extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * The content translation manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  protected $contentTranslationManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a CreateEntityTranslation object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager
   *   The content translation manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContentTranslationManagerInterface $content_translation_manager, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->contentTranslationManager = $content_translation_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('content_translation.manager'),
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'source_langcode' => LanguageInterface::LANGCODE_DEFAULT,
      'target_langcodes' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $langcode_options = [];
    foreach ($this->languageManager->getLanguages() as $language) {
      $langcode_options[$language->getId()] = $language->getName();
    }
    $form['source_langcode'] = [
      '#type' => 'select',
      '#title' => $this->t('Translation source'),
      '#description' => $this->t('Which language to use as the source for the translations.'),
      '#options' => $langcode_options,
      '#default_value' => $this->configuration['target_langcodes'],
      '#required' => TRUE,
    ];

    $form['target_langcodes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Translations Languages'),
      '#options' => $langcode_options,
      '#default_value' => $this->configuration['target_langcodes'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['source_langcode'] = $form_state->getValue('source_langcode');
    $this->configuration['target_langcodes'] = $form_state->getValue('target_langcodes');
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities): void {
    /** @var \Drupal\Core\Entity\EntityInterface[] $entities */
    foreach ($entities as $entity) {
      $this->execute($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL): void {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    if ($entity === NULL || !$entity->isTranslatable()) {
      return;
    }

    $target_langcodes = array_filter($this->configuration['target_langcodes']);
    if (empty($target_langcodes)) {
      return;
    }

    $source_langcode = $this->configuration['source_langcode'];
    $content_translation_manager = $this->contentTranslationManager;

    $source_translation = $entity->hasTranslation($source_langcode) ? $entity->getTranslation($source_langcode) : $entity;
    $source_translation_metadata = $content_translation_manager->getTranslationMetadata($source_translation);
    if ($source_translation_metadata->getSource() === LanguageInterface::LANGCODE_NOT_SPECIFIED || $source_translation_metadata->getSource() === NULL) {
      $source_translation_metadata->setSource($source_langcode);
    }

    $translations_added = FALSE;
    foreach ($target_langcodes as $langcode) {
      // Translation already exists.
      if ($source_langcode == $langcode || $entity->hasTranslation($langcode)) {
        continue;
      }

      $translation = $entity->addTranslation($langcode, $source_translation->toArray());
      $translations_added = TRUE;

      // Avoid "Invalid translation language (und) specified" errors.
      $translation_metadata = $content_translation_manager->getTranslationMetadata($translation);
      if ($translation_metadata->getSource() === LanguageInterface::LANGCODE_NOT_SPECIFIED) {
        $translation_metadata->setSource($source_langcode);
      }

      // We need to create translations for Reference fields not translatable.
      foreach ($entity->getFieldDefinitions() as $field_name => $definition) {
        // If the field is not translatable and is an entity reference field,
        // we need to check if the referenced entities are translatable and
        // create translations for them as well.
        if ($definition->isTranslatable()) {
          continue;
        }

        // Translate the referenced entities that have translation enabled.
        $field_classs = $definition->getClass();
        if (is_subclass_of($field_classs, EntityReferenceFieldItemList::class)) {
          $target_type = $definition->getSetting('target_type');
          if ($content_translation_manager->isEnabled($target_type)) {
            foreach ($entity->{$field_name}->referencedEntities() as $referenced_entity) {
              if ($referenced_entity instanceof ContentEntityInterface && $referenced_entity->hasTranslation($source_langcode) && !$referenced_entity->hasTranslation($langcode)) {
                $referenced_entity = $referenced_entity->addTranslation($langcode, $referenced_entity->getTranslation($source_langcode)->toArray());
                $referenced_entity->save();
              }
            }
          }
        }
      }
    }

    // Save the entity if translations were added.
    if ($translations_added) {
      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE): bool {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $object */
    return $object->access('update', $account, $return_as_object);
  }

}
