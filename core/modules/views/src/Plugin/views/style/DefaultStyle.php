<?php

namespace Drupal\views\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;

/**
 * Unformatted style plugin to render rows.
 *
 * Row are rendered one after another with no decorations.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "default",
 *   title = @Translation("Unformatted list"),
 *   help = @Translation("Displays rows one after another."),
 *   theme = "views_view_unformatted",
 *   display_types = {"normal"}
 * )
 */
class DefaultStyle extends StylePluginBase {

  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;

  /**
   *
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    // Add support for selecting an HTML label to use for the grouping.
    $c = count($this->options['grouping']);
    // Add a form for every grouping, plus one.
    for ($i = 0; $i <= $c; $i++) {
      $grouping = !empty($this->options['grouping'][$i]) ? $this->options['grouping'][$i] : [];
      $grouping += ['field' => '', 'rendered' => TRUE, 'rendered_strip' => FALSE, 'grouping_label_element' => ''];
      $form['grouping'][$i]['grouping_label_element'] = [
        '#type' => 'select',
        '#title' => $this->t('Grouping Label Tag', ['@number' => $i + 1]),
        '#options' => $this->getLabelElements(),
        '#default_value' => $grouping['grouping_label_element'],
        '#description' => $this->t('You may specify a wrapper tag by which to group the records.'),
      ];
    }
  }

}
