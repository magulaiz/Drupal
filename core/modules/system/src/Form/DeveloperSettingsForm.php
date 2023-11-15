<?php

namespace Drupal\system\Form;

use Drupal\Core\Asset\AssetCollectionOptimizerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure developer settings for this site.
 *
 * @internal
 */
class DeveloperSettingsForm extends ConfigFormBase {

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructs a DeveloperSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state, Settings $settings) {
    parent::__construct($config_factory);

    $this->state = $state;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state'),
      $container->get('settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'system_developer_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['system.developer_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'system/drupal.system';

    $form['theme_development'] = [
      '#type' => 'details',
      '#title' => $this->t('Theme development'),
      '#description' => $this->t('Warning: Do not enable these settings on production sites.'),
      '#open' => TRUE,
    ];

    // Do not allow change this setting from UI when it is overridden in
    // settings.php file.
    $twig_debug_overridden = $this->settings->get('twig_debug');
    $form['theme_development']['twig_debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Twig debug mode'),
      '#description' => $this->t("Enables Twig debug mode, which provides Twig's <code>dump()</code> function, outputs template suggestions to HTML comments, and allows to set Twig auto reload and to disable caches."),
      '#default_value' => $this->state->get('twig_debug', FALSE),
    ];

    // Add a note to description if field value is overriden in settings file.
    // Also, disable field and set overriden value as default value.
    if (!is_null($twig_debug_overridden)) {
      $form['theme_development']['twig_debug']['#description'] .= ' ' . $this->t('Overridden in settings file.');
      $form['theme_development']['twig_debug']['#disabled'] = TRUE;
      $form['theme_development']['twig_debug']['#default_value'] = $twig_debug_overridden;
    }

    // Do not allow change this setting from UI when it is overridden in
    // settings.php file.
    $twig_autoreload_overridden = $this->settings->get('twig_autoreload');
    $form['theme_development']['twig_autoreload'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Twig auto reload'),
      '#description' => $this->t('When enabled, Twig templates are recompiled when the source code changes.'),
      '#default_value' => $this->state->get('twig_autoreload', FALSE),
      '#states' => [
        'visible' => [
          'input[data-drupal-selector="edit-twig-debug"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    // Add a note to description if field value is overriden in settings file.
    // Also, disable field and set overriden value as default value.
    if (!is_null($twig_autoreload_overridden)) {
      $form['theme_development']['twig_autoreload']['#description'] .= ' ' . $this->t('Overridden in settings file.');
      $form['theme_development']['twig_autoreload']['#disabled'] = TRUE;
      $form['theme_development']['twig_autoreload']['#default_value'] = $twig_autoreload_overridden;
    }

    // Do not allow change this setting from UI when it is overridden in
    // settings.php file.
    $twig_cache_overridden = $this->settings->get('twig_cache_disable');
    $form['theme_development']['twig_cache_disable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable Twig cache'),
      '#description' => $this->t('Disables Twig, page, render and dynamic page caches.'),
      '#default_value' => $this->state->get('twig_cache_disable', FALSE),
      '#states' => [
        'visible' => [
          'input[data-drupal-selector="edit-twig-debug"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    // Add a note to description if field value is overriden in settings file.
    // Also, disable field and set overriden value as default value.
    if (!is_null($twig_cache_overridden)) {
      $form['theme_development']['twig_cache_disable']['#description'] .= ' ' . $this->t('Overridden in settings file.');
      $form['theme_development']['twig_cache_disable']['#disabled'] = TRUE;
      $form['theme_development']['twig_cache_disable']['#default_value'] = $twig_cache_overridden;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save values as disabled if they are overridden.
    $twig_debug = is_null($this->settings->get('twig_debug'))
      ? $form_state->getValue('twig_debug')
      : FALSE;
    $twig_autoreload = is_null($this->settings->get('twig_autoreload'))
      ? $form_state->getValue('twig_autoreload')
      : FALSE;
    $twig_cache_disable = is_null($this->settings->get('twig_cache_disable'))
      ? $form_state->getValue('twig_cache_disable')
      : FALSE;

    // Save the values to state.
    $this->state->set('twig_debug', (bool) $twig_debug);
    $this->state->set('twig_autoreload', (bool) $twig_autoreload);
    $this->state->set('twig_cache_disable', (bool) $twig_cache_disable);

    // Clear caches so changes make effect.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
