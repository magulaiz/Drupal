<?php

/**
 * @file
 * Describes API functions for tour module.
 */

/**
 * @defgroup help_docs Help and documentation
 * @{
 * Documenting modules, themes, and install profiles
 *
 * @section sec_tour Tours
 * Modules can provide tours of administrative pages by creating tour config
 * files and placing them in their config/optional subdirectory. See
 * @link https://www.drupal.org/docs/8/api/tour-api/overview Tour API overview @endlink
 * for more information. The contributed
 * @link https://www.drupal.org/project/tour_ui Tour UI module @endlink
 * can also be used to create tour config files.
 * @}
 */

/**
 * Extension of entity.api.php
 *
 *  Hooks invoked during the operation of building a render array:
 *  - hook_entity_view_mode_alter()
 *  - hook_ENTITY_TYPE_build_defaults_alter()
 *  - hook_entity_build_defaults_alter()
 *
 *  View builders for some types override these hooks, notably:
 *  - The Tour view builder does not invoke any hooks. Note that in other view
 *    builders, the view alter hooks are run later in the process.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Allow modules to alter tour items before render.
 *
 * @param array $tour_tips
 *   Array of \Drupal\tour\TipPluginInterface items.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The tour which contains the $tour_tips.
 */
function hook_tour_tips_alter(array &$tour_tips, \Drupal\Core\Entity\EntityInterface $entity) {
  foreach ($tour_tips as $tour_tip) {
    if ($tour_tip->get('id') == 'tour-code-test-1') {
      $tour_tip->set('body', 'Altered by hook_tour_tips_alter');
    }
  }
}

/**
 * Allow modules to alter tip plugin definitions.
 *
 * @param array $info
 *   The array of tip plugin definitions, keyed by plugin ID.
 *
 * @see \Drupal\tour\Annotation\Tip
 */
function hook_tour_tips_info_alter(&$info) {
  // Swap out the class used for this tip plugin.
  if (isset($info['text'])) {
    $info['class'] = 'Drupal\mymodule\Plugin\tour\tip\MyCustomTipPlugin';
  }
}

/**
 * @} End of "addtogroup hooks".
 */
