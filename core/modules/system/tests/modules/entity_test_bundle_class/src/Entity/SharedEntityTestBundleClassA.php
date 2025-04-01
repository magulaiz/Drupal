<?php

declare(strict_types=1);

namespace Drupal\entity_test_bundle_class\Entity;

use Drupal\Core\Entity\Attribute\Bundle;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_test\Entity\EntityTest;

/**
 * A bundle class that shares the same entity type as entity_test.
 */
#[Bundle(
  entityTypeId: 'shared_type',
  bundle: 'bundle_a',
  label: new TranslatableMarkup('Bundle A'),
)]
class SharedEntityTestBundleClassA extends EntityTest {
}
