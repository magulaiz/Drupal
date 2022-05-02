<?php

namespace Drupal\views\Plugin\views\field_views_data;

use Drupal\views\FieldViewsDataPluginBase;

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
class Uuid extends FieldViewsDataPluginBase {

}
