<?php

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a key/value store migrate destination plugin.
 *
 * @MigrateDestination(
 *   id = "key_value",
 * )
 */
class KeyValue extends DestinationBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new key/value store destination plugin instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   *   The key/value factory service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, protected KeyValueFactoryInterface $keyValue) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('keyvalue'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'collection' => [
        'type' => 'string',
        'max_length' => 128,
        'is_ascii' => TRUE,
      ],
      'name' => [
        'type' => 'string',
        'max_length' => 128,
        'is_ascii' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    return [
      'collection' => $this->t('Collection'),
      'name' => $this->t('Name'),
      'value' => $this->t('Value'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []): array|bool {
    if (!$collection = $row->getDestinationProperty('collection')) {
      throw new \RuntimeException('Attempted to insert a key/value entry without a collection.');
    }
    if (!$name = $row->getDestinationProperty('name')) {
      throw new \RuntimeException('Attempted to insert a key/value entry without a name.');
    }
    $this->keyValue->get($collection)->set($name, $row->getDestinationProperty('value'));
    return [$collection, $name];
  }

  /**
   * {@inheritdoc}
   */
  public function supportsRollback(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier): void {
    $this->keyValue->get($destination_identifier['collection'])->delete($destination_identifier['name']);
  }

}
