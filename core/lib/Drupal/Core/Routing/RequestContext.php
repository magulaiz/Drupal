<?php

namespace Drupal\Core\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;

/**
 * Holds information about the current request.
 *
 * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use
 *   Symfony\Component\Routing\RequestContext instance instead.
 * @see https://www.drupal.org/node/3279668
 */
class RequestContext extends SymfonyRequestContext {

  /**
   * The scheme, host and base path, for example "http://example.com/d8".
   *
   * @var string
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0.
   *   Without replacement.
   * @see https://www.drupal.org/node/3279668
   */
  protected $completeBaseUrl;

  /**
   * Populates the context from the current request from the request stack.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The current request stack.
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use
   *   Symfony\Component\Routing\RequestContext instance instead.
   * @see https://www.drupal.org/node/3279668
   */
  public function fromRequestStack(RequestStack $request_stack) {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use Symfony\Component\Routing\RequestContext instance instead. See https://www.drupal.org/node/3279668', E_USER_DEPRECATED);
    $this->fromRequest($request_stack->getCurrentRequest());
  }

  /**
   * {@inheritdoc}
   */
  public function fromRequest(Request $request): static {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use Symfony\Component\Routing\RequestContext instance instead. See https://www.drupal.org/node/3279668', E_USER_DEPRECATED);
    // @todo Extract the code in DrupalKernel::initializeRequestGlobals.
    //   See https://www.drupal.org/node/2404601
    if (isset($GLOBALS['base_url'])) {
      $this->setCompleteBaseUrl($GLOBALS['base_url']);
    }

    return parent::fromRequest($request);
  }

  /**
   * Gets the scheme, host and base path.
   *
   * For example, in an installation in a subdirectory "d8", it should be
   * "https://example.com/d8".
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use
   *   \Drupal::service("app")->getBaseUrl() instead.
   * @see https://www.drupal.org/node/3279668
   */
  public function getCompleteBaseUrl() {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use \Drupal::service("app")->getBaseUrl() instead. See https://www.drupal.org/node/3279668', E_USER_DEPRECATED);
    return $this->completeBaseUrl;
  }

  /**
   * Sets the complete base URL for the Request context.
   *
   * @param string $complete_base_url
   *   The complete base URL.
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use
   *   Symfony\Component\Routing\RequestContext instance instead.
   * @see https://www.drupal.org/node/3279668
   */
  public function setCompleteBaseUrl($complete_base_url) {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use Symfony\Component\Routing\RequestContext instance instead. See https://www.drupal.org/node/3279668', E_USER_DEPRECATED);
    $this->completeBaseUrl = $complete_base_url;
  }

}
