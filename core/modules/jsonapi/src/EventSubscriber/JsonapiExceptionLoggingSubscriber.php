<?php

namespace Drupal\jsonapi\EventSubscriber;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\EventSubscriber\ExceptionLoggingSubscriber;
use Drupal\Core\EventSubscriber\ExceptionLoggingSubscriberInterface;
use Drupal\Core\Utility\Error;
use Drupal\jsonapi\Exception\UnprocessableHttpEntityException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Logging subscriber to handle JSON:API-specific exceptions.
 */
final class JsonapiExceptionLoggingSubscriber implements ExceptionLoggingSubscriberInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\EventSubscriber\ExceptionLoggingSubscriberInterface $inner
   *   Wrapped subscriber.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   */
  public function __construct(
    protected ExceptionLoggingSubscriberInterface $inner,
    protected LoggerInterface $logger
  ) {}

  /**
   * Log an HTTP 422 Unprocessable exception.
   *
   * @param UnprocessableHttpEntityException $exception
   *   The exception.
   */
  protected function on422(UnprocessableHttpEntityException $exception): void {
    [$message, $context] = Error::decodeExceptionWithMessage($exception);
    $this->logger->warning(
      $message,
      NestedArray::mergeDeep(
        $context,
        ['violations' => $this->normalizeValidationConstraintViolations($exception->getViolations())]
      )
    );
  }

  /**
   * Normalize validation constraint violations for inclusion in log context.
   *
   * @param EntityConstraintViolationListInterface $violations
   *   Violations list.
   * @return array
   *   Array of normalized violations.
   */
  protected function normalizeValidationConstraintViolations(EntityConstraintViolationListInterface $violations): array {
    $normalized = [];
    foreach ($violations as $violation) {
      assert($violation instanceof ConstraintViolation);
      $normalized[] = (string) $violation;
    }
    return $normalized;
  }

  /**
   * Log all exceptions.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The event to process.
   */
  public function onException(ExceptionEvent $event): void {
    if ($event->getThrowable() instanceof UnprocessableHttpEntityException) {
      $this->on422($event->getThrowable());
    }
    else {
      $this->inner->onException($event);
    }
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return ExceptionLoggingSubscriber::getSubscribedEvents();
  }

}
