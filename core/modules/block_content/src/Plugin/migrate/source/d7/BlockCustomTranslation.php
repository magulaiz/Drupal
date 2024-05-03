<?php

namespace Drupal\block_content\Plugin\migrate\source\d7;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\BlockCustomTranslation as MigrateDrupalD7BlockCustomTranslation;

/**
 * Drupal 7 i18n content block translations source from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\source\d7\BlockCustomTranslation
 *   instead.
 * @see https://www.drupal.org/node/3439256
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 */
class BlockCustomTranslation extends MigrateDrupalD7BlockCustomTranslation {

  /**
   * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
   * \Drupal\migrate_drupal\Plugin\migrate\source\d7\BlockCustomTranslation::CUSTOM_BLOCK_TABLE
   * instead.
   *
   * @see https://www.drupal.org/node/3439256
   */
  const CUSTOM_BLOCK_TABLE = 'block_custom';

  /**
   * @deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use
   * \Drupal\migrate_drupal\Plugin\migrate\source\d7\BlockCustomTranslation::I18N_STRING_TABLE
   * instead.
   *
   * @see https://www.drupal.org/node/3439256
   */
  const I18N_STRING_TABLE = 'i18n_string';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    @trigger_error('\Drupal\block_content\Plugin\migrate\source\d7\BlockCustomTranslation is deprecated in drupal:10.3.0 and is removed from drupal:12.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\source\d7\BlockCustomTranslation instead. See https://www.drupal.org/node/3439256', E_USER_DEPRECATED);
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
  }

}
