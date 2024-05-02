<?php

namespace Drupal\Core\Render\Builder;

use Drupal\Core\Render\RenderableInterface;

/**
 * Define a base that all theme builders extend.
 */
abstract class BuilderBase implements BuilderBaseInterface, RenderableInterface {

  /**
   * An array of the internal representation of the theme instance.
   *
   * @var array
   */
  protected array $renderable = [];

  /**
   * {@inheritdoc}
   */
  public static function create(): static {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function toRenderable(): array {
    return $this->renderable;
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value): static {
    $this->renderable['#' . $key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function prefix($value): static {
    return $this->set('prefix', $value);
  }

  /**
   * {@inheritdoc}
   */
  public function suffix($value): static {
    return $this->set('suffix', $value);
  }

  /**
   * {@inheritdoc}
   */
  public function access($value): static {
    return $this->set('access', $value);
  }

  /**
   * Set the attributes property on the image.
   *
   * @param array $value
   *   The value to set.
   *
   * @return $this
   */
  public function attributes(array $value): static {
    return $this->set('attributes', $value);
  }

}
