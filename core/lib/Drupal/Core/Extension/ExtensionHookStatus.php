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

  /**
   * Determines if a given extension hook status is valid.
   *
   * @param bool $procedural_hooks
   *   The procedural_hooks to validate.
   *
   * @return bool
   *   TRUE if the procedural_hooks value is valid, otherwise FALSE.
   */
  public static function isValid(string $procedural_hooks) : bool {
    $valid_values = [
      self::SCAN,
      self::SKIP,
    ];
    return in_array($procedural_hooks, $valid_values, TRUE);
  }

}
