<?php

namespace Drupal\layout_builder_test\Plugin\Layout;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Layout\LayoutInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginWithFormsTrait;

/**
 * Provides a plugin that does not extend \Drupal\Core\Layout\LayoutDefault.
 *
 * @Layout(
 *   id = "layout_builder_test_no_form_plugin",
 *   label = @Translation("Layout Builder Test No Form Plugin"),
 *   regions = {
 *     "main" = {
 *       "label" = @Translation("Main Region")
 *     }
 *   },
 * )
 */
class LayoutBuilderTestNoFormPlugin extends PluginBase implements LayoutInterface {

  use PluginWithFormsTrait;

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = $regions;
    $build['#settings'] = $this->getConfiguration();
    $build['#layout'] = $this->pluginDefinition;
    $build['#theme'] = $this->pluginDefinition->getThemeHook();
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
   */
  public function getFormClass($operation) {
    return $this->getPluginDefinition()->get('forms')[$operation] ?? NULL;
  }

}
