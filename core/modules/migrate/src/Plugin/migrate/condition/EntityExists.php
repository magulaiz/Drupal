<?php

namespace Drupal\migrate\Plugin\migrate\condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
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
 * @see \Drupal\migrate\Plugin\MigrateConditionInterface
 *
 * @MigrateConditionPlugin(
 *   id = "entity_exists",
 *   requires = {"entity_type"}
 * )
 */
class EntityExists extends ConditionBase implements ContainerFactoryPluginInterface {

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
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    try {
      $this->storage = $entity_type_manager->getStorage($configuration['entity_type']);
    }
    catch (\Exception $e) {
      throw new \InvalidArgumentException('The entity_type configured for entity_exists could not be loaded.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    if (!is_int($source) && !is_string($source)) {
      throw new MigrateException("The source value for entity_exists must be an integer or a string.");
    }
    $entity = $this->storage->load($source);
    if ($entity instanceof EntityInterface) {
      return TRUE;
    }
    return FALSE;
  }

}
