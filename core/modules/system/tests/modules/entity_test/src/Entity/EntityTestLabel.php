<?php

declare(strict_types=1);

use Drupal\Core\StringTranslation\TranslatableMarkup;

namespace Drupal\entity_test\Entity;

/**
 * Test entity class.
 */
#[\Drupal\Core\Entity\Attribute\ContentEntityType(id: 'entity_test_label', label: new TranslatableMarkup('Entity Test label'), handlers: ['access' => 'Drupal\entity_test\EntityTestAccessControlHandler', 'view_builder' => 'Drupal\entity_test\EntityTestViewBuilder'], base_table: 'entity_test_label', render_cache: FALSE, entity_keys: ['uuid' => 'uuid', 'id' => 'id', 'label' => 'name', 'bundle' => 'type', 'langcode' => 'langcode'])]
class EntityTestLabel extends EntityTest {

}
