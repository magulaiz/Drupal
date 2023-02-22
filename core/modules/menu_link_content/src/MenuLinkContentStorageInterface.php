<?php

namespace Drupal\menu_link_content;

use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for menu_link_content entity storage classes.
 *
 * @method \Drupal\menu_link_content\MenuLinkContentInterface create(array $values = [])
 * @method null|\Drupal\menu_link_content\MenuLinkContentInterface load($id)
 * @method null|\Drupal\menu_link_content\MenuLinkContentInterface loadRevision($revision_id)
 * @method null|\Drupal\menu_link_content\MenuLinkContentInterface loadUnchanged($id)
 * @method \Drupal\menu_link_content\MenuLinkContentInterface[] loadMultiple(array $ids = NULL)
 * @method \Drupal\menu_link_content\MenuLinkContentInterface[] loadByProperties(array $values = [])
 * @method null|int save(\Drupal\menu_link_content\MenuLinkContentInterface $entity)
 * @method void restore(\Drupal\menu_link_content\MenuLinkContentInterface $entity)
 */
interface MenuLinkContentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of menu link IDs with pending revisions.
   *
   * @return int[]
   *   An array of menu link IDs which have pending revisions, keyed by their
   *   revision IDs.
   *
   * @internal
   */
  public function getMenuLinkIdsWithPendingRevisions();

}
