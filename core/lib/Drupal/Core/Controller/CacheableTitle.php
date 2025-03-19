<?php

namespace Drupal\Core\Controller;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Allows attaching cacheability metadata to a title.
 */
class CacheableTitle extends BubbleableMetadata {

  /**
   * The title.
   *
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup|string|null
   */
  protected TranslatableMarkup|string|null $title;

  /**
   * Gets the title.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|string|null
   */
  public function getTitle(): TranslatableMarkup|string|null {
    return $this->title;
  }

  /**
   * Sets the title.
   *
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|string|null $title
   *   The title.
   */
  public function setTitle(TranslatableMarkup|string $title): void {
    $this->title = $title;
  }

}
