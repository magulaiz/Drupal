<?php

namespace Drupal\language\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Return response for Language route.
 */
class TitleController extends ControllerBase {

  /**
   * The _title_callback for the language.content_settings_page route.
   *
   * @return string
   *   The page title.
   */
  public function setTitle() {
    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      return t('Content language and translation');
    }
    else {
      return t('Content language');
    }
  }

}
