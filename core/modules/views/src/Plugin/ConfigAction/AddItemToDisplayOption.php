<?php

namespace Drupal\views\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\ViewExecutable;

/**
 * Config action for adding item to display option.
 */
#[ConfigAction(
  id: 'view:addItemToDisplayOption',
  admin_label: new TranslatableMarkup('Add an item to a Views display option'),
  entity_types: ['view'],
)]
class AddItemToDisplayOption extends ConfigActionDisplayOptionBase {

  /**
   * Add item to view display option.
   *
   * {@inheritdoc}
   */
  protected function applySingle(array $value, int $key, ViewExecutable $view): void {
    if (empty($value)) {
      throw new ConfigActionException(sprintf('View %s cannot be updated because no option settings were provided', $view->id()));
    }
    if (empty($value['option'])) {
      throw new ConfigActionException('No view display option provided');
    }
    $option = $value['option'];
    if (empty($value['item'])) {
      throw new ConfigActionException(sprintf('No view display %s item name provided', $option));
    }
    $item = $value['item'];
    if (empty($value['settings'])) {
      throw new ConfigActionException('No view display option settings provided');
    }
    $settings = $value['settings'];

    $display_id = 'default';
    if (!empty($value['display_id'])) {
      $display_id = $value['display_id'];
    }
    $override = FALSE;
    if (!empty($value['override'])) {
      $override = TRUE;
    }
    $allow_update = FALSE;
    if (!empty($value['allow_update'])) {
      $allow_update = TRUE;
    }
    $view->setDisplay($display_id);
    $option_settings = $view->displayHandlers->get($display_id)->getOption($option);
    if (!empty($option_settings[$item]) && !$allow_update) {
      throw new ConfigActionException(sprintf('Item %s already exists in %s display for %s', $item, $display_id, $option));
    }
    $option_settings[$item] = $settings;
    if ($override) {
      $view->displayHandlers->get($display_id)->overrideOption($option, $option_settings);
    }
    else {
      $view->displayHandlers->get($display_id)->setOption($option, $option_settings);
    }
  }

}
