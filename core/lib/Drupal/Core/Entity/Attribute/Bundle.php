<?php

declare(strict_types=1);

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Attribute for registering a bundle class.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Bundle {

  /**
   * Constructs a Bundle attribute object.
   *
   * @param string $entityTypeId
   *   The entity type ID.
   * @param string|null $bundle
   *   The bundle ID, or NULL to use the entity type ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $label
   *   The label of the bundle.
   */
  public function __construct(
    // @todo see if we can infer the entity type automatically.
    public string $entityTypeId,
    public ?string $bundle = NULL,
    public ?TranslatableMarkup $label = NULL,
  ) {
    $this->bundle ??= $this->entityTypeId;
  }

}
