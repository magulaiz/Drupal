<?php

namespace Drupal\element_info_test\Element;

use Drupal\Core\Render\Element\Tel;

/**
 * Provides a render element that deprecates variables.
 *
 * @RenderElement("deprecated_variables")
 */
class DeprecatedVariables extends Tel {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    $info = parent::getInfo();
    $info['#deprecations'] = [
      'size' => "'size' is deprecated in drupal:X.0.0 and is removed from drupal:Y.0.0. Use 'new_size' instead. See https://www.example.com.",
    ];
    $info['#pre_render'][] = [
        [$class, 'preRenderDeprecatedVariable'],
    ];
    return $info
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderDeprecatedVariable($element) {
    $element['#deprecations']['maxlength'] = "'maxlength' is deprecated in drupal:X.0.0 and is removed from drupal:Y.0.0. Use 'new_maxlength' instead. See https://www.example.com.";
    return $element;
  }

}
