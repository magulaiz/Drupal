<?php

namespace Drupal\block;

/**
 * Generates unique machine names for blocks.
 */
interface BlockMachineNameGeneratorInterface {

  /**
   * Based on a suggested string generates a unique machine name for a block.
   *
   * @param string $suggestion
   *   The suggested block ID.
   * @param string $theme
   *   The machine name of the theme.
   *
   * @return string
   *   Returns the unique name.
   */
  public function getUniqueMachineName(string $suggestion, string $theme): string;

}
