<?php

namespace Drupal\mongodb\Plugin\views\argument;

use Drupal\user\Plugin\views\argument\RolesRid as CoreRolesRid;

/**
 * Overriding the views argument plugin "user__roles_rid".
 */
class RolesRid extends CoreRolesRid {

  use ManyToOneTrait;

}
