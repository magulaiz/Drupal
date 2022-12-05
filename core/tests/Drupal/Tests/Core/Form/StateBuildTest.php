<?php

namespace Drupal\Tests\Core\Form;

use Drupal\Core\Form\States\StateInterface;
use Drupal\Core\Form\States\WatcherInterface;
use Drupal\Core\Form\States\StatesBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Form\States\StatesBuilder
 * @group Form
 */
class StateBuildTest extends UnitTestCase {

  /**
   * @var \Drupal\Core\Form\States\StatesBuilder
   */
  protected StatesBuilder $builder;

  protected function setUp(): void {
    parent::setUp();
    $this->builder = StatesBuilder::create();
  }

  public function testAllRemoteConditions(): void {
    $result = $this->builder->addStates(
      $this->builder->state()->setChecked([
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
          ->isEmpty(),
      ])
    )->toArray();
    $expected = [
      StateInterface::CHECKED => [
        '.container' => [
          WatcherInterface::READWRITE => TRUE,
          WatcherInterface::VALID => TRUE,
          WatcherInterface::TOUCHED => FALSE,
          WatcherInterface::RELEVANT => TRUE,
          WatcherInterface::EXPANDED => TRUE,
          WatcherInterface::CHECKED => TRUE,
          WatcherInterface::FILLED => TRUE,
          WatcherInterface::VALUE => 'one',
        ],
        '.container-opposite' => [
          WatcherInterface::READONLY => TRUE,
          WatcherInterface::INVALID => TRUE,
          WatcherInterface::UNTOUCHED => FALSE,
          WatcherInterface::IRRELEVANT => TRUE,
          WatcherInterface::COLLAPSED => TRUE,
          WatcherInterface::UNCHECKED => TRUE,
          WatcherInterface::EMPTY => TRUE,
        ],
      ],
    ];
    $this->assertSame($expected, $result);
  }

  public function testAllStates(): void {
    $same_remote_conditions = [$this->builder
      ->watch('.container')
      ->isReadwrite()];
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
      '.container' => [WatcherInterface::READWRITE => TRUE],
    ];
    $expected = [
      StateInterface::CHECKED => $expected_remote_condition,
      StateInterface::UNCHECKED => $expected_remote_condition,
      StateInterface::VALID => $expected_remote_condition,
      StateInterface::INVALID => $expected_remote_condition,
      StateInterface::RELEVANT => $expected_remote_condition,
      StateInterface::IRRELEVANT => $expected_remote_condition,
      StateInterface::ENABLED => $expected_remote_condition,
      StateInterface::DISABLED => $expected_remote_condition,
      StateInterface::TOUCHED => $expected_remote_condition,
      StateInterface::UNTOUCHED => $expected_remote_condition,
      StateInterface::VISIBLE => $expected_remote_condition,
      StateInterface::INVISIBLE => $expected_remote_condition,
      '_impossible' => $expected_remote_condition,
    ];
    $this->assertSame($expected, $result);
  }

  public function testConditions() {

  }

}
