<?php

namespace Drupal\media\Controller;

class MediaFieldListController {

  public function message() {
    return [
      '#markup' => t('THIS IS A MESSAGE INFORMING THE USER OF THE MOVE'),
    ];
  }

}
