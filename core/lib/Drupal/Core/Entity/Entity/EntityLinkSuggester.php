<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the entity links suggester configuration entity class.
 *
 * Entity link suggesters …
 *
 * @see \Drupal\Core\Entity\EntityDisplayRepositoryInterface::getAllFormModes()
 * @see \Drupal\Core\Entity\EntityDisplayRepositoryInterface::getFormModes()
 *
 * @ConfigEntityType(
 *   id = "entity_link_suggester",
 *   label = @Translation("Link suggester"),
 *   label_collection = @Translation("Link suggesters"),
 *   label_singular = @Translation("link suggester"),
 *   label_plural = @Translation("link suggesters"),
 *   label_count = @PluralTranslation(
 *     singular = "@count link suggester",
 *     plural = "@count link suggesters",
 *   ),
 *   admin_permission = "administer site configuration",
 *   handlers = {
 *     "list_builder" = "\Drupal\Core\Entity\Entity\EntityLinkSuggesterListBuilder",
 *     "form" = {
 *       "default" = "\Drupal\Core\Entity\Entity\EntityLinkSuggesterForm",
 *       "delete" = "\Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "\Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   links = {
 *     "collection" = "/admin/config/content/link-suggesters",
 *     "add-form" = "/admin/config/content/link-suggesters/add",
 *     "edit-form" = "/admin/config/content/link-suggesters/{entity_link_suggester}/edit",
 *     "delete-form" = "/admin/config/content/link-suggesters/{entity_link_suggester}/delete",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "admin_label"
 *   },
 *   config_export = {
 *     "id",
 *     "admin_label",
 *     "entity_types",
 *   }
 * )
 */
class EntityLinkSuggester extends ConfigEntityBase implements EntityLinkSuggesterInterface {

  /**
   * The link suggester machine name.
   *
   * @var string
   */
  protected $id;

  /**
   * The administrative label of the link suggester entity.
   *
   * @var string
   */
  protected $admin_label;

  /**
   * The allowed linkable entity types.
   *
   * @var array[]
   *
   * @see `type: core.entity_link_suggestions.*:entity_types` for the structure.
   */
  protected $entity_types;

  /**
   * {@inheritdoc}
   */
  public function getEntityTypes(): ?array {
    return $this->entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $entity_type_manager = \Drupal::entityTypeManager();
    foreach ($this->entity_types ?? [] as $entity_type_id => $details) {
      $entity_type = $entity_type_manager->getDefinition($entity_type_id);

      // Depend on all extensions for the enabled linkable entity types.
      $this->addDependency('module', $entity_type->getProvider());

      // Depend on all bundle entity types that are included.
      if ($entity_type->getBundleEntityType()) {
        $bundle_entities = $entity_type_manager
          ->getStorage($entity_type->getBundleEntityType())
          ->loadMultiple();
        $included_bundles = $details['bundles'];
        $bundle_entities = $included_bundles === NULL
          ? $bundle_entities
          : array_intersect_key($bundle_entities, $included_bundles);
        foreach ($bundle_entities as $bundle_entity) {
          $this->addDependency('config', $bundle_entity->getConfigDependencyName());
        }
      }
    }

    return $this;
  }

}
