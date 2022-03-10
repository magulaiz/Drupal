<?php

namespace Drupal\field_ui\Routing;

@trigger_error('The ' . __NAMESPACE__ . '\EntityBundleRouteEnhancer is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Instead, use \Drupal\Core\Entity\Enhancer\EntityBundleRouteEnhancer. See https://www.drupal.org/node/3245017', E_USER_DEPRECATED);

use Drupal\Core\Entity\Enhancer\EntityBundleRouteEnhancer;

/**
 * Enhances Field UI routes by adding proper information about the bundle name.
 *
 * @deprecated in drupal:9.4.0 and is removed from drupal:10.0.0.
 * Use \Drupal\Core\Entity\Enhancer\EntityBundleRouteEnhancer.
 *
 * @see https://www.drupal.org/node/3245017
 */
class FieldUiRouteEnhancer implements EnhancerInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a FieldUiRouteEnhancer object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    if (!$this->applies($defaults[RouteObjectInterface::ROUTE_OBJECT])) {
      return $defaults;
    }
    if (($bundle = $this->entityTypeManager->getDefinition($defaults['entity_type_id'])->getBundleEntityType()) && isset($defaults[$bundle])) {
      // Field UI forms only need the actual name of the bundle they're dealing
      // with, not an upcasted entity object, so provide a simple way for them
      // to get it.
      $defaults['bundle'] = $defaults['_raw_variables']->get($bundle);
    }

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  protected function applies(Route $route) {
    return ($route->hasOption('_field_ui'));
  }

}
