<?php

namespace Drupal\TestTools;

use Drupal\Core\Utility\Attribute\OmitFromDump;
use ReflectionClass;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

/**
 * Provides handlers for the Symfony VarDumper to work within tests.
 *
 * This allows the dump() function to produce output on the terminal without
 * causing PHPUnit to complain.
 */
class TestVarDumper {

  /**
   * A CLI handler for \Symfony\Component\VarDumper\VarDumper.
   */
  public static function cliHandler($var) {
    $cloner = new VarCloner();

    if (is_object($var)) {
      // Add a caster specifically for the class of the object being dumped, as
      // defining a general caster doesn't seem to be supported.
      $casters = [
        get_class($var) => static::class . '::' . 'removePropertiesCaster',
      ];
      $cloner->addCasters($casters);
    }

    $dumper = new CliDumper();
    fwrite(STDOUT, "\n");
    $dumper->setColors(TRUE);
    $dumper->dump(
      $cloner->cloneVar($var),
      function ($line, $depth, $indent_pad) {
        // A negative depth means "end of dump".
        if ($depth >= 0) {
          // Adds a two spaces indentation to the line.
          fwrite(STDOUT, str_repeat($indent_pad, $depth) . $line . "\n");
        }
      }
    );
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


  /**
   * A HTML handler for \Symfony\Component\VarDumper\VarDumper.
   */
  public static function htmlHandler($var) {
    $cloner = new VarCloner();
    $dumper = new HtmlDumper();
    $dumper->dump($cloner->cloneVar($var));
  }

}
