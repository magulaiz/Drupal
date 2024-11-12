<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginElementsSubsetInterface;
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
    ];
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

    $allowed_bundles = [];
    $all_bundle_info = $this->entityTypeBundleInfo->getAllBundleInfo();
    foreach ($all_bundle_info as $entity_type => $bundles) {
      foreach ($bundles as $key => $bundle) {
        if (!empty($bundle['ckeditor5_link_suggestions'])) {
          $allowed_bundles[$entity_type][$key] = $key;
        }
      }
    }

    $header = [
      'enabled' => $this->t('Provide link suggestions for entity type'),
      'bundles' => $this->t('Bundles'),
    ];
    $form['entity_types'] = [
      '#type' => 'table',
      '#header' => $header,
      '#title' => $this->t('Allowed entity types'),
    ];

    $entity_types = $this->entityTypeManager->getDefinitions();
    foreach ($allowed_bundles as $entity_type_id => $bundles) {
      $entity_type = $entity_types[$entity_type_id];
      $bundles_for_entity_type = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      $bundles_list = [];
      foreach ($bundles as $bundle) {
        $bundles_list[] = $bundles_for_entity_type[$bundle]['label'];
      }
      $row = [
        'enabled' => [
          '#markup' => $entity_types[$entity_type_id]->isCommonReferenceTarget()
            ? $this->t("@label <small>(commonly linked)</small>", ['@label' => $entity_type->getCollectionLabel()])
            : $entity_type->getCollectionLabel(),
        ],
        'bundles' => [
          [
            '#markup' => $this->t('Included %bundles', [
              '%bundles' => $this->entityTypeManager
                ->getDefinition($entity_type->getBundleEntityType())
                ->getPluralLabel(),
            ]),
          ], [
            '#list_type' => 'ol',
            '#theme' => 'item_list',
            '#items' => $bundles_list,
          ],
        ],
      ];
      $form['entity_types'][$entity_type_id] = $row;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Match the config schema structure at
    // ckeditor5.plugin.ckeditor5_link_entity_suggestions.
    $form_value = $form_state->getValue('allow_download_links');
    $form_state->setValue('allow_download_links', (bool) $form_value);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['allow_download_links'] = $form_state->getValue('allow_download_links');
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
