<?php

namespace Drupal\Core\Extension;

/**
 * Extension hooks status.
 *
 * Whether HookCollectorPass should scan for procedural hooks.
 */
final class ExtensionHookStatus {

  /**
   * The string used to identify the procedural hook status in an .info.yml file.
   */
  const PROCEDURAL_HOOKS = 'procedural_hooks';

  /**
   * Scan for procedural hooks.
   */
  const SCAN = 'scan';

  /**
   * Skip procedural scan.
   */
  const SKIP = 'skip';

}
