<?php

namespace Drupal\Core\Template;

use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A class providing Drupal Twig Debug extension.
 */
final class DebugExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    // Override Twig built in debugger when Symfony VarDumper is available to
    // improve developer experience.
    // @see \Twig\Extension\DebugExtension
    // @see \Symfony\Component\VarDumper\VarDumper
    if (class_exists(VarDumper::class)) {
      return [
        new TwigFunction('dump', [self::class, 'dump'], ['needs_context' => TRUE, 'needs_environment' => TRUE, 'is_variadic' => TRUE]),
      ];
    }

    return [];
  }

  /**
   * Dumps information about variables using Symfony VarDumper.
   *
   * @param \Twig\Environment $env
   *   The Twig environment.
   * @param array $context
   *   Variables from the Twig template.
   * @param array $variables
   *   (optional) Variable(s) to dump.
   */
  public static function dump(Environment $env, array $context, ...$variables): void {
    if (!$env->isDebug()) {
      return;
    }

    if (class_exists(VarDumper::class)) {
      if (func_num_args() === 2) {
        VarDumper::dump($context);
      }
      else {
        array_walk($variables, VarDumper::dump(...));
      }
    }
    else {
      throw new \LogicException('Could not dump the variable because symfony/var-dumper component is not installed.');
    }
  }

}
