<?php

namespace Drupal\path\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\path\PathVariant\PathVariantRepositoryInterface;
use Drupal\path\Plugin\Field\FieldType\PathItem;
use Drupal\path_alias\PathAliasStorage;
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

    $item = $items[$delta];
    assert($item instanceof PathItem);

    $element += ['#element_validate' => [[static::class, 'validateFormElement']]];

    $element['alias'] = [
      '#type' => 'textfield',
      '#title' => $this->fieldDefinition->getLabel(),
      '#default_value' => $item->alias,
      '#required' => $element['#required'],
      '#maxlength' => 255,
      '#description' => $this->t('Specify an alternative path by which this data can be accessed. For example, type "/about" when writing an about page.'),
    ];

    // Pass through item values as hidden values:
    $element['langcode'] = ['#type' => 'value', '#value' => $item->langcode];
    $element['pid'] = ['#type' => 'value', '#value' => $item->pid];
    $element['source'] = [
      '#type' => 'value',
      '#value' => $entity->isNew() ? NULL : static::pathVariantRepository()->getInternalPathByPathVariant($entity, $item->variant),
    ];
    $element['variant'] = ['#type' => 'value', '#value' => $item->variant];

    // If the advanced settings tabs-set is available (normally rendered in the
    // second column on wide-resolutions), place the field as a details element
    // in this tab-set.
    if (array_key_exists('advanced', $form)) {
      $element += [
        '#type' => 'details',
        '#title' => $this->t('URL path settings'),
        '#open' => $item->alias !== NULL,
        '#group' => 'advanced',
        '#access' => $entity->get($items->getName())->access('edit'),
        '#attributes' => [
          'class' => ['path-form'],
        ],
        '#attached' => [
          'library' => ['path/drupal.path'],
        ],
      ];
      $element['#weight'] = (float) sprintf('30.%s', $delta);
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state): array {
    $elements = [];

    foreach ($items as $delta => $item) {
      $titleArgs = [
        '@title' => $this->fieldDefinition->getLabel(),
        '@variant' => $item->variant?->getLabel() ?? $this->t('Unknown'),
      ];

      $element = [
        '#title' => $items->count() === 1
          ? $this->t('@title', $titleArgs, ['context' => 'entity path no variants'])
          // Display variant label when there is at least one available.
          : $this->t('@title (@variant)', $titleArgs, ['context' => 'entity path variants']),
      ];
      $elements[$delta] = $this->formSingleElement($items, $delta, $element, $form, $form_state);
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
      $path_alias = static::pathAliasStorage()->create([
        'path' => $element['source']['#value'],
        'alias' => $alias,
        'langcode' => $element['langcode']['#value'],
      ]);

      foreach ($path_alias->validate() as $violation) {
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
   * Get path variant repository service.
   */
  private static function pathVariantRepository(): PathVariantRepositoryInterface {
    /** @var \Drupal\path\PathVariant\PathVariantRepositoryInterface */
    return \Drupal::service(PathVariantRepositoryInterface::class);
  }

  /**
   * Path alias storage.
   */
  private static function pathAliasStorage(): PathAliasStorage {
    /** @var \Drupal\path_alias\PathAliasStorage */
    return \Drupal::entityTypeManager()->getStorage('path_alias');
  }

}
