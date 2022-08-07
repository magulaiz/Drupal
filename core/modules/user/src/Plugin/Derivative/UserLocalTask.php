<?php

namespace Drupal\user\Plugin\Derivative;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides local task definitions for all entity bundles.
 */
class UserLocalTask extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Creates a UserLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param \Drupal\Core\Routing\RouteProviderInterface|null $route_provider
   *   The route provider.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, ?RouteProviderInterface $route_provider = NULL) {
    if ($route_provider === NULL) {
      @trigger_error('Calling ' . __METHOD__ . ' without the $route_provider argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3302306', E_USER_DEPRECATED);
      $route_provider = \Drupal::service('router.route_provider');
    }
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    $entity_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_definitions as $bundle_type_id => $bundle_entity_type) {
      if (!$bundle_entity_type->hasLinkTemplate('entity-permissions-form')) {
        continue;
      }

      if (!$entity_type_id = $bundle_entity_type->getBundleOf()) {
        continue;
      }

      $entity_type = $entity_definitions[$entity_type_id];
      if (!$base_route = $entity_type->get('field_ui_base_route')) {
        continue;
      }

      $route_name = "entity.$bundle_type_id.entity_permissions_form";
      if (empty($this->routeProvider->getRoutesByNames([$route_name]))) {
        continue;
      }

      $this->derivatives["permissions_$bundle_type_id"] = [
        'route_name' => $route_name,
        'weight' => 10,
        'title' => $this->t('Manage permissions'),
        'base_route' => $base_route,
      ] + $base_plugin_definition;

    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
