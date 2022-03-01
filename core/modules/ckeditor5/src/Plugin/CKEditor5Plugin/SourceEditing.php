<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Source Editing plugin configuration.
 *
 * @internal
 *   Plugin classes are internal.
 */
class SourceEditing extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['allowed_tags'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Manually editable HTML tags'),
      '#default_value' => implode(' ', $this->configuration['allowed_tags']),
      '#description' => $this->t('A list of HTML tags that can be used while editing source. It is only necessary to add tags that are not already supported by other enabled plugins. For example, if "Bold" is enabled, it is not necessary to add the <code>&lt;strong&gt;</code> tag, but it may be necessary to add <code>&lt;dl&gt;&lt;dt&gt;&lt;dd&gt;</code> in a format that does not have a definition list plugin, but requires definition list markup.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Match the config schema structure at ckeditor5.plugin.ckeditor5_heading.
    $form_value = $form_state->getValue('allowed_tags');
    if (!is_array($form_value)) {
      $config_value = HTMLRestrictions::fromString($form_value)->toCKEditor5ElementsArray();
      $form_state->setValue('allowed_tags', $config_value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['allowed_tags'] = $form_state->getValue('allowed_tags');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'allowed_tags' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(HTMLRestrictions $other_plugins_elements = NULL): array {
    // @see \Drupal\ckeditor5\Plugin\CKEditor5PluginManager::getProvidedElements()
    if ($other_plugins_elements === NULL) {
      throw new \LogicException();
    }

    $restrictions = HTMLRestrictions::fromString(implode(' ', $this->configuration['allowed_tags']));
    if ($restrictions->getAllowedElements(FALSE) === $restrictions->getAllowedElements(TRUE)) {
      // If there are no wildcard tags, there is nothing to resolve: we can
      // return the configuration directly.
      return $this->configuration['allowed_tags'];
    }

    // Otherwise, compute the concrete elements that the wildcard tags resolve
    // into. For this, we need to know the concrete elements allowed by all
    // other enabled CKEditor 5 plugins, so that the wildcard tags in the
    // "allowed_tags" SourceEditing configuration can resolve into them.

    // Merge SourceEditing restrictions: these are the elements supported
    // by all enabled CKEditor 5 plugins: SourceEditing + all others.
    $all_plugins_elements = $other_plugins_elements->merge($restrictions);
    // Finally, determine which are the net new elements.
    // @see \Drupal\ckeditor5\SmartDefaultSettings::addToolbarItemsToMatchHtmlAttributesInFormat()
    $net_new_elements = $all_plugins_elements->diff($other_plugins_elements);

    // Note: this still contains wildcard tags. The wildcard tags' concrete
    // effects when combined with the other CKEditor 5 plugins are explicit now.
    return $net_new_elements->toCKEditor5ElementsArray();
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor, HTMLRestrictions $resolved_allowed_elements = NULL): array {
    // @see \Drupal\ckeditor5\Plugin\CKEditor5PluginManager::getCKEditor5PluginConfig()
    if ($resolved_allowed_elements === NULL) {
      throw new \LogicException();
    }

    // @see \Drupal\ckeditor5\Plugin\CKEditor5PluginManager::getCKEditor5PluginConfig()
    return [
      'htmlSupport' => [
        'allow' => $resolved_allowed_elements->toGeneralHtmlSupportConfig(),
      ],
    ];
  }

}
