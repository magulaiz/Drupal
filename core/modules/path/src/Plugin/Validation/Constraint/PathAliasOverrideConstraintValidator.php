<?php

namespace Drupal\path\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Drupal\Core\Routing\RouteMatchInterface;
use Psr\Log\LoggerInterface;


/**
 * Constraint validator for changing path aliases in pending revisions.
 */
class PathAliasOverrideConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Creates a new PathAliasConstraintValidator instance.
 * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   */
  public function __construct(protected AccountProxyInterface $currentUser,
                              protected Settings $settings,
                              protected LoggerInterface $logger) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('settings'),
      $container->get('logger.channel.path')
    );
  }

  private function checkPathRoute($path) : bool {

    $pathExists = FALSE;
    /** @var RouteMatchInterface $route_match */
    $route_match = \Drupal::service('router.no_access_checks'); //->matchRequest($request);

    try {
      $route_match->match($path);
      $pathExists = TRUE;
    } catch (ResourceNotFoundException|MethodNotAllowedException|ParamNotConvertedException $e) {
      // There's nothing to do with these exceptions as they indicate that a URI doesn't map
      // to an expected route.
    } catch (\Exception $e) {
      // Any other exceptions at this point are unexpected, so we're just going to log them
      // and continue on as if the URI doesn't map to a known route.
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



// Example usage:
  $route_match = $this->checkPathRoute($value->alias);
  if ($route_match) {
    $this->context->addViolation($constraint->message);
  }

//
//    if ($this->currentUser->hasPermission('override url aliases')) {
//      return;
//    }
//    $path_parts = explode('/', $value->alias, 2);
//    if (empty($path_parts[0])) {
//      // This path would probably fail validation in other ways than this one.
//      return;
//    }
//    $restricted_paths = $this->settings->get('path_restricted_paths',
//      [
//        'node',
//        'taxonomy',
//        'user',
//      ],
//    );
//    if (in_array($path_parts[0], $restricted_paths)) {
//
//    }

  }

}
