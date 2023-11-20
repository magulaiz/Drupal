<?php

namespace Drupal\Core\StackMiddleware;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\EventSubscriber\AjaxResponseSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Expands the compressed ajax_page_state query parameter into an array.
 */
class AjaxPageState implements HttpKernelInterface {

  /**
   * Constructs a new AjaxPageState instance.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The wrapped HTTP kernel.
   */
  public function __construct(protected readonly HttpKernelInterface $httpKernel) {
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MAIN_REQUEST, $catch = TRUE): Response {
    if ($type === static::MAIN_REQUEST) {
      if ($request->request->has(AjaxResponseSubscriber::AJAX_PAGE_STATE_REQUEST_PARAMETER)) {
        $request->request->set(AjaxResponseSubscriber::AJAX_PAGE_STATE_REQUEST_PARAMETER, $this->parseAjaxPageState($request->request->all(AjaxResponseSubscriber::AJAX_PAGE_STATE_REQUEST_PARAMETER)));
      }
      elseif ($request->query->has(AjaxResponseSubscriber::AJAX_PAGE_STATE_REQUEST_PARAMETER)) {
        $request->query->set(AjaxResponseSubscriber::AJAX_PAGE_STATE_REQUEST_PARAMETER, $this->parseAjaxPageState($request->query->all(AjaxResponseSubscriber::AJAX_PAGE_STATE_REQUEST_PARAMETER)));
      }
    }
    return $this->httpKernel->handle($request, $type, $catch);
  }

  /**
   * Parse the ajax_page_state variable in the request.
   *
   * Decompresses the libraries array key.
   *
   * @param array $ajax_page_state
   *   An array of query parameters, where the libraries parameter is compressed.
   *
   * @return array
   */
  private function parseAjaxPageState(array $ajax_page_state): array {
    $ajax_page_state['libraries'] = UrlHelper::uncompressQueryParameter($ajax_page_state['libraries']);
    return $ajax_page_state;
  }

}
