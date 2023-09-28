<?php

namespace Drupal\Tests;

class PerformanceData {

  /**
   * The number of stylesheets requested.
   */
  protected int $stylesheetCount = 0;

  /**
   * The number of scripts requested.
   */
  protected int $scriptCount = 0;

  /**
   * Sets the stylesheet request count.
   *
   * @param int $count
   *   The number of stylesheet requests recorded.
   */
  public function setStylesheetCount($count): void {
    $this->stylesheetCount = $count;
  }

  /**
   * Gets the stylesheet request count.
   *
   * @return int
   *   The number of stylesheet requests recorded.
   */
  public function getStylesheetCount(): int {
    return $this->stylesheetCount;
  }

  /**
   * Sets the script request count.
   *
   * @param int $count
   *   The number of script requests recorded.
   */
  public function setScriptCount($count) {
    $this->scriptCount = $count;
  }

  /**
   * Gets the script request count.
   *
   * @return int
   *   The number of script requests recorded.
   */
  public function getScriptCount(): int {
    return $this->scriptCount;
  }

}
