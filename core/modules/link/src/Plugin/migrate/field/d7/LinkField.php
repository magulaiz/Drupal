<?php

namespace Drupal\link\Plugin\migrate\field\d7;

use Drupal\migrate_drupal\Plugin\migrate\field\d6\LinkField as MdLinkField;

/**
 *
 *  @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
 *   \Drupal\migrate_drupal\Plugin\migrate\field\d7\LinkField instead.
 *
 *  @see https://www.drupal.org/node/1234567
 */
class LinkField extends MdLinkField {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    @trigger_error(__CLASS__ . ' is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\migrate_drupal\Plugin\migrate\field\d7\LinkField instead. See https://www.drupal.org/node/1234567', E_USER_DEPRECATED);
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

}
