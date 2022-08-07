<?php

namespace Drupal\Core\Entity\Menu;

use Drupal\Core\Entity\EntityHandlerInterface;

/**
 * Default link provider for config entity types.
 */
class DefaultConfigEntityLinksProvider extends BaseEntityLinksProvider implements EntityHandlerInterface {

  /**
   * The ID of the menu link under which to place the collection.
   *
   * Config entity types that have a collection route typically get a menu link
   * that is placed in the 'admin > structure' section.
   */
  protected $collectionParentMenuLinkId = 'system.admin_structure';

  /**
   * {@inheritdoc}
   */
  protected function getCollectionMenuLink(array $base_plugin_definition) {
    if ($this->routeExists($this->getRouteName('collection'))) {
      // Config entity types that have a collection route get a menu link that
      // is placed in the 'admin > structure' section.
      $link = $base_plugin_definition;

      $link['title'] = $this->entityType->getCollectionLabel();
      $link['description'] = t('Create and manage fields, forms, and display settings for @plural-label.', [
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
  protected function getEditFormTaskLink(array $base_plugin_definition) {
    // Config entities have just one task here, but for entity types which are
    // bundles (such as node_type), Field UI module hangs its own tabs for the
    // associated content entity's fields off the bundle entity's edit form
    // route.
    $edit_form_route_name = $this->getRouteName('edit_form');
    if ($this->routeExists($edit_form_route_name)) {
      $link = $base_plugin_definition;

      $link['title'] = $this->t('Edit');
      $link['route_name'] = $edit_form_route_name;
      $link['base_route'] = $edit_form_route_name;

      return $link;
    }
  }

}
