<?php

namespace Drupal\Core\Render\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form element for a set of dynamic options.
 *
 * Depending on the number of options and the threshold value, if behaves as a
 * set of checkboxes / radios or as a select form element.
 *
 * Global properties:
 * - #options: An associative array whose keys are the values returned for each
 *   option, and whose values are the labels next to each checkbox. The
 *   #options array cannot have a 0 key, as it would not be possible to discern
 *   checked and unchecked states.
 * - #required: (optional) Whether the user needs to select an option (TRUE)
 *   or not (FALSE). Defaults to FALSE.
 * - #multiple: (optional) Indicates whether one or more options can be
 *   selected. It is used to determine whether to render checkboxes or
 *   radios. Defaults to FALSE.
 * - #select_threshold: If the number of options is bigger than the threshold, a
 *   select element rendered be used instead of checkboxes / radios.
 *   Defaults to 7.
 * Select specific properties. These properties only applies is the rendered
 * element is a select instead of checkboxes / radios:
 * - #sort_options: (optional) If set to TRUE (default is FALSE), sort the
 *   options by their labels, after rendering and translation is complete.
 *   Can be set within an option group to sort that group.
 * - #sort_start: (optional) Option index to start sorting at, where 0 is the
 *   first option. Can be used within an option group. If an empty option is
 *   being added automatically (see #empty_option and #empty_value properties),
 *   this defaults to 1 to keep the empty option at the top of the list.
 *   Otherwise, it defaults to 0.
 * - #empty_option: (optional) The label to show for the first default option.
 *   By default, the label is automatically set to "- Select -" for a required
 *   field and "- None -" for an optional field.
 * - #empty_value: (optional) The value for the first default option, which is
 *   used to determine whether the user submitted a value or not.
 *   - If #required is TRUE, this defaults to '' (an empty string).
 *   - If #required is not TRUE and this value isn't set, then no extra option
 *     is added to the select control, leaving the control in a slightly
 *     illogical state, because there's no way for the user to select nothing,
 *     since all user agents automatically preselect the first available
 *     option. But people are used to this being the behavior of select
 *     controls.
 *     @todo Address the above issue in Drupal 8.
 *   - If #required is not TRUE and this value is set (most commonly to an
 *     empty string), then an extra option (see #empty_option above)
 *     representing a "non-selection" is added with this as its value.
 * - #multiple: (optional) Indicates whether one or more options can be
 *   selected. Defaults to FALSE.
 * - #default_value: Must be NULL or not set in case there is no value for the
 *   element yet, in which case a first default option is inserted by default.
 *   Whether this first option is a valid option depends on whether the field
 *   is #required or not.
 * - #size: The number of rows in the list that should be visible at one time.
 *
 * Usage example, in this case will behave as 'radios' form element:
 * @code
 * $form['favorites']['colors'] = array(
 *   '#type' => 'dynamic_options',
 *   '#options' => array('blue' => $this->t('Blue'), 'red' => $this->t('Red')),
 *   '#title' => $this->t('Which colors do you like?'),
 *   '#multiple' => FALSE,
 *   ...
 * );
 * @endcode
 *
 * Usage example, in this case will behave as 'checkboxes' form element:
 * @code
 * $form['favorites']['colors'] = array(
 *   '#type' => 'dynamic_options',
 *   '#options' => array('blue' => $this->t('Blue'), 'red' => $this->t('Red')),
 *   '#title' => $this->t('Which colors do you like?'),
 *   '#multiple' => TRUE,
 *   ...
 * );
 * @endcode
 *
 * Usage example, in this case will behave as 'select' form element:
 * @code
 * $form['favorites']['colors'] = array(
 *   '#type' => 'dynamic_options',
 *   '#options' => array('blue' => $this->t('Blue'), 'red' => $this->t('Red')),
 *   '#title' => $this->t('Which colors do you like?'),
 *   '#select_threshold' => 1,
 *   ...
 * );
 * @endcode
 *
 * Element properties may be set on single option items as follows.
 *
 * @code
 * $form['favorites']['colors']['blue']['#description'] = $this->t('The color of the sky.');
 * @endcode
 *
 * @see \Drupal\Core\Render\Element\Checkboxes
 * @see \Drupal\Core\Render\Element\Select
 *
 * @FormElement("dynamic_options")
 */
class DynamicOptions extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;

    return [
      '#input' => TRUE,
      '#multiple' => FALSE,
      '#sort_options' => FALSE,
      '#sort_start' => NULL,
      '#select_threshold' => 7,
      '#process' => [
        [$class, 'processDynamicOptions'],
      ],
    ];
  }

  /**
   * Processes a dynamic_options form element.
   */
  public static function processDynamicOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    /** @var \Drupal\Core\Render\ElementInfoManagerInterface $manager */
    $manager = \Drupal::service('plugin.manager.element_info');
    $args = [&$element, &$form_state, &$complete_form];

    // Determine the basic form element to render.
    if (count($element['#options']) > $element['#select_threshold']) {
      $type = 'select';
    }
    else {
      $type = $element['#multiple'] ? 'checkboxes' : 'radios';
    }

    $info = $manager->getInfo($type);

    // Call the basic element #process functions.
    foreach ($info['#process'] as $callback) {
      call_user_func_array($callback, $args);
    }

    // Set the basic element definition.
    $element['#type'] = $type;
    $element += $info;

    return $element;
  }

}
