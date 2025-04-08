<?php

namespace Drupal\path\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'path' entity field type.
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
    $alias = $this->get('alias')->getValue();
    $pid = $this->get('pid')->getValue();
    $langcode = $this->get('langcode')->getValue();

    return ($alias === NULL || $alias === '') && ($pid === NULL || $pid === '') && ($langcode === NULL || $langcode === '');
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    $alias = $this->get('alias')->getValue();
    if ($alias !== NULL) {
      $this->set('alias', trim($alias));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave($update) {
    $path_alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    $entity = $this->getEntity();
    $alias = $this->get('alias')->getValue();
    $pid = $this->get('pid')->getValue();

    // Load the path alias entity if the entity is being updated and its ID is
    // known.
    $path_alias = NULL;
    if ($update && $pid) {
      /** @var \Drupal\path_alias\PathAliasInterface $path_alias */
      $path_alias = $path_alias_storage->load($pid);
    }

    // Stop at this point if the alias hasn't been changed, even if it's a
    // fallback alias that can't be managed by this item.
    $existing_alias = NULL;
    if ($path_alias) {
      $existing_alias = $path_alias->getAlias();
    }
    if ($alias == $existing_alias) {
      return;
    }

    $is_multilingual = FALSE;
    if ($entity instanceof TranslatableInterface) {
      $is_multilingual = count($entity->getTranslationLanguages()) > 1;
    }

    // Detect if the alias this item holds can be updated or deleted.
    $has_own_alias = !empty($path_alias);
    if ($is_multilingual) {
      // On multilingual entities, the alias can only be managed if it has the
      // same language as the item. Otherwise, the editor would be able to
      // change the fallback alias by editing an entity translation.
      $has_own_alias = $has_own_alias && $path_alias->language()->getId() === $this->getLangcode();
    }

    // If we have an alias, we need to update or create a path alias entity.
    if ($alias && $has_own_alias) {
      $path_alias->setAlias($alias);
      $path_alias->save();
    }
    elseif ($alias && !$has_own_alias) {
      $path_alias = $path_alias_storage->create([
        'path' => '/' . $entity->toUrl()->getInternalPath(),
        'alias' => $alias,
        'langcode' => $this->getLangcode(),
      ]);
      $path_alias->save();
      $this->set('pid', $path_alias->id());
    }
    // Otherwise, delete the old alias if the user erased it.
    elseif ($has_own_alias) {
      $path_alias = $path_alias_storage->load($pid);
      if ($entity->isDefaultRevision()) {
        $path_alias_storage->delete([$path_alias]);
      }
      else {
        $path_alias_storage->deleteRevision($path_alias->getRevisionID());
      }
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

}
