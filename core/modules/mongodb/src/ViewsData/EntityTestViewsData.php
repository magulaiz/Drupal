<?php

namespace Drupal\mongodb\ViewsData;

use Drupal\Component\Utility\NestedArray;

/**
 * The MongoDB implementation of \Drupal\entity_test\EntityTestViewsData.
 */
class EntityTestViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    if ($this->entityType->id() === 'entity_test_computed_field') {
      $data['entity_test_computed_field']['computed_string_field'] = [
        'title' => $this->t('Computed String Field'),
        'field' => [
          'id' => 'field',
          'default_formatter' => 'string',
          'field_name' => 'computed_string_field',
        ],
      ];
    }

    // Added for MongoDB.
    if ($this->entityType->id() === 'entity_test_multivalue_basefield') {
      $data['entity_test_multivalue_basefield']['name'] = [
        'title' => $this->t('Name'),
        'help' => $this->t('The name of the test entity.'),
        'field' => [
          'id' => 'field',
        ],
        'argument' => [
          'id' => 'string',
        ],
        'filter' => [
          'id' => 'string',
        ],
        'sort' => [
          'id' => 'standard',
        ],
        'entity field' => 'name',
        'real field' => 'entity_test_multivalue_basefield__name.name_value',
      ];
      // $data['entity_test_multivalue_basefield']['name_value'] = $data['entity_test_multivalue_basefield']['name'];
    }

    if ($this->entityType->id() == 'entity_test') {
      $data = NestedArray::mergeDeep($data, \Drupal::state()->get('entity_test.views_data', []));
    }

    return $data;
  }

}
