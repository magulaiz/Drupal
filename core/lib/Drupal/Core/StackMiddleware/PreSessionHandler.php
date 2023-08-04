<?php

namespace Drupal\Core\StackMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Sets the status key for the upload progress.
 */
class PreSessionHandler implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs a new PreSessionHandler instance.
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
    $pathInfo = $request->getPathInfo();
    if ($pathInfo && str_contains($pathInfo, '/file/progress/')) {
      if (session_status() !== \PHP_SESSION_NONE) {
        throw new \RuntimeException('Failed to start the session: already started by PHP.');
      }

      if (!session_start()) {
        throw new \RuntimeException('Failed to start the session.');
      }

      $prefix = ini_get('session.upload_progress.prefix');
      $key = explode('/', $request->getPathInfo())[3];
      $request->attributes->set('status', $_SESSION[$prefix . $key]);
      session_write_close();
    }
    return $this->httpKernel->handle($request, $type, $catch);
  }

}
