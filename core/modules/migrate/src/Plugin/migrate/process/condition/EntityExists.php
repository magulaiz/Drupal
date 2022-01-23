<?php

namespace Drupal\migrate\Plugin\migrate\process\condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an entity_exists condition.
 *
 * Available configuration keys:
 * - entity_type: The machine name of the entity type.
 *
 * Examples:
 *
 * Skip a process if field_tags does not correspond to an existing term.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     condition: entity_exists
 *     negate: true
 *     configuration:
 *       entity_type: taxonomy_term
 *     source: field_tags
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessConditionPluginInterface
 *
 * @MigrateProcessConditionPlugin(
 *   id = "entity_exists",
 *   requires = {"entity_type"}
 * )
 */
class EntityExists extends ProcessConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * EntityExists constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage($configuration['entity_type'])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    if (is_array($source)) {
      $source = reset($source);
    }

    $entity = $this->storage->load($source);
    if ($entity instanceof EntityInterface) {
      return TRUE;
    }
    return FALSE;
  }

}
