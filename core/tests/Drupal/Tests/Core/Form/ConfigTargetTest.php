<?php

namespace Drupal\Tests\Core\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\ConfigTarget;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Form\ConfigTarget
 * @group Form
 */
class ConfigTargetTest extends UnitTestCase {

  /**
   * @covers \Drupal\Core\Form\ConfigFormBase::storeConfigKeyToFormElementMap
   */
  public function testDuplicateTargetsNotAllowed(): void {
    $form = [
      'test' => [
        '#type' => 'text',
        '#default_value' => 'A test',
        '#config_target' => new ConfigTarget('system.site', 'admin_compact_mode', 'intval', 'boolval'),
        '#name' => 'test',
        '#array_parents' => ['test'],
      ],
      'duplicate' => [
        '#type' => 'text',
        '#config_target' => new ConfigTarget('system.site', 'admin_compact_mode', 'intval', 'boolval'),
        '#name' => 'duplicate',
        '#array_parents' => ['duplicate'],
      ],
    ];

    $test_form = new class(
      $this->prophesize(ConfigFactoryInterface::class)->reveal(),
      $this->prophesize(TypedConfigManagerInterface::class)->reveal(),
    ) extends ConfigFormBase {
      use RedundantEditableConfigNamesTrait;

      public function getFormId() {
        return 'test';
      }

    };
    $form_state = new FormState();
    $test_form->storeConfigKeyToFormElementMap($form['test'], $form_state);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Two #config_targets both target "admin_compact_mode" in the "system.site" config: `$form[\'test\']` and `$form[\'duplicate\']`.');
    $test_form->storeConfigKeyToFormElementMap($form['duplicate'], $form_state);
  }

  /**
   * @covers ::fromForm
   * @covers ::fromString
   */
  public function testFromFormString() {
    $form = [
      'group' => [
        '#type' => 'details',
        'test' => [
          '#type' => 'text',
          '#default_value' => 'A test',
          '#config_target' => 'system.site:name',
          '#name' => 'test',
          '#parents' => ['test'],
        ],
      ],
    ];
    $config_target = ConfigTarget::fromForm(['group', 'test'], $form);
    $this->assertSame('system.site', $config_target->configName);
    $this->assertSame('name', $config_target->propertyPath);
    $this->assertSame(['test'], $config_target->elementParents);
  }

  /**
   * @covers ::fromForm
   */
  public function testFromFormConfigTarget() {
    $form = [
      'test' => [
        '#type' => 'text',
        '#default_value' => 'A test',
        '#config_target' => new ConfigTarget('system.site', 'admin_compact_mode', 'intval', 'boolval'),
        '#name' => 'test',
        '#parents' => ['test'],
      ],
    ];
    $config_target = ConfigTarget::fromForm(['test'], $form);
    $this->assertSame('system.site', $config_target->configName);
    $this->assertSame('admin_compact_mode', $config_target->propertyPath);
    $this->assertSame(['test'], $config_target->elementParents);
    $this->assertSame(1, ($config_target->fromConfig)(TRUE));
    $this->assertFalse(($config_target->toConfig)('0'));
  }

  /**
   * @covers ::fromForm
   * @dataProvider providerTestFromFormException
   */
  public function testFromFormException(array $form, array $array_parents, string $exception_message) {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage($exception_message);
    ConfigTarget::fromForm($array_parents, $form);
  }

  public function providerTestFromFormException(): array {
    return [
      'No #config_target' => [
        [
          'test' => [
            '#type' => 'text',
            '#default_value' => 'A test',
          ],
        ],
        ['test'],
        'The form element [test] does not have the #config_target property set',
      ],
      'No #config_target nested' => [
        [
          'group' => [
            '#type' => 'details',
            'test' => [
              '#type' => 'text',
              '#default_value' => 'A test',
            ],
          ],
        ],
        ['group', 'test'],
        'The form element [group][test] does not have the #config_target property set',
      ],
      'Boolean #config_target nested' => [
        [
          'group' => [
            '#type' => 'details',
            'test' => [
              '#type' => 'text',
              '#config_target' => FALSE,
              '#default_value' => 'A test',
            ],
          ],
        ],
        ['group', 'test'],
        'The form element [group][test] #config_target property is not a string or a ConfigTarget object',
      ],
    ];
  }

}
