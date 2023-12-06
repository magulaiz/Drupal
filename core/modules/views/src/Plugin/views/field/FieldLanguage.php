<?php

namespace Drupal\views\Plugin\views\field;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Psr\Container\ContainerInterface;

/**
 * Displays the language of an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_language")
 */
class FieldLanguage extends FieldPluginBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a \Drupal\field\Plugin\views\field\FieldLanguage object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
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
  public function access(AccountInterface $account) {
    // Don't display the field in case the site is not multilingual, because
    // there is no point in doing so.
    if (!$this->languageManager->isMultilingual()) {
      return FALSE;
    }

    return parent::access($account);
  }

}
