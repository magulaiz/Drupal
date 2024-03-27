<?php

declare(strict_types=1);

namespace Drupal\views_config_entity_test\Entity;

use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines a configuration-based entity type used for testing Views data.
 */
#[ConfigEntityType(
  id: 'views_config_entity_test',
  label: new TranslatableMarkup('Test config entity type with Views data'),
  config_prefix: 'type',
  entity_keys: [
    'id' => 'id',
    'label' => 'name',
  ],
  handlers: [
    'list_builder' => 'Drupal\Core\Entity\EntityListBuilder',
    'views_data' => 'Drupal\views_config_entity_test\ViewsConfigEntityTestViewsData',
  ],
  admin_permission: 'administer modules',
  config_export: [
    'id',
    'label',
  ],
)]
class ViewsConfigEntityTest extends ConfigEntityBase {
}
