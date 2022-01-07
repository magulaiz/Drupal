<?php

namespace Drupal\datetime_range\Plugin\views\filter;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Database\Query\Condition;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\views\FieldAPIHandlerTrait;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Filter to handle date ranges.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("datetime_range")
 */
class DateRange extends FilterPluginBase {

  use FieldAPIHandlerTrait;

  /**
   * Date format for SQL conversion.
   *
   * @var string
   *
   * @see \Drupal\views\Plugin\views\query\Sql::getDateFormat()
   */
  protected $dateFormat;

  /**
   * Constructs a new DateRange handler.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Determine the storage type for this field.
    // This is used later to determine what kind of input field to use for
    // user input.
    $definition = $this->getFieldStorageDefinition();
    if ($definition->getSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      $this->dateFormat = DateTimeItemInterface::DATE_STORAGE_FORMAT;
    }
    else {
      $this->dateFormat = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    // This plugin is used for both the start date and the end date view field,
    // so we need to make sure both know the two view field names.
    $this->startDateField = str_replace('_end_value', '_value', $this->realField);
    $this->endDateField = str_replace('_value', '_end_value', $this->startDateField);
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    $options = $this->operatorOptions('title');
    $output = $options[$this->operator];
    if (in_array($this->operator, $this->operatorValues(2))) {
      $output .= ' ' . $this->t('@start and @end', ['@start' => $this->value['start'], '@end' => $this->value['end']]);
    }
    elseif (in_array($this->operator, $this->operatorValues(1))) {
      $output .= ' ' . $this->value['value'];
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['operator'] = ['default' => 'happens_during_inclusive'];

    $options['value'] = [
      'contains' => [
        'start' => ['default' => date(DateTimeItemInterface::DATETIME_STORAGE_FORMAT)],
        'end' => ['default' => date(DateTimeItemInterface::DATETIME_STORAGE_FORMAT, strtotime("+1 day"))],
        'value' => ['default' => date(DateTimeItemInterface::DATETIME_STORAGE_FORMAT)],
      ],
    ];

    $options['expose'] = [
      'contains' => [
        'start_placeholder' => ['default' => $this->t('Enter start datetime')],
        'end_placeholder' => ['default' => $this->t('Enter end datetime')],
        'placeholder' => ['default' => $this->t('Enter a datetime')],
      ],
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultExposeOptions() {
    parent::defaultExposeOptions();
    $this->options['expose']['start_placeholder'] = $this->t('Enter start datetime');
    $this->options['expose']['end_placeholder'] = $this->t('Enter end datetime');
    $this->options['expose']['placeholder'] = $this->t('Enter a datetime');
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    $form['expose']['start_placeholder'] = [
      '#type' => 'textfield',
      '#default_value' => $this->options['expose']['start_placeholder'],
      '#title' => $this->t('Start datetime placeholder'),
      '#size' => 40,
      '#description' => $this->t('Hint text that appears inside the Start datetime field when empty.'),
    ];
    $form['expose']['end_placeholder'] = [
      '#type' => 'textfield',
      '#default_value' => $this->options['expose']['end_placeholder'],
      '#title' => $this->t('End datetime placeholder'),
      '#size' => 40,
      '#description' => $this->t('Hint text that appears inside the End datetime field when empty.'),
    ];
    // Setup #states for all operators with two value.
    $states = [[':input[name="options[expose][use_operator]"]' => ['checked' => TRUE]]];
    foreach ($this->operatorValues(2) as $operator) {
      $states[] = [
        ':input[name="options[operator]"]' => ['value' => $operator],
      ];
    }
    $form['expose']['start_placeholder']['#states']['visible'] = $states;
    $form['expose']['end_placeholder']['#states']['visible'] = $states;

    $form['expose']['placeholder'] = [
      '#type' => 'textfield',
      '#default_value' => $this->options['expose']['placeholder'],
      '#title' => $this->t('Placeholder'),
      '#size' => 40,
      '#description' => $this->t('Hint text that appears inside the field when empty.'),
    ];
    // Setup #states for all operators with one value.
    $form['expose']['placeholder']['#states']['visible'] = [[':input[name="options[expose][use_operator]"]' => ['checked' => TRUE]]];
    foreach ($this->operatorValues(1) as $operator) {
      $form['expose']['placeholder']['#states']['visible'][] = [
        ':input[name="options[operator]"]' => ['value' => $operator],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    // Do some minor translation of the exposed input.
    if (empty($this->options['exposed'])) {
      return TRUE;
    }

    // Rewrite the input value so that it's in the correct format and the
    // parent gets the right data.
    if (!empty($this->options['expose']['identifier'])) {
      $value = &$input[$this->options['expose']['identifier']];
      if (!is_array($value)) {
        $value = [
          'value' => $value,
        ];
      }
    }

    $exposedInput = parent::acceptExposedInput($input);

    if (empty($this->options['expose']['required'])) {
      // We have to do some of our own checking for non-required filters.
      $info = $this->operators();
      if (!empty($info[$this->operator]['values'])) {
        switch ($info[$this->operator]['values']) {
          case 1:
            if ($value['value'] === '') {
              return FALSE;
            }
            break;

          case 2:
            if ($value['start'] === '' && $value['end'] === '') {
              return FALSE;
            }
            break;
        }
      }
    }

    return $exposedInput;
  }

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = [
      'starts_on' => [
        'title' => $this->t('Starts on/at'),
        'values' => 1,
      ],
      'starts_before' => [
        'title' => $this->t('Starts before'),
        'values' => 1,
      ],
      'starts_on_before' => [
        'title' => $this->t('Starts on or before'),
        'values' => 1,
      ],
      'starts_after' => [
        'title' => $this->t('Starts after'),
        'values' => 1,
      ],
      'starts_on_after' => [
        'title' => $this->t('Starts on or after'),
        'values' => 1,
      ],
      'starts_between' => [
        'title' => $this->t('Starts between'),
        'values' => 2,
      ],
      'ends_on' => [
        'title' => $this->t('Ends on/at'),
        'values' => 1,
      ],
      'ends_before' => [
        'title' => $this->t('Ends before'),
        'values' => 1,
      ],
      'ends_on_before' => [
        'title' => $this->t('Ends on or before'),
        'values' => 1,
      ],
      'ends_after' => [
        'title' => $this->t('Ends after'),
        'values' => 1,
      ],
      'ends_on_after' => [
        'title' => $this->t('Ends on or after'),
        'values' => 1,
      ],
      'ends_between' => [
        'title' => $this->t('Ends between'),
        'values' => 2,
      ],
      'happens_on' => [
        'title' => $this->t('Happens on'),
        'values' => 1,
      ],
      'happens_between' => [
        'title' => $this->t('Happens between'),
        'values' => 2,
      ],
      'happens_between_inclusive' => [
        'title' => $this->t('Happens between (Including outer limits)'),
        'values' => 2,
      ],
      'happens_during' => [
        'title' => $this->t('Happens during'),
        'values' => 2,
      ],
      'happens_during_inclusive' => [
        'title' => $this->t('Happens during (Including outer limits)'),
        'values' => 2,
      ],
    ];

    return $operators;
  }

  /**
   * Provide a list of all the operators.
   *
   * @param string $which
   *   A method that returns operator operations.
   */
  public function operatorOptions($which = 'title') {
    $options = [];
    foreach ($this->operators() as $id => $info) {
      $options[$id] = $info[$which];
    }

    return $options;
  }

