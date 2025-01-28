<?php

namespace Drupal\mongodb\Hook;

use Drupal\Core\Database\Database;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for MongoDB.
 */
class MongodbHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      case 'help.page.mongodb':
        $output = '';
        $output .= '<h2>' . t('About') . '</h2>';
        $output .= '<p>' . t('The MongoDB module provides the connection between Drupal and a MongoDB database. For more information, see the <a href=":mongodb">online documentation for the MongoDB module</a>.', [
          ':mongodb' => 'https://git.drupalcode.org/project/mongodb/-/tree/3.x?ref_type=heads',
        ]) . '</p>';
        return $output;
    }
  }

  /**
   * Implements hook_entity_type_alter().
   */
  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array &$entity_types) : void {
    $connection = NULL;
    if (Database::getConnectionInfo()) {
      $connection = Database::getConnection();
    }

    if ($connection && $connection->driver() == 'mongodb') {
      if (!empty($entity_types['content_moderation_state'])) {
        $entity_types['content_moderation_state']->setHandlerClass('storage_schema', 'Drupal\mongodb\modules\content_moderation\ContentModerationStateStorageSchema');
      }

      if (!empty($entity_types['node'])) {
        $entity_types['node']->setStorageClass('Drupal\mongodb\modules\node\NodeStorage');
      }

      if (!empty($entity_types['user'])) {
        $entity_types['user']->setStorageClass('Drupal\mongodb\modules\user\UserStorage');
      }

      if (!empty($entity_types['view'])) {
        $entity_types['view']->setStorageClass('Drupal\mongodb\modules\views\ViewStorage');
        $entity_types['view']->setClass('Drupal\mongodb\modules\views\View');
      }
    }
  }

  /**
   * Implements hook_field_info_alter().
   */
  #[Hook('field_info_alter')]
  public function fieldInfoAlter(&$info): void {
    if (isset($info['boolean']['class'])) {
      $info['boolean']['class'] = 'Drupal\mongodb\Plugin\Field\FieldType\BooleanItem';
      $info['boolean']['provider'] = 'mongodb';
    }
    if (isset($info['changed']['class'])) {
      $info['changed']['class'] = 'Drupal\mongodb\Plugin\Field\FieldType\ChangedItem';
      $info['changed']['provider'] = 'mongodb';
    }
    if (isset($info['created']['class'])) {
      $info['created']['class'] = 'Drupal\mongodb\Plugin\Field\FieldType\CreatedItem';
      $info['created']['provider'] = 'mongodb';
    }
    if (isset($info['timestamp']['class'])) {
      $info['timestamp']['class'] = 'Drupal\mongodb\Plugin\Field\FieldType\TimestampItem';
      $info['timestamp']['provider'] = 'mongodb';
    }
  }

}
