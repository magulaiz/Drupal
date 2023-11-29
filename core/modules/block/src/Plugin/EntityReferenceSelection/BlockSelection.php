<?php

declare(strict_types=1);

namespace Drupal\block\Plugin\EntityReferenceSelection;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\PhpSelection;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides specific access control for the block entity type.
 *
 * @EntityReferenceSelection(
 *   id = "default:block",
 *   label = @Translation("Block selection"),
 *   entity_types = {"block"},
 *   group = "default",
 *   weight = 1
 * )
 */
final class BlockSelection extends PhpSelection {

  /**
   * Constructs a new BlockSelection object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   The block manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityRepositoryInterface $entity_repository, protected BlockManagerInterface $blockManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $module_handler, $current_user, $entity_field_manager, $entity_type_bundle_info, $entity_repository);
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
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('plugin.manager.block'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS'): QueryInterface {
    $query = parent::buildEntityQuery($match, $match_operator);
    $configuration = $this->getConfiguration();
    if (!empty($configuration['block_categories'])) {
      $plugins = [];
      $definitions = $this->blockManager->getSortedDefinitions();
      foreach ($definitions as $id => $definition) {
        if (in_array((string) $definition['category'], array_keys($configuration['block_categories']), TRUE)) {
          $plugins[] = $id;
        }
      }
      $query->condition('plugin', $plugins, 'IN');
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    unset($form['auto_create']);
    $categories = array_values($this->blockManager->getCategories());
    $options = [];
    foreach ($categories as $category) {
      $untranslated_category = $category instanceof TranslatableMarkup
        ? $category->getUntranslatedString()
        : (string) $category;
      $options[$untranslated_category] = $category;
    }
    $configuration = $this->getConfiguration();
    $form['block_categories'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Choose which block categories should be available for selection.'),
      '#options' => $options,
    ];
    if (!empty($configuration['block_categories'])) {
      $configured_categories = array_keys($configuration['block_categories']);
      $form['block_categories']['#default_value'] = array_combine($configured_categories, $configured_categories);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::validateConfigurationForm($form, $form_state);

    // If no checkboxes were checked for 'block_categories', store NULL ("all
    // categories are referenceable"). Otherwise, clean up the submitted value
    // to make the config export more readable.
    $form_key = ['settings', 'handler_settings', 'block_categories'];
    if (!$form_state->getValue($form_key)) {
      return;
    }
    $selections = array_filter(array_map(function ($category): int {
      return (int) !empty($category);
    }, $form_state->getValue($form_key)));
    $form_state->setValue($form_key, empty($selections) ? NULL : $selections);
  }

}
