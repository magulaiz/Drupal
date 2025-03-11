<?php

declare(strict_types = 1);

namespace Drupal\hk_extra_test\Hook;

use Drupal\Core\Extension\ProceduralCall;
use Drupal\Core\Hook\Attribute\ReOrderHook;
use Drupal\Core\Hook\OrderBefore;

#[ReOrderHook('procedural_alter', ProceduralCall::class, 'hk_a_test_procedural_alter', new OrderBefore(['hk_b_test'], [], ['procedural_subtype_alter']))]
class Ordering {

}
