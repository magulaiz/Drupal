<?php

declare(strict_types=1);

namespace Drupal\config_install_test_optional\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Test configuration entity.
 *
 * @ConfigEntityType(
 *   id = "config_install_test_optional",
 *   label = @Translation("Config Install Test Optional Entity"),
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   config_export = {
 *     "id",
 *   }
 * )
 */
class ConfigInstallTestOptional extends ConfigEntityBase {}
