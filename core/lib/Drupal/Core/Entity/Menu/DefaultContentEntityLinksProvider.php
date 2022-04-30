<?php

namespace Drupal\Core\Entity\Menu;

use Drupal\Core\Entity\EntityHandlerInterface;

/**
 * Default link provider for content entity types.
 */
class DefaultContentEntityLinksProvider extends BaseEntityLinksProvider implements EntityHandlerInterface {

  /**
   * {@inheritdoc}
   */
  protected function getCollectionMenuLink($base_plugin_definition) {
    if ($this->routeExists($this->getRouteName('collection'))) {
      // Content entity types that have a collection route get a menu link that
      // is placed under 'admin > content', as well as the task link.
      // See https://www.drupal.org/project/drupal/issues/2862859 for a plan to
      // change this UI.
      $link = $base_plugin_definition;

      $link['title'] = $this->entityType->getCollectionLabel();
      $link['description'] = t('List and edit @plural-label.', [
        '@plural-label' => $this->entityType->getPluralLabel(),
      ]);
      $link['route_name'] = $this->getRouteName('collection');
      $link['parent'] = 'system.admin_content';

      return $link;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionTaskLink($base_plugin_definition) {
    // Content entities follow the pattern to get a tab under /admin/content.
    // This has several problems: the UI scales badly, and the absence of node
    // module breaks it.
    // See https://www.drupal.org/project/drupal/issues/2862859 for a plan to
    // change this UI.
    if ($this->routeExists($this->getRouteName('collection'))) {
      $link = $base_plugin_definition;

      $link['title'] = $this->entityType->getCollectionLabel();
      $link['route_name'] = $this->getRouteName('collection');
      $link['base_route'] = "system.admin_content";

      return $link;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalTaskLink($base_plugin_definition) {
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
  protected function getEditFormTaskLink($base_plugin_definition) {
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
  protected function getDeleteFormTaskLink($base_plugin_definition) {
    if ($this->routeExists($this->getRouteName('delete_form')) && $this->routeExists($this->getRouteName('canonical'))) {
      $link = $base_plugin_definition;

      $link['title'] = $this->t('Delete');
      $link['route_name'] = $this->getRouteName('delete_form');
      $link['base_route'] = $this->getRouteName('canonical');

      return $link;
    }
  }

}
