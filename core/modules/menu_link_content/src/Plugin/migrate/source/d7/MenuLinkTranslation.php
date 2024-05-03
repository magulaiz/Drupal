<?php

namespace Drupal\menu_link_content\Plugin\migrate\source\d7;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\MenuLinkTranslation as MigrateDrupalD7MenuLinkTranslation;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal 7 i18n menu link translations source from database.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\source\d7\MenuLinkTranslation
 *   instead.
 *
 * @see https://www.drupal.org/node/3439256
 */
class MenuLinkTranslation extends MigrateDrupalD7MenuLinkTranslation {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    @trigger_error('\Drupal\menu_link_content\Plugin\migrate\source\d7\MenuLinkTranslation::create() is deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\source\d7\MenuLinkTranslation::create() instead. See https://www.drupal.org/node/3439256', E_USER_DEPRECATED);
    return parent::create($container, $configuration, $plugin_id, $plugin_definition, $migration);
  }

}
