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
 * CKEditor 5 Style plugin configuration.
 *
 * @internal
 *   Plugin classes are internal.
 */
class Style extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['styles'] = [
      '#title' => $this->t('Styles'),
      '#title_display' => 'invisible',
      '#type' => 'textarea',
      '#description' => $this->t('A list of classes that will be provided in the "Style" dropdown. Enter one or more classes on each line in the format: element.classA.classB|Label. Example: h1.title|Title. Advanced example: h1.fancy.title|Fancy title.<br />These styles should be available in your theme\'s CSS file.'),
    ];
    if (!empty($this->configuration['styles'])) {
      $as_selectors = '';
      foreach ($this->configuration['styles'] as $style) {
        [$tag, $classes] = self::getTagAndClasses(HTMLRestrictions::fromString($style['element']));
        $as_selectors .= sprintf("%s.%s|%s\n", $tag, implode('.', $classes), $style['label']);
      }
      $form['styles']['#default_value'] = $as_selectors;
    }

    return $form;
  }

  /**
   * Gets the tag and classes for a parsed style element.
   *
   * @param \Drupal\ckeditor5\HTMLRestrictions $style_element
   *   A parsed style element.
   *
   * @return array
   *   An array containing two values:
   *   - a HTML tag name
   *   - a list of classes
   */
  private static function getTagAndClasses(HTMLRestrictions $style_element): array {
    $tag = array_keys($style_element->getAllowedElements())[0];
    $classes = array_keys($style_element->getAllowedElements()[$tag]['class']);
    return [$tag, $classes];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Match the config schema structure at ckeditor5.plugin.ckeditor5_style.
    $form_value = $form_state->getValue('styles');
    assert(is_string($form_value));
    $lines = explode("\n", $form_value);
    $styles = [];
    foreach ($lines as $index => $line) {
      if (empty(trim($line))) {
        continue;
      }

      // Parse the line.
      [$selector, $label] = explode('|', trim($line));

      // Validate the selector.
      $selector_matches = [];
      if (!preg_match('/^([a-z][0-9a-zA-Z\-]*)((\.[a-zA-Z0-9\-_]+)+)$/', $selector, $selector_matches)) {
        $form_state->setError($form['styles'], $this->t('Line @line-number does not contain a valid value. Enter a valid CSS selector containing, followed by a pipe symbol and a label.', ['@line-number' => $index + 1]));
      }

      // Parse selector into tag + classes and normalize.
      $tag = $selector_matches[1];
      $classes = array_filter(explode('.', $selector_matches[2]));
      $normalized = HTMLRestrictions::fromString(sprintf('<%s class="%s">', $tag, implode(' ', $classes)));

      $styles[] = [
        'element' => $normalized->toCKEditor5ElementsArray()[0],
        'label' => $label,
      ];
    }
    $form_state->setValue('styles', $styles);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['styles'] = $form_state->getValue('styles');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'styles' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    return array_column($this->configuration['styles'], 'element');
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $definitions = [];
    foreach ($this->configuration['styles'] as $style) {
      [$tag, $classes] = self::getTagAndClasses(HTMLRestrictions::fromString($style['element']));
      // Transform configured styles to the configuration structure expected by
      // the CKEditor 5 Style plugin.
      $definitions[] = [
        'name' => $style['label'],
        'element' => $tag,
        'classes' => $classes,
      ];
    }
    return [
      'style' => [
        'definitions' => $definitions,
      ],
    ];
  }

}
