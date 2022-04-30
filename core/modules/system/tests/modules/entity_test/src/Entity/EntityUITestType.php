<?php

namespace Drupal\entity_test\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Provides the Entity UI Test Type entity.
 *
 * @ConfigEntityType(
 *   id = "entity_ui_test_type",
 *   label = @Translation("Entity UI Test Type"),
 *   label_collection = @Translation("Entity UI Test Types"),
 *   label_singular = @Translation("entity ui test type"),
 *   label_plural = @Translation("entity ui test types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count entity ui test type",
 *     plural = "@count entity ui test types",
 *   ),
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "link_provider" = "Drupal\Core\Entity\Menu\DefaultConfigEntityLinksProvider",
 *     "form" = {
 *       "default" = "Drupal\entity_test\Form\EntityUITestTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\entity_test\EntityUITestTypeListBuilder",
 *   },
 *   admin_permission = "administer entity ui test types",
 *   bundle_of = "entity_ui_test",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/entity_ui_test_type/add",
 *     "canonical" = "/admin/structure/entity_ui_test_type/{entity_ui_test_type}",
 *     "collection" = "/admin/structure/entity_ui_test_type",
 *     "edit-form" = "/admin/structure/entity_ui_test_type/{entity_ui_test_type}/edit",
 *     "delete-form" = "/admin/structure/entity_ui_test_type/{entity_ui_test_type}/delete",
 *   },
 * )
 */
class EntityUITestType extends ConfigEntityBundleBase {

  /**
   * Machine name.
   *
   * @var string
   */
  protected $id = '';

  /**
   * Name.
   *
   * @var label
   */
  protected $label = '';

}
