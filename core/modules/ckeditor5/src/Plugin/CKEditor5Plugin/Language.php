<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 Language plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class Language extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Language constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, string $plugin_id, CKEditor5PluginDefinition $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    switch ($this->configuration['language_list']) {
      case 'all':
        $predefined_languages = LanguageManager::getStandardLanguageList();
        break;

      case 'enabled':
        $enabled_languages = $this->languageManager->getLanguages();
        $predefined_languages = [];
        foreach ($enabled_languages as $language) {
          $predefined_languages[$language->getId()] = [
            $language->getName(),
            $language->getDirection() === "rtl" ? LanguageInterface::DIRECTION_RTL : $language,
          ];
        }
        break;

      case 'un':
      default:
        $predefined_languages = LanguageManager::getUnitedNationsLanguageList();
    }

    // Generate the language_list setting as expected by the CKEditor Language
    // plugin, but key the values by the full language name so that we can sort
    // them later on.
    $language_list = [];
    foreach ($predefined_languages as $langcode => $language) {
      $english_name = $language[0];
      $direction = empty($language[2]) ? NULL : $language[2];
      $language_list[$english_name] = [
        'title' => $english_name,
        'languageCode' => $langcode,
      ];
      if ($direction === LanguageInterface::DIRECTION_RTL) {
        $language_list[$english_name]['textDirection'] = 'rtl';
      }
    }

    // Sort on full language name.
    ksort($language_list);
    $dynamic_plugin_config = $static_plugin_config;
    $dynamic_plugin_config['language']['textPartLanguage'] = array_values($language_list);
    return $dynamic_plugin_config;
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\editor\Form\EditorImageDialog
   * @see editor_image_upload_settings_form()
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $enabled_languages = $this->languageManager->getLanguages();
    $predefined_languages = LanguageManager::getStandardLanguageList();
    $form['language_list'] = [
      '#title' => $this->t('Language list'),
      '#title_display' => 'invisible',
      '#type' => 'select',
      '#options' => [
        'un' => $this->t("United Nations' official languages"),
        'all' => $this->t('All @count languages', ['@count' => count($predefined_languages)]),
        'enabled' => $this->t("@count enabled languages", ['@count' => count($enabled_languages)]),
      ],
      '#default_value' => $this->configuration['language_list'],
      '#description' => $this->t('The list of languages in the dropdown can either contain the <a href=":url">six official languages of the UN</a>, all @count languages that are available by default in Drupal, or just those languages that are enabled for this site.', [
        ':url' => 'https://www.un.org/en/sections/about-un/official-languages',
        '@count' => count($predefined_languages),
      ]),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['language_list'] = $form_state->getValue('language_list');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['language_list' => 'un'];
  }

}
