<?php

namespace Drupal\Core\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\SubformStateInterface;

/**
 * @todo.
 */
class PluginFormManager implements PluginFormManagerInterface {

  /**
   * The plugin form factory.
   *
   * @var \Drupal\Core\Plugin\PluginFormFactoryInterface
   */
  protected $pluginFormFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new PluginFormManager.
   *
   * @param \Drupal\Core\Plugin\PluginFormFactoryInterface $plugin_form_factory
   *   The plugin form factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(PluginFormFactoryInterface $plugin_form_factory, ModuleHandlerInterface $module_handler) {
    $this->pluginFormFactory = $plugin_form_factory;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Returns a plugin form instance.
   *
   * @param \Drupal\Component\Plugin\PluginInspectionInterface $plugin
   *   The plugin the form is for.
   * @param string $operation
   *   The name of the operation to use, e.g., 'add' or 'edit'.
   * @param string $fallback_operation
   *   (optional) The name of the fallback operation to use.
   *
   * @return \Drupal\Core\Plugin\PluginFormInterface
   *   A plugin form instance.
   */
  protected function getFormObject(PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL) {
    // Consult the plugin first if it provides multiple forms.
    if ($plugin instanceof PluginWithFormsInterface) {
      return $this->pluginFormFactory->createInstance($plugin, $operation, $fallback_operation);
    }

    // Use the plugin itself if it is also a form.
    if ($plugin instanceof PluginFormInterface) {
      return $plugin;
    }

    throw new \InvalidArgumentException(sprintf('The "%s" plugin does not provide a "%s" form', $plugin->getPluginId(), $operation));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL) {
    $form = $this->getFormObject($plugin, $operation, $fallback_operation)->buildConfigurationForm($form, $form_state);
    $this->moduleHandler->alter('plugin_subform', $form, $form_state, $plugin);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL) {
    $this->getFormObject($plugin, $operation, $fallback_operation)->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, SubformStateInterface $form_state, PluginInspectionInterface $plugin, $operation, $fallback_operation = NULL) {
    $this->getFormObject($plugin, $operation, $fallback_operation)->submitConfigurationForm($form, $form_state);
  }

}
