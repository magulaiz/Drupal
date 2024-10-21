<?php

namespace Drupal\jsonapi_test_non_cacheable_methods\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Hook\Attribute\Hook;
class JsonapiTestNonCacheableMethodsHooks
{
    /**
     * Implements hook_entity_presave().
     */
    #[Hook('entity_presave')]
    public function entityPresave(\Drupal\Core\Entity\EntityInterface $entity)
    {
        \Drupal\Core\Url::fromRoute('<front>')->toString();
    }
    /**
     * Implements hook_entity_predelete().
     */
    #[Hook('entity_predelete')]
    public function entityPredelete(\Drupal\Core\Entity\EntityInterface $entity)
    {
        \Drupal\Core\Url::fromRoute('<front>')->toString();
    }
}
