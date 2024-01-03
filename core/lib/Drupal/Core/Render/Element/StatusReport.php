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
    $severities = static::getSeverities();
    $grouped_requirements = [];
    foreach ($element['#requirements'] as $key => $requirement) {
      $severity = $severities[RequirementSeverity::INFO->value];
      if (isset($requirement['severity'])) {
        if (is_int($requirement['severity'])) {
          @\trigger_error('Calling ' . __METHOD__ . '() with \'severity\' as int values instead of RequirementSeverity enums is deprecated in drupal:10.3.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3410939', \E_USER_DEPRECATED);
          $requirement['severity'] = RequirementSeverity::from($requirement['severity']);
        }
        $requirement_severity = $requirement['severity'] === RequirementSeverity::OK ? RequirementSeverity::INFO : $requirement['severity'];
        $severity = $severities[$requirement_severity->value];
      }
      elseif (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE == 'install') {
        $severity = $severities[RequirementSeverity::OK->value];
      }

      $grouped_requirements[$severity['status']]['title'] = $severity['title'];
      $grouped_requirements[$severity['status']]['type'] = $severity['status'];
      $grouped_requirements[$severity['status']]['items'][$key] = $requirement;
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
   */
  public static function getSeverities() {
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
