<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 Entity Link Suggestions plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class EntityLinkSuggestions extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * EntityLinkSuggestions constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly EntityTypeBundleInfoInterface $entityTypeBundleInfo,

  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'allow_download_links' => TRUE,
      // This means all suggestions are allowed.
      'suggestions' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['allow_download_links'] = [
      '#title' => $this->t('Allow the user to create <em>download links</em>'),
      '#type' => 'checkbox',
      '#description' => $this->t('Allow content creators to create a download link on entities that support it, by adding a <a href=":url"><code>download</code></a> attribute that will cause the browser to download the linked file.', [':url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element/a#download']),
      '#default_value' => $this->configuration['allow_download_links']
    ];

    $entity_type_options = [];
    $common_reference_targets = [];
    $bundle_entity_type_ids = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Entity types must either have links or specify a link_target handler.
      if (!$entity_type->hasLinkTemplate('canonical') && !$entity_type->hasHandlerClass('link_target', 'view')) {
        continue;
      }
      $entity_type_options[$entity_type_id] = $entity_type->getCollectionLabel();
      if ($entity_type->isCommonReferenceTarget()) {
        $common_reference_targets[] = $entity_type_id;
      }
      if ($entity_type->getBundleEntityType() !== NULL) {
        $bundle_entity_type_ids[$entity_type_id] = $entity_type->getBundleEntityType();
      }
    }

    $allowed_entity_type_ids = ($this->configuration['suggestions'] !== NULL)
      // Either use the actually configured
      ? array_column($this->configuration['suggestions'], 'entity_type_id')
      // Or fall back to a sensible default when creating a new text editor:
      // entity types marked as common reference targets.
      : (
        $form_state->get('editor')->isNew()
          ? $common_reference_targets
          : []
      );

    // Ensure the checkboxes are presented in alphabetical order rather than the
    // arbitrary order that the entity type manager returns entity types.
    natcasesort($entity_type_options);
    $form['allowed_entity_types'] = [
      '#title' => $this->t('Provide link suggestions for:'),
      '#type' => 'checkboxes',
      '#options' => $entity_type_options,
      '#default_value' => $allowed_entity_type_ids,
      '#description' => $this->t('If none are selected, all will be allowed. If an entity type is missing from the above list, it is not linkable. For each entity type that supports <em>bundles</em> and that has at least 2 bundles, it is possible to select which bundles link suggestions should appear for.'),
    ];

    $form['per_bundle'] = [
      '#type' => 'container',
      '#optional' => TRUE,
      '#tree' => TRUE,
    ];

    foreach ($bundle_entity_type_ids as $entity_type_id => $bundle_entity_type_id) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $bundle_entity_type = $this->entityTypeManager->getDefinition($bundle_entity_type_id);
      $bundles_for_entity_type = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      if (count($bundles_for_entity_type) < 2) {
        continue;
      }
      $bundle_type_labels[] = $bundle_entity_type->getSingularLabel();
      $form['per_bundle'][$entity_type_id] = [
        '#type' => 'fieldset',
        '#states' => [
          'visible' => [
            ':input[name="editor[settings][plugins][ckeditor5_link_entity_suggestions][allowed_entity_types][' . $entity_type_id . ']"]' => ['checked' => TRUE],
          ],
        ],
        // The per-entity type fieldsets must match the order of the entity type
        // checkboxes above.
        '#weight' => array_search($entity_type_id, array_keys($entity_type_options)),
      ];

      $current_configuration_for_entity_type = array_filter(
        $this->configuration['suggestions'] ?? [],
        fn(array $s) => $s['entity_type_id'] === $entity_type_id
      );
      $current_configuration_for_entity_type = empty($current_configuration_for_entity_type) ? NULL : reset($current_configuration_for_entity_type);

      $form['per_bundle'][$entity_type_id]['bundle'] = [
        '#title' => $this->t('Limit %entity-type-label link suggestions per %bundle-label', [
          '%entity-type-label' => $entity_type->getCollectionLabel(),
          '%bundle-label' => $bundle_entity_type->getSingularLabel(),
        ]),
        '#type' => 'checkboxes',
        '#options' => array_combine(
          array_keys($bundles_for_entity_type),
          array_column($bundles_for_entity_type, 'label')
        ),
        '#default_value' => $current_configuration_for_entity_type
          ? $current_configuration_for_entity_type['bundles'] ?? []
          : [],
        '#description' => $this->t('If none are selected, all will be allowed.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Match the config schema structure at ckeditor5.plugin.ckeditor5_link_entity_suggestions.
    $form_value = $form_state->getValue('allow_download_links');
    $form_state->setValue('allow_download_links', (bool) $form_value);

    $suggestions = [];
    $entity_type_ids = Checkboxes::getCheckedCheckboxes($form_state->getValue('allowed_entity_types'));
    foreach ($entity_type_ids as $entity_type_id) {
      $suggestion = [
        'entity_type_id' => $entity_type_id,
        'bundles' => Checkboxes::getCheckedCheckboxes($form_state->getValue(['per_bundle', $entity_type_id, 'bundle'], [])),
      ];
      if (empty($suggestion['bundles'])) {
        $suggestion['bundles'] = NULL;
      }
      $suggestions[] = $suggestion;
    }
    if (empty($entity_type_ids)) {
      $suggestions = NULL;
    }
    $form_state->setValue('suggestions', $suggestions);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['allow_download_links'] = $form_state->getValue('allow_download_links');
    $this->configuration['suggestions'] = $form_state->getValue('suggestions');
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    $subset = $this->getPluginDefinition()->getElements();
    $download_links_enabled = $this->getConfiguration()['allow_download_links'];
    if (!$download_links_enabled) {
      $subset = array_diff($subset, ['<a download>']);
    }
    return $subset;
  }

}
