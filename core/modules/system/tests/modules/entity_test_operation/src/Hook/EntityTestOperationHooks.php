<?php

namespace Drupal\entity_test_operation\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Hook\Attribute\Hook;
class EntityTestOperationHooks
{
    /**
     * Implements hook_entity_operation().
     */
    #[Hook('entity_operation')]
    public function entityOperation(\Drupal\Core\Entity\EntityInterface $entity)
    {
        return ['test' => ['title' => \t('Front page'), 'url' => \Drupal\Core\Url::fromRoute('<front>'), 'weight' => 0]];
    }
}