  /**
   * Provides a list of all the operators.
   *
   * @param int $values
   *   A method that returns operator values.
   */
  protected function operatorValues($values = 1) {
    $options = [];
    foreach ($this->operators() as $id => $info) {
      if ($info['values'] == $values) {
        $options[] = $id;
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $form['value']['#tree'] = TRUE;

    if (!empty($form['operator'])) {
      $source = ':input[name="options[operator]"]';
    }

    if ($exposed = $form_state->get('exposed')) {
      $identifier = $this->options['expose']['identifier'];

      if (empty($this->options['expose']['use_operator']) || empty($this->options['expose']['operator_id'])) {
        // Exposed and locked.
        $which = in_array($this->operator, $this->operatorValues(2)) ? 'minmax' : 'value';
      }
      else {
        $source = ':input[name="' . $this->options['expose']['operator_id'] . '"]';
      }
    }
    $value_date_obj = new \DateTime($this->value['value'], new \DateTimeZone('UTC'));
    $value_date_obj->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $value_date_timestamp = $value_date_obj->getTimestamp();
    $value_date_timestamp_object = $this->value['value'] !== NULL ? DrupalDateTime::createFromTimestamp($value_date_timestamp, date_default_timezone_get()) : NULL;

    $start_date_obj = new \DateTime($this->value['start'], new \DateTimeZone('UTC'));
    $start_date_obj->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $start_date_timestamp = $start_date_obj->getTimestamp();
    $start_date_timestamp_object = $this->value['start'] !== NULL ? DrupalDateTime::createFromTimestamp($start_date_timestamp, date_default_timezone_get()) : NULL;

    $end_date_obj = new \DateTime($this->value['end'], new \DateTimeZone('UTC'));
    $end_date_obj->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $end_date_timestamp = $end_date_obj->getTimestamp();
    $end_date_timestamp_object = $this->value['end'] !== NULL ? DrupalDateTime::createFromTimestamp($end_date_timestamp, date_default_timezone_get()) : NULL;

    // Simple HTML5 date field for field with date only storage.
    if ($this->dateFormat == DateTimeItemInterface::DATE_STORAGE_FORMAT) {
      $form['value']['value'] = [
        '#type' => 'date',
        '#title' => $this->t('Value'),
        '#size' => 30,
        '#default_value' => $this->value['value'],
      ];
    }
    // Combination of HTML5 date & time fields for field with
    // date & time storage.
    elseif ($this->dateFormat == DateTimeItemInterface::DATETIME_STORAGE_FORMAT) {
      $form['value']['value'] = [
        '#type' => 'datetime',
        '#date_date_element' => 'date',
        '#date_time_element' => 'time',
        '#title' => $this->t('Value'),
        '#size' => 30,
        '#default_value' => $value_date_timestamp_object,
      ];
    }

    // Setup #states for all operators with one value.
    foreach ($this->operatorValues(1) as $operator) {
      $form['value']['value']['#states']['visible'][] = [
        $source => ['value' => $operator],
      ];
    }

    // Simple HTML5 date field for field with date only storage.
    if ($this->dateFormat == DateTimeItemInterface::DATE_STORAGE_FORMAT) {
      $form['value']['start'] = [
        '#type' => 'date',
        '#title' => $this->t('Start'),
        '#size' => 30,
        '#default_value' => $this->value['start'],
      ];
    }
    // Combination of HTML5 date & time fields for field with
    // date & time storage.
    elseif ($this->dateFormat == DateTimeItemInterface::DATETIME_STORAGE_FORMAT) {
      $form['value']['start'] = [
        '#type' => 'datetime',
        '#date_date_element' => 'date',
        '#date_time_element' => 'time',
        '#title' => $this->t('Start'),
        '#size' => 30,
        '#default_value' => $start_date_timestamp_object,
      ];
    }

    // Setup #states for all operators with one value.
    foreach ($this->operatorValues(2) as $operator) {
      $form['value']['start']['#states']['visible'][] = [
        $source => ['value' => $operator],
      ];
    }

    // Simple HTML5 date field for field with date only storage.
    if ($this->dateFormat == DateTimeItemInterface::DATE_STORAGE_FORMAT) {
      $form['value']['end'] = [
        '#type' => 'date',
        '#title' => $this->t('End'),
        '#size' => 30,
        '#default_value' => $this->value['end'],
      ];
    }
    // Combination of HTML5 date & time fields for field with
    // date & time storage.
    elseif ($this->dateFormat == DateTimeItemInterface::DATETIME_STORAGE_FORMAT) {
      $form['value']['end'] = [
        '#type' => 'datetime',
        '#date_date_element' => 'date',
        '#date_time_element' => 'time',
        '#title' => $this->t('End'),
        '#size' => 30,
        '#default_value' => $end_date_timestamp_object,
      ];
    }

    // Setup #states for all operators with one value.
    foreach ($this->operatorValues(2) as $operator) {
      $form['value']['end']['#states']['visible'][] = [
        $source => ['value' => $operator],
      ];
    }

    if (!isset($form['value'])) {
      // Ensure there is something in the 'value'.
      $form['value'] = [
        '#type' => 'value',
        '#value' => NULL,
      ];
    }
  }

  /**
   * Submit Handler to convert DateTime Object to string values.
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    // Cleaning form values.
    $form_values = $form_state->cleanValues()->getValues();
    $form_input_values = $form_state->getUserInput();
    $value = !empty($form_values['options']['value']['value']) ? $form_values['options']['value']['value']->format('Y-m-dTH:i:s') : '';
    $start = !empty($form_values['options']['value']['start']) ? $form_values['options']['value']['start']->format('Y-m-dTH:i:s') : '';
    $end = !empty($form_values['options']['value']['end']) ? $form_values['options']['value']['end']->format('Y-m-dTH:i:s') : '';

    if ($value) {
      $form_state->setValue(['options', 'value', 'value'], $value);
    }
    if ($start) {
      $form_state->setValue(['options', 'value', 'start'], $start);
    }
    if ($end) {
      $form_state->setValue(['options', 'value', 'end'], $end);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Get the user input in date/time storage format for SQL query
    // comparison.
    if (!empty($this->value['start'])) {
      $start = new DateTimePlus($this->value['start']);
      $start = $start->format($this->dateFormat);
    }
    if (!empty($this->value['start'])) {
      $end = new DateTimePlus($this->value['end']);
      $end = $end->format($this->dateFormat);
    }
    if (!empty($this->value['start'])) {
      $value = new DateTimePlus($this->value['value']);
      $value = $value->format($this->dateFormat);
    }

    $this->ensureMyTable();
    $startDateField = "$this->tableAlias.$this->startDateField";
    $endDateField = "$this->tableAlias.$this->endDateField";

    // See the following comment on drupal.org issue for logic behind
    // all the following combinations:
    // @see https://www.drupal.org/project/drupal/issues/2924061#comment-13684573
    switch ($this->operator) {
      case 'starts_on':
        $this->startsOn($startDateField, $value);
        break;

      case 'starts_before':
        $this->startsBefore($startDateField, $value);
        break;

      case 'starts_on_before':
        $this->startsOnOrBefore($startDateField, $value);
        break;

      case 'starts_after':
        $this->startsAfter($startDateField, $value);
        break;

      case 'starts_on_after':
        $this->startsOnOrAfter($startDateField, $value);
        break;

      case 'starts_between':
        $this->startsBetween($startDateField, $start, $end);
        break;

      case 'ends_on':
        $this->endsOn($endDateField, $value);
        break;

      case 'ends_before':
        $this->endsBefore($endDateField, $value);
        break;

      case 'ends_on_before':
        $this->endsOnOrBefore($endDateField, $value);
        break;

      case 'ends_after':
        $this->endsAfter($endDateField, $value);
        break;

      case 'ends_on_after':
        $this->endsOnOrAfter($endDateField, $value);
        break;

      case 'ends_between':
        $this->endsBetween($endDateField, $start, $end);
        break;

      case 'happens_on':
        $this->happensOn($startDateField, $endDateField, $value);
        break;

      case 'happens_between':
        $this->happensBetween($startDateField, $endDateField, $start, $end);
        break;

      case 'happens_between_inclusive':
        $this->happensBetweenInclusive($startDateField, $endDateField, $start, $end);
        break;

      case 'happens_during':
        $this->happensDuring($startDateField, $endDateField, $start, $end);
        break;

      case 'happens_during_inclusive':
      default:
        $this->happensDuringInclusive($startDateField, $endDateField, $start, $end);
        break;
    }
  }

  /**
   * Adding starts on filter to the query.
   */
  protected function startsOn($startDateField, $value) {
    $this->query->addWhere($this->options['group'], $startDateField, $value, 'LIKE');
  }

  /**
   * Adding starts before filter to the query.
   */
  protected function startsBefore($startDateField, $value) {
    $this->query->addWhere($this->options['group'], $startDateField, $value, '<');
  }

  /**
   * Adding starts on or before filter to the query.
   */
  protected function startsOnOrBefore($startDateField, $value) {
    $this->query->addWhere($this->options['group'], $startDateField, $value, '<=');
  }

  /**
   * Adding starts after filter to the query.
   */
  protected function startsAfter($startDateField, $value) {
    $this->query->addWhere($this->options['group'], $startDateField, $value, '>');
  }

  /**
   * Adding starts on or after filter to the query.
   */
  protected function startsOnOrAfter($startDateField, $value) {
    $this->query->addWhere($this->options['group'], $startDateField, $value, '>=');
  }

  /**
   * Adding starts between filter to the query.
   */
  protected function startsBetween($startDateField, $start, $end) {
    $this->query->addWhere($this->options['group'], $startDateField, [$start, $end], 'BETWEEN');
  }

  /**
   * Adding ends on filter to the query.
   */
  protected function endsOn($endDateField, $value) {
    $this->query->addWhere($this->options['group'], $endDateField, $value, '=');
  }

  /**
   * Adding ends before filter to the query.
   */
  protected function endsBefore($endDateField, $value) {
    $this->query->addWhere($this->options['group'], $endDateField, $value, '<');
  }

  /**
   * Adding ends on or before filter to the query.
   */
  protected function endsOnOrBefore($endDateField, $value) {
    $this->query->addWhere($this->options['group'], $endDateField, $value, '<=');
  }

  /**
   * Adding ends after filter to the query.
   */
  protected function endsAfter($endDateField, $value) {
    $this->query->addWhere($this->options['group'], $endDateField, $value, '>');
  }

  /**
   * Adding ends on or after filter to the query.
   */
  protected function endsOnOrAfter($endDateField, $value) {
    $this->query->addWhere($this->options['group'], $endDateField, $value, '>=');
  }

  /**
   * Adding ends between filter to the query.
   */
  protected function endsBetween($endDateField, $start, $end) {
    $this->query->addWhere($this->options['group'], $endDateField, [$start, $end], 'BETWEEN');
  }

  /**
   * Adding happens on filter to the query.
   */
  protected function happensOn($startDateField, $endDateField, $value) {
    $andCondition = (new Condition('AND'));
    $andCondition
      ->condition($startDateField, $value, '<=')
      ->condition($endDateField, $value, '>=');
    $this->query->addWhere($this->options['group'], $andCondition);
  }

  /**
   * Adding happens between filter to the query.
   */
  protected function happensBetween($startDateField, $endDateField, $start, $end) {
    $andCondition = (new Condition('AND'));
    $andCondition
      ->condition($startDateField, $start, '>')
      ->condition($endDateField, $end, '<');
    $this->query->addWhere($this->options['group'], $andCondition);
  }

  /**
   * Adding happens between inclusive filter to the query.
   */
  protected function happensBetweenInclusive($startDateField, $endDateField, $start, $end) {
    $andCondition = (new Condition('AND'));
    $andCondition
      ->condition($startDateField, $start, '>=')
      ->condition($endDateField, $end, '<=');
    $this->query->addWhere($this->options['group'], $andCondition);
  }

  /**
   * Adding happens during filter to the query.
   */
  protected function happensDuring($startDateField, $endDateField, $start, $end) {
    $andCondition = new Condition('AND');
    $andCondition
      ->condition($startDateField, $end, '<')
      ->condition($endDateField, $start, '>');
    $this->query->addWhere($this->options['group'], $andCondition);
  }

  /**
   * Adding happens during inclusive filter to the query.
   */
  protected function happensDuringInclusive($startDateField, $endDateField, $start, $end) {
    $andCondition = new Condition('AND');
    $andCondition
      ->condition($startDateField, $end, '<=')
      ->condition($endDateField, $start, '>=');
    $this->query->addWhere($this->options['group'], $andCondition);
  }

}
