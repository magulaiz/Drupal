<?php

declare(strict_types=1);

use Drupal\Core\StringTranslation\TranslatableMarkup;

namespace Drupal\entity_test\Entity;

/**
 * Defines the test entity class for testing definition addition.
 *
 * This entity type is initially not defined. It is enabled when needed to test
 * the related updates.
 */
#[\Drupal\Core\Entity\Attribute\ContentEntityType(id: 'entity_test_new', label: new TranslatableMarkup('New test entity'), base_table: 'entity_test_new', entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'bundle' => 'type', 'label' => 'name', 'langcode' => 'langcode'])]
class EntityTestNew extends EntityTest {
}
