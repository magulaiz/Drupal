<?php

namespace Drupal\Core\Entity\Menu;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Base class for entity link providers.
 *
 * This is intended to work with the routes provided by
 * \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider. In particular,
 * self::getRouteName() assumes that the route names for this handler's entity
 * type follow that route provider's conventions, as follows:
 *  - entity.ENTITY_TYPE.collection
 *  - entity.ENTITY_TYPE.canonical
 *  - entity.ENTITY_TYPE.add_page
 *  - entity.ENTITY_TYPE.add_form
 *  - entity.ENTITY_TYPE.edit_form
 *  - entity.ENTITY_TYPE.delete_form
 *
 * Plugins may be created with the following IDs:
 *  - Menu link plugins:
 *    - system.entity:entity.ENTITY_TYPE.collection
 * - Task link plugins:
 *    - system.entity:entity.ENTITY_TYPE.collection
 *    - system.entity:entity.ENTITY_TYPE.canonical
 *    - system.entity:entity.ENTITY_TYPE.edit_form
 *    - system.entity:entity.ENTITY_TYPE.delete_form
 * - Action link plugins:
 *    - system.entity:entity.ENTITY_TYPE.add_page
 *    - system.entity:entity.ENTITY_TYPE.add_form
 *
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
abstract class BaseEntityLinksProvider implements EntityLinksProviderInterface, EntityHandlerInterface {

  use StringTranslationTrait;

  /**
   * The entity type this handler is for.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The ID of the entity type this handler is for.
   *
   * @var string
   */
  protected $entityTypeID;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Creates a new BaseEntityLinksProvider.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type this handler is for.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider service.
   */
  public function __construct(EntityTypeInterface $entity_type, RouteProviderInterface $route_provider) {
    $this->entityType = $entity_type;
    $this->entityTypeID = $entity_type->id();
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuLinks($base_plugin_definition) {
    $link_derivative_plugins = [];

    if ($collection_link = $this->getCollectionMenuLink($base_plugin_definition)) {
      $link_derivative_plugins[$this->getRouteName('collection')] = $collection_link;
    }

    return $link_derivative_plugins;
  }

  /**
   * Returns the collection menu link, if one should be created.
   *
   * @param array $base_plugin_definition
   *   The base link plugin definition.
   *
   * @return array|null
   *   The plugin definition, or NULL if no link should be provided.
   */
  protected function getCollectionMenuLink($base_plugin_definition) {
  }

  /**
   * {@inheritdoc}
   */
  public function getTaskLinks($base_plugin_definition) {
    $task_derivative_plugins = [];

    if ($collection_task_link = $this->getCollectionTaskLink($base_plugin_definition)) {
      $task_derivative_plugins[$this->getRouteName('collection')] = $collection_task_link;
    }

    if ($canonical_task_link = $this->getCanonicalTaskLink($base_plugin_definition)) {
      $task_derivative_plugins[$this->getRouteName('canonical')] = $canonical_task_link;
    }

    if ($edit_link = $this->getEditFormTaskLink($base_plugin_definition)) {
      $task_derivative_plugins[$this->getRouteName('edit_form')] = $edit_link;
    }

    if ($delete_form_task_link = $this->getDeleteFormtaskLink($base_plugin_definition)) {
      $task_derivative_plugins[$this->getRouteName('delete_form')] = $delete_form_task_link;
    }

    return $task_derivative_plugins;
  }

  /**
   * Gets the collection task link, if one should be created.
   *
   * @param array $base_plugin_definition
   *   The base link plugin definition.
   *
   * @return array|null
   *   The plugin definition, or NULL if no link should be provided.
   */
  protected function getCollectionTaskLink($base_plugin_definition) {
  }

  /**
   * Gets the canonical task link, if one should be created.
   *
   * @param array $base_plugin_definition
   *   The base link plugin definition.
   *
   * @return array|null
   *   The plugin definition, or NULL if no link should be provided.
   */
  protected function getCanonicalTaskLink($base_plugin_definition) {
  }

  /**
   * Gets the edit form task link, if one should be created.
   *
   * @param array $base_plugin_definition
   *   The base link plugin definition.
   *
   * @return array|null
   *   The plugin definition, or NULL if no link should be provided.
   */
  protected function getEditFormTaskLink($base_plugin_definition) {
  }

  /**
   * Gets the delete form task link, if one should be created.
   *
   * @param array $base_plugin_definition
   *   The base link plugin definition.
   *
   * @return array|null
   *   The plugin definition, or NULL if no link should be provided.
   */
  protected function getDeleteFormTaskLink($base_plugin_definition) {
  }

  /**
   * {@inheritdoc}
   */
  public function getActionLinks($base_plugin_definition) {
    $action_derivative_plugins = [];

    if ($add_action_link = $this->getAddActionLink($base_plugin_definition)) {
      $action_derivative_plugins[$this->getRouteName('add')] = $add_action_link;
    }

    return $action_derivative_plugins;
  }

  /**
   * Gets the add action link, if one should be created.
   *
   * @param array $base_plugin_definition
   *   The base link plugin definition.
   *
   * @return array|null
   *   The plugin definition, or NULL if no link should be provided.
   */
  protected function getAddActionLink($base_plugin_definition) {
    // The 'add' action link appears on the collection, so don't show one if
    // there is no collection.
    if (!$this->entityType->hasLinkTemplate('collection')) {
      return;
    }

    $add_action_route = $this->getAddActionRouteName();

    if (!$add_action_route) {
      return;
    }

    $link['title'] = $this->t('Add @entity-type', [
      // Use the singular label, as it is in lower case to be used within a
      // longer piece of text.
      '@entity-type' => $this->entityType->getSingularLabel(),
    ]);
    $link['route_name'] = $add_action_route;
    $link['appears_on'][] = $this->getRouteName('collection');

    return $link;
  }

  /**
   * Gets the route name for the add action.
   *
   * Helper for getAddActionLink() for easier overriding for entity types that
   * do something different with their add route.
   *
   * The route for the action is either the add page or the add form. We don't
   * repeat the logic that the route provider does, but just check for whether
   * the routes exist.
   *
   * @return string|null
   *   The route name, or NULL if no route is found.
   */
  protected function getAddActionRouteName() {
    if ($this->routeExists($this->getRouteName('add_page'))) {
      return $this->getRouteName('add_page');
    }
    elseif ($this->routeExists($this->getRouteName('add_form'))) {
      return $this->getRouteName('add_form');
    }
    else {
      return;
    }
  }

  /**
   * Checks whether a route exists.
   *
   * This is a convenience method because the route provider service doesn't
   * have a method to check for the existence of a route.
   *
   * @param string $route_name
   *   The name of the route to check.
   *
   * @return bool
   *   Returns TRUE if the route exists, FALSE if not.
   */
  protected function routeExists($route_name) {
    $routes = $this->routeProvider->getRoutesByNames([$route_name]);
    if (empty($routes)) {
      return FALSE;
    }
    else {
      return TRUE;
    }
  }

  /**
   * Gets the route name for a particular relationship.
   *
   * This assumes that the current entity type has as its route provider
   * \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider, or has a route
   * provider that uses the same pattern for its route names.
   *
   * @param string $suffix
   *   The final part of the route name. This is analogous but not the same as
   *   the link relationship type parameter of
   *   \Drupal\Core\Entity\EntityInterface::toUrl(), as
   *   \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider generally uses
   *   underscores where the link relationship uses a hyphen.
   *
   * @return string
   *   The route name.
   */
  protected function getRouteName($suffix) {
    return "entity.{$this->entityTypeID}.{$suffix}";
  }

}
