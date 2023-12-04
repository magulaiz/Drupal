<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Render\RenderableInterface;

/**
 * Provides a common interface for entity link suggesters.
 */
interface EntityLinkSuggesterInterface extends ConfigEntityInterface {

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
   * Describes which entity types and bundles can be linked.
   *
   * @return \Drupal\Core\Render\RenderableInterface
   */
  public function describe(): RenderableInterface;

}
