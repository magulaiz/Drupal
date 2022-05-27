<?php

namespace Drupal\views\Plugin\views\display;

use Drupal\Core\Form\FormStateInterface;

/**
 * The plugin that handles an embed display.
 *
 * @ingroup views_display_plugins
 *
 * @todo: Wait until annotations/plugins support access methods.
 * no_ui => !\Drupal::config('views.settings')->get('ui.show.display_embed'),
 *
 * @ViewsDisplay(
 *   id = "embed",
 *   title = @Translation("Embed"),
 *   help = @Translation("Provide a display which can be embedded using the views api."),
 *   theme = "views_view",
 *   uses_menu_links = FALSE
 * )
 */
class Embed extends DisplayPluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesAttachments = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['block_hide_empty'] = ['default' => FALSE];

    return $options;
  }

  /**
   * The display block handler returns the structure necessary for a block.
   */
  public function execute() {
    // Prior to this being called, the $view should already be set to this
    // display, and arguments should be set on the view.
    $element = $this->view->render();
    if ($this->outputIsEmpty() && $this->getOption('block_hide_empty') && empty($this->view->style_plugin->definition['even empty'])) {
      return [];
    }
    else {
      return $element;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preview() {
    $element = parent::preview();
    if ($this->outputIsEmpty() && $this->getOption('block_hide_empty') && empty($this->view->style_plugin->definition['even empty'])) {
      return [];
    }
    else {
      return $element;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);
    $options['block_hide_empty'] = [
      'category' => 'other',
      'title' => $this->t('Hide block if the view output is empty'),
      'value' => $this->getOption('block_hide_empty') ? $this->t('Yes') : $this->t('No'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    switch ($form_state->get('section')) {
      case 'block_hide_empty':
        $form['#title'] .= $this->t('Block empty settings');

        $form['block_hide_empty'] = [
          '#title' => $this->t('Hide block if no result/empty text'),
          '#type' => 'checkbox',
          '#description' => $this->t('Hide the block if there is no result and no empty text and no header/footer which is shown on empty result'),
          '#default_value' => $this->getOption('block_hide_empty'),
        ];
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    parent::submitOptionsForm($form, $form_state);

    $section = $form_state->get('section');
    switch ($section) {
      case 'block_hide_empty':
        $this->setOption($section, $form_state->getValue($section));
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildRenderable(array $args = [], $cache = TRUE) {
    $build = parent::buildRenderable($args, $cache);
    $build['#embed'] = TRUE;
    return $build;
  }

}
