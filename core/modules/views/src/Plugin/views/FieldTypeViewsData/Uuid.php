<?php

namespace Drupal\views\Plugin\views\FieldTypeViewsData;

use Drupal\views\FieldTypeViewsData;

/**
 * Defines views data for fields of type "uuid".
 *
 * @ViewsFieldData(
 *   id = "uuid",
 *   field = {
 *     "click sortable" = FALSE,
 *   },
 *   argument = {
 *     "id" = "string",
 *   },
 *   filter = {
 *     "id" = "string",
 *   }
 * )
 */
class Uuid extends FieldTypeViewsData {

}
