<?php

namespace Drupal\layout_builder\Plugin\Block;

use Drupal\block_content\Access\RefinableDependentAccessInterface;
use Drupal\block_content\Access\RefinableDependentAccessTrait;

/**
 * Defines an inline block plugin type that depends on block_content module.
 *
 * @internal
 *   Plugin classes are internal.
 */
class RefinableDependentInlineBlock extends InlineBlock implements RefinableDependentAccessInterface {

  use RefinableDependentAccessTrait;

}
