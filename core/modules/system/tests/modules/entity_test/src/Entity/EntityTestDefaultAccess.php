<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a test entity class with no access control handler.
 */
#[\Drupal\Core\Entity\Attribute\ContentEntityType(id: 'entity_test_default_access', label: new TranslatableMarkup('Test entity with default access'), base_table: 'entity_test_default_access', entity_keys: ['id' => 'id', 'bundle' => 'type'])]
class EntityTestDefaultAccess extends EntityTest {

}
