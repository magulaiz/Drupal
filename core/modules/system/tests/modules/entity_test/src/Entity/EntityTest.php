<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the test entity class.
 *
 * @ContentEntityType(
 *   id = "entity_test",
 *   label = @Translation("Test entity"),
 *   handlers = {
 *     "list_builder" = "Drupal\entity_test\EntityTestListBuilder",
 *     "view_builder" = "Drupal\entity_test\EntityTestViewBuilder",
 *     "access" = "Drupal\entity_test\EntityTestAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\entity_test\EntityTestForm",
 *       "delete" = "Drupal\entity_test\EntityTestDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "views_data" = "Drupal\entity_test\EntityTestViewsData"
 *   },
 *   base_table = "entity_test",
 *   admin_permission = "administer entity_test content",
 *   persistent_cache = FALSE,
 *   list_cache_contexts = { "entity_test_view_grants" },
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "owner" = "user_id",
 *   },
 *   links = {
 *     "canonical" = "/entity_test/{entity_test}",
 *     "add-form" = "/entity_test/add",
 *     "edit-form" = "/entity_test/manage/{entity_test}/edit",
 *     "delete-form" = "/entity_test/delete/entity_test/{entity_test}",
 *   },
 *   field_ui_base_route = "entity.entity_test.admin_form",
 * )
 *
 * Note that this entity type annotation intentionally omits the "create" link
 * template. See https://www.drupal.org/node/2293697.
 */
class EntityTest extends ContentEntityBase implements EntityOwnerInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    if (empty($values['type'])) {
      $values['type'] = $storage->getEntityTypeId();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the test entity.'))
      ->setTranslatable(TRUE)
      ->setSetting('max_length', 64)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('Time the entity was created'))
      ->setTranslatable(TRUE);

    $fields += static::ownerBaseFieldDefinitions($entity_type);

    return $fields + \Drupal::state()->get($entity_type->id() . '.additional_base_field_definitions', []);
  }

  /**
   * Sets the name.
   *
   * @param string $name
   *   Name of the entity.
   *
   * @return $this
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * Returns the name.
   *
   * @return string
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityKey($key) {
    // Typically this protected method is used internally by entity classes and
    // exposed publicly through more specific getter methods. So that test cases
    // are able to set and access entity keys dynamically, update the visibility
    // of this method to public.
    return parent::getEntityKey($key);
  }

}
