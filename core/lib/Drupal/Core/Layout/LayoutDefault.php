<?php

namespace Drupal\Core\Layout;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\ContextAwarePluginTrait;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\PreviewAwarePluginInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a default class for Layout plugins.
 */
class LayoutDefault extends PluginBase implements LayoutInterface, PluginFormInterface, PreviewAwarePluginInterface {

  use ContextAwarePluginAssignmentTrait;
  use ContextAwarePluginTrait;

  /**
   * Whether the plugin is being rendered in preview mode.
   *
   * @var bool
   */
  protected $inPreview = FALSE;

  /**
   * The layout definition.
   *
   * @var \Drupal\Core\Layout\LayoutDefinition
   */
  protected $pluginDefinition;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $cacheable_metadata = new CacheableMetadata();
    // Ensure $build only contains defined regions and in the order defined.
    $build = [];
    $route_name = \Drupal::routeMatch()->getRouteName();
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      if (array_key_exists($region_name, $regions)) {
        foreach ($regions[$region_name] as $uuid => $block) {
          // Remove empty blocks from the list but retain their cache metadata.
          if (Element::isEmpty($block)) {
            $cacheable_metadata->addCacheableDependency(CacheableMetadata::createFromRenderArray($block));
            unset($regions[$region_name][$uuid]);
          }
        }
        if (!empty($regions[$region_name])) {
          $build[$region_name] = $regions[$region_name];
        }
      }
    }

    // Only add the theme info if there is something to render.
    if ($build || str_starts_with($route_name, 'layout_builder.')) {
      $build['#theme'] = $this->pluginDefinition->getThemeHook();
      if ($library = $this->pluginDefinition->getLibrary()) {
        $build['#attached']['library'][] = $library;
      }
    }

    // Always add the relevant metadata.
    $build['#in_preview'] = $this->inPreview;
    $build['#settings'] = $this->getConfiguration();
    $build['#layout'] = $this->pluginDefinition;
    $cacheable_metadata->applyTo($build);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\Core\Layout\LayoutDefinition
   */
  public function getPluginDefinition() {
    return parent::getPluginDefinition();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrative label'),
      '#default_value' => $this->configuration['label'],
    ];
    $contexts = $form_state->getTemporaryValue('gathered_contexts') ?: [];
    $form['context_mapping'] = $this->addContextAssignmentElement($this, $contexts);
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
    $this->configuration['label'] = $form_state->getValue('label');
  }

  /**
   * {@inheritdoc}
   */
  public function setInPreview(bool $in_preview): void {
    $this->inPreview = $in_preview;
  }

}
