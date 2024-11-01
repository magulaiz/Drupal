<?php

namespace Drupal\path\Plugin\Field\FieldType;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Represents a configurable entity path field.
 */
class PathFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $entity = $this->getEntity();
    $path_suffixes = $this->getPathSuffixes($entity);
    if (!$entity->isNew()) {
      /** @var \Drupal\path_alias\AliasRepositoryInterface $path_alias_repository */
      $path_alias_repository = \Drupal::service('path_alias.repository');

      $delta = 0;
      foreach ($path_suffixes as $view_mode_id => $path_suffix) {
        [, $view_mode] = \explode('.', $view_mode_id);
        // Default the langcode to the current language if this is a new entity or
        // there is no alias for an existent entity.
        // @todo Set the langcode to not specified for untranslatable fields
        //   in https://www.drupal.org/node/2689459.
        $value = ['langcode' => $this->getLangcode(), 'viewMode' => $view_mode];
        if ($path_alias = $path_alias_repository->lookupBySystemPath('/' . $entity->toUrl()->getInternalPath() . $path_suffix, $this->getLangcode())) {
          $value = [
            'alias' => $path_alias['alias'],
            'pid' => $path_alias['id'],
            'langcode' => $path_alias['langcode'],
            'viewMode' => $view_mode,
          ];
        }
        $this->list[$delta] = $this->createItem($delta, $value);
        $delta++;
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function defaultAccess($operation = 'view', ?AccountInterface $account = NULL) {
    if ($operation == 'view') {
      return AccessResult::allowed();
    }
    return AccessResult::allowedIfHasPermissions($account, ['create url aliases', 'administer url aliases'], 'OR')->cachePerPermissions();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Delete all aliases associated with this entity in the current language.
    $entity = $this->getEntity();
    $path_alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    $path_suffixes = $this->getPathSuffixes($entity);
    $entities = [];
    foreach ($path_suffixes as $path_suffix) {
      $entities += $path_alias_storage->loadByProperties([
        'path' => '/' . $entity->toUrl()->getInternalPath() . $path_suffix,
        'langcode' => $entity->language()->getId(),
      ]);
    }
    $path_alias_storage->delete($entities);
  }

  /**
   * Gets all the path variants for this entity.
   *
   * A view mode can be configured to have a page display. An alias may be
   * provided for each enabled view mode.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to lookup path variants for.
   *
   * @return array
   *   An array of path suffixes keyed by view mode. Each value can be appended
   *   to the canonical URL of the entity to build the complete URL.
   */
  protected function getPathSuffixes(mixed $entity): array {
    $view_mode_storage = \Drupal::entityTypeManager()->getStorage('entity_view_mode');
    $entity_type_id = $entity->getEntityTypeId();
    $view_mode_ids = $view_mode_storage
      ->getQuery()
      ->exists('path')
      ->condition('id', $entity->getEntityTypeId() . '.', 'STARTS_WITH')
      ->condition('id', $entity_type_id . '.full', '<>')
      ->accessCheck(FALSE)
      ->execute();
    $hidden_displays = \Drupal::entityTypeManager()->getStorage('entity_view_display')->getQuery()
      ->accessCheck(FALSE)
      ->condition('targetEntityType', $entity_type_id)
      ->condition('bundle', $entity->bundle())
      ->condition('pageDisplay', FALSE)
      ->execute();
    $hidden_display_view_modes = \array_map(fn (string $view_display_id) => \sprintf('%s.%s', $entity_type_id, \explode('.', $view_display_id)[2]), $hidden_displays);
    $view_mode_ids = \array_diff($view_mode_ids, $hidden_display_view_modes);
    $base = [$entity_type_id . '.full' => ''];
    if (\count($view_mode_ids) === 0) {
      return $base;
    }
    $view_modes = $view_mode_storage->loadMultiple($view_mode_ids);
    return $base + \array_map(fn(EntityViewModeInterface $view_mode) => '/' . $view_mode->getPath(), $view_modes);
  }

}
