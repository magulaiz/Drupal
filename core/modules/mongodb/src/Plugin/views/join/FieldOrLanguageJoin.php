<?php

namespace Drupal\mongodb\Plugin\views\join;

use Drupal\views\Plugin\views\join\FieldOrLanguageJoin as CoreFieldOrLanguageJoin;

/**
 * Overrides the views join plugin "field_or_language_join".
 */
class FieldOrLanguageJoin extends CoreFieldOrLanguageJoin {

  use JoinPluginTrait;

}
