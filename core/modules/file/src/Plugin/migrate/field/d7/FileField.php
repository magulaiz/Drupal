<?php

namespace Drupal\file\Plugin\migrate\field\d7;

use Drupal\migrate_drupal\Plugin\migrate\field\d7\FileField as MdFileField;

/**
 *
 *  @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
 *   Drupal\migrate_drupal\Plugin\migrate\field\d7\FileField instead.
 *
 *  @see https://www.drupal.org/node/1234567
 */
class FileField extends MdFileField {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    @trigger_error(__CLASS__ . ' is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\d7\FileField instead. See https://www.drupal.org/node/1234567', E_USER_DEPRECATED);
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
