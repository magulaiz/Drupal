<?php

namespace Drupal\Core\Update;

/**
 * Deprecated Update handler functionality class.
 *
 * @deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Removed
 *   without replacement.
 *
 * @see https://www.drupal.org/node/3013060
 */
class DeprecatedUpdate extends Update {

  /**
   * {@inheritdoc}
   *
   * @deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. No
   *   replacement provided.
   *
   * @see https://www.drupal.org/node/3013060
   */
  public function systemSchemaRequirements(): array {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. No replacement provided. See https://www.drupal.org/node/3013060', E_USER_DEPRECATED);
    return parent::systemSchemaRequirements();
  }

  /**
   * {@inheritdoc}
   *
   * @deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. No
   *   replacement provided.
   *
   * @see https://www.drupal.org/node/3013060
   */
  public function fixMissingSchema(): void {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. No replacement provided. See https://www.drupal.org/node/3013060', E_USER_DEPRECATED);
    parent::fixMissingSchema();
  }

}
