<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\Entity\EntityLinkSuggester;
use Drupal\Core\Entity\Entity\EntityLinkSuggesterInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 Entity Link Suggestions plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class EntityLinkSuggestions extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, CKEditor5PluginElementsSubsetInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;
  use DynamicPluginConfigWithCsrfTokenUrlTrait;

  /**
   * EntityLinkSuggestions constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly EntityTypeBundleInfoInterface $entityTypeBundleInfo,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $dynamic_plugin_config = $static_plugin_config;
    $dynamic_plugin_config['drupalEntityLinkSuggestions']['suggestionsUrl'] = self::getUrlWithReplacedCsrfTokenPlaceholder(
      Url::fromRoute('ckeditor5.entity_link_suggestions')
        ->setRouteParameter('editor', $editor->id())
        // @see initializeAutocomplete() in core/modules/ckeditor5/js/ckeditor5_plugins/drupalEntityLinkSuggestions/src/index.js
        ->setRouteParameter('host_entity_type_id', '_')
        ->setRouteParameter('host_entity_langcode', '_')
    );
    return $dynamic_plugin_config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'allow_download_links' => TRUE,
      'suggester' => NULL,
    ];
  }

  /**
   * Whether the given entity type is linkable.
   *
   * Entity types must either have links or specify a link_target handler.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type to evaluate.
   *
   * @return bool
   *   TRUE if it is linkable, FALSE if not.
   */
  public static function isLinkableEntityType(EntityTypeInterface $entity_type): bool {
    $canonical = $entity_type->getLinkTemplate('canonical');
    $edit_form = $entity_type->getLinkTemplate('edit-form');
    return ($canonical !== FALSE && $canonical !== $edit_form) || $entity_type->hasHandlerClass('link_target', 'view');
  }

  /**
   * Gets the allowed bundles for the given entity type from plugin config.
   *
   * @param array $configuration
   *   The complete plugin configuration.
   * @param string $entity_type_id
   *   The entity type to find the suggestion configuration for.
   *
   * @return null|array
   *   NULL if all bundles are allowed, a list of bundle names otherwise.
   *
   * @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection::defaultConfiguration()
   */
  public static function getAllowedBundlesForEntityType(array $configuration, string $entity_type_id): ?array {
    assert(array_keys($configuration) === ['allow_download_links', 'suggestions']);
    $filtered = array_filter(
      $configuration['suggestions'] ?? [],
      fn(array $s) => $s['entity_type_id'] === $entity_type_id
    );
    if (empty($filtered)) {
      return NULL;
    }
    assert(count($filtered) === 1 && array_keys(reset($filtered)) === ['entity_type_id', 'bundles']);
    return reset($filtered)['bundles'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['allow_download_links'] = [
      '#title' => $this->t('Allow the user to create <em>download links</em>'),
      '#type' => 'checkbox',
      '#description' => $this->t('Allow content creators to create a download link on entities that support it, by adding a <a href=":url"><code>download</code></a> attribute that will cause the browser to download the linked file.', [':url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML/Element/a#download']),
      '#default_value' => $this->configuration['allow_download_links'],
    ];

    $modal_dialog_options = [
      'attributes' => [
        'class' => 'use-ajax',
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode(['width' => '50em']),
      ],
      'query' => [
        'destination' => Url::fromRoute(route_name: '<current>')->toString(),
      ],
    ];

    $link_suggesters = EntityLinkSuggester::loadMultiple();
    $form['suggester'] = [
      '#title' => $this->t('Provide link suggestions for'),
      '#type' => 'radios',
      '#options' => array_combine(
        // Keys: config dependency names.
        array_map(
          fn (EntityLinkSuggesterInterface $s) => $s->getConfigDependencyName(),
          $link_suggesters,
        ),
        // Values: render arrays with `#title` and `#description`.
        array_map(
          fn (EntityLinkSuggesterInterface $s) => [
            '#title' => $s->toLink(rel: 'edit-form', options: $modal_dialog_options)->toString(),
            '#description' => $s->describe(),
          ],
          $link_suggesters
        ),
      ),
      '#default_value' => $this->configuration['suggester'],
    ];
    // An extra pseudo-option that cannot be selected by the end user, to allow
    // creating a new link suggester..
    $form['suggester']['#options'][] = [
      '#disabled' => TRUE,
      '#title' => Link::fromTextAndUrl(
        $this->t('Create new link suggester'),
        Url::fromRoute('entity.entity_link_suggester.add_form')->setOptions($modal_dialog_options)
      )->toString(),
      '#description' => $this->t('If none of the existing link suggesters are a good match, create a new one.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Match the config schema structure at ckeditor5.plugin.ckeditor5_link_entity_suggestions.
    $form_value = $form_state->getValue('allow_download_links');
    $form_state->setValue('allow_download_links', (bool) $form_value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['allow_download_links'] = $form_state->getValue('allow_download_links');
    $this->configuration['suggester'] = $form_state->getValue('suggester');
  }

  /**
   * {@inheritdoc}
   */
  public function getElementsSubset(): array {
    $subset = $this->getPluginDefinition()->getElements();
    $download_links_enabled = $this->getConfiguration()['allow_download_links'];
    if (!$download_links_enabled) {
      $subset = array_diff($subset, ['<a download>']);
    }
    return $subset;
  }

}
