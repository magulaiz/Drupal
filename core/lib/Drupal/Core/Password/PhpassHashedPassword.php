<?php

namespace Drupal\Core\Password;

use Drupal\phpass\Password\PhpassHashedPassword as PasswordCompatibility;

@trigger_error('\Drupal\Core\Password\PhpassHashedPassword is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. The password compatibility service has been moved to the phpass module. deprecated Use \Drupal\phpass\Password\PhpassHashedPassword instead.', E_USER_DEPRECATED);

/**
 * Deprecated legacy password hashing framework.
 *
 * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. The
 *   password compatibility service has been moved to the phpass module.
 *   Use \Drupal\phpass\Password\PhpassHashedPassword instead.
 *
 * @see https://www.drupal.org/node/3322420
 */
class PhpassHashedPassword extends PasswordCompatibility {}
