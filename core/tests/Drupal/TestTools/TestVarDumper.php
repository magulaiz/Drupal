<?php

namespace Drupal\TestTools;

use Drupal\Core\Utility\VarDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * Provides handlers for the Symfony VarDumper to work within tests.
 *
 * This allows the dump() function to produce output on the terminal without
 * causing PHPUnit to complain.
 */
class TestVarDumper extends VarDumper {

  /**
   * A CLI handler for \Symfony\Component\VarDumper\VarDumper.
   */
  public static function cliHandler($var) {
    $cloner = new VarCloner();

    static::addCasters($cloner, $var);

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

}
