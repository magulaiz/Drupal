<?php

namespace Drupal\media_library;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\file\Entity\File;

/**
 * The media library opener for text editors.
 *
 * @internal
 *   This is an internal part of Media Library's text editor integration.
 */
class MediaLibraryEditorOpener implements MediaLibraryOpenerInterface {

  /**
   * The text format entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $filterStorage;

  /**
   * The media storage.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * The MediaLibraryEditorOpener constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->filterStorage = $entity_type_manager->getStorage('filter_format');
    $this->mediaStorage = $entity_type_manager->getStorage('media');
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess(MediaLibraryState $state, AccountInterface $account) {
    $filter_format_id = $state->getOpenerParameters()['filter_format_id'];
    $filter_format = $this->filterStorage->load($filter_format_id);
    if (empty($filter_format)) {
      return AccessResult::forbidden()
        ->addCacheTags(['filter_format_list'])
        ->setReason("The text format '$filter_format_id' could not be loaded.");
    }
    $filters = $filter_format->filters();
    return $filter_format->access('use', $account, TRUE)
      ->andIf(AccessResult::allowedIf($filters->has('media_embed') && $filters->get('media_embed')->status === TRUE));
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionResponse(MediaLibraryState $state, array $selected_ids) {
    $selected_media = $this->mediaStorage->load(reset($selected_ids));
    $selected_source = $selected_media->getSource();
    $plugin_id = $selected_source->getPluginId();

    // Get type id
    $type_id = $state->getSelectedTypeId();

    // Assume source type
    $src_type = $plugin_id;

    $alt = '';
    $url = '';
    if ($selected_media) {
      $fid = $selected_source->getSourceFieldValue($selected_media);
      $file = File::load($fid);
      if ($file) {
        $url = $file->createFileUrl();
      } else {
        $url = $fid;
      }

      if ($src_type == 'image') {
        $alt = $selected_source->getMetadata($selected_media, 'thumbnail_alt_value');
      }
    }

    $response = new AjaxResponse();
    $values = [
      'attributes' => [
        'data-entity-type' => 'media',
        'data-entity-uuid' => $selected_media->uuid(),
        'src' => $url,
        'alt' => $alt,
        'type-id' => $type_id,
        'src-type' => $src_type,
      ],
    ];
    $response->addCommand(new EditorDialogSave($values));

    return $response;
  }

}
