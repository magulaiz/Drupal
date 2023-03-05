<?php

namespace Drupal\telephone\Plugin\migrate\field\d7;

use Drupal\migrate_drupal\Plugin\migrate\field\d7\PhoneField as MdPhoneField;

/**
 *
 *  @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\field\d7\PhoneField instead.
 *
 *  @see https://www.drupal.org/node/1234567
 */
class PhoneField extends MdPhoneField {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    @trigger_error(__CLASS__ . ' is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\d7\PhoneField instead. See https://www.drupal.org/node/1234567', E_USER_DEPRECATED);
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
