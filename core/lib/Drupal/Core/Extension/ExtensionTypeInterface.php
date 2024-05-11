<?php

namespace Drupal\Core\Extension;

/**
 * Interface for supported extension types.
 */
interface ExtensionTypeInterface {

  public const MODULE = 'module';
  public const THEME = 'theme';
  public const PROFILE = 'profile';
  public const THEME_ENGINE = 'theme_engine';

}
