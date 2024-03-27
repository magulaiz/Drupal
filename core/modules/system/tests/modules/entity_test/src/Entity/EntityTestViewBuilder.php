<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Test entity class for testing a view builder.
 */
#[ContentEntityType(
  id: 'entity_test_view_builder',
  label: new TranslatableMarkup('Entity Test view builder'),
  render_cache: FALSE,
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'label' => 'name',
    'bundle' => 'type',
    'langcode' => 'langcode',
  ],
  handlers: [
    'access' => 'Drupal\entity_test\EntityTestAccessControlHandler',
    'view_builder' => 'Drupal\entity_test\EntityTestViewBuilderOverriddenView',
  ],
  base_table: 'entity_test_view_builder',
)]
class EntityTestViewBuilder extends EntityTest {

}
