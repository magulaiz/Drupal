<?php

namespace Drupal\block_content\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\BoxTranslation as MigrateDrupal6BoxTranslation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal 6 i18n content block translations source from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\source\d6\BoxTranslation instead.
 * @see https://www.drupal.org/node/3439256
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 */
class BoxTranslation extends MigrateDrupal6BoxTranslation {

  /**
   * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
   * \Drupal\migrate_drupal\Plugin\migrate\source\d6\BoxTranslation::CUSTOM_BLOCK_TABLE
   * instead.
   *
   * @see https://www.drupal.org/node/3439256
   */
  const CUSTOM_BLOCK_TABLE = 'boxes';

  /**
   * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
   * \Drupal\migrate_drupal\Plugin\migrate\source\d6\BoxTranslation::I18N_STRING_TABLE
   * instead.
   *
   * @see https://www.drupal.org/node/3439256
   */
  const I18N_STRING_TABLE = 'i18n_strings';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    @trigger_error('\Drupal\block_content\Plugin\migrate\source\d6\BoxTranslation::create() is deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\source\d6\BoxTranslation::create() instead. See https://www.drupal.org/node/3439256', E_USER_DEPRECATED);
    return parent::create($container, $configuration, $plugin_id, $plugin_definition, $migration);
  }

}
