<?php

namespace Drupal\system\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\MaintenanceModeInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Url;
use Drupal\user\PermissionHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure maintenance settings for this site.
 *
 * @internal
 */
class SiteMaintenanceModeForm extends ConfigFormBase {

  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The permission handler.
   *
   * @var \Drupal\user\PermissionHandlerInterface
   */
  protected $permissionHandler;

  /**
   * The maintenance mode service.
   *
   * @var \Drupal\Core\Site\MaintenanceModeInterface
   */
  protected $maintenanceMode;

  /**
   * Constructs a new SiteMaintenanceModeForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state keyvalue collection to use.
   * @param \Drupal\user\PermissionHandlerInterface $permission_handler
   *   The permission handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state, PermissionHandlerInterface $permission_handler, MaintenanceModeInterface $maintenance_mode = NULL) {
    parent::__construct($config_factory);
    // @ToDo: Remove state service injection before drupal:10.0.0.
    $this->state = $state;
    $this->permissionHandler = $permission_handler;
    if ($maintenance_mode === NULL) {
      $maintenance_mode = \Drupal::service('maintenance_mode');
    }
    $this->maintenanceMode = $maintenance_mode;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state'),
      $container->get('user.permissions'),
      $container->get('maintenance_mode')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'system_site_maintenance_mode';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['system.maintenance'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('system.maintenance');
    $permissions = $this->permissionHandler->getPermissions();
    $permission_label = $permissions['access site in maintenance mode']['title'];
    $form['maintenance_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Put site into maintenance mode'),
      '#default_value' => $this->maintenanceMode->isEnabled(),
      '#description' => $this->t('Visitors will only see the maintenance mode message. Only users with the "@permission-label" <a href=":permissions-url">permission</a> will be able to access the site. Authorized users can log in directly via the <a href=":user-login">user login</a> page.', ['@permission-label' => $permission_label, ':permissions-url' => Url::fromRoute('user.admin_permissions')->toString(), ':user-login' => Url::fromRoute('user.login')->toString()]),
    ];
    $form['maintenance_mode_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message to display when in maintenance mode'),
      '#default_value' => $config->get('message'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('system.maintenance')
      ->set('message', $form_state->getValue('maintenance_mode_message'))
      ->save();

    if ($form_state->getValue('maintenance_mode')) {
      $this->maintenanceMode->enable();
    }
    else {
      $this->maintenanceMode->disable();
    }
    parent::submitForm($form, $form_state);
  }

}
