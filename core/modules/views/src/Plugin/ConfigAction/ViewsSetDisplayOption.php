<?php

namespace Drupal\views\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\ViewExecutable;

/**
 * Config action for setting display option.
 */
#[ConfigAction(
  id: 'view:setDisplayOption',
  admin_label: new TranslatableMarkup('Views set display option'),
  entity_types: ['view'],
)]
class ViewsSetDisplayOption extends ViewsDisplayOptionBase {

  /**
   * Configure view display option.
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
    $view->setDisplay($display_id);
    if ($override) {
      $view->displayHandlers->get($display_id)->overrideOption($option, $settings);
    }
    else {
      $view->displayHandlers->get($display_id)->setOption($option, $settings);
    }
  }

}
