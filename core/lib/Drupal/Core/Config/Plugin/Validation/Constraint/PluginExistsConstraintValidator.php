<?php

declare(strict_types = 1);

namespace Drupal\Core\Config\Plugin\Validation\Constraint;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PluginExists constraint.
 */
class PluginExistsConstraintValidator extends ConstraintValidator implements ContainerAwareInterface, ContainerInjectionInterface {

  use ContainerAwareTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $validator = new static();
    $validator->setContainer($container);
    return $validator;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $plugin_id, Constraint $constraint) {
    assert($constraint instanceof PluginExistsConstraint);

    $manager = $this->container->get($constraint->manager);
    assert($manager instanceof PluginManagerInterface);

    if (!$manager->hasDefinition($plugin_id)) {
      $this->context->addViolation($constraint->message, [
        '@plugin_id' => $plugin_id,
      ]);
    }
  }

}
