<?php

namespace Drupal\search_query_alter\Hook;

use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Hook\Attribute\Hook;
class SearchQueryAlterHooks
{
    /**
     * Implements hook_query_TAG_alter().
     *
     * Tags search_$type with $type node_search.
     */
    #[Hook('query_search_node_search_alter')]
    public function querySearchNodeSearchAlter(\Drupal\Core\Database\Query\AlterableInterface $query)
    {
        // For testing purposes, restrict the query to node type 'article' only.
        $query->condition('n.type', 'article');
    }
}
