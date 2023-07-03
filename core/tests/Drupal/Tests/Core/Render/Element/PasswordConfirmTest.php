<?php

namespace Drupal\Tests\Core\Render\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\PasswordConfirm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Render\Element\PasswordConfirm
 * @group Render
 */
class PasswordConfirmTest extends UnitTestCase {

  /**
   * @covers ::valueCallback
   *
   * @dataProvider providerTestValueCallback
   */
  public function testValueCallback($expected, $element, $input) {
    $form_state = $this->prophesize(FormStateInterface::class)->reveal();
    $this->assertSame($expected, PasswordConfirm::valueCallback($element, $input, $form_state));
  }

  /**
   * Data provider for testValueCallback().
   */
  public function providerTestValueCallback() {
    $data = [];
    $data[] = ['', [], NULL];
    $data[] = ['', ['#default_value' => ['pass2' => 'value']], NULL];
    $data[] = ['', ['#default_value' => ['pass2' => 'value']], FALSE];
    $data[] = ['123456', [], ['pass1' => '123456', 'pass2' => 'qwerty']];
    $data[] = ['123', [], ['pass1' => 123, 'pass2' => 234]];
    $data[] = ['', [], ['pass1' => ['array'], 'pass2' => 234]];

    return $data;
  }

}
