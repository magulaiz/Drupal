<?php

declare(strict_types=1);

namespace Drupal\Core\Ajax;

use Drupal\Component\Utility\UrlHelper;

/**
 * Provides an AJAX command for opening a modal with URL.
 */
class OpenModalDialogWithUrl implements CommandInterface {

  /**
   * Constructs a OpenModalDialogWithUrl object.
   *
   * OpenDialogCommand is a similar class which opens modals but works
   * differently as it needs all data to be passed through dialogOptions while
   * OpenModalDialogWithUrl fetches the data from routing info of the URL.
   *
   * @param string $url
   *   Only Internal URLs or URLs with the same domain and base path are
   *   allowed.
   * @param array $settings
   *   The dialog settings.
   *
   * @see \Drupal\Core\Ajax\OpenDialogCommand
   */
  public function __construct(
    protected string $url,
    protected array $settings,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function render() {
    if (!UrlHelper::isExternal($this->url) || UrlHelper::externalIsLocal($this->url, $this->getBaseURL())) {
      return [
        'command' => 'openModalDialogWithUrl',
        'url' => $this->url,
        'dialogOptions' => $this->settings,
      ];
    }
    throw new \LogicException('External URLs are not allowed.');
  }

  /**
   * Gets the complete base URL.
   */
  private function getBaseUrl() {
    $requestContext = \Drupal::service('router.request_context');
    return $requestContext->getCompleteBaseUrl();
  }

}
