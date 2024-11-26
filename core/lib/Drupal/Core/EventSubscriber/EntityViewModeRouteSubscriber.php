<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Ensures that routes can be provided by entity view modes.
 */
class EntityViewModeRouteSubscriber extends RouteSubscriberBase {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityTypeBundleInfoInterface $bundleInfo,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    $viewModeStorage = $this->entityTypeManager->getStorage('entity_view_mode');
    $query = $viewModeStorage->getQuery();
    $or = $query->orConditionGroup();
    $or->exists('path')
      ->condition('id', '.full', 'ENDS_WITH');
    $mode_ids = $query
      ->condition($or)
      ->accessCheck(FALSE)
      ->execute();
    if (\count($mode_ids) === 0) {
      return;
    }
    $modes = $viewModeStorage->loadMultiple($mode_ids);
    /** @var \Drupal\Core\Entity\EntityViewModeInterface $mode */
    foreach ($modes as $id => $mode) {
      $path = $mode->getPath();
      if ($path === NULL) {
        continue;
      }
      [, $view_mode] = \explode('.', $id);
      $entity_type_id = $mode->getTargetType();
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $template = $entity_type->getLinkTemplate('canonical');
      if ($template === FALSE) {
        continue;
      }
      $hidden_displays = $this->entityTypeManager->getStorage('entity_view_display')->getQuery()
        ->accessCheck(FALSE)
        ->condition('targetEntityType', $entity_type_id)
        ->condition('mode', $view_mode)
        ->condition('pageDisplay', FALSE)
        ->execute();

      $enabled_bundles = NULL;
      if (\count($hidden_displays) !== 0) {
        $bundles = $this->bundleInfo->getBundleInfo($entity_type_id);
        $enabled_bundles = \array_diff(\array_keys($bundles), \array_map(static fn (string $id) => \explode('.', $id)[1], $hidden_displays));
      }

      if ($view_mode === 'full') {
        if (count($hidden_displays) === 0) {
          continue;
        }

        // Add the bundles condition for enabled bundles to the canonical route.
        $route = $collection->get(\sprintf('entity.%s.canonical', $entity_type_id));
        $parameters = $route->getOption('parameters');
        if (\array_key_exists($entity_type_id, $parameters)) {
          $parameters[$entity_type_id]['bundle'] = $enabled_bundles;
        }
        $route->setOption('parameters', $parameters);
        continue;
      }

      // Add routes for any additional view modes with a path.
      $route = new Route(\sprintf('%s/%s', $template, $path));
      $parameters = [
        $entity_type_id => ['type' => 'entity:' . $entity_type_id],
      ];
      $route
        ->addDefaults([
          '_entity_view' => \sprintf('%s.%s', $entity_type_id, $view_mode),
          '_title_callback' => '\Drupal\Core\Entity\Controller\EntityController::title',
        ])
        ->setRequirement('_entity_access', \sprintf('%s.view', $entity_type_id));

      if ($enabled_bundles !== NULL) {
        // Add the bundle restriction.
        $parameters[$entity_type_id]['bundle'] = $enabled_bundles;
      }

      $route->setOption('parameters', $parameters);

      $canonical_route = $collection->get(\sprintf('entity.%s.canonical', $entity_type_id));
      if ($canonical_route !== NULL) {
        $requirement = $canonical_route->getRequirement($entity_type_id);
        if ($requirement !== NULL) {
          $route->setRequirement($entity_type_id, $requirement);
        }
      }
      $collection->add(\sprintf('entity.%s.entity_view_display__%s', $entity_type_id, $view_mode), $route);
    }
  }

}
