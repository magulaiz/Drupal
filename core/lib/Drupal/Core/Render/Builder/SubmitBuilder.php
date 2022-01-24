<?php

/**
 * @file
 * Contains Drupal\Core\Render\Builder\SubmitBuilder.
 */

namespace Drupal\Core\Render\Builder;

/**
 * Builder class for the 'submit' element.
 */
class SubmitBuilder extends Button {

  protected $renderable = ['#type' => 'submit'];

}
