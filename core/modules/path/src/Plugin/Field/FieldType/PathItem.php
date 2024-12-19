<?php

declare(strict_types=1);

namespace Drupal\path\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\path\PathVariant\CorePathVariants;
use Drupal\path\PathVariant\PathVariantRepositoryInterface;
use Drupal\path_alias\PathAliasStorage;

/**
 * Defines the 'path' field type.
 *
 * @property string|null $alias
 * @property int|null $pid
 * @property string|null $langcode
 * @property \Drupal\path\PathVariant\PathVariant|null $variant
 */
#[FieldType(
  id: "path",
  label: new TranslatableMarkup("Path"),
  description: new TranslatableMarkup("An entity field containing a path alias and related data."),
  default_widget: "path",
  no_ui: TRUE,
  list_class: PathFieldItemList::class,
  constraints: ["PathAlias" => []],
)]
class PathItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['alias'] = DataDefinition::create('string')
      ->setLabel(t('Path alias'));
    $properties['pid'] = DataDefinition::create('integer')
      ->setLabel(t('Path id'));
    $properties['langcode'] = DataDefinition::create('string')
      ->setLabel(t('Language Code'));
    $properties['variant'] = DataDefinition::create('any')
      ->setLabel(t('Path variant'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return ($this->alias === NULL || $this->alias === '') && ($this->pid === NULL || $this->pid === '') && ($this->langcode === NULL || $this->langcode === '');
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    if ($this->alias !== NULL) {
      $this->alias = trim($this->alias);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
    // If we have an alias, create or update a path alias entity.
    if (is_string($this->alias) && strlen($this->alias) > 0) {
      // When this is an entity insert or there is no existing path alias ID:
      if (!$update || $this->pid === NULL) {
        $variant = $this->variant ?? static::pathVariantRepository()->getDefaultPathVariant($this->getEntity());

        $path_alias = static::pathAliasStorage()->create([
          'path' => static::pathVariantRepository()->getInternalPathByPathVariant($this->getEntity(), $variant),
          'alias' => $this->alias,
          'variant' => $variant->getVariant() === CorePathVariants::Default ? NULL : (string) $variant,
          // If specified, rely on the langcode property for the language, so that the
          // existing language of an alias can be kept. That could for example be
          // unspecified even if the field/entity has a specific langcode.
          'langcode' => ($this->langcode && $this->pid) ? $this->langcode : $this->getLangcode(),
        ]);
        $path_alias->save();
        $this->pid = (int) $path_alias->id();
      }
      // When this is an entity update and there is an existing path alias ID:
      elseif ($this->pid !== NULL) {
        $path_alias = static::pathAliasStorage()->load($this->pid);

        if ($this->alias !== $path_alias->getAlias()) {
          $path_alias->setAlias($this->alias)->save();
        }
      }
    }
    elseif ($this->pid !== NULL) {
      // Otherwise, delete the old alias if the user erased it.
      $path_alias = static::pathAliasStorage()->load($this->pid);
      $this->getEntity()->isDefaultRevision()
        ? static::pathAliasStorage()->delete([$path_alias])
        : static::pathAliasStorage()->deleteRevision($path_alias->getRevisionId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['alias'] = '/' . str_replace(' ', '-', strtolower($random->sentences(3)));
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'alias';
  }

  /**
   * Path alias storage.
   */
  private static function pathAliasStorage(): PathAliasStorage {
    /** @var \Drupal\path_alias\PathAliasStorage */
    return \Drupal::entityTypeManager()->getStorage('path_alias');
  }

  /**
   * Get path variant repository service.
   */
  private static function pathVariantRepository(): PathVariantRepositoryInterface {
    /** @var \Drupal\path\PathVariant\PathVariantRepositoryInterface */
    return \Drupal::service(PathVariantRepositoryInterface::class);
  }

}
