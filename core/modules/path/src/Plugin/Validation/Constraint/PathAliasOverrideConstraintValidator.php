<?php

namespace Drupal\path\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\Router;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use JsonSchema\Exception\ResourceNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for changing path aliases in pending revisions.
 */
class PathAliasOverrideConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Creates a new PathAliasConstraintValidator instance.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   */
  public function __construct(
    protected AccountProxyInterface $currentUser,
    protected Settings $settings,
    protected LoggerInterface $logger,
    protected Router $router,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('settings'),
      $container->get('logger.channel.router'),
      $container->get('router.no_access_checks'),
    );
  }

  /**
   *
   */
  private function checkPathRoute($path) : bool {

    $pathExists = FALSE;

    try {
      $this->router->match($path);
      $pathExists = TRUE;
    }
    catch (ResourceNotFoundException |
          MethodNotAllowedException $e) {
      // There's nothing to do with these exceptions
      // as they indicate that a URI doesn't map to an expected route.
    }
    catch (\Exception $e) {
      // Any other exceptions at this point are unexpected, so we're just
      // going to log them and continue on as if the URI doesn't map to a
      // known route.
      $this->logger->notice("An exception {$e->getMessage()} occurred determining if the alias exists.");
    }

    return $pathExists;

  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) : void {
    if (empty($value->alias)) {
      return;
    }

    $route_match = $this->checkPathRoute($value->alias);
    if ($route_match) {
      $this->context->addViolation($constraint->message, ["%alias" => $value->alias]);
    }

  }

}
