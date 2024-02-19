<?php

declare(strict_types=1);

namespace Drupal\Core\Extension\Requirement;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The requirements severity enum.
 */
enum RequirementSeverity: int {

  /*
   * Informational message only.
   */
  case INFO = -1;

  /*
   * Requirement successfully met.
   */
  case OK = 0;

  /*
   * Warning condition; proceed but flag warning.
   */
  case WARNING = 1;

  /*
   * Error condition; abort installation.
   */
  case ERROR = 2;

  /**
   * Returns the translated title of the severity.
   */
  public function title(): TranslatableMarkup {
    return match ($this) {
      self::INFO => new TranslatableMarkup('Checked'),
      self::OK => new TranslatableMarkup('OK'),
      self::WARNING => new TranslatableMarkup('Warnings found'),
      self::ERROR => new TranslatableMarkup('Errors found'),
    };
  }

  /**
   * Returns the status of the severity.
   */
  public function status(): string {
    return match ($this) {
      self::INFO => 'checked',
      self::OK => 'ok',
      self::WARNING => 'warning',
      self::ERROR => 'error',
    };

  }

  /**
   * Determines the most severe requirement in a list of requirements.
   *
   * @param array<string, array{title: \Drupal\Core\StringTranslation\TranslatableMarkup, value: mixed, description: \Drupal\Core\StringTranslation\TranslatableMarkup, severity: \Drupal\Core\Extension\Requirement\RequirementSeverity}> $requirements
   *   An array of requirements, in the same format as is returned by
   *   hook_requirements().
   *
   * @return \Drupal\Core\Extension\Requirement\RequirementSeverity
   *   The most severe requirement.
   */
  public static function maxSeverityFromRequirements(array $requirements): RequirementSeverity {
    RequirementSeverity::convertLegacyIntSeveritiesToEnums($requirements);
    return array_reduce(
      $requirements,
      function (RequirementSeverity $severity, $requirement) {
        $requirementSeverity = $requirement['severity'] ?? RequirementSeverity::OK;
        return RequirementSeverity::from(max($severity->value, $requirementSeverity->value));
      },
      RequirementSeverity::OK
    );
  }

  /**
   * Converts legacy int value severities to enums.
   *
   * @param array<string, array{title: \Drupal\Core\StringTranslation\TranslatableMarkup, value: mixed, description: \Drupal\Core\StringTranslation\TranslatableMarkup, severity: \Drupal\Core\Extension\Requirement\RequirementSeverity}> $requirements
   *   An array of requirements, in the same format as is returned by
   *   hook_requirements().
   */
  public static function convertLegacyIntSeveritiesToEnums(array &$requirements): void {
    foreach ($requirements as &$requirement) {
      if (isset($requirement['severity']) && \is_int($requirement['severity'])) {
        @\trigger_error('Calling methods with an array of $requirements with \'severity\' as int values instead of ' . RequirementSeverity::class . ' enums is deprecated in drupal:10.3.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3410939', \E_USER_DEPRECATED);
        $requirement['severity'] = RequirementSeverity::from($requirement['severity']);
      }
    }
  }

  /**
   * Returns if the given severity is less than the current severity.
   */
  public function isMoreSevereThan(RequirementSeverity $severity): bool {
    return $this->value > $severity->value;
  }

}
