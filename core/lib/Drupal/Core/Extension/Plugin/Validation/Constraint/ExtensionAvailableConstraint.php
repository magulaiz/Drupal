<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Check if a module is available.
 */
#[Constraint(
  id: 'ExtensionAvailable',
  label: new TranslatableMarkup('Extension exists', [], ['context' => 'Validation'])
)]
class ExtensionAvailableConstraint extends SymfonyConstraint {

  /**
   * The error message for a non-existent module.
   *
   * @var string
   */
  public string $moduleNotExistsMessage = "Module '@name' does not exist.";

  /**
   * The error message for a non-existent theme.
   *
   * @var string
   */
  public string $themeNotExistsMessage = "Theme '@name' does not exist.";

  /**
   * The error message for a non-existent profile.
   *
   * @var string
   */
  public string $profileNotExistsMessage = "Profile '@name' does not exist.";

  /**
   * The type of extension to look for. Can be 'module' or 'theme'.
   *
   * @var string
   */
  public string $type;

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions(): array {
    return ['type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption(): ?string {
    return 'type';
  }

}
