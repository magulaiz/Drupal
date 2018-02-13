<?php

namespace Drupal\Core\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\SubformStateInterface;

/**
 * Provides an interface for building, validating, and submitting plugin forms.
 */
interface PluginFormManagerInterface {

  /**
   * Builds the plugin subform.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\SubformStateInterface $form_state
   *   The current state of the subform.
   * @param \Drupal\Component\Plugin\PluginInspectionInterface $plugin
   *   The plugin the form is for.
   * @param string $operation
   *   The name of the operation to use, e.g., 'add' or 'edit'.
   * @param string $fallback_operation
   *   (optional) The name of the fallback operation to use.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL);

  /**
   * Validates the plugin subform.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\SubformStateInterface $form_state
   *   The current state of the subform.
   * @param \Drupal\Component\Plugin\PluginInspectionInterface $plugin
   *   The plugin the form is for.
   * @param string $operation
   *   The name of the operation to use, e.g., 'add' or 'edit'.
   * @param string $fallback_operation
   *   (optional) The name of the fallback operation to use.
   */
  public function validateForm(array &$form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL);

  /**
   * Submits the plugin subform.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\SubformStateInterface $form_state
   *   The current state of the subform.
   * @param \Drupal\Component\Plugin\PluginInspectionInterface $plugin
   *   The plugin the form is for.
   * @param string $operation
   *   The name of the operation to use, e.g., 'add' or 'edit'.
   * @param string $fallback_operation
   *   (optional) The name of the fallback operation to use.
   */
  public function submitForm(array &$form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL);

}
