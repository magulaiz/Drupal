<?php

namespace Drupal\Core\Action;

use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Provides a container for lazily loading Action plugins.
 */
class ActionPluginCollection extends DefaultSingleLazyPluginCollection {

  /**
   * {@inheritdoc}
   */
  // phpcs:ignore Drupal.Commenting.FunctionComment.MissingReturnComment
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

}
