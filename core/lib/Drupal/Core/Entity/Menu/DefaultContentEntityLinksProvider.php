<?php

namespace Drupal\Core\Entity\Menu;

use Drupal\Core\Entity\EntityHandlerInterface;

/**
 * Default link provider for content entity types.
 */
class DefaultContentEntityLinksProvider extends BaseEntityLinksProvider implements EntityHandlerInterface {

  /**
   * The ID of the menu link under which to place the collection.
   *
   * Content entity types that have a collection route typically get both a menu
   * link that places their collection under 'admin > content', as well as a
   * task link which puts their collection alongside the collection for nodes.
   *
   * This however has several problems: the UI scales badly, and the absence of
   * node module breaks it. See
   * https://www.drupal.org/project/drupal/issues/2862859 for a plan to change
   * this UI.
   */
  protected $collectionParentMenuLinkId = 'system.admin_content';

  /**
   * {@inheritdoc}
   */
  protected function getCollectionMenuLink(array $base_plugin_definition) {
    if ($this->routeExists($this->getRouteName('collection'))) {
      // Create a menu item for the collection under the parent menu item.
      $link = $base_plugin_definition;

      $link['title'] = $this->entityType->getCollectionLabel();
      $link['description'] = t('List and edit @plural-label.', [
        '@plural-label' => $this->entityType->getPluralLabel(),
      ]);
      $link['route_name'] = $this->getRouteName('collection');
      $link['parent'] = $this->collectionParentMenuLinkId;

      return $link;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionTaskLink(array $base_plugin_definition) {
    // Place a tab under the collection parent menu item.
    if ($this->routeExists($this->getRouteName('collection'))) {
      $link = $base_plugin_definition;

      $link['title'] = $this->entityType->getCollectionLabel();
      $link['route_name'] = $this->getRouteName('collection');
      $link['base_route'] = $this->collectionParentMenuLinkId;

      return $link;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalTaskLink(array $base_plugin_definition) {
    $canonical_route_name = $this->getRouteName('canonical');
    if ($this->routeExists($canonical_route_name)) {
      $link = $base_plugin_definition;

      $link['title'] = $this->t('View');
      $link['route_name'] = $canonical_route_name;
      $link['base_route'] = $canonical_route_name;

      return $link;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditFormTaskLink(array $base_plugin_definition) {
    if ($this->routeExists($this->getRouteName('edit_form')) && $this->routeExists($this->getRouteName('canonical'))) {
      $link = $base_plugin_definition;

      $link['title'] = $this->t('Edit');
      $link['route_name'] = $this->getRouteName('edit_form');
      $link['base_route'] = $this->getRouteName('canonical');

      return $link;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeleteFormTaskLink(array $base_plugin_definition) {
    if ($this->routeExists($this->getRouteName('delete_form')) && $this->routeExists($this->getRouteName('canonical'))) {
      $link = $base_plugin_definition;

      $link['title'] = $this->t('Delete');
      $link['route_name'] = $this->getRouteName('delete_form');
      $link['base_route'] = $this->getRouteName('canonical');

      return $link;
    }
  }

}
