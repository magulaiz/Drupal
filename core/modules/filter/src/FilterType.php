<?php

declare(strict_types=1);

namespace Drupal\filter;

/**
 * Defines the possible filter types.Reversible transformation filters.
 */
enum FilterType {

  /**
   * Non-HTML markup language filters that generate HTML.
   */
  case MarkupLanguage;

  /**
   * HTML tag and attribute restricting filters.
   */
  case HtmlRestrictor;

  /**
   * Reversible transformation filters.
   */
  case TransformReversible;

  /**
   * Irreversible transformation filters.
   */
  case TransformIrreversible;

  /**
   * Backwards compatibility with legacy integer values.
   *
   * @param int $legacyInt
   *   The legacy integer value from \Drupal\filter\Plugin\FilterInterface.
   */
  public static function fromLegacyInt(int $legacyInt): self {
    // @todo trigger deprecation warning.
    return match ($legacyInt) {
      0 => self::MarkupLanguage,
      1 => self::HtmlRestrictor,
      2 => self::TransformReversible,
      3 => self::TransformIrreversible,
      default => throw new \InvalidArgumentException("Invalid legacy integer value: $legacyInt"),
    };
  }

}
