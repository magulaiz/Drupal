<?php

namespace Drupal\Core\Ajax;

/**
 * Provides an AJAX command for opening a modal with URL.
 */
class OpenModalDialogWithUrl implements CommandInterface {

  /**
   * Constructs a OpenModalWithUrl object.
   *
   * @param string $url
   *   The URL of the page.
   * @param array $settings
   *   The dialog settings.
   */
  public function __construct(
    protected string $url,
    protected array $settings
  ) {}

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'openDialogWithUrl',
      'url' => $this->url,
      'dialogOptions' => $this->settings,
    ];
  }

}
