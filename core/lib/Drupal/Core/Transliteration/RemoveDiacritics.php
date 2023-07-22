<?php

namespace Drupal\Core\Transliteration;

use Drupal\Component\Transliteration\RemoveDiacritics as BaseRemoveDiacritics;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Enhances RemoveDiacritics with an alter hook.
 *
 * @ingroup transliteration
 * @see hook_remove_diacritics_map_alter()
 */
class RemoveDiacritics extends BaseRemoveDiacritics {

  /**
   * Constructs a RemoveDiacritics object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler to execute the transliteration_overrides alter hook.
   */
  public function __construct(protected ModuleHandlerInterface $moduleHandler) {
  }

  /**
   * {@inheritdoc}
   */
  protected function initMap(): array {
    $map = parent::initMap();
    $this->moduleHandler->alter('remove_diacritics_map', $map);
    return $map;
  }

}
