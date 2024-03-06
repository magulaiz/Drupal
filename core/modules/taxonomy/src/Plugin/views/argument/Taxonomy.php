<?php

namespace Drupal\taxonomy\Plugin\views\argument;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler for basic taxonomy tid.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("taxonomy")
 */
class Taxonomy extends NumericArgument implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $termStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $term_storage, protected ?EntityRepositoryInterface $entityRepository = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->termStorage = $term_storage;
    if ($this->entityRepository === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $entityRepository argument is deprecated in drupal:10.3.0 and will be required in drupal:11.0.0. See https://www.drupal.org/project/drupal/issues/2765297', E_USER_DEPRECATED);
      $this->entityRepository = \Drupal::service('entity.repository');
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
      $container->get('entity_type.manager')->getStorage('taxonomy_term'),
      $container->get('entity.repository')
    );
  }

  /**
   * Override the behavior of title(). Get the title of the node.
   */
  public function title() {
    // There might be no valid argument.
    if ($this->argument) {
      $term = $this->entityRepository->getCanonical('taxonomy_term', $this->argument);
      if (!empty($term)) {
        return $term->label();
      }
    }
    // TODO review text
    return $this->t('No name');
  }

}
