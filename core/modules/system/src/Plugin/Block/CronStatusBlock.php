<?php

namespace Drupal\system\Plugin\Block;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display Cron status.
 *
 * @Block(
 *   id = "system_cron_status_block",
 *   admin_label = @Translation("Cron status"),
 * )
 */
class CronStatusBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new CronStatusBlock instance.
   *
   * @param array $configuration
   *   An array of configuration settings.
   * @param mixed $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\Time $time
   *   The time service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected FormBuilderInterface $formBuilder, protected ConfigFactoryInterface $configFactory, protected StateInterface $state, protected Time $time, protected DateFormatterInterface $dateFormatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('config.factory'),
      $container->get('state'),
      $container->get('datetime.time'),
      $container->get('date.formatter')
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
    $cron_last =  $this->state->get('system.cron_last');
    if (!is_numeric($cron_last)) {
      $cron_last =  $this->state->get('install_time', 0);
    }

    // Determine severity based on time since cron last ran.
    $severity = -1;
    $request_time =  $this->time->getRequestTime();
    if ($request_time - $cron_last > $threshold_error) {
      $severity = 2;
    }
    elseif ($request_time - $cron_last > $threshold_warning) {
      $severity = 1;
    }

    // Set summary and description based on values determined above.
    $summary = $this->t('Last run @time ago', ['@time' => $this->dateFormatter->formatTimeDiffSince($cron_last)]);


    $x['cron'] = [
      'title' => $this->t('Cron maintenance tasks'),
      'severity' => $severity,
      'value' => $summary,
    ];
    $data['cron']['value'] = $summary;
    if ($severity != -1) {
      $data['cron']['description'][] = [
        [
          '#markup' => $this->t('Cron has not run recently.'),
          '#suffix' => ' ',
        ],
        [
          '#markup' => $this->t('For more information, see the online handbook entry for <a href=":cron-handbook">configuring cron jobs</a>.', [':cron-handbook' => 'https://www.drupal.org/cron']),
          '#suffix' => ' ',
        ],
      ];
    }
    $data['cron']['description'][] = [
      [
        '#type' => 'link',
        '#prefix' => '(',
        '#title' => $this->t('more information'),
        '#suffix' => ')',
        '#url' => Url::fromRoute('system.cron_settings'),
      ],
      [
        '#prefix' => '<span class="cron-description__run-cron">',
        '#suffix' => '</span>',
        '#type' => 'link',
        '#title' => $this->t('Run cron'),
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
