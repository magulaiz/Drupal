<?php

namespace Drupal\views\Plugin\views\field_views_data;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\Plugin\views\FieldTypeViewsDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for field views data plugins
 */
abstract class FieldViewsDataPluginBase extends PluginBase implements FieldTypeViewsDataInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Views data plugin definition.
   *
   * @var array
   */
  protected $plugin_definition;

  /**
   * Creates a new instance of the data type.
   *
   * @param array $configuration
   *   The configuration for this specific instance.
   * @param string $plugin_id
   *   The id of the plugin to create.
   * @param array $plugin_definition
   *   The definition of the plugin's available options.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    foreach (['argument', 'field', 'filter', 'sort'] as $key) {
      if (isset($plugin_definition[$key])) {
        $this->plugin_definition[$key] = $plugin_definition[$key];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getViewsData(FieldStorageDefinitionInterface $field_storage, $column_name) {
    $views_field = $this->plugin_definition;

    // Provide a nicer, less verbose label for the main column within a field.
    if ($field_storage->getMainPropertyName() == $column_name) {
      $views_field['title'] = $field_storage->getLabel();
    }
    else {
      $views_field['title'] = $field_storage->getLabel() . " ($column_name)";
    }

    if ($description = $field_storage->getDescription()) {
      $views_field['help'] = $description;
    }

    return $views_field;
  }

}
