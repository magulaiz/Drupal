<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Defines the Test entity mul bundle configuration entity.
 */
#[\Drupal\Core\Entity\Attribute\ConfigEntityType(id: 'entity_test_mul_bundle', label: new TranslatableMarkup('Test entity multilingual bundle'), handlers: ['access' => '\Drupal\Core\Entity\EntityAccessControlHandler', 'form' => ['default' => '\Drupal\Core\Entity\BundleEntityFormBase'], 'route_provider' => ['html' => 'Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider']], admin_permission: 'administer entity_test_mul_with_bundle content', bundle_of: 'entity_test_mul_with_bundle', entity_keys: ['id' => 'id', 'label' => 'label'], config_export: ['id', 'label', 'description'], links: ['add-form' => '/entity_test_mul_bundle/add'])]
class EntityTestMulBundle extends ConfigEntityBundleBase implements EntityDescriptionInterface {

  /**
   * The machine name.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name.
   *
   * @var string
   */
  protected $label;

  /**
   * The description.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}
