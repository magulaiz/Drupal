<?php

namespace Drupal\field\Plugin\migrate\field;

use Drupal\migrate_drupal\Plugin\migrate\field\Email as MdEmail;

/**
 * MigrateField Plugin for Drupal 6 and 7 email fields.
 *
 *  @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\field\DateField instead.
 *
 *  @see https://www.drupal.org/node/1234567
 */
class Email extends MdEmail {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    @trigger_error(__CLASS__ . ' is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\DateField instead. See https://www.drupal.org/node/1234567', E_USER_DEPRECATED);
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
