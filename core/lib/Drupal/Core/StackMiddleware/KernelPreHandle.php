<?php

namespace Drupal\Core\StackMiddleware;

use Drupal\Core\DrupalKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Prepares the environment after page caching ran.
 */
class KernelPreHandle implements HttpKernelInterface {

  /**
   * The main Drupal kernel.
   *
   * @var \Drupal\Core\DrupalKernelInterface
   */
  protected $drupalKernel;

  /**
   * Constructs a new KernelPreHandle instance.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The wrapped HTTP kernel.
   * @param \Drupal\Core\DrupalKernelInterface $drupal_kernel
   *   The main Drupal kernel.
   */
  public function __construct(
    protected HttpKernelInterface $httpKernel,
    DrupalKernelInterface $drupal_kernel,
  ) {
    $this->drupalKernel = $drupal_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MAIN_REQUEST, $catch = TRUE): Response {
    $this->drupalKernel->preHandle($request);

    return $this->httpKernel->handle($request, $type, $catch);
  }

}
