<?php

namespace Drupal\Core\Controller\ArgumentResolver;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Yields a PSR7 request object based on the request object passed along.
 */
final class Psr7RequestValueResolver implements ArgumentValueResolverInterface, ValueResolverInterface {

  /**
   * Constructs a new ControllerResolver.
   *
   * @param \Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface $httpMessageFactory
   *   The PSR-7 converter.
   */
  public function __construct(protected HttpMessageFactoryInterface $httpMessageFactory)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function supports(Request $request, ArgumentMetadata $argument): bool {
    return $argument->getType() == ServerRequestInterface::class;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(Request $request, ArgumentMetadata $argument): array {
    return $argument->getType() === ServerRequestInterface::class ? [$this->httpMessageFactory->createRequest($request)] : [];
  }

}
