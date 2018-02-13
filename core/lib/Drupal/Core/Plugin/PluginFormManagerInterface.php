<?php

namespace Drupal\Core\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\SubformStateInterface;

/**
 * @todo.
 */
interface PluginFormManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL);

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL);

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL);

}
