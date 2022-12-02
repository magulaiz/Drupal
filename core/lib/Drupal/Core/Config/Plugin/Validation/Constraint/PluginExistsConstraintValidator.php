<?php

declare(strict_types = 1);

namespace Drupal\Core\Config\Plugin\Validation\Constraint;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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

    $definition = $manager->getDefinition($plugin_id, FALSE);
    if (empty($definition)) {
      $this->context->addViolation($constraint->unknownPluginMessage, [
        '@plugin_id' => $plugin_id,
      ]);
      return;
    }

    if ($constraint->interface) {
      if ($definition instanceof PluginDefinitionInterface) {
        $class = $definition->getClass();
      }
      elseif (is_array($definition)) {
        $class = $definition['class'];
      }
      else {
        throw new UnexpectedTypeException($definition, 'array|' . PluginDefinitionInterface::class);
      }

      if (!is_a($class, $constraint->interface, TRUE)) {
        $this->context->addViolation($constraint->invalidInterfaceMessage, [
          '@plugin_id' => $plugin_id,
          '@interface' => $constraint->interface,
        ]);
      }
    }
  }

}
