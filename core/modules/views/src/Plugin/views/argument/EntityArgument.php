<?php

namespace Drupal\views\Plugin\views\argument;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept an entity ID value.
 *
 * This handler accepts the identifiers of entities themselves. The definition
 * defines the `entity_type` parameter to determine what kind of ID to load.
 * Entity reference ID values are handled by EntityReferenceArgument.
 *
 * @see \Drupal\views\Plugin\views\argument\EntityReferenceArgument
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("entity_id")
 */
class EntityArgument extends NumericArgument implements ContainerFactoryPluginInterface {

  protected EntityRepositoryInterface $entityRepository;

  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityRepositoryInterface | EntityStorageInterface | EntityTypeManagerInterface $entityRepository,
    ?EntityTypeManagerInterface $entityTypeManager = NULL,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityRepository = $entityRepository instanceof EntityRepositoryInterface ? $entityRepository : \Drupal::service('entity.repository');
    $this->entityTypeManager = $entityTypeManager ?? \Drupal::service('entity_type.manager');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function titleQuery() {
    $titles = [];

    $entities = $this->entityTypeManager->getStorage($this->definition['entity_type'])->loadMultiple($this->value);
    foreach ($entities as $entity) {
      $titles[$entity->id()] = $this->entityRepository->getTranslationFromContext($entity)->label();
    }
    return $titles;
  }

  /**
   * Array of deprecated storage properties that legacy classes might access.
   *
   * This class is replacing many separate plugins from different core modules,
   * each of which had a storage property for their own entity type. We can't
   * use Drupal\Core\DependencyInjection\DeprecatedServicePropertyTrait since
   * these are not registered as services, but are the storage "sub-service"
   * from the entityTypeManager for each entity type.
   */
  protected array $deprecatedStorageProperties = [
    'nodeStorage' => 'node',
    'termStorage' => 'taxonomy_term',
    'vocabularyStorage' => 'taxonomy_vocabulary',
    'storage' => 'user',
  ];

  /**
   * Allows to access deprecated/removed properties.
   *
   * This method must be public.
   */
  public function __get($name) {
    if (isset($this->deprecatedStorageProperties[$name])) {
      return $this->entityTypeManager->getStorage($this->deprecatedStorageProperties[$name]);
    }
  }

}
