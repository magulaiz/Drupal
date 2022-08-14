<?php

namespace Drupal\user\Plugin\migrate;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Derives the 'user_timestamp' migration plugin.
 */
class UserTimestampDeriver extends DeriverBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    foreach ($this->getTimestampTypes() as $field => $title) {
      foreach ([6, 7] as $drupal_version) {
        $this->derivatives["$field:d$drupal_version"] = [
          'label' => $title,
          'migration_tags' => [
            "Drupal $drupal_version",
            'Content',
          ],
          'source' => [
            'plugin' => "d{$drupal_version}_user",
            'constants' => [
              'collection' => "user.timestamp.$field",
            ],
          ],
          'process' => [
            'collection' => 'constants/collection',
            'name' => 'uid',
            'value' => $field,
          ],
          'destination' => [
            'plugin' => 'key_value'
          ],
        ] + $base_plugin_definition;
      }
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

  /**
   * The user timestamp fields to be migrated.
   *
   * @return array
   *   Associative array with fields as keys and migration label as values.
   */
  protected function getTimestampTypes(): array {
    return [
      'access' => $this->t('User access timestamp'),
      'login' => $this->t('User login timestamp'),
    ];
  }

}
