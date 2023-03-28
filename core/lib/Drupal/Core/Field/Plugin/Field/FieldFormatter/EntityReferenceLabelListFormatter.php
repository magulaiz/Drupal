<?php

namespace Drupal\Core\Field\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_label_list",
 *   label = @Translation("Label list"),
 *   description = @Translation("Display the label of the referenced entities as list."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceLabelListFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'list_type' => 'ol',
    ] + parent::defaultSettings();
  }

  public static function getListTypeOptions() {
    return [
      'ol' => t('Ordered list'),
      'ul' => t('Unordered list'),
      'comma' => t('Comma separated'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements = parent::settingsForm($form, $form_state);
    $elements['list_type'] = [
      '#title' => t('List type'),
      '#type' => 'select',
      '#options' => static::getListTypeOptions(),
      '#default_value' => $this->getSetting('list_type'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $list_type_options = static::getListTypeOptions();
    $summary[] = $this->t('List type: @list_type', ['@list_type' => $list_type_options[$this->getSetting('list_type')]]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $element_items = $this->viewElements($items, $langcode);

    $item_list = [
      '#theme' => 'item_list',
      '#items' => $element_items,
    ];

    $list_type = $this->getSetting('list_type');

    if (in_array($list_type, ['ul', 'ol'])) {
      $item_list['#list_type'] = $list_type;
    } else if ($list_type == 'comma') {
      $item_list['#context'] = ['list_style' => 'comma-list'];
    }

    $elements = parent::view($items, $langcode);
    $elements['#theme'] = 'field_item_list';
    $elements['#items'] = $item_list;
        $elements = [];
    $output_as_link = $this->getSetting('link');

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      $label = $entity->label();
      // If the link is to be displayed and the entity has a uri, display a
      // link.
      if ($output_as_link && !$entity->isNew()) {
        try {
          $uri = $entity->toUrl();
        }
        catch (UndefinedLinkTemplateException $e) {
          // This exception is thrown by \Drupal\Core\Entity\Entity::urlInfo()
          // and it means that the entity type doesn't have a link template nor
          // a valid "uri_callback", so don't bother trying to output a link for
          // the rest of the referenced entities.
          $output_as_link = FALSE;
        }
      }

      if ($output_as_link && isset($uri) && !$entity->isNew()) {
        $elements[$delta] = [
          '#type' => 'link',
          '#title' => $label,
          '#url' => $uri,
          '#options' => $uri->getOptions(),
        ];

        if (!empty($items[$delta]->_attributes)) {
          $elements[$delta]['#options'] += ['attributes' => []];
          $elements[$delta]['#options']['attributes'] += $items[$delta]->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and shouldn't be rendered in the field template.
          unset($items[$delta]->_attributes);
        }
      }
      else {
        $elements[$delta] = ['#plain_text' => $label];
      }
      $elements[$delta]['#cache']['tags'] = $entity->getCacheTags();
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity) {
    return $entity->access('view label', NULL, TRUE);
  }

}
