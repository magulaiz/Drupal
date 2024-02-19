<?php

namespace Drupal\Core\Render\Element;

use Drupal\Core\Extension\Requirement\RequirementSeverity;
use Drupal\Core\Render\Attribute\RenderElement;

/**
 * Creates status report page element.
 */
#[RenderElement('status_report')]
class StatusReport extends RenderElementBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#theme' => 'status_report_grouped',
      '#priorities' => [
        'error',
        'warning',
        'checked',
        'ok',
      ],
      '#pre_render' => [
        [$class, 'preRenderGroupRequirements'],
      ],
    ];
  }

  /**
   * #pre_render callback to group requirements.
   */
  public static function preRenderGroupRequirements($element) {
    $grouped_requirements = [];
    RequirementSeverity::convertLegacyIntSeveritiesToEnums($element['#requirements']);
    /** @var array{title: \Drupal\Core\StringTranslation\TranslatableMarkup, value: mixed, description: \Drupal\Core\StringTranslation\TranslatableMarkup, severity: \Drupal\Core\Extension\Requirement\RequirementSeverity} $requirement */
    foreach ($element['#requirements'] as $key => $requirement) {
      $severity = RequirementSeverity::INFO;
      if (isset($requirement['severity'])) {
        $requirement_severity = $requirement['severity'] === RequirementSeverity::OK ? RequirementSeverity::INFO : $requirement['severity'];
        $severity = $requirement_severity;
      }
      elseif (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE == 'install') {
        $severity = RequirementSeverity::OK;
      }

      $grouped_requirements[$severity->status()]['title'] = $severity->title();
      $grouped_requirements[$severity->status()]['type'] = $severity->status();
      $grouped_requirements[$severity->status()]['items'][$key] = $requirement;
    }

    // Order the grouped requirements by a set order.
    $order = array_flip($element['#priorities']);
    uksort($grouped_requirements, function ($a, $b) use ($order) {
      return $order[$a] <=> $order[$b];
    });

    $element['#grouped_requirements'] = $grouped_requirements;

    return $element;
  }

  /**
   * Gets the severities.
   *
   * @return array
   *
   * @deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. There is no
   *   replacement.
   *
   * @see https://www.drupal.org/node/3410939
   */
  public static function getSeverities() {
    @\trigger_error('Calling ' . __METHOD__ . '() is deprecated in drupal:10.3.0 and is removed from in drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/3410939', \E_USER_DEPRECATED);
    return [
      RequirementSeverity::INFO->value => [
        'title' => t('Checked', [], ['context' => 'Examined']),
        'status' => 'checked',
      ],
      RequirementSeverity::OK->value => [
        'title' => t('OK'),
        'status' => 'ok',
      ],
      RequirementSeverity::WARNING->value => [
        'title' => t('Warnings found'),
        'status' => 'warning',
      ],
      RequirementSeverity::ERROR->value => [
        'title' => t('Errors found'),
        'status' => 'error',
      ],
    ];
  }

}
