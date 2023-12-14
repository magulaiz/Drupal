<?php

namespace Drupal\Core\Entity\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides derivative action link plugins for entity types.
 *
 * @see \Drupal\Core\Entity\Menu\BaseEntityLinksProvider
 * @see plugin_api
 */
class EntityActionLinkDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an EntityActionLinkDeriver instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The type entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $plugin_definitions = [];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type) {
      if (!$entity_type->hasLinkProvider()) {
        continue;
      }

      $link_provider_handler = $this->entityTypeManager->getHandler($entity_type->id(), 'link_provider');

      $plugin_definitions += $link_provider_handler->getActionLinks($base_plugin_definition);
    }

    return $plugin_definitions;
  }

}
