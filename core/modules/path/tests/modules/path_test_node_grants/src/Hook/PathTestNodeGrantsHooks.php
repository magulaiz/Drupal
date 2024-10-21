<?php

namespace Drupal\path_test_node_grants\Hook;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Hook\Attribute\Hook;
class PathTestNodeGrantsHooks
{
    /**
     * Implements hook_node_grants().
     */
    #[Hook('node_grants')]
    public function nodeGrants(\Drupal\Core\Session\AccountInterface $account, $operation) : array
    {
        $grants = [];
        return $grants;
    }
}
