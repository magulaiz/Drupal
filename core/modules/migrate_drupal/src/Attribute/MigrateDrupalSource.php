<?php

declare(strict_types=1);

namespace Drupal\migrate_drupal\Attribute;

use Drupal\migrate\Attribute\MigrateSource;

/**
 * Defines a MigrateDrupalSource attribute.
 *
 * Plugin Namespace: Plugin\migrate\source
 *
 * For a working example, see
 * \Drupal\migrate_drupal\Plugin\migrate\source\UrlAlias
 *
 * @ingroup migration
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class MigrateDrupalSource extends MigrateSource {

  /**
   * Constructs a migrate_drupal source plugin attribute object.
   *
   * @param string $id
   *   A unique identifier for the source plugin.
   * @param string|null $source_module
   *   (optional) Identifies the system providing the data the source plugin
   *   will read. The source plugin itself determines how the value is used. For
   *   example, Migrate Drupal's source plugins expect source_module to be the
   *   name of a module that must be installed and enabled in the source
   *   database.
   * @param bool $requirements_met
   *   (optional) Whether requirements are met. Defaults to true. The source
   *   plugin itself determines how the value is used. For example, Migrate
   *   Drupal's source plugins expect source_module to be the name of a module
   *   that must be installed and enabled in the source database.
   * @param mixed $minimum_version
   *   (optional) Specifies the minimum version of the source provider. This can
   *   be any type, and the source plugin itself determines how it is used. For
   *   example, Migrate Drupal's source plugins expect this to be an integer
   *   representing the minimum installed database schema version of the module
   *   specified by source_module.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   *
   * @see \Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase::checkRequirements
   */
  public function __construct(
    string $id,
    public readonly ?string $source_module = NULL,
    bool $requirements_met = TRUE,
    mixed $minimum_version = NULL,
    ?string $deriver = NULL,
  ) {
    parent::__construct($id, $requirements_met, $minimum_version, $deriver);
  }

}
