<?php

namespace Drupal\user\Plugin\Validation\Constraint;

/**
 * Gets the cancel methods.
 */
class UserCancelMethodsConstraints
{
  /**
 * Gets the cancel methods.
 */
 public static function getCancelMethodChoices():array{
   return user_cancel_methods(TRUE);
 }
}
