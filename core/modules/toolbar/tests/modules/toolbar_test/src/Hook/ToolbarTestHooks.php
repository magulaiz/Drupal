<?php

namespace Drupal\toolbar_test\Hook;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Hook\Attribute\Hook;
class ToolbarTestHooks
{
    /**
     * Implements hook_toolbar().
     */
    #[Hook('toolbar')]
    public function toolbar()
    {
        $items['testing'] = ['#type' => 'toolbar_item', 'tab' => ['#type' => 'link', '#title' => \t('Test tab'), '#url' => \Drupal\Core\Url::fromRoute('<front>'), '#options' => ['attributes' => ['id' => 'toolbar-tab-testing', 'title' => \t('Test tab')]]], 'tray' => ['#heading' => \t('Test tray'), '#wrapper_attributes' => ['id' => 'toolbar-tray-testing'], 'content' => ['#theme' => 'item_list', '#items' => [\Drupal\Core\Link::fromTextAndUrl(\t('link 1'), \Drupal\Core\Url::fromRoute('<front>', [], ['attributes' => ['title' => 'Test link 1 title']]))->toRenderable(), \Drupal\Core\Link::fromTextAndUrl(\t('link 2'), \Drupal\Core\Url::fromRoute('<front>', [], ['attributes' => ['title' => 'Test link 2 title']]))->toRenderable(), \Drupal\Core\Link::fromTextAndUrl(\t('link 3'), \Drupal\Core\Url::fromRoute('<front>', [], ['attributes' => ['title' => 'Test link 3 title']]))->toRenderable()], '#attributes' => ['class' => ['toolbar-menu']]]], '#weight' => 50];
        $items['empty'] = ['#type' => 'toolbar_item'];
        return $items;
    }
}
