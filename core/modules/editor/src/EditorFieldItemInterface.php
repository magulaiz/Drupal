<?php

namespace Drupal\editor;

/**
 * Interface for fields which contain a formatted text field with an editor.
 */
interface EditorFieldItemInterface
{

  /**
   * Get the formatted text content of the field.
   *
   * @return string
   *   The formatted text content of the field.
   */
  public function getFormattedText();

}
