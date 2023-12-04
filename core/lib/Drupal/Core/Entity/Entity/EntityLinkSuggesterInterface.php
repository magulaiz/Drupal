<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Render\RenderableInterface;

/**
 * Provides a common interface for entity link suggesters.
 */
interface EntityLinkSuggesterInterface extends ConfigEntityInterface {

  /**
   * Whether the given entity type is linkable.
   *
   * Entity types must either have links or specify a link_target handler.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type to evaluate.
   *
   * @return bool
   *   TRUE if it is linkable, FALSE if not.
   */
  public static function isLinkableEntityType(EntityTypeInterface $entity_type): bool;

  /**
   * Gets the linkable entity types allowed by this link suggester.
   *
   * @return null|array
   *   NULL indicates all linkable entity types are allowed. If an array, the
   *   keys are entity type IDs, the values match the config schema.
   *
   * @see core.entity_link_suggestions.*
   */
  public function getEntityTypes(): ?array;

  /**
   * Gets the allowed bundles for the given entity type.
   *
   * @param string $entity_type_id
   *   The entity type to find the suggestion configuration for.
   *
   * @return null|array
   *   NULL if all bundles are allowed, a list of bundle names otherwise.
   *
   * @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection::defaultConfiguration()
   */
  public function getAllowedBundlesForEntityType(string $entity_type_id): ?array;

  /**
   * Describes which entity types and bundles can be linked.
   *
   * @return \Drupal\Core\Render\RenderableInterface
   */
  public function describe(): RenderableInterface;

}
