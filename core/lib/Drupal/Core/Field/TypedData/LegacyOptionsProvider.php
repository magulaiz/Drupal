<?php

namespace Drupal\Core\Field\TypedData;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\Options\DependentOptionsProviderInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Provides legacy support for field types implementing the provider interface.
 */
class LegacyOptionsProvider implements DependentOptionsProviderInterface, FieldStorageDefinitionAwareOptionsProviderInterface {

  use FieldStorageDefinitionAwareOptionsProviderTrait;

  /**
   * The field item.
   *
   * @var \Drupal\Core\Field\FieldItemInterface|null
   */
  protected $fieldItem;

  /**
   * {@inheritdoc}
   */
  public function setData(?TypedDataInterface $data = NULL) {
    // We get some field item property passed, so fetch the parent.
    $this->fieldItem = isset($data) ? $data->getParent() : NULL;
    return $this;
  }

  /**
   * Returns a field item of the field, possibly being a dummy item.
   *
   * @return \Drupal\Core\Field\FieldItemInterface|\Drupal\Core\TypedData\OptionsProviderInterface
   *   The field item.
   */
  protected function getFieldItem() {
    if (!isset($this->fieldItem)) {
      $field_definition = $this->getFieldStorageDefinition();
      // We support two legacy cases here:
      // - Base field definitions, in which case the object is the field
      //   definition also.
      // - Field storage config, for which we cannot know the bundle if no data
      //   is passed what previously was not required. If the legacy options
      //   provider is used in this non-legacy way, throw an exception.
      if ($field_definition instanceof BaseFieldDefinition) {
        $items = \Drupal::typedDataManager()
          ->create($field_definition);
        $this->fieldItem = $items[0];
      }
      elseif ($field_definition instanceof FieldStorageConfig) {
        throw new \LogicException(sprintf('The options provider defined for field %s implements the Legacy API and does not support non-legacy usage without data being passed. Convert the options provider to the latest API in order to allow this usage.',
          Html::escape($field_definition->id())));
      }
      else {
        throw new \LogicException("Legacy options provider is used in some non-legacy scenario.");
      }
    }
    return $this->fieldItem;
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleValues(?AccountInterface $account = NULL) {
    return $this->getFieldItem()->getPossibleValues($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleOptions(?AccountInterface $account = NULL) {
    return $this->getFieldItem()->getPossibleOptions($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableValues(?AccountInterface $account = NULL) {
    return $this->getFieldItem()->getSettableValues($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableOptions(?AccountInterface $account = NULL) {
    return $this->getFieldItem()->getSettableOptions($account);
  }

}
