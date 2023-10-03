<?php

namespace Drupal\Tests;

/**
 * Value object to store performance information collected from requests.
 *
 * @see Drupal\Tests\PerformanceTestTrait::collectPerformanceData().
 */
class PerformanceData {

  /**
   * The number of stylesheets requested.
   */
  public readonly int $stylesheetCount;

  /**
   * The number of scripts requested.
   */
  public readonly int $scriptCount;

  /**
   * The original return value from a callable.
   */
  protected $returnValue;

  /**
   * Sets the stylesheet request count.
   *
   * @param int $count
   *   The number of stylesheet requests recorded.
   */
  public function setStylesheetCount(int $count): void {
    $this->stylesheetCount = $count;
  }

  /**
   * Sets the script request count.
   *
   * @param int $count
   *   The number of script requests recorded.
   */
  public function setScriptCount(int $count) {
    $this->scriptCount = $count;
  }

  /**
   * Sets the original return value.
   *
   * @param mixed $return
   *   The original return value.
   */
  public function setReturnValue($return): void {
    $this->returnValue = $return;
  }

}
