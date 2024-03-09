<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Test entity class.
 */
#[\Drupal\Core\Entity\Attribute\ContentEntityType(id: 'entity_test_external', label: new TranslatableMarkup('Entity test external'), base_table: 'entity_test_external', entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'bundle' => 'type'], links: ['canonical' => '/entity_test_external/{entity_test_external}'])]
class EntityTestExternal extends EntityTest {

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = NULL, array $options = []) {
    if ($rel === 'canonical') {
      return Url::fromUri('http://example.com', $options);
    }
    return parent::toUrl($rel, $options);
  }

}
