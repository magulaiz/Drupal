<?php

namespace Drupal\node\Menu;

use Drupal\Core\Entity\Menu\DefaultContentEntityLinksProvider;

class NodeLinksProvider extends DefaultContentEntityLinksProvider {

  /**
   * {@inheritdoc}
   */
  protected function getAddActionLink(array $base_plugin_definition): ?array {
    $add_action_route = $this->getAddActionRouteName();

    if (!$add_action_route) {
      return NULL;
    }

    $link['title'] = $this->t('Add @entity-type', [
      // Use the singular label, as it is in lower case to be used within a
      // longer piece of text.
      '@entity-type' => $this->entityType->getSingularLabel(),
    ]);
    $link['route_name'] = $add_action_route;
    $link['appears_on'][] = 'system.admin_content';

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  protected function getAddActionRouteName(): ?string {
    return 'node.add';
  }

}
