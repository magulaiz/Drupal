<?php

namespace Drupal\views;

use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a BC layer for modules providing old configurations.
 *
 * @internal
 */
class ViewsConfigUpdater implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * The views data service.
   *
   * @var \Drupal\views\ViewsData
   */
  protected $viewsData;

  /**
   * An array of helper data for the multivalue base field update.
   *
   * @var array
   */
  protected $multivalueBaseFieldsUpdateTableInfo;

  /**
   * Flag determining whether deprecations should be triggered.
   *
   * @var bool
   */
  protected $deprecationsEnabled = TRUE;

  /**
   * Stores which deprecations were triggered.
   *
   * @var bool
   */
  protected $triggeredDeprecations = [];

  /**
   * ViewsConfigUpdater constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   The typed config manager.
   * @param \Drupal\views\ViewsData $views_data
   *   The views data service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    TypedConfigManagerInterface $typed_config_manager,
    ViewsData $views_data
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->typedConfigManager = $typed_config_manager;
    $this->viewsData = $views_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.typed'),
      $container->get('views.views_data')
    );
  }

  /**
   * Sets the deprecations enabling status.
   *
   * @param bool $enabled
   *   Whether deprecations should be enabled.
   */
  public function setDeprecationsEnabled($enabled) {
    $this->deprecationsEnabled = $enabled;
  }

  /**
   * Performs all required updates.
   *
   * @param \Drupal\views\ViewEntityInterface $view
   *   The View to update.
   *
   * @return bool
   *   Whether the view was updated.
   */
  public function updateAll(ViewEntityInterface $view) {
    return $this->processDisplayHandlers($view, FALSE, function (&$handler, $handler_type, $key, $display_id) use ($view) {
      $changed = FALSE;
      if ($this->processResponsiveImageLazyLoadFieldHandler($handler, $handler_type, $view)) {
        $changed = TRUE;
      }
      if ($this->processEmptyGroupColumn($handler, $handler_type, $key, $display_id, $view)) {
        $changed = TRUE;
      }
      return $changed;
    });
  }

  /**
   * Add lazy load options to all responsive_image type field configurations.
   *
   * @param \Drupal\views\ViewEntityInterface $view
   *   The View to update.
   *
   * @return bool
   *   Whether the view was updated.
   */
  public function needsResponsiveImageLazyLoadFieldUpdate(ViewEntityInterface $view): bool {
    return $this->processDisplayHandlers($view, TRUE, function (&$handler, $handler_type) use ($view) {
      return $this->processResponsiveImageLazyLoadFieldHandler($handler, $handler_type, $view);
    });
  }

  /**
   * Processes responsive_image type fields.
   *
   * @param array $handler
   *   A display handler.
   * @param string $handler_type
   *   The handler type.
   * @param \Drupal\views\ViewEntityInterface $view
   *   The View being updated.
   *
   * @return bool
   *   Whether the handler was updated.
   */
  protected function processResponsiveImageLazyLoadFieldHandler(array &$handler, string $handler_type, ViewEntityInterface $view): bool {
    $changed = FALSE;

    // Add any missing settings for lazy loading.
    if (($handler_type === 'field')
      && isset($handler['plugin_id'], $handler['type'])
      && $handler['plugin_id'] === 'field'
      && $handler['type'] === 'responsive_image'
      && !isset($handler['settings']['image_loading'])) {
      $handler['settings']['image_loading'] = ['attribute' => 'eager'];
      $changed = TRUE;
    }

    return $changed;
  }

  /**
   * Processes all display handlers.
   *
   * @param \Drupal\views\ViewEntityInterface $view
   *   The View to update.
   * @param bool $return_on_changed
   *   Whether processing should stop after a change is detected.
   * @param callable $handler_processor
   *   A callback performing the actual update.
   *
   * @return bool
   *   Whether the view was updated.
   */
  protected function processDisplayHandlers(ViewEntityInterface $view, $return_on_changed, callable $handler_processor) {
    $changed = FALSE;
    $displays = $view->get('display');
    $handler_types = ['field', 'argument', 'sort', 'relationship', 'filter'];

    foreach ($displays as $display_id => &$display) {
      foreach ($handler_types as $handler_type) {
        $handler_type_plural = $handler_type . 's';
        if (!empty($display['display_options'][$handler_type_plural])) {
          foreach ($display['display_options'][$handler_type_plural] as $key => &$handler) {
            if ($handler_processor($handler, $handler_type, $key, $display_id)) {
              $changed = TRUE;
              if ($return_on_changed) {
                return $changed;
              }
            }
          }
        }
      }
    }

    if ($changed) {
      $view->set('display', $displays);
    }

    return $changed;
  }

  /**
   * Add eager load option to all oembed type field configurations.
   *
   * @param \Drupal\views\ViewEntityInterface $view
   *   The View to update.
   *
   * @return bool
   *   Whether the view was updated.
   */
  public function needsOembedEagerLoadFieldUpdate(ViewEntityInterface $view) {
    return $this->processDisplayHandlers($view, TRUE, function (&$handler, $handler_type) use ($view) {
      return $this->processOembedEagerLoadFieldHandler($handler, $handler_type, $view);
    });
  }

  /**
   * Processes oembed type fields.
   *
   * @param array $handler
   *   A display handler.
   * @param string $handler_type
   *   The handler type.
   * @param \Drupal\views\ViewEntityInterface $view
   *   The View being updated.
   *
   * @return bool
   *   Whether the handler was updated.
   */
  protected function processOembedEagerLoadFieldHandler(array &$handler, string $handler_type, ViewEntityInterface $view): bool {
    $changed = FALSE;

    // Add any missing settings for lazy loading.
    if (($handler_type === 'field')
      && isset($handler['plugin_id'], $handler['type'])
      && $handler['plugin_id'] === 'field'
      && $handler['type'] === 'oembed'
      && !array_key_exists('loading', $handler['settings'])) {
      $handler['settings']['loading'] = ['attribute' => 'eager'];
      $changed = TRUE;
    }

    $deprecations_triggered = &$this->triggeredDeprecations['3212351'][$view->id()];
    if ($this->deprecationsEnabled && $changed && !$deprecations_triggered) {
      $deprecations_triggered = TRUE;
      @trigger_error(sprintf('The oEmbed loading attribute update for view "%s" is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Profile, module and theme provided configuration should be updated to accommodate the changes described at https://www.drupal.org/node/3275103.', $view->id()), E_USER_DEPRECATED);
    }

    return $changed;
  }

  /**
   * Checks if there are any fields that have an empty group_column.
   *
   * @param \Drupal\views\ViewEntityInterface $view
   *   The View to update.
   *
   * @return bool
   *   Whether the view was updated.
   */
  public function needsFixForEmptyGroupColumn(ViewEntityInterface $view): bool {
    return $this->processDisplayHandlers($view, TRUE, function (&$handler, $handler_type, $key, $display_id) use ($view) {
      return $this->processEmptyGroupColumn($handler, $handler_type, $key, $display_id, $view);
    });
  }

  /**
   * Fixes empty group columns.
   *
   * Some fields could be saved without a group column, this assures that every
   * field has a default group column.
   *
   * @param array $handler
   *   A display handler.
   * @param string $handler_type
   *   The handler type.
   * @param string $key
   *   The handler key.
   * @param string $display_id
   *   The handler display ID.
   * @param \Drupal\views\ViewEntityInterface $view
   *   The view being updated.
   */
  public function processEmptyGroupColumn(array &$handler, string $handler_type, string $key, string $display_id, ViewEntityInterface $view): bool {
    if ($handler_type !== 'field') {
      return FALSE;
    }
    $changed = FALSE;
    if (!empty($handler['plugin_id']) && $handler['plugin_id'] === 'field' && isset($handler['group_column']) && empty($handler['group_column'])) {
      // Attempt to load the field storage definition of the field.
      $executable = $view->getExecutable();
      $executable->setDisplay($display_id);
      /** @var \Drupal\views\Plugin\views\field\FieldHandlerInterface $field_handler */
      $field_handler = $executable->getDisplay()->getHandler('field', $handler['id']);
      if ($entity_type_id = $field_handler->getEntityType()) {
        $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type_id);

        $field_storage = NULL;
        if (isset($handler['field']) && isset($field_storage_definitions[$handler['field']])) {
          $field_storage = $field_storage_definitions[$handler['field']];
        }
        elseif (isset($handler['entity_field']) && isset($field_storage_definitions[$handler['entity_field']])) {
          $field_storage = $field_storage_definitions[$handler['entity_field']];
        }
        if ($field_storage !== NULL) {
          // Use the field's main property as default column. If the field
          // item does not define a main property, use the first column as
          // default column.
          $default_column = $field_storage->getMainPropertyName();
          if (empty($default_column)) {
            $column_names = array_keys($field_storage->getColumns());
            $default_column = $column_names[0];
          }
          $handler['group_column'] = $default_column;
          $changed = TRUE;
        }
      }
    }

    $deprecations_triggered = &$this->triggeredDeprecations['2815881'][$view->id()];
    if ($this->deprecationsEnabled && $changed && !$deprecations_triggered) {
      $deprecations_triggered = TRUE;
      @trigger_error(sprintf('The field "%s" has its "group_column" set to an empty value for the "%s" view. This is deprecated in drupal:10.1.0 and is disallowed in drupal:11.0.0. Module-provided Views configuration should be updated to accommodate the changes. See https://www.drupal.org/node/3255641', $handler['field'], $view->id()), E_USER_DEPRECATED);
    }

    return $changed;
  }

}
