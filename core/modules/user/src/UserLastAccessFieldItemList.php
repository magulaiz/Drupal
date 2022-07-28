<?php

namespace Drupal\user;

use Drupal\Core\Field\FieldItemList;

/**
 * Field item list class for user last access computed field.
 */
class UserLastAccessFieldItemList extends FieldItemList {

  use UserTimestampTrait;

}
