<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Test entity class.
 */
#[ContentEntityType(
  id: 'entity_test_label',
  label: new TranslatableMarkup('Entity Test label'),
  render_cache: FALSE,
  entity_keys: [
    'uuid' => 'uuid',
    'id' => 'id',
    'label' => 'name',
    'bundle' => 'type',
    'langcode' => 'langcode',
  ],
  handlers: [
    'access' => 'Drupal\entity_test\EntityTestAccessControlHandler',
    'view_builder' => 'Drupal\entity_test\EntityTestViewBuilder',
  ],
  base_table: 'entity_test_label',
)]
class EntityTestLabel extends EntityTest {

}
