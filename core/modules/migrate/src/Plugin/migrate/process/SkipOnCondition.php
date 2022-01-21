<?php

namespace Drupal\migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Skips processing the current row when the input value matches a condition.
 *
 * Available configuration keys:
 * - operation: (required) Operation to perform data comparison. Supported
 *   operations are: "==", "<", ">", "empty", "is_null", "contains". Refer to
 *   SkipOnCondition::getSupportedOperations() method for examples.
 * - compare: (optional) The value to be compared against for operators, that
 *   require additional data (i.e. "<", "==").
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - method: (optional) What to do if the input value is empty. Possible values:
 *   - row: Skips the entire row when an empty value is encountered.
 *   - process: Prevents further processing of the input property when the value
 *     is empty.
 * - message: (optional) A message to be logged in the {migrate_message_*} table
 *   for this row. Messages are only logged for the 'row' method. If not set,
 *   nothing is logged in the message table.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_condition",
 *   handle_multiples = TRUE
 * )
 */
class SkipOnCondition extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (!$op = $configuration['operation'] ?? NULL) {
      throw new \InvalidArgumentException('The "operation" must be set.');
    }
    if (!isset($this->getSupportedOperations()[$op])) {
      throw new \InvalidArgumentException('The "' . $configuration['operation'] . '" operation is not supported.');
    }

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $op = $this->configuration['operation'];
    $operation_data = $this->getSupportedOperations()[$op];

    if (isset($operation_data['requires'])) {
      foreach ($operation_data['requires'] as $required_property) {
        if (!isset($this->configuration[$required_property])) {
          throw new \InvalidArgumentException('The "' . $op . '" operation requires ' . $required_property . '" to be set.');
        }
      }
    }

    return parent::transform($value, $migrate_executable, $row, $destination_property);
  }

  /**
   * Skips the current row when value is not set.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   The input value, $value, if it is not empty.
   *
   * @throws \Drupal\migrate\MigrateSkipRowException
   *   Thrown if the source property is not set and the row should be skipped,
   *   records with STATUS_IGNORED status in the map.
   */
  public function row($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->isApplicable($value)) {
      $message = !empty($this->configuration['message']) ? $this->configuration['message'] : '';
      throw new MigrateSkipRowException($message);
    }
    return $value;
  }

  /**
   * Stops processing the current property when value is not set.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   The input value, $value, if it is not empty.
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   *   Thrown if the source property is not set and rest of the process should
   *   be skipped.
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->isApplicable($value)) {
      throw new MigrateSkipProcessException();
    }
    return $value;
  }

  /**
   * Checks whether the given value is applicable to be skipped.
   *
   * @param mixed $value
   *   The input value.
   *
   * @return bool
   *   Final comparison result, considering the negate option.
   */
  protected function isApplicable($value): bool {
    $negate = $this->configuration['negate'] ?? FALSE;
    $result = $this->compare($value, $this->configuration['operation'], $this->configuration['compared_value'] ?? NULL);
    return $negate ? !$result : $result;
  }

  /**
   * Performs the data comparison.
   *
   * @param mixed $value
   *   The input value.
   * @param $operation
   *   Comparison operation. See ::getSupportedOperations() for the list of
   *   supported operations.
   * @param $compared_value
   *   The value to be compared against process value.
   *
   * @return bool
   *   Data comparison result.
   */
  protected function compare($value, $operation, $compared_value = NULL): bool {
    switch ($operation) {
      case '==':
        return $value == $compared_value;

      case '<':
        return $value < $compared_value;

      case '>':
        return $value > $compared_value;

      case 'empty':
        return !$value;

      case 'is_null':
        return $value === NULL;

      case 'contains':
        return \is_string($value) && \str_contains($value, $compared_value) ||
          \is_array($value) && \in_array($compared_value, $value, TRUE);

      default:
        return FALSE;
    }
  }

  /**
   * Contains information about supported operations.
   *
   * @return array
   */
  protected function getSupportedOperations() {
    return [
      '==' => [],
      '<' => [
        'requires' => ['compared_value'],
      ],
      '>' => [
        'requires' => ['compared_value'],
      ],
      'empty' => [],
      'is_null' => [],
      'contains' => [
        'requires' => ['compared_value'],
        'types' => ['string', 'array'],
      ],
    ];
  }

}
