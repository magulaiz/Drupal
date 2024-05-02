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
   * @param string $value
   *   The value to set.
   *
   * @return $this
   */
  public function uri(string $value): static {
    return $this->set('uri', $value);
  }

  /**
   * Set the width property on the image.
   *
   * @param string|int $value
   *   The value to set.
   *
   * @return $this
   */
  public function width(string|int $value): static {
    return $this->set('width', $value);
  }

  /**
   * Set the height property on the image.
   *
   * @param string|int $value
   *   The value to set.
   *
   * @return $this
   */
  public function height(string|int $value): static {
    return $this->set('height', $value);
  }

  /**
   * Set the alt property on the image.
   *
   * @param string|null $value
   *   The value to set.
   *
   * @return $this
   */
  public function alt(?string $value): static {
    return $this->set('alt', $value);
  }

  /**
   * Set the title property on the image.
   *
   * @param string|null $value
   *   The value to set.
   *
   * @return $this
   */
  public function title(?string $value): static {
    return $this->set('title', $value);
  }

  /**
   * Set the sizes property on the image.
   *
   * @param string $value
   *   The value to set.
   *
   * @return $this
   */
  public function sizes(string $value): static {
    return $this->set('sizes', $value);
  }

  /**
   * Set the srcset property on the image.
   *
   * @param array $value
   *   The value to set.
   *
   * @return $this
   */
  public function srcset(array $value): static {
    return $this->set('srcset', $value);
  }

}
