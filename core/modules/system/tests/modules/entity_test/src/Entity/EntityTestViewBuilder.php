<?php

declare(strict_types=1);

use Drupal\Core\StringTranslation\TranslatableMarkup;

namespace Drupal\entity_test\Entity;

/**
 * Test entity class for testing a view builder.
 */
#[\Drupal\Core\Entity\Attribute\ContentEntityType(id: 'entity_test_view_builder', label: new TranslatableMarkup('Entity Test view builder'), handlers: ['access' => 'Drupal\entity_test\EntityTestAccessControlHandler', 'view_builder' => 'Drupal\entity_test\EntityTestViewBuilderOverriddenView'], base_table: 'entity_test_view_builder', render_cache: FALSE, entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'label' => 'name', 'bundle' => 'type', 'langcode' => 'langcode'])]
class EntityTestViewBuilder extends EntityTest {

}
