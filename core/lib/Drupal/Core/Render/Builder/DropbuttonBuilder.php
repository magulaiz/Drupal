<?php

namespace Drupal\Core\Render\Builder;

/**
 * Builder class for the 'image' element.
 */
class DropbuttonBuilder extends BuilderBase {

  /**
   * {@inheritdoc}
   */
  protected array $renderable = ['#type' => 'dropbutton'];

  /**
   * Set the links property on the dropbutton.
   *
   * @param array $value
   *   The list of links for this dropbutton.
   *
   * @return $this
   *
   * @see template_preprocess_links()
   */
  public function links(array $value): static {
    return $this->set('links', $value);
  }

  /**
   * A string defining a type of dropbutton variant for styling proposes.
   *
   * Renders as class `dropbutton--#dropbutton_type`.
   *
   * @param string $value
   *   The type of dropbutton.
   *
   * @return $this
   */
  public function type(string $value): static {
    return $this->set('dropbutton_type', $value);
  }

}
