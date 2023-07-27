<?php

namespace Drupal\Core\StackMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Prepares the environment after page caching ran.
 */
class PreSessionHandler implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs a new KernelPreHandle instance.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The wrapped HTTP kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MAIN_REQUEST, $catch = TRUE): Response {
    $prefix = ini_get('session.upload_progress.prefix');
    $pathInfo = $request->getPathInfo();
    $key = '';
    if ($pathInfo) {
      $key = explode('/', $request->getPathInfo())[3];
    }
    if (isset($_SESSION[$prefix . $key])) {
      $request->attributes->set('status', $_SESSION[$prefix . $key]);
    }
    return $this->httpKernel->handle($request, $type, $catch);
  }

}
