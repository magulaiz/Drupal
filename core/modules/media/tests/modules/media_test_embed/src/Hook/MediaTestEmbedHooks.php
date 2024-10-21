<?php

namespace Drupal\media_test_embed\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Hook\Attribute\Hook;
class MediaTestEmbedHooks
{
    /**
     * Implements hook_entity_view_alter().
     */
    #[Hook('entity_view_alter')]
    public function entityViewAlter(&$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display)
    {
        $build['#attributes']['data-media-embed-test-active-theme'] = \Drupal::theme()->getActiveTheme()->getName();
        $build['#attributes']['data-media-embed-test-view-mode'] = $display->getMode();
    }
    /**
     * Implements hook_entity_access().
     */
    #[Hook('entity_access')]
    public function entityAccess(\Drupal\Core\Entity\EntityInterface $entity, $operation, \Drupal\Core\Session\AccountInterface $account)
    {
        return \Drupal\Core\Access\AccessResult::neutral()->addCacheTags(['_media_test_embed_filter_access:' . $entity->getEntityTypeId() . ':' . $entity->id()]);
    }
}
