<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Action\Plugin\ConfigAction\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * @internal
 *   This API is experimental.
 */
final class SimpleConfigUpdateDeriver extends DeriverBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // Create BC and deprecation notice for the old ID.
    $this->derivatives['simple_config_update'] = $base_plugin_definition;
    $this->derivatives['simple_config_update']['deprecation_message'] = $this->t('The "simple_config_update" plugin ID is deprecated. Use "simpleConfigUpdate" instead.');

    return $this->derivatives;
  }

}
