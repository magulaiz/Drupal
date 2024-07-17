<?php

declare(strict_types=1);

namespace Drupal\block\Plugin\ConfigAction;

use Drupal\Component\Plugin\Derivative\DeriverBase;

final class PlaceBlockDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives['placeBlockInAdminTheme'] = [
      'which_theme' => 'admin',
    ] + $base_plugin_definition;
    $this->derivatives['placeBlockInDefaultTheme'] = [
      'which_theme' => 'default',
    ] + $base_plugin_definition;

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
