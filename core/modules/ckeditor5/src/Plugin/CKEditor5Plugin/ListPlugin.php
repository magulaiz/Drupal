<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 List plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ListPlugin extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['reversed' => TRUE, 'startIndex' => TRUE, 'styles' => TRUE];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['reversed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to reverse an ordered list'),
      '#default_value' => $this->configuration['reversed'],
    ];
    $form['startIndex'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to specify the start index of an ordered list'),
      '#default_value' => $this->configuration['startIndex'],
    ];
    $form['styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to choose a list style type'),
      '#description' => $this->t('Available list style types for ordered lists: letters and Roman numerals instead of only numbers. Available list style types for unordered lists: circles and squares instead of only discs.'),
      '#default_value' => $this->configuration['styles'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_value = $form_state->getValue('reversed');
    $form_state->setValue('reversed', (bool) $form_value);
    $form_value = $form_state->getValue('startIndex');
    $form_state->setValue('startIndex', (bool) $form_value);
    $form_value = $form_state->getValue('styles');
    $form_state->setValue('styles', (bool) $form_value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['reversed'] = $form_state->getValue('reversed');
    $this->configuration['startIndex'] = $form_state->getValue('startIndex');
    $this->configuration['styles'] = $form_state->getValue('styles');
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $static_plugin_config['list']['properties'] = array_merge(
      $static_plugin_config['list']['properties'],
      $this->getConfiguration()
    );

    // Generate configuration to use `type` attribute-based list styles on <ul>
    // and <ol> elements.
    if ($this->configuration["styles"]) {
      $static_plugin_config["list"]["properties"]["styles"] = [];
      $static_plugin_config["list"]["properties"]["styles"]['useAttribute'] = TRUE;
      $static_plugin_config['list']['allow'] = [
        [
          'name' => 'ul',
          'attributes' => ['type' => TRUE],
          'classes' => TRUE,
          'styles' => TRUE,
        ],
        [
          'name' => 'ol',
          'attributes' => ['type' => TRUE],
          'classes' => TRUE,
          'styles' => TRUE,
        ],
      ];
    }

    return $static_plugin_config;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    $subset = $this->getPluginDefinition()->getElements();
    if (!$this->getConfiguration()['styles']) {
      $subset = array_diff($subset, [
        '<ul type>',
        '<ol type>',
      ]);
    }
    $subset = array_diff($subset, ['<ol reversed start>']);
    $reversed_enabled = $this->getConfiguration()['reversed'];
    $start_index_enabled = $this->getConfiguration()['startIndex'];
    $subset[] = "<ol" . ($reversed_enabled ? ' reversed' : '') . ($start_index_enabled ? ' start' : '') . '>';
    return $subset;
  }

}
