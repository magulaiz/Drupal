<?php

namespace Drupal\menu_link_content\Plugin\migrate\source\d6;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d6\MenuLinkTranslation as MigrateDrupalD6MenuLinkTranslation;

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

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    @trigger_error('\Drupal\menu_link_content\Plugin\migrate\source\d6\MenuLinkTranslation is deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\source\d6\MenuLinkTranslation instead. See https://www.drupal.org/node/3439256', E_USER_DEPRECATED);
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
  }

}
