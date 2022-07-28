<?php

namespace Drupal\user;

use Drupal\Core\Field\FieldItemList;

/**
 * Field item list class for user last login computed field.
 */
class UserLastLoginFieldItemList extends FieldItemList {

  use UserTimestampTrait;

}
