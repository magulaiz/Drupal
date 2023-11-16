<?php

namespace Drupal\Core\Utility;

use Drupal\Core\Utility\Attribute\OmitFromDump;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Provides handlers for the Symfony VarDumper.
 *
 * In PHPUnit tests, this is enabled automatically. Outside of tests, this needs
 * to be enabled with the 'setup_var_dumper' setting in settings.php.
 */
class VarDumper {

  /**
   * A handler for \Symfony\Component\VarDumper\VarDumper.
   */
  public static function handler($var) {
    $cloner = new VarCloner();

    static::addCasters($cloner, $var);

    $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
    $dumper->dump($cloner->cloneVar($var));
  }

  /**
   * Adds our caster which ignored marked properties.
   *
   * Helper for handlers.
   *
   * @param \Symfony\Component\VarDumper\Cloner\VarCloner $cloner
   *   The cloner.
   * @param mixed $var
   *   The variable being dumped.
   */
  public static function addCasters(VarCloner $cloner, $var) {
    if (is_object($var)) {
      // Add a caster specifically for the class of the object being dumped, as
      // defining a general caster doesn't seem to be supported.
      $casters = [
        get_class($var) => static::class . '::' . 'removePropertiesCaster',
      ];
      $cloner->addCasters($casters);
    }
  }

  /**
   * Caster to remove properties from objects.
   *
   * Removes properties marked with the \Drupal\Core\Utility\OmitFromDump
   * attribute.
   */
  public static function removePropertiesCaster($object, $array, Stub $stub, $isNested, $filter) {
    if (is_object($object)) {
      $reflection_class = new \ReflectionClass($object);
      foreach ($reflection_class->getProperties() as $reflection_property) {
        if ($reflection_property->getAttributes(OmitFromDump::class)) {
          unset($array["\0*\0" . $reflection_property->getName()]);
        }
      }
    }

    return $array;
  }

}
