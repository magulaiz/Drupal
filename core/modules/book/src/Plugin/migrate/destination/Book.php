<?php

namespace Drupal\book\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Row;

/**
 * @MigrateDestination(
 *   id = "book",
 *   provider = "book"
 * )
 */
class Book extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function updateEntity(EntityInterface $entity, Row $row) {
    if ($entity->book) {
      $book = $row->getDestinationProperty('book');
      foreach ($book as $key => $value) {
        $entity->book[$key] = $value;
      }
    }
    else {
      $entity->book = $row->getDestinationProperty('book');
    }
    if (($mid = $entity->book['pid']) && ($menuLink = MenuLinkContent::load($mid)) && $url = $menuLink->getUrlObject()) {
      $parameters = $url->getRouteParameters();
      if (isset($parameters['node'])) {
        $entity->book['pid'] = $parameters['node'];
      }
    }
    return parent::updateEntity($entity, $row);
  }

}
