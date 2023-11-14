<?php

declare(strict_types=1);

namespace Drupal\locale;

/**
 * Enumeration of the types of overrides of existing translations.
 */
enum TranslationOverrideType: string {

  // Override any translation.
  // @see \LOCALE_TRANSLATION_OVERWRITE_ALL
  case All = 'all';

  // Only override non-customized translations.
  // @see LOCALE_TRANSLATION_OVERWRITE_NON_CUSTOMIZED
  case NonCustomized = 'non_customized';

  // Don't override existing translations.
  // @see LOCALE_TRANSLATION_OVERWRITE_NONE
  case None = 'none';

}
