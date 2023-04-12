<?php

namespace Drupal\toolbar\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The config form for the toolbar module.
 */
class ToolbarSettingsForm extends ConfigFormBase {

  /**
   * The cache menu instance.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheMenu;

  /**
   * The menu link manager instance.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * AdminToolbarSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory for the form.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager
   *   A menu link manager instance.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheMenu
   *   A cache menu instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MenuLinkManagerInterface $menuLinkManager, CacheBackendInterface $cacheMenu) {
    parent::__construct($config_factory);
    $this->cacheMenu = $cacheMenu;
    $this->menuLinkManager = $menuLinkManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.menu.link'),
      $container->get('cache.menu')
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return [
      'toolbar.settings',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'toolbar_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('toolbar.settings');
    $depth_values = range(1, 9);
    $form['menu_depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu depth'),
      '#description' => $this->t('Maximal depth of displayed menu.'),
      '#default_value' => $config->get('menu_depth'),
      '#options' => array_combine($depth_values, $depth_values),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('toolbar.settings')
      ->set('menu_depth', $form_state->getValue('menu_depth'))
      ->save();
    parent::submitForm($form, $form_state);
    $this->cacheMenu->invalidateAll();
    $this->menuLinkManager->rebuild();
  }

}
