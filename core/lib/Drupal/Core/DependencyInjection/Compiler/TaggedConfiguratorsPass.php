<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Drupal\Component\Utility\Reflection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to add tagged services as configurators for other services.
 */
class TaggedConfiguratorsPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $configurator_callbacks_by_target = [];
    $configurator_ids = $container->findTaggedServiceIds('configurator');
    foreach ($configurator_ids as $id => $configurator_tags) {
      $definition = $container->getDefinition($id);
      foreach ($configurator_tags as $configurator_tag) {
        $class = $definition->getClass();
        // In this version, the 'method' key is required.
        // @todo Add option to discover methods by attributes.
        $method_name = $configurator_tag['method'] ?? NULL;
        if (!$method_name) {
          throw new LogicException(sprintf(
            "Missing 'method' key on 'configurator' service tag on '%s'.",
            $id,
          ));
        }
        $method = new \ReflectionMethod($class, $method_name);
        $parameters = $method->getParameters();
        $first_parameter = array_shift($parameters);
        if (!$first_parameter) {
          throw new LogicException(sprintf(
            "The configurator method '%s' for '%s' must have at least one parameter.",
            $class . '::' . $method_name . '()',
            $id,
          ));
        }
        $priority = $configurator_tag['priority'] ?? 0;
        $extra_args = [];
        if ($parameters) {
          // Determine values for additional parameters.
          $named_values = $configurator_tag + ['priority' => $priority];
          $last_non_default_arg_index = 0;
          foreach ($parameters as $i => $parameter) {
            $value = $named_values[$parameter->getName()] ?? NULL;
            if ($value !== NULL) {
              $last_non_default_arg_index = $i;
              $extra_args[] = $value;
            }
            elseif ($parameter->isOptional()) {
              $extra_args[] = $parameter->getDefaultValue();
            }
            else {
              throw new LogicException(sprintf(
                "No value found for required parameter %s on %s for %s.",
                '$' . $parameter->getName(),
                $class . '::' . $method_name . '()',
                $id,
              ));
            }
          }
          // Remove trailing default arguments.
          $extra_args = array_slice($extra_args, 0, $last_non_default_arg_index + 1);
        }
        $target_id = $configurator_tag['target'] ?? NULL;
        if ($target_id === NULL) {
          // Determine the target id from the type of the first argument.
          // This will likely not be used in Drupal core, but it allows contrib
          // and custom code to omit the 'target' key, if they use class and
          // interface names as service ids.
          $target_id = Reflection::getParameterClassName($first_parameter);
          if ($target_id === NULL) {
            throw new LogicException(sprintf(
              "The configurator method '%s' for '%s' must have a class-like type on its first parameter, if the configurator tag does not specify a 'target'.",
              $class . '::' . $method_name . '()',
              $id,
            ));
          }
        }
        $configurator_callbacks_by_target[$target_id][$priority][] = [
          [new Reference($id), $method_name],
          $extra_args,
        ];
      }
    }
    foreach ($configurator_callbacks_by_target as $target_id => $configurator_callbacks_by_priority) {
      ksort($configurator_callbacks_by_priority);
      $callbacks = array_merge(...$configurator_callbacks_by_priority);
      $target_definition = $container->getDefinition($target_id);
      $target_definition->setConfigurator([
        new Definition(MultiConfigurator::class, [$callbacks]),
        'configure',
      ]);
    }
  }

}
