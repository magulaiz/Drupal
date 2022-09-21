<?php

namespace Drupal\system\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure System settings for this site.
 */
class MenuLinksetSettingsForm extends ConfigFormBase {

  /**
   * The router builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * Constructs the routerBuilder service.
   *
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   */
  public function __construct(RouteBuilderInterface $router_builder) {
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_linkset_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['system.linkset'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['linkset']['enable_endpoint'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the menu linkset endpoint'),
      '#description' => $this->t('See the <a href="@docs-link">decoupled menus documentation</a> for more information.', [
          '@docs-link' => 'https://www.drupal.org/docs/develop/decoupled-drupal/decoupled-menus',
    ]),
      '#default_value' => $this->config('system.linkset')->get('enable_endpoint'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('system.linkset')
      ->set('enable_endpoint', $form_state->getValue('enable_endpoint'))
      ->save();
    $this->routerBuilder->setRebuildNeeded();
    parent::submitForm($form, $form_state);
  }

}
