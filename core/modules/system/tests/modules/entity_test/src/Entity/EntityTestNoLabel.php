<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Test entity class.
 */
#[\Drupal\Core\Entity\Attribute\ContentEntityType(id: 'entity_test_no_label', label: new TranslatableMarkup('Entity Test without label'), internal: TRUE, persistent_cache: FALSE, base_table: 'entity_test_no_label', handlers: ['access' => 'Drupal\entity_test\EntityTestAccessControlHandler'], entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'bundle' => 'type'])]
class EntityTestNoLabel extends EntityTest {

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getName();
  }

}
