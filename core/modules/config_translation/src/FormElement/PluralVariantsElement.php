<?php

namespace Drupal\config_translation\FormElement;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Language\LanguageInterface;

/**
 * Defines form elements for plurals in configuration translation.
 */
class PluralVariantsElement extends FormElementBase {

  /**
   * {@inheritdoc}
   */
  protected function getSourceElement(LanguageInterface $source_language, $source_config) {
    $plurals = $this->getNumberOfPlurals($source_language->getId());
    $element = [
      '#type' => 'fieldset',
      '#title' => new FormattableMarkup('@label <span class="visually-hidden">(@source_language)</span>', [
        // Labels originate from configuration schema and are translatable.
        '@label' => $this->t($this->definition->getLabel()),
        '@source_language' => $source_language->getName(),
      ]),
      '#tree' => TRUE,
    ];
    for ($i = 0; $i < $plurals; $i++) {
      $element[$i] = [
        '#type' => 'item',
        // @todo Should use better labels https://www.drupal.org/node/2499639
        '#title' => $i == 0 ? $this->t('Singular form') : $this->formatPlural($i, 'First plural form', '@count. plural form'),
        '#markup' => new FormattableMarkup('<span lang="@langcode">@value</span>', [
          '@langcode' => $source_language->getId(),
          '@value' => $source_config[$i] ?? $this->t('(Empty)'),
        ]),
      ];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslationElement(LanguageInterface $translation_language, $source_config, $translation_config) {
    $plurals = $this->getNumberOfPlurals($translation_language->getId());
    $element = [
      '#type' => 'fieldset',
      '#title' => new FormattableMarkup('@label <span class="visually-hidden">(@translation_language)</span>', [
        // Labels originate from configuration schema and are translatable.
        '@label' => $this->t($this->definition->getLabel()),
        '@translation_language' => $translation_language->getName(),
      ]),
      '#tree' => TRUE,
    ];
    for ($i = 0; $i < $plurals; $i++) {
      $element[$i] = [
        '#type' => 'textfield',
        // @todo Should use better labels https://www.drupal.org/node/2499639
        '#title' => $i == 0 ? $this->t('Singular form') : $this->formatPlural($i, 'First plural form', '@count. plural form'),
        '#default_value' => $translation_config[$i] ?? '',
        '#attributes' => ['lang' => $translation_language->getId()],
      ];
    }
    return $element;
  }

}
