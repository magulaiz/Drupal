<?php

declare(strict_types=1);

namespace Drupal\path\PathVariant;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\path\Enum\PathVariantEnumInterface;

/**
 * Represents a path variant for a bundle.
 */
final class PathVariant {

  /**
   * Represents the default variant.
   */
  public const DEFAULT = 'default';

  private function __construct(
    private PathVariantEnumInterface|string $variant,
    private \Stringable|string $label,
  ) {
  }

  /**
   * Represents a path variant for a bundle.
   */
  public static function create(
    PathVariantEnumInterface|string $variant,
    \Stringable|string $label,
  ): static {
    return new static($variant, $label);
  }

  /**
   * Create a variant representing the default for an entity.
   *
   * @internal
   *   Not for public use.
   */
  public static function createDefault(): static {
    return new static(CorePathVariants::Default, new TranslatableMarkup('Default'));
  }

  /**
   * The variant, either a string or enum.
   *
   * Implementations of static variants should use enums, variants backed by
   * user configuration may choose to use string.
   *
   * @return \Drupal\path\Enum\PathVariantEnumInterface|string
   */
  public function getVariant(): PathVariantEnumInterface|string {
    return $this->variant;
  }

  /**
   * Convert the variant to a string.
   *
   * Used as the value of the PathAlias variant field.
   */
  public function getVariantStringed(): string {
    return $this->variant instanceof PathVariantEnumInterface ? $this->variant->getMachineName() : $this->variant;
  }

  /**
   * Get the user-facing label for this variant.
   */
  public function getLabel(): \Stringable|string {
    return $this->label;
  }

  /**
   * Converts the variant to a string.
   *
   * Primarily used as the value of the PathAlias variant field and
   * serialization.
   */
  public function __toString(): string {
    return $this->getVariantStringed();
  }

}
