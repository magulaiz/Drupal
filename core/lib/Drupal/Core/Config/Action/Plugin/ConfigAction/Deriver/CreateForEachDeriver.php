<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Action\Plugin\ConfigAction\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * @internal
 *   This API is experimental.
 */
final class CreateForEachDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    $this->derivatives['createIfNotExists'] = $base_plugin_definition + [
      'create_action' => 'entity_create:createIfNotExists',
    ];
    $this->derivatives['create'] = $base_plugin_definition + [
      'create_action' => 'entity_create:create',
    ];
    return $this->derivatives;
  }

}
