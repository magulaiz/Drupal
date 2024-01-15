<?php

namespace Drupal\system\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display 'Site branding' elements.
 *
 * @Block(
 *   id = "system_cron_status_block",
 *   admin_label = @Translation("Cron status"),
 *   forms = {
 *     "settings_tray" = "Drupal\system\Form\SystemBrandingOffCanvasForm",
 *   },
 * )
 */
class CronStatusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a SystemBrandingBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $cron_config = $this->configFactory->get('system.cron');
    // Cron warning threshold defaults to two days.
    $threshold_warning = $cron_config->get('threshold.requirements_warning');
    // Cron error threshold defaults to two weeks.
    $threshold_error = $cron_config->get('threshold.requirements_error');

    // Determine when cron last ran.
    $cron_last = \Drupal::state()->get('system.cron_last');
    if (!is_numeric($cron_last)) {
      $cron_last = \Drupal::state()->get('install_time', 0);
    }

    // Determine severity based on time since cron last ran.
    $severity = -1;
    $request_time = \Drupal::time()->getRequestTime();
    if ($request_time - $cron_last > $threshold_error) {
      $severity = 2;
    }
    elseif ($request_time - $cron_last > $threshold_warning) {
      $severity = 1;
    }

    // Set summary and description based on values determined above.
    $summary = t('Last run @time ago', ['@time' => \Drupal::service('date.formatter')->formatTimeDiffSince($cron_last)]);


    $x['cron'] = [
      'title' => t('Cron maintenance tasks'),
      'severity' => $severity,
      'value' => $summary,
    ];
    $data['cron']['value'] = $summary;
    if ($severity != -1) {
      $data['cron']['description'][] = [
        [
          '#markup' => t('Cron has not run recently.'),
          '#suffix' => ' ',
        ],
        [
          '#markup' => t('For more information, see the online handbook entry for <a href=":cron-handbook">configuring cron jobs</a>.', [':cron-handbook' => 'https://www.drupal.org/cron']),
          '#suffix' => ' ',
        ],
      ];
    }
    $data['cron']['description'][] = [
      [
        '#type' => 'link',
        '#prefix' => '(',
        '#title' => t('more information'),
        '#suffix' => ')',
        '#url' => Url::fromRoute('system.cron_settings'),
      ],
      [
        '#prefix' => '<span class="cron-description__run-cron">',
        '#suffix' => '</span>',
        '#type' => 'link',
        '#title' => t('Run cron'),
        '#url' => Url::fromRoute('system.run_cron'),
        '#attributes' => [
          'class' => ['button', 'button--small', 'button--primary', 'system-status-general-info__run-cron'],
        ],
      ],
    ];

    $build['info'] = [
      '#theme' => 'status_report_general_info_cron',
      '#cron' => $data['cron'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->configFactory->get('system.cron')->getCacheTags()
    );
  }

}
