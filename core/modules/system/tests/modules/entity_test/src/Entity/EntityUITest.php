<?php

namespace Drupal\entity_test\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Provides the Entity UI Test entity.
 *
 * @ContentEntityType(
 *   id = "entity_ui_test",
 *   label = @Translation("Entity UI Test"),
 *   label_collection = @Translation("Entity UI Tests"),
 *   label_singular = @Translation("entity ui test"),
 *   label_plural = @Translation("entity ui tests"),
 *   label_count = @PluralTranslation(
 *     singular = "@count entity ui test",
 *     plural = "@count entity ui tests",
 *   ),
 *   bundle_label = @Translation("Entity UI Test Type"),
 *   base_table = "entity_ui_test",
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *     "link_provider" = "Drupal\Core\Entity\Menu\DefaultContentEntityLinksProvider",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\entity_test\EntityUITestListBuilder",
 *   },
 *   admin_permission = "administer entity ui tests",
 *   entity_keys = {
 *     "id" = "entity_ui_test_id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *   },
 *   bundle_entity_type = "entity_ui_test_type",
 *   field_ui_base_route = "entity.entity_ui_test_type.edit_form",
 *   links = {
 *     "add-page" = "/entity_ui_test/add",
 *     "add-form" = "/entity_ui_test/add/{entity_ui_test_type}",
 *     "canonical" = "/entity_ui_test/{entity_ui_test}",
 *     "collection" = "/admin/content/entity_ui_test",
 *     "delete-form" = "/entity_ui_test/{entity_ui_test}/delete",
 *     "edit-form" = "/entity_ui_test/{entity_ui_test}/edit",
 *   },
 * )
 */
class EntityUITest extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t("Title"))
      ->setRequired(TRUE)
      ->setSetting("max_length", 255)
      ->setDisplayOptions("form", [
        'type' => "string_textfield",
        'weight' => "-5",
      ])
      ->setDisplayConfigurable("view", TRUE)
      ->setDisplayConfigurable("form", TRUE);

    return $fields;
  }

}
