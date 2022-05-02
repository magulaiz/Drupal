<?php

namespace Drupal\views\Plugin\views\field_views_data;

use Drupal\views\FieldViewsDataPluginBase;

/**
 * Defines views data for fields of type "boolean".
 *
 * @ViewsFieldData(
 *   id = "boolean",
 *   argument = {
 *     "id" = "numeric",
 *   },
 *   filter = {
 *     "id" = "boolean",
 *   }
 * )
 */
class Boolean extends FieldViewsDataPluginBase {

}
