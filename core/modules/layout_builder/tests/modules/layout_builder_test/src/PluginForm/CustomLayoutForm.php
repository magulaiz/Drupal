<?php

namespace Drupal\layout_builder_test\PluginForm;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a custom form for a layout plugin.
 */
class CustomLayoutForm extends PluginFormBase {

  use StringTranslationTrait;

  /**
   * The plugin this form is for.
   *
   * @var \Drupal\Core\Layout\LayoutDefault
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->plugin->buildConfigurationForm($form, $form_state);
    $form['custom_element'] = [
      '#title' => $this->t('Custom element'),
      '#type' => 'select',
      '#options' => [
        'foo' => $this->t('Foo'),
        'bar' => $this->t('Bar'),
      ],
      '#default_value' => $this->plugin->getConfiguration()['custom_element'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->plugin->submitConfigurationForm($form, $form_state);
    $configuration = $this->plugin->getConfiguration();
    $configuration['custom_element'] = $form_state->getValue('custom_element');
    $this->plugin->setConfiguration($configuration);
  }

}
