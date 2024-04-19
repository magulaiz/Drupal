<?php

declare(strict_types=1);

namespace Drupal\block_content\Routing;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides HTML routes for block content pages.
 */
class BlockContentRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    protected ImmutableConfig $config
  ) {
    parent::__construct($entity_type_manager, $entity_field_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): DefaultHtmlRouteProvider|BlockContentRouteProvider|EntityHandlerInterface|static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory')->get('block_content.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalRoute(EntityTypeInterface $entity_type): Route {
    if ($this->config->get('standalone_url')) {
      return parent::getCanonicalRoute($entity_type);
    }
    return parent::getEditFormRoute($entity_type);
  }

}
