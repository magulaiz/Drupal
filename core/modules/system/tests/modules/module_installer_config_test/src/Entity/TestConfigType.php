<?php

declare(strict_types=1);

namespace Drupal\module_installer_config_test\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines a configuration-based entity type used for testing.
 */
#[\Drupal\Core\Entity\Attribute\ConfigEntityType(id: 'test_config_type', label: new TranslatableMarkup('Test entity type'), handlers: ['list_builder' => 'Drupal\Core\Entity\EntityListBuilder'], admin_permission: 'administer modules', config_prefix: 'type', entity_keys: ['id' => 'id', 'label' => 'name'], config_export: ['id', 'label'])]
class TestConfigType extends ConfigEntityBase {
}
