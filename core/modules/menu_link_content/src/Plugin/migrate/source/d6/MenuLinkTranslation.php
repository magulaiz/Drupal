<?php

namespace Drupal\menu_link_content\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\MenuLinkTranslation as MigrateDrupalD6MenuLinkTranslation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal 6 i18n menu link translations source from database.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\source\d6\MenuLinkTranslation
 *   instead.
 *
 * @see https://www.drupal.org/node/3439256
 */
class MenuLinkTranslation extends MigrateDrupalD6MenuLinkTranslation {

  /**
   * Drupal 6 table names.
   *
   * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
   *  \Drupal\migrate_drupal\Plugin\migrate\source\d6\MenuLinkTranslation::I18N_STRING_TABLE
   *  instead.
   *
   * @see https://www.drupal.org/node/3439256
   */
  const I18N_STRING_TABLE = 'i18n_strings';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    @trigger_error('\Drupal\menu_link_content\Plugin\migrate\source\d6\MenuLinkTranslation::create() is deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\source\d6\MenuLinkTranslation::create() instead. See https://www.drupal.org/node/3439256', E_USER_DEPRECATED);
    return parent::create($container, $configuration, $plugin_id, $plugin_definition, $migration);
  }

}
