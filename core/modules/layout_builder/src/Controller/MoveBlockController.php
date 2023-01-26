<?php

namespace Drupal\layout_builder\Controller;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a controller to move a block.
 *
 * @internal
 *   Controller classes are internal.
 */
class MoveBlockController implements ContainerInjectionInterface {

  use LayoutRebuildTrait;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * LayoutController constructor.
   *
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, AccessManagerInterface $access_manager) {
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->accessManager = $access_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('access_manager')
    );
  }

  /**
   * Moves a block to another region.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta_from
   *   The delta of the original section.
   * @param int $delta_to
   *   The delta of the destination section.
   * @param string $region_to
   *   The new region for this block.
   * @param string $block_uuid
   *   The UUID for this block.
   * @param string|null $preceding_block_uuid
   *   (optional) If provided, the UUID of the block to insert this block after.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response.
   */
  public function build(SectionStorageInterface $section_storage, int $delta_from, int $delta_to, $region_to, $block_uuid, $preceding_block_uuid = NULL) {
    $section = $section_storage->getSection($delta_from);

    $component = $section->getComponent($block_uuid);
    $section->removeComponent($block_uuid);

    // If the block is moving from one section to another, update the original
    // section and load the new one.
    if ($delta_from !== $delta_to) {
      $section = $section_storage->getSection($delta_to);
    }

    // If a preceding block was specified, insert after that. Otherwise add the
    // block to the front.
    $component->setRegion($region_to);
    if (isset($preceding_block_uuid)) {
      $section->insertAfterComponent($preceding_block_uuid, $component);
    }
    else {
      $section->insertComponent(0, $component);
    }

    $this->layoutTempstoreRepository->set($section_storage);
    return $this->rebuildLayout($section_storage);
  }

  /**
   * Checks if moving a block to another region is allowed.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta_from
   *   The delta of the original section.
   * @param int $delta_to
   *   The delta of the destination section.
   * @param string $region_to
   *   The new region for this block.
   * @param string $block_uuid
   *   The UUID for this block.
   * @param string|null $preceding_block_uuid
   *   (optional) If provided, the UUID of the block to insert this block after.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   An JSON response with the result of the access check.
   */
  public function allowed(SectionStorageInterface $section_storage, int $delta_from, int $delta_to, $region_to, $block_uuid, $preceding_block_uuid = NULL) {
    // Check if user has access to the layout_builder.move_block route.
    $access = $this->accessManager->checkNamedRoute(
      'layout_builder.move_block',
      [
        'section_storage_type' => $section_storage->getStorageType(),
        'section_storage' => $section_storage->getStorageId(),
        'delta_from' => $delta_from,
        'delta_to' => $delta_to,
        'region_to' => $region_to,
        'block_uuid' => $block_uuid,
        'preceding_block_uuid' => $preceding_block_uuid,
      ]
    );

    // Return the result of the access check as JSON.
    return new JsonResponse([
      'access' => ($access instanceof AccessResultInterface) ? $access->isAllowed() : $access
    ]);
  }

}
