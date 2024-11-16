<?php

namespace Drupal\path\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'path' widget.
 */
#[FieldWidget(
  id: 'path',
  label: new TranslatableMarkup('URL alias'),
  field_types: ['path']
)]
class PathWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $entity = $items->getEntity();

    $viewMode = \Drupal::entityTypeManager()->getStorage('entity_view_mode')->load(\sprintf('%s.%s', $entity->getEntityTypeId(), $items[$delta]->viewMode));
    $suffix = $viewMode?->getPath() ?? '';
    if ($suffix !== '') {
      $suffix = '/' . $suffix;
    }
    $element += [
      '#element_validate' => [[static::class, 'validateFormElement']],
    ];
    $element['view_mode_label'] = [
      '#type' => 'item',
      '#title' => $this->t('View mode'),
      '#value' => $viewMode?->label() ?? $this->t('Full'),
      '#access' => FALSE,
    ];
    $element['viewMode'] = [
      '#type' => 'value',
      '#value' => $items[$delta]->viewMode,
    ];
    $element['alias'] = [
      '#type' => 'textfield',
      '#title' => $element['#title'],
      '#default_value' => $items[$delta]->alias,
      '#required' => $element['#required'],
      '#maxlength' => 255,
      '#description' => $this->t('Specify an alternative path by which this data can be accessed. For example, type "/about" when writing an about page.'),
    ];
    $element['pid'] = [
      '#type' => 'value',
      '#value' => $items[$delta]->pid,
    ];
    $element['source'] = [
      '#type' => 'value',
      '#value' => !$entity->isNew() ? '/' . $entity->toUrl()->getInternalPath() . $suffix : NULL,
    ];
    $element['langcode'] = [
      '#type' => 'value',
      '#value' => $items[$delta]->langcode,
    ];

    // If the advanced settings tabs-set is available (normally rendered in the
    // second column on wide-resolutions), place the field as a details element
    // in this tab-set.
    if (isset($form['advanced']) && $items->count() <= 2) {
      $element += [
        '#type' => 'details',
        '#title' => $this->t('URL path settings'),
        '#open' => !empty($items[$delta]->alias),
        '#group' => 'advanced',
        '#access' => $entity->get('path')->access('edit'),
        '#attributes' => [
          'class' => ['path-form'],
        ],
        '#attached' => [
          'library' => ['path/drupal.path'],
        ],
      ];
      $element['#weight'] = 30;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);
    // Remove add more.
    unset($elements['add_more']);
    // Remove empty item.
    unset($elements[$items->count() - 1]);
    foreach ($items as $delta => $item) {
      if (!\array_key_exists($delta, $elements)) {
        continue;
      }
      if ($items->count() > 2) {
        $elements[$delta]['alias']['#title'] = $this->t('@title (@view_mode view mode)', [
          '@title' => $this->fieldDefinition->getLabel(),
          '@view_mode' => $elements[$delta]['view_mode_label']['#value'] ?? $this->t('Unknown'),
        ]);
        // Remove the duplicated description.
        unset($elements[$delta]['alias']['#description']);
      }
      else {
        $elements[$delta]['alias']['#title'] = $this->fieldDefinition->getLabel();
        $elements[$delta]['#title'] = $this->fieldDefinition->getLabel();
        $elements[$delta]['_weight']['#access'] = FALSE;
      }
      // Remove the remove option.
      unset($elements[$delta]['_actions']);
    }
    if ($items->count() > 2) {
      $elements = [
        'elements' => $elements,
        '#type' => 'details',
        '#title' => $this->t('URL path settings'),
        '#open' => !empty($items[0]->alias),
        '#description' => $this->t('Specify an alternative path by which this data can be accessed. For example, type "/about" when writing an about page.'),
        '#group' => 'advanced',
        '#access' => $items->getEntity()->get('path')->access('edit'),
        '#attributes' => [
          'class' => ['path-form'],
        ],
        '#attached' => [
          'library' => ['path/drupal.path'],
        ],
        '#weight' => 30,
      ];
    }
    return $elements;
  }

  /**
   * Form element validation handler for URL alias form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateFormElement(array &$element, FormStateInterface $form_state) {
    // Trim the submitted value of whitespace and slashes.
    $alias = rtrim(trim($element['alias']['#value']), " \\/");
    if ($alias !== '') {
      $form_state->setValueForElement($element['alias'], $alias);

      /** @var \Drupal\path_alias\PathAliasInterface $path_alias */
      $path_alias = \Drupal::entityTypeManager()->getStorage('path_alias')->create([
        'path' => $element['source']['#value'],
        'alias' => $alias,
        'langcode' => $element['langcode']['#value'],
      ]);
      $violations = $path_alias->validate();

      foreach ($violations as $violation) {
        // Newly created entities do not have a system path yet, so we need to
        // disregard some violations.
        if (!$path_alias->getPath() && $violation->getPropertyPath() === 'path') {
          continue;
        }
        $form_state->setError($element['alias'], $violation->getMessage());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return $element['alias'];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    if (\array_key_exists('_original_delta', $values[0])) {
      unset($values[0]['_original_delta']);
      return $values[0];
    }
    return $values;
  }

}
