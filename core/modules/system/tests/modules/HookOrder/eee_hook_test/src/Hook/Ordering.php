<?php

declare(strict_types=1);

namespace Drupal\eee_hook_test\Hook;

use Drupal\Core\Extension\ProceduralCall;
use Drupal\Core\Hook\Attribute\ReOrderHook;
use Drupal\Core\Hook\Order\OrderBefore;

/**
 * Hooks for testing ordering.
 */
#[ReOrderHook('procedural_alter', ProceduralCall::class, 'aaa_hook_test_procedural_alter', new OrderBefore(['bbb_hook_test'], []))]
class Ordering {

}
