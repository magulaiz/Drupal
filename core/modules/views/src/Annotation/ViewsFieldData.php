<?php

namespace Drupal\views\Annotation;

/**
 * Defines a Plugin annotation object for views data of fields.
 *
 * @Annotation
 */
class ViewsFieldData extends ViewsPluginAnnotationBase {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The field handler definition.
   *
   * @var array
   */
  public $field = [];

  /**
   * The argument handler definition.
   *
   * @var array
   */
  public $argument = [];

  /**
   * The sort handler definition.
   *
   * @var array
   */
  public $sort = [];

  /**
   * The filter handler definition.
   *
   * @var array
   */
  public $filter = [];

}
