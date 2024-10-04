<?php

declare(strict_types=1);

namespace Drupal\Core\File\MimeType;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Modifies the MIME type map by calling hook_file_mimetype_mapping_alter().
 *
 * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use a service
 *   that implements MimeTypeMapModifierInterface instead.
 *
 * @see https://www.drupal.org/node/2311679
 */
class LegacyHookMimeTypeMapModifier implements MimeTypeMapModifierInterface {

  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function modifyMimeTypeMap(MimeTypeMapInterface $map): void {
    if (!$map instanceof DefaultMimeTypeMap) {
      return;
    }
    $mapping = $map->getMapping();
    $this->moduleHandler->alterDeprecated(
      'This hook is deprecated in drupal:11.1.0 and will be removed before drupal:12.0.0. Implement service tagged with mime_type_map_modifier instead. See https://www.drupal.org/node/2311679',
      'file_mimetype_mapping',
      $mapping,
    );
    $map->setMapping($mapping);
  }

}
