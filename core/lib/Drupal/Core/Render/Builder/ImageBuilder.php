<?php

/**
 * @file
 * Contains Drupal\Core\Render\Builder\ImageBuilder.
 */

namespace Drupal\Core\Render\Builder;

/**
 * Builder class for the 'image' element.
 */
class ImageBuilder extends BuilderBase {

  /**
   * {@inheritdoc}
   */
  protected array $renderable = ['#theme' => 'image'];

  /**
   * Set the uri property on the image.
   *
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function uri($value) {
    $this->set('uri', $value);
    return $this;
  }

  /**
   * Set the width property on the image.
   *
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function width($value) {
    $this->set('width', $value);
    return $this;
  }

  /**
   * Set the height property on the image.
   *
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function height($value) {
    $this->set('height', $value);
    return $this;
  }

  /**
   * Set the alt property on the image.
   *
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function alt($value) {
    $this->set('alt', $value);
    return $this;
  }

  /**
   * Set the title property on the image.
   *
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function title($value) {
    $this->set('title', $value);
    return $this;
  }

  /**
   * Set the sizes property on the image.
   *
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function sizes($value) {
    $this->set('sizes', $value);
    return $this;
  }

  /**
   * Set the srcset property on the image.
   *
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function srcset($value) {
    $this->set('srcset', $value);
    return $this;
  }

}
