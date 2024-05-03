<?php

namespace Drupal\taxonomy\Plugin\migrate\source\d7;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\TermLocalizedTranslation as MigrateDrupalD7TermLocalizedTranslation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal 7 i18n taxonomy terms source from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\source\d7\TermLocalizedTranslation
 *   instead.
 *
 * @see https://www.drupal.org/node/3439256
 *
 * @see \Drupal\taxonomy\Plugin\migrate\source\d7\Term
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 */
class TermLocalizedTranslation extends MigrateDrupalD7TermLocalizedTranslation {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    @trigger_error('\Drupal\taxonomy\Plugin\migrate\source\d7\TermLocalizedTranslation::create() is deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\source\d7\TermLocalizedTranslation::create() instead. See https://www.drupal.org/node/3439256', E_USER_DEPRECATED);
    return parent::create($container, $configuration, $plugin_id, $plugin_definition, $migration);
  }

}
