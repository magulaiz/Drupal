<?php

declare(strict_types=1);

namespace Drupal\Core\Update;

use Drupal\Core\Entity\EntityLastInstalledSchemaRepository;

/**
 * Customizes the container for running updates.
 */
class UpdateEntityLastInstalledSchemaRepository extends EntityLastInstalledSchemaRepository {

  /**
   * {@inheritdoc}
   */
  public function getLastInstalledFieldStorageDefinitions($entity_type_id) {
    $old_store = $this->keyValueFactory->get('entity.definitions.installed');
    if ($definitions = $old_store->get("$entity_type_id.field_storage_definitions")) {
      $this->setLastInstalledFieldStorageDefinitions($entity_type_id, $definitions);
      $old_store->delete("$entity_type_id.field_storage_definitions");
    }
    return parent::getLastInstalledFieldStorageDefinitions($entity_type_id);
  }

}
