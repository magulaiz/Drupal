<?php

/**
 * @file
 * Hooks provided by the Single Directory Components module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allows altering the list of discovered SDC component plugins.
 *
 * Modules are able to alter specific SDC component definitions.
 *
 * @param array[] $definitions
 *   An associative array of SDC component plugin definitions, keyed by the
 *   plugin ID. Each value is the plugin definition array.
 */
function hook_sdc_info_alter(array &$definitions): void {
  $definitions['sdc_test:my-button']['name'] = "My button";
}

/**
 * @} End of "addtogroup hooks".
 */
