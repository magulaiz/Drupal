<?php

declare(strict_types=1);

namespace Drupal\file\Upload;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an access checker for input stream file uploads.
 */
class FileUploadAccessCheck implements AccessInterface {

  /**
   * Creates a FileUploadAccessCheck.
   */
  public function __construct(
    protected EntityFieldManagerInterface $entityFieldManager,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Checks file upload access.
   */
  public function access(AccountInterface $account, string $entity_type_id, ?string $bundle, string $field_name): AccessResultInterface {
    try {
      $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
      if (!isset($field_definitions[$field_name])) {
        return AccessResult::forbidden(sprintf('"%s" does not exist', $field_name));
      }

      $field_definition = $field_definitions[$field_name];
      if ($field_definition->getSetting('target_type') !== 'file') {
        return AccessResult::forbidden(sprintf('"%s" is not a file field', $field_name));
      }

      $entity_access_control_handler = $this->entityTypeManager->getAccessControlHandler($entity_type_id);
      // Ignore the bundle param if no bundle is defined.
      $bundle = $this->entityTypeManager->getDefinition($entity_type_id)
        ->hasKey('bundle') ? $bundle : NULL;
      return $entity_access_control_handler->createAccess($bundle, $account, [], TRUE)
        ->andIf($entity_access_control_handler->fieldAccess('edit', $field_definition, $account, NULL, TRUE));
    }
    catch (\Exception $e) {
      return AccessResult::forbidden($e->getMessage());
    }
  }

}
