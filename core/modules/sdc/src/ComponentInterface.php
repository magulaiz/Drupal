<?php

namespace Drupal\sdc;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

interface ComponentInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * The template path.
   *
   * @return string|null
   *   The path to the template.
   */
  public function getTemplatePath(): ?string;

  /**
   * The auto-computed library name.
   *
   * @return string
   *   The library name.
   */
  public function getLibraryName(): string;

}
