<?php

namespace Drupal\node\Menu;

use Drupal\Core\Entity\Menu\DefaultConfigEntityLinksProvider;

/**
 * Link provider for the node_type entity type.
 *
 * @see node_menu_links_discovered_alter()
 * @see node_local_tasks_alter()
 * @see node_menu_local_actions_alter()
 */
class NodeTypeLinksProvider extends DefaultConfigEntityLinksProvider {

  /**
   * {@inheritdoc}
   */
  protected function getAddActionRouteName(): ?string {
    // @todo Remove this when when node links IDs are switched to the common
    // pattern.
    return 'node.type_add';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionMenuLink(array $base_plugin_definition): ?array {
    $link = parent::getCollectionMenuLink($base_plugin_definition);

    $link['description'] = $this->t('Create and manage fields, forms, and display settings for your content.');

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionTaskLink(array $base_plugin_definition): ?array {
    // Place a tab under the collection parent menu item.
    if ($this->routeExists($this->getRouteName('collection'))) {
      $link = $base_plugin_definition;

      $link['title'] = $this->entityType->getCollectionLabel();
      $link['route_name'] = $this->getRouteName('collection');
      $link['base_route'] = $this->collectionParentMenuLinkId;

      return $link;
    }
    else {
      return NULL;
    }
  }

}
