<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Render\RenderableInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the entity links suggester configuration entity class.
 *
 * Entity link suggesters allow crafting a consistent linking experience across
 * forms, entity types, fields and widgets. Each entity link suggester can be
 * configured to suggest links for all every (linkable) entity type or for only
 * a subset of (linkable) entity types. For each entity type that has bundles,
 * it's possible to restrict to a subset of those as well.
 * Entity link suggesters do not perform the actual querying. They only contain
 * instructions to be followed. It is strongly recommended to not use entity
 * queries, but to use entity reference selection plugins to perform the actual
 * querying.
 *
 * @see \Drupal\Core\Entity\Entity\EntityLinkSuggesterInterface::isLinkableEntityType
 * @see \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
 *
 * The CKEditor 5 module provides a sample implementation.
 *
 * @see \Drupal\ckeditor5\Controller\EntityLinkSuggestionsController::getSuggestions()
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
 *     "label" = "admin_label",
 *   },
 *   config_export = {
 *     "id",
 *     "admin_label",
 *     "entity_types",
 *   }
 * )
 */
class EntityLinkSuggester extends ConfigEntityBase implements EntityLinkSuggesterInterface, RenderableInterface {

  use StringTranslationTrait;

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
  public static function isLinkableEntityType(EntityTypeInterface $entity_type): bool {
    $canonical = $entity_type->getLinkTemplate('canonical');
    $edit_form = $entity_type->getLinkTemplate('edit-form');
    return ($canonical !== FALSE && $canonical !== $edit_form) || $entity_type->hasHandlerClass('link_target', 'view');
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypes(): ?array {
    return $this->entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllowedBundlesForEntityType(string $entity_type_id): ?array {
    $filtered = array_filter(
      $this->entity_types ?? [],
      fn(array $s) => $s['entity_type'] === $entity_type_id
    );
    if (empty($filtered)) {
      return NULL;
    }
    assert(count($filtered) === 1 && array_keys(reset($filtered)) === ['entity_type', 'bundles']);
    return reset($filtered)['bundles'];
  }

  /**
   * {@inheritdoc}
   */
  public function toRenderable() {
    $entity_types = $this->getEntityTypes();
    $render_array = [];
    if ($entity_types === NULL) {
      $render_array = ['#markup' => '<em>' . $this->t('Everything') . '</em>'];
    }
    else {
      $entity_type_manager = \Drupal::entityTypeManager();
      $bundle_info = \Drupal::service('entity_type.bundle.info');
      $items = [];
      foreach ($entity_types as $entity_type_id => $detailed_settings) {
        $entity_type = $entity_type_manager->getDefinition($entity_type_id);
        $label = $entity_type->getCollectionLabel();
        $bundle_labels = $entity_type->getBundleEntityType()
          ? array_column($bundle_info->getBundleInfo($entity_type_id), 'label')
          : [];
        if (!$entity_type->getBundleEntityType()) {
          $items[] = $entity_type->getCollectionLabel();
        }
        else {
          if ($detailed_settings['bundles'] === NULL) {
            $items[] = $this->t('@linkable-entity-type-label <small>(<em>all</em> @bundle-label)</small>', [
              '@linkable-entity-type-label' => $label,
              '@bundle-label' => $entity_type_manager
                ->getDefinition($entity_type->getBundleEntityType())
                ->getPluralLabel(),
            ]);
          }
          else {
            $items[] = $this->t('@linkable-entity-type-label <small>(only @included-bundle-label-list)</small>', [
              '@linkable-entity-type-label' => $label,
              '@included-bundle-label-list' => implode(', ', array_intersect_key($bundle_labels, $detailed_settings['bundles'])),
            ]);
          }
        }
      }
      $render_array = [
        '#list_type' => 'ol',
        '#theme' => 'item_list',
        '#items' => $items,
      ];
    }
    return $render_array;
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
