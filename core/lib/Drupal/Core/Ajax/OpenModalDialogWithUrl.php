<?php

declare(strict_types=1);

namespace Drupal\Core\Ajax;

/**
 * Provides an AJAX command for opening a modal with URL.
 */
class OpenModalDialogWithUrl implements CommandInterface {

  /**
   * Constructs a OpenModalDialogWithUrl object.
   *
   * @see OpenDialogCommand a similar class which deals with opening modals
   * but we don't want to inherit any of its public methods except render()
   * hence not extending it. For usage examples and better understanding the
   * difference between the two @see AjaxCommandsTest
   *
   * @param string $url
   *   The URL of the page.
   * @param array $settings
   *   The dialog settings.
   */
  public function __construct(
    protected string $url,
    protected array $settings,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'openModalDialogWithUrl',
      'url' => $this->url,
      'dialogOptions' => $this->settings,
    ];
  }

}
