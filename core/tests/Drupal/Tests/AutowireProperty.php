<?php

declare(strict_types=1);

namespace Drupal\Tests;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Autowire properties.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class AutowireProperty extends Autowire {

}
