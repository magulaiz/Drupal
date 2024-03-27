<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the test entity class.
 */
#[ContentEntityType(
  id: 'entity_test_field_methods',
  label: new TranslatableMarkup('Test entity - field methods and data table'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'bundle' => 'type',
    'label' => 'name',
    'langcode' => 'langcode',
  ],
  handlers: [
    'view_builder' => 'Drupal\entity_test\EntityTestViewBuilder',
    'access' => 'Drupal\entity_test\EntityTestAccessControlHandler',
    'form' => [
      'default' => 'Drupal\entity_test\EntityTestForm',
      'delete' => 'Drupal\entity_test\EntityTestDeleteForm',
    ],
    'views_data' => 'Drupal\views\EntityViewsData',
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider',
    ],
  ],
  admin_permission: 'administer entity_test content',
  base_table: 'entity_test_field_methods',
  data_table: 'entity_test_field_methods_property',
  translatable: TRUE,
)]
class EntityTestFieldMethods extends EntityTestMul {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['test_invocation_order'] = BaseFieldDefinition::create('auto_incrementing_test')
      ->setLabel(t('Test field method invocation order.'))
      ->setTranslatable(TRUE);

    return $fields;
  }

}
