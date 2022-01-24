<?php

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
    return $this->set('uri', $value);
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
    return $this->set('width', $value);
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
    return $this->set('height', $value);
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
    return $this->set('alt', $value);
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
    return $this->set('title', $value);
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
    return $this->set('sizes', $value);
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
    return $this->set('srcset', $value);
  }

}
