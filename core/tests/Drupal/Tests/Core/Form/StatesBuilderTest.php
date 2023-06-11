<?php

namespace Drupal\Tests\Core\Form;

use Drupal\Core\Form\States\FormElementStateInterface;
use Drupal\Core\Form\States\FormElementWatcherInterface;
use Drupal\Core\Form\States\FormElementStatesBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Form\States\FormElementStatesBuilder
 * @group Form
 */
class StatesBuilderTest extends UnitTestCase {

  /**
   * States builder.
   *
   * @var \Drupal\Core\Form\States\FormElementStatesBuilder
   */
  protected FormElementStatesBuilder $builder;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->builder = new FormElementStatesBuilder();
  }

  /**
   * Test remote conditions.
   */
  public function testAllRemoteConditions(): void {
    $result = $this->builder->addStates(
      $this->builder->state()->setChecked(
        $this->builder->watch('.container')
          ->isReadwrite()
          ->isValid()
          ->isTouched(FALSE)
          ->isRelevant()
          ->isExpanded()
          ->isChecked()
          ->isFilled()
          ->valueEqualTo('one'),
        $this->builder->watch('.container-opposite')
          ->isReadonly()
          ->isInvalid()
          ->isUntouched(FALSE)
          ->isIrrelevant()
          ->isCollapsed()
          ->isUnchecked()
          ->isEmpty()
      )
    )->toArray();
    $expected = [
      FormElementStateInterface::CHECKED => [
        '.container' => [
          FormElementWatcherInterface::READWRITE => TRUE,
          FormElementWatcherInterface::VALID => TRUE,
          FormElementWatcherInterface::TOUCHED => FALSE,
          FormElementWatcherInterface::RELEVANT => TRUE,
          FormElementWatcherInterface::EXPANDED => TRUE,
          FormElementWatcherInterface::CHECKED => TRUE,
          FormElementWatcherInterface::FILLED => TRUE,
          FormElementWatcherInterface::VALUE => 'one',
        ],
        '.container-opposite' => [
          FormElementWatcherInterface::READONLY => TRUE,
          FormElementWatcherInterface::INVALID => TRUE,
          FormElementWatcherInterface::UNTOUCHED => FALSE,
          FormElementWatcherInterface::IRRELEVANT => TRUE,
          FormElementWatcherInterface::COLLAPSED => TRUE,
          FormElementWatcherInterface::UNCHECKED => TRUE,
          FormElementWatcherInterface::EMPTY => TRUE,
        ],
      ],
    ];
    $this->assertSame($expected, $result);
  }

  /**
   * Test documented states.
   */
  public function testAllStates(): void {
    $same_remote_conditions = $this->builder
      ->watch('.container')
      ->isReadwrite();
    $result = $this->builder->addStates(
      $this->builder->state()->setChecked($same_remote_conditions),
      $this->builder->state()->setUnchecked($same_remote_conditions),
      $this->builder->state()->setValid($same_remote_conditions),
      $this->builder->state()->setInvalid($same_remote_conditions),
      $this->builder->state()->setRelevant($same_remote_conditions),
      $this->builder->state()->setIrrelevant($same_remote_conditions),
      $this->builder->state()->setEnabled($same_remote_conditions),
      $this->builder->state()->setDisabled($same_remote_conditions),
      $this->builder->state()->setTouched($same_remote_conditions),
      $this->builder->state()->setUntouched($same_remote_conditions),
      $this->builder->state()->setVisible($same_remote_conditions),
      $this->builder->state()->setInvisible($same_remote_conditions),
      $this->builder->state()->setCustomState('_impossible', $same_remote_conditions),
    )->toArray();
    $expected_remote_condition = [
      '.container' => [FormElementWatcherInterface::READWRITE => TRUE],
    ];
    $expected = [
      FormElementStateInterface::CHECKED => $expected_remote_condition,
      FormElementStateInterface::UNCHECKED => $expected_remote_condition,
      FormElementStateInterface::VALID => $expected_remote_condition,
      FormElementStateInterface::INVALID => $expected_remote_condition,
      FormElementStateInterface::RELEVANT => $expected_remote_condition,
      FormElementStateInterface::IRRELEVANT => $expected_remote_condition,
      FormElementStateInterface::ENABLED => $expected_remote_condition,
      FormElementStateInterface::DISABLED => $expected_remote_condition,
      FormElementStateInterface::TOUCHED => $expected_remote_condition,
      FormElementStateInterface::UNTOUCHED => $expected_remote_condition,
      FormElementStateInterface::VISIBLE => $expected_remote_condition,
      FormElementStateInterface::INVISIBLE => $expected_remote_condition,
      '_impossible' => $expected_remote_condition,
    ];
    $this->assertSame($expected, $result);
  }

  /**
   * Test multiple remote conditions.
   */
  public function testMultipleRemoteConditions() {
    $result = $this->builder->addStates(
      $this->builder->state()->setVisible(
        $this->builder->watch(':input[name="select_trigger"]')
          ->valueEqualTo('value2')
          ->valueEqualTo('value3')
      )
    )->toArray();
    $expected = [
      'visible' => [
        ':input[name="select_trigger"]' => [
          ['value' => 'value2'],
          ['value' => 'value3'],
        ],
      ],
    ];
    $this->assertSame($expected, $result);
  }

  /**
   * Test condition groups.
   */
  public function testConditionGroups() {
    $result = $this->builder->addStates(
      $this->builder->state()->setVisible(
        $this->builder->or(
          $this->builder->watch(':input[name="select"]')->valueEqualTo('1')
        ),
        $this->builder->or(
          $this->builder->watch(':input[name="number"]')->valueEqualTo('1')
        )
      ),
      $this->builder->state()->setEnabled(
        $this->builder->and(
          $this->builder->watch(':input[name="select"]')->valueEqualTo('1'),
        ),
        $this->builder->and(
          $this->builder->watch(':input[name="number"]')->valueEqualTo('1')
        )
      ),
      $this->builder->state()->setValid(
        $this->builder->xor(
          $this->builder->watch(':input[name="select"]')->valueEqualTo('1'),
        ),
        $this->builder->xor(
          $this->builder->watch(':input[name="number"]')->valueEqualTo('1')
        )
      )
    )->toArray();
    $expected = [
      'visible' => [
        [':input[name="select"]' => ['value' => '1']],
        'or',
        [':input[name="number"]' => ['value' => '1']],
      ],
      'enabled' => [
        [':input[name="select"]' => ['value' => '1']],
        'and',
        [':input[name="number"]' => ['value' => '1']],
      ],
      'valid' => [
        [':input[name="select"]' => ['value' => '1']],
        'xor',
        [':input[name="number"]' => ['value' => '1']],
      ],
    ];
    $this->assertSame($expected, $result);
  }

}
