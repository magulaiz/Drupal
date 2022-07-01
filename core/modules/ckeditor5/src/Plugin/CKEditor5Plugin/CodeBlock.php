<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 CodeBlock plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class CodeBlock extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * The languages that cannot be disabled.
   *
   * @var string[]
   */
  const ALWAYS_ENABLED_LANGUAGES = [
    'plaintext',
  ];

  /**
   * The default configuration for this plugin.
   *
   * @var string[][]
   */
  const DEFAULT_CONFIGURATION = [
    'enabled_languages' => [
      'c',
      'cpp',
      'cs',
      'css',
      'diff',
      'html',
      'java',
      'javascript',
      'php',
      'python',
      'ruby',
      'typescript',
      'xml',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return static::DEFAULT_CONFIGURATION;
  }

  /**
   * Computes all valid choices for the "enabled_languages" setting.
   *
   * @see ckeditor5.schema.yml
   *
   * @return string[]
   *   All valid choices.
   */
  public static function validChoices(): array {
    $cke5_plugin_manager = \Drupal::service('plugin.manager.ckeditor5.plugin');
    assert($cke5_plugin_manager instanceof CKEditor5PluginManagerInterface);
    $plugin_definition = $cke5_plugin_manager->getDefinition('ckeditor5_codeBlock');
    assert($plugin_definition->getClass() === static::class);
    return array_diff(
      array_column($plugin_definition->getCKEditor5Config()['codeBlock']['languages'], 'language'),
      static::ALWAYS_ENABLED_LANGUAGES
    );
  }

  /**
   * Gets all enabled languages.
   *
   * @return string[]
   *   The value in the plugins.ckeditor5_codeBlock.enabled_languages
   *   configuration plus the languages that are always enabled.
   */
  private function getEnabledLanguages(): array {
    return array_merge(
      self::ALWAYS_ENABLED_LANGUAGES,
      $this->configuration['enabled_languages']
    );
  }

  /**
   * {@inheritdoc}
   *
   * Form for choosing which languages are available for code blocks.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['enabled_languages'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Enabled Languages'),
      '#description' => $this->t('These are the languages that will appear in the codeblock languages dropdown.'),
    ];

    $languages = $this->getPluginDefinition()->getCKEditor5Config()['codeBlock']['languages'];
    foreach ($languages as $language_option) {
      $language = $language_option['language'];

      if (in_array($language, self::ALWAYS_ENABLED_LANGUAGES, TRUE)) {
        continue;
      }

      $form['enabled_languages'][$language] = self::generateCheckboxForLanguageOption($language_option);
      $form['enabled_languages'][$language]['#default_value'] = in_array($language, $this->configuration['enabled_languages'], TRUE) ? $language : NULL;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Match the config schema structure at ckeditor5.plugin.ckeditor5_codeBlock.
    $form_value = $form_state->getValue('enabled_languages');
    $config_value = array_values(array_filter($form_value));
    $form_state->setValue('enabled_languages', $config_value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['enabled_languages'] = $form_state->getValue('enabled_languages');
  }

  /**
   * Generates checkbox for a CKEditor 5 codeblock plugin config option.
   *
   * @param array $language_option
   *   A language option configuration as the CKEditor 5 Heading plugin expects
   *   in its configuration.
   *
   * @return array
   *   The checkbox render array.
   */
  private static function generateCheckboxForLanguageOption(array $language_option): array {
    // This requires the `language` and `label` properties. The `class` property
    // is optional.
    assert(array_key_exists('language', $language_option));
    assert(array_key_exists('label', $language_option));

    $checkbox = [
      '#type' => 'checkbox',
      '#title' => $language_option['label'],
      '#return_value' => $language_option['language'],
    ];
    if (isset($language_option['class'])) {
      $checkbox['#label_attributes']['class'][] = $language_option['class'];
    }

    return $checkbox;
  }

  /**
   * {@inheritdoc}
   *
   * Filters the language options to those chosen in editor config.
   *
   * @see https://api.drupal.org/api/drupal/core%21modules%21ckeditor5%21ckeditor5.api.php/function/hook_ckeditor5_plugin_info_alter
   * @see https://ckeditor.com/docs/ckeditor5/latest/features/code-blocks.html#configuring-code-block-languages
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $enabled_languages = $this->getEnabledLanguages();
    $all_language_options = $static_plugin_config['codeBlock']['languages'];

    $configured_language_options = array_filter($all_language_options, function ($option) use ($enabled_languages) {
      return in_array($option['language'], $enabled_languages, TRUE);
    });

    return [
      'codeBlock' => [
        'languages' => array_values($configured_language_options),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    // We do not use a real subset. Since we only allow classes starting with
    // "language-". We return the elements from the plugin definition instead.
    // If we want to limit the classes to only the enabled code languages we
    // need to loosen the restriction to allow all classes. This is because the
    // diff functionality on attribute values does not support wildcards (yet).
    // @see \Drupal\ckeditor5\HTMLRestrictions::doDiff()
    $plugin_definition = $this->getPluginDefinition();
    assert($plugin_definition instanceof CKEditor5PluginDefinition);
    return $plugin_definition->getElements();
  }

}
