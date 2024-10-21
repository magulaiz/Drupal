<?php

namespace Drupal\views_test_query_access\Hook;

use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Entity\Sql\DefaultTableMapping;
use Drupal\Core\Hook\Attribute\Hook;
class ViewsTestQueryAccessHooks
{
    /**
     * Implements hook_query_TAG_alter() for the 'media_access' query tag.
     */
    #[Hook('query_media_access_alter')]
    public function queryMediaAccessAlter(\Drupal\Core\Database\Query\AlterableInterface $query)
    {
        \_views_test_query_access_restrict_by_uuid($query);
    }
    /**
     * Implements hook_query_TAG_alter() for the 'block_content_access' query tag.
     */
    #[Hook('query_block_content_access_alter')]
    public function queryBlockContentAccessAlter(\Drupal\Core\Database\Query\AlterableInterface $query)
    {
        \_views_test_query_access_restrict_by_uuid($query);
    }
}
