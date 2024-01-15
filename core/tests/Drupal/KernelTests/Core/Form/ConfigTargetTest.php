<?php

namespace Drupal\KernelTests\Core\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\ConfigTarget;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests config target.
 *
 * @group Form
 */
class ConfigTargetTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * Tests config target with a closure.
   */
  public function testSerialization(): void {
    $test_form = new class(
      $this->prophesize(ConfigFactoryInterface::class)->reveal(),
      $this->prophesize(TypedConfigManagerInterface::class)->reveal(),
    ) extends ConfigFormBase {
      use RedundantEditableConfigNamesTrait;

      public function buildForm(array $form, FormStateInterface $form_state) {
        $form['site_name'] = [
          '#type' => 'textfield',
          '#title' => 'Site name',
          '#config_target' => new ConfigTarget(
            'system.site',
            'name',
            fromConfig: fn ($value) => $value ?: 'Kittens',
          ),
        ];

        return $form;
      }

      public function getFormId() {
        return 'test';
      }

    };
    $form_builder = $this->container->get('form_builder');

    $form_state = new FormState();
    $built_form = $form_builder->getForm($test_form, $form_state);
    $form_builder->setCache($built_form['#build_id'], $built_form, $form_state);

    $cached_form = $form_builder->getCache($built_form['#build_id'], $form_state);
    $this->assertSame('Kittens', ($cached_form['site_name']['#config_target']->fromConfig)(FALSE));
  }

}
