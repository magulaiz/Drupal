<?php

namespace Drupal\navigation;

use Drupal\Core\Block\BlockPluginTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginTrait;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\Core\Plugin\PreviewAwarePluginInterface;
use Drupal\Core\Render\PreviewFallbackInterface;

/**
 * Defines a base navigation block class that most plugins will extend.
 *
 * This abstract class mimics the \Drupal\Core\Block\BlockBase. Navigation
 * block plugins are block like in that they have all the features of blocks
 * including configuration, forms, context, and cacheability metadata.
 */
abstract class NavigationBlockPluginBase extends PluginBase implements NavigationBlockPluginInterface, PluginWithFormsInterface, PreviewAwarePluginInterface, PreviewFallbackInterface, ContextAwarePluginInterface {

  // @todo Should be decouple here and duplicate BlockPluginTrait into a
  //   NavigationBlockPluginTrait?
  use BlockPluginTrait {
    buildConfigurationForm as traitBuildConfigurationForm;
    baseConfigurationDefaults as traitBaseConfigurationDefaults;
  }
  use ContextAwarePluginTrait;
  use ContextAwarePluginAssignmentTrait;

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->traitBuildConfigurationForm($form, $form_state);

    // Add context mapping UI form elements.
    $contexts = $form_state->getTemporaryValue('gathered_contexts') ?: [];
    $form['context_mapping'] = $this->addContextAssignmentElement($this, $contexts);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function baseConfigurationDefaults() {
    $configuration = $this->traitBaseConfigurationDefaults();
    $configuration['label_display'] = FALSE;
    return $configuration;
  }

}
