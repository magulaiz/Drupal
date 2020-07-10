<?php

namespace Drupal\layout_builder_test\PluginForm;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormBase;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a custom form for a layout plugin.
 */
class CustomLayoutForm extends PluginFormBase {

  use StringTranslationTrait;

  /**
   * The plugin.
   *
   * @var \Drupal\Core\Layout\LayoutInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->plugin instanceof PluginFormInterface ? $this->plugin->buildConfigurationForm($form, $form_state) : [];
    $form['custom_element'] = [
      '#title' => $this->t('Custom element'),
      '#type' => 'textfield',
      '#default_value' => $this->plugin->getConfiguration()['custom_element'] ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($this->plugin instanceof PluginFormInterface) {
      $this->plugin->submitConfigurationForm($form, $form_state);
    }
    $configuration = $this->plugin->getConfiguration();
    $configuration['custom_element'] = $form_state->getValue('custom_element');
    $this->plugin->setConfiguration($configuration);
  }

}
