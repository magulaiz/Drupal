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
<<<<<<< HEAD
    return ['reversed' => TRUE, 'startIndex' => TRUE];
=======
    return [
      'properties' => ['reversed' => TRUE, 'startIndex' => TRUE],
      'multiBlock' => TRUE,
    ];
>>>>>>> upstream/11.x
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['reversed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to reverse an ordered list'),
<<<<<<< HEAD
      '#default_value' => $this->configuration['reversed'],
=======
      '#default_value' => $this->configuration['properties']['reversed'],
>>>>>>> upstream/11.x
    ];
    $form['startIndex'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to specify the start index of an ordered list'),
<<<<<<< HEAD
      '#default_value' => $this->configuration['startIndex'],
=======
      '#default_value' => $this->configuration['properties']['startIndex'],
    ];
    $form['multiBlock'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow the user to create paragraphs in list items (or other block elements)'),
      '#default_value' => $this->configuration['multiBlock'],
>>>>>>> upstream/11.x
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
<<<<<<< HEAD
=======
    $form_value = $form_state->getValue('multiBlock');
    $form_state->setValue('multiBlock', (bool) $form_value);
>>>>>>> upstream/11.x
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
<<<<<<< HEAD
    $this->configuration['reversed'] = $form_state->getValue('reversed');
    $this->configuration['startIndex'] = $form_state->getValue('startIndex');
=======
    $this->configuration['properties']['reversed'] = $form_state->getValue('reversed');
    $this->configuration['properties']['startIndex'] = $form_state->getValue('startIndex');
    $this->configuration['multiBlock'] = $form_state->getValue('multiBlock');
>>>>>>> upstream/11.x
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
<<<<<<< HEAD
    $static_plugin_config['list']['properties'] = array_merge(
      $static_plugin_config['list']['properties'],
      $this->getConfiguration()
    );
=======
    $static_plugin_config['list']['properties'] = $this->getConfiguration()['properties'] + $static_plugin_config['list']['properties'];
    $static_plugin_config['list']['multiBlock'] = $this->getConfiguration()['multiBlock'];
>>>>>>> upstream/11.x
    return $static_plugin_config;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    $subset = $this->getPluginDefinition()->getElements();
    $subset = array_diff($subset, ['<ol reversed start>']);
<<<<<<< HEAD
    $reversed_enabled = $this->getConfiguration()['reversed'];
    $start_index_enabled = $this->getConfiguration()['startIndex'];
=======
    $reversed_enabled = $this->getConfiguration()['properties']['reversed'];
    $start_index_enabled = $this->getConfiguration()['properties']['startIndex'];
>>>>>>> upstream/11.x
    $subset[] = "<ol" . ($reversed_enabled ? ' reversed' : '') . ($start_index_enabled ? ' start' : '') . '>';
    return $subset;
  }

}
