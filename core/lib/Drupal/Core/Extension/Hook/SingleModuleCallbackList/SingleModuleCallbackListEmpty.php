<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\SingleModuleCallbackList;

/**
 * Shortcut object for the case when the list is empty.
 */
class SingleModuleCallbackListEmpty implements SingleModuleCallbackListInterface {

  /**
   * {@inheritdoc}
   */
  public function containsMainFunction(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(array $args = []): mixed {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCallbacks(): array {
    return [];
  }

}
