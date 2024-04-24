<?php

namespace Drupal\KernelTests\Core\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\KernelTests\KernelTestBase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\Core\Plugin\PluginFormManager
 * @group Plugin
 */
class PluginFormManagerTest extends KernelTestBase implements FormInterface, PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['plugin_test'];

  /**
   * @covers ::buildForm
   * @covers ::getFormObject
   */
  public function testBuildForm(): void {
    $form_builder = $this->container->get('form_builder');

    $plugin_form = $this->prophesize(PluginInspectionInterface::class)->willImplement(PluginFormInterface::class);
    $plugin_form->getPluginId()->willReturn('plugin_form');
    $plugin_form->buildConfigurationForm(Argument::cetera())->willReturn(['#markup' => 'plugin form']);
    $form = $form_builder->getForm($this, $plugin_form->reveal());
    $expected = [
      '#markup' => 'plugin form',
      '#plugin_test_plugin_subform_alter' => TRUE,
    ];
    $this->assertSame($expected, array_intersect_key($form['settings'], $expected));

    $plugin_with_forms = $this->prophesize(PluginWithFormsInterface::class);
    $plugin_with_forms->getPluginId()->willReturn('plugin_with_forms');
    $plugin_with_forms->hasFormClass('configure')->willReturn(TRUE);
    $plugin_with_forms->getFormClass('configure')->willReturn(static::class);
    $form = $form_builder->getForm($this, $plugin_with_forms->reveal());
    $expected['#markup'] = 'plugin with forms';
    $this->assertSame($expected, array_intersect_key($form['settings'], $expected));

    $plugin_form_with_forms = $this->prophesize(PluginWithFormsInterface::class)->willImplement(PluginFormInterface::class);
    $plugin_form_with_forms->getPluginId()->willReturn('plugin_form_with_forms');
    $plugin_form_with_forms->hasFormClass('configure')->willReturn(TRUE);
    $plugin_form_with_forms->getFormClass('configure')->willReturn(static::class);
    $plugin_form_with_forms->buildConfigurationForm(Argument::cetera())->shouldNotBeCalled();
    $form = $form_builder->getForm($this, $plugin_form_with_forms->reveal());
    $this->assertSame($expected, array_intersect_key($form['settings'], $expected));
  }

  /**
   * @covers ::getFormObject
   */
  public function testBuildFormException(): void {
    $form_builder = $this->container->get('form_builder');
    $foo_plugin = $this->prophesize(PluginInspectionInterface::class);
    $foo_plugin->getPluginId()->willReturn('foo');
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The "foo" plugin does not provide a "configure" form');
    $form_builder->getForm($this, $foo_plugin->reveal());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'test_plugin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, PluginInspectionInterface $plugin = NULL) {
    $form_state->set('plugin', $plugin);
    $form['#tree'] = TRUE;
    $form['settings'] = [];
    $subform_state = SubformState::createForSubform($form['settings'], $form, $form_state);
    $form['settings'] = $this->container->get('plugin_form.manager')->buildForm($form['settings'], $subform_state, $plugin, 'configure');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $plugin = $form_state->get('plugin');
    $subform_state = SubformState::createForSubform($form['settings'], $form, $form_state);
    $this->container->get('plugin_form.manager')->validateForm($form['settings'], $subform_state, $plugin, 'configure');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $plugin = $form_state->get('plugin');
    $subform_state = SubformState::createForSubform($form['settings'], $form, $form_state);
    $this->container->get('plugin_form.manager')->submitForm($form['settings'], $subform_state, $plugin, 'configure');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#markup'] = 'plugin with forms';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
