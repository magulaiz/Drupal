<?php

namespace Drupal\image\EventSubscriber;

use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Error handler for image style generation.
 */
class ImageExceptionResponseHandler implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Handles exceptions while generating invalid image style derivatives.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The event to process.
   */
  public function onException(ExceptionEvent $event) {
    $exception = $event->getThrowable();

    // If this is not a 404, we don't need to check for an invalid image style.
    if (!($exception instanceof NotFoundHttpException)) {
      return;
    }

    // Intercept 404 exceptions thrown by image style routes when an image
    // style is invalid/unknown. This subscriber prevents the standard Drupal
    // 404 error page or a generic fast_404 error message, and instead returns
    // a minimal 404 error with a relevant message for troubleshooting.
    $previous_exception = $exception->getPrevious();
    if ($previous_exception instanceof ParamNotConvertedException) {
      $route_name = $previous_exception->getRouteName();
      $parameters = $previous_exception->getRawParameters();

      if (
        in_array($route_name, ['image.style_public', 'image.style_private']) &&
        isset($parameters['image_style'])
      ) {

        $event->setResponse(new Response($this->t('Error generating image, invalid image style "@image_style".', ['@image_style' => $parameters['image_style']]), Response::HTTP_NOT_FOUND));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::EXCEPTION => ['onException'],
    ];
  }

}
