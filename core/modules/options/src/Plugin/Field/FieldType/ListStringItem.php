<?php

namespace Drupal\options\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'list_string' field type.
 *
 * @FieldType(
 *   id = "list_string",
 *   label = @Translation("List (text)"),
 *   description = {
 *     @Translation("Values stored are text values"),
 *     @Translation("For example, 'US States': IL => Illinois, IA => Iowa, IN => Indiana"),
 *   },
 *   category = "selection_list",
 *   weight = -50,
 *   default_widget = "options_select",
 *   default_formatter = "list_default",
 * )
 */
class ListStringItem extends ListItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Text value'))
      ->addConstraint('Length', ['max' => 255])
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'value' => ['value'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function allowedValuesDescription() {
    $description = '<p>' . $this->t('The name will be used in displayed options and edit forms.');
    $description .= '<br/>' . $this->t('The key is the stored value.');
    $description .= '</p>';
    $description .= '<p>' . $this->t('Allowed HTML tags in labels: @tags', ['@tags' => FieldFilteredMarkup::displayAllowedTags()]) . '</p>';
    return $description;
  }

  /**
   * {@inheritdoc}
   */
  protected static function validateAllowedValue($option) {
    if (mb_strlen($option) > 255) {
      return new TranslatableMarkup('Allowed values list: each key must be a string at most 255 characters long.');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected static function castAllowedValue($value) {
    return (string) $value;
  }

  /**
   * Checks for existing keys for allowed values.
   */
  public static function exists(): bool {
    // Without access to the current form state, we cannot know if a given key
    // is in use. Return FALSE in all cases.
    return FALSE;
  }

}
