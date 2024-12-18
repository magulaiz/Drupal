<?php

declare(strict_types=1);

namespace Drupal\path\Plugin\Field\FieldType;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\path\PathVariant\CorePathVariants;
use Drupal\path\PathVariant\PathVariantRepositoryInterface;
use Drupal\path\PathVariant\PlaceHolderInternalPath;
use Drupal\path_alias\AliasRepositoryInterface;
use Drupal\path_alias\PathAliasStorage;

/**
 * Represents a path which may be overridden.
 */
class PathFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $entity = $this->getEntity();
    $delta = 0;
    foreach (static::pathVariantRepository()->getInternalPaths($entity) as [$internalPath, $variant]) {
      $langCode = $this->getLangcode();
      $pathAlias = $internalPath instanceof PlaceHolderInternalPath
        ? NULL
        : static::pathAliasRepository()->lookupBySystemPath(
          $internalPath,
          $langCode,
          // The 'full' view mode gets the default/legacy NULL value.
          $variant->getVariant() === CorePathVariants::Default ? NULL : (string) $variant,
        );

      $value = [
        // Default the langcode to the current language if this is a new
        // entity or there is no alias for an existent entity.
        // @todo Set the langcode to not specified for untranslatable fields
        // in https://www.drupal.org/node/2689459.
        'langcode' => $langCode,
        'variant' => $variant,
      ];
      $this->list[$delta] = $this->createItem(
        $delta,
        ($pathAlias !== NULL ? [
          'alias' => $pathAlias['alias'],
          'pid' => $pathAlias['id'],
          'langcode' => $pathAlias['langcode'],
        ] : []) + $value,
      );
      $delta++;
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
    /** @var \Drupal\path_alias\PathAliasInterface[] $pathAliases */
    $pathAliases = [];
    foreach (static::pathVariantRepository()->getInternalPaths($entity) as [$internalPath]) {
      $pathAliases += static::pathAliasStorage()->loadByProperties([
        'path' => $internalPath,
        'langcode' => $entity->language()->getId(),
      ]);
    }
    static::pathAliasStorage()->delete($pathAliases);
  }

  /**
   * Path alias storage.
   */
  private static function pathAliasStorage(): PathAliasStorage {
    /** @var \Drupal\path_alias\PathAliasStorage */
    return \Drupal::entityTypeManager()->getStorage('path_alias');
  }

  /**
   * Path alias repository service.
   */
  private static function pathAliasRepository(): AliasRepositoryInterface {
    /** @var \Drupal\path_alias\AliasRepositoryInterface */
    return \Drupal::service(AliasRepositoryInterface::class);
  }

  /**
   * Get path variant repository service.
   */
  private static function pathVariantRepository(): PathVariantRepositoryInterface {
    /** @var \Drupal\path\PathVariant\PathVariantRepositoryInterface */
    return \Drupal::service(PathVariantRepositoryInterface::class);
  }

}
