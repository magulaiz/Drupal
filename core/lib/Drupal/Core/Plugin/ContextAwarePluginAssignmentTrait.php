<?php

namespace Drupal\Core\Plugin;

/**
 * Handles context assignments for context-aware plugins.
 */
trait ContextAwarePluginAssignmentTrait {

  /**
   * Ensures the t() method is available.
   *
   * @see \Drupal\Core\StringTranslation\StringTranslationTrait
   */
  abstract protected function t($string, array $args = [], array $options = []);

  /**
   * Wraps the context handler.
   *
   * @return \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected function contextHandler() {
    return \Drupal::service('context.handler');
  }

  /**
   * Builds a form element for assigning a context to a given slot.
   *
   * @param \Drupal\Core\Plugin\ContextAwarePluginInterface $plugin
   *   The context-aware plugin.
   * @param \Drupal\Component\Plugin\Context\ContextInterface[] $contexts
   *   An array of contexts.
   *
   * @return array
   *   A form element for assigning context.
   */
  protected function addContextAssignmentElement(ContextAwarePluginInterface $plugin, array $contexts) {
    return $this->contextHandler()->getContextAssignmentElement($plugin, $contexts);
  }

}
