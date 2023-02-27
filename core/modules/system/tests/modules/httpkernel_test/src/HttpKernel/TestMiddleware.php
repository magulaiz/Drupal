<?php

namespace Drupal\httpkernel_test\HttpKernel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Provides a test middleware.
 */
class TestMiddleware implements HttpKernelInterface {

  /**
   * Constructs a new TestMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $kernel
   *   The decorated kernel.
   * @param mixed $optionalArgument
   *   (optional) An optional argument.
   */
  public function __construct(protected HttpKernelInterface $kernel, protected $optionalArgument = NULL)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MAIN_REQUEST, $catch = TRUE): Response {
    $request->attributes->set('_hello', 'world');
    if ($request->attributes->has('_optional_argument')) {
      $request->attributes->set('_previous_optional_argument', $request->attributes->get('_optional_argument'));
    }
    elseif (isset($this->optionalArgument)) {
      $request->attributes->set('_optional_argument', $this->optionalArgument);
    }

    return $this->kernel->handle($request, $type, $catch);
  }

}
