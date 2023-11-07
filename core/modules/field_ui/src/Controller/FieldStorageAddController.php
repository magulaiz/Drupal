<?php

declare(strict_types=1);

namespace Drupal\field_ui\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FallbackFieldTypeCategory;
use Drupal\Core\Field\FieldTypeCategoryManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\Url;
use Drupal\field_ui\FieldUI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for building the field instance form.
 *
 * @internal
 */
final class FieldStorageAddController extends ControllerBase {
  use AjaxHelperTrait;

  /**
   * The name of the entity type.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The entity bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * FieldConfigAddController constructor.
   */
  public function __construct(
    protected FieldTypePluginManagerInterface $fieldTypePluginManager,
    protected FieldTypeCategoryManagerInterface $fieldTypeCategoryManager,
    protected PrivateTempStore $tempStore,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.field_type_category'),
      $container->get('tempstore.private')->get('field_ui'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, $entity_type_id = NULL, $bundle = NULL) {
    $this->entityTypeId = $entity_type_id;
    $this->bundle = $bundle;

    $field_type_options = $unique_definitions = [];
    $grouped_definitions = $this->fieldTypePluginManager->getGroupedDefinitions($this->fieldTypePluginManager->getUiDefinitions(), 'label', 'id');
    $category_definitions = $this->fieldTypeCategoryManager->getDefinitions();
    // Invoke a hook to get category properties.
    foreach ($grouped_definitions as $category => $field_types) {
      foreach ($field_types as $name => $field_type) {
        $unique_definitions[$category][$name] = ['unique_identifier' => $name] + $field_type;
        if ($this->fieldTypeCategoryManager->hasDefinition($category)) {
          $category_plugin = $this->fieldTypeCategoryManager->createInstance($category, $unique_definitions[$category][$name], $category_definitions[$category]);
          $field_type_options[$category_plugin->getPluginId()] = ['unique_identifier' => $name] + $field_type;
        }
        else {
          $field_type_options[(string) $field_type['label']] = ['unique_identifier' => $name] + $field_type;
        }
      }
    }
    $form['add-label'] = [
      '#type' => 'label',
      '#title' => t('Choose a type of field'),
      '#title_display' => 'before',
      '#required' => TRUE,
    ];

    $form['add'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'add-field-container',
      ],
    ];

    $field_type_options_radios = [];
    foreach ($field_type_options as $id => $field_type) {
      /** @var  \Drupal\Core\Field\FieldTypeCategoryInterface $category_info */
      $category_info = $this->fieldTypeCategoryManager->createInstance($field_type['category'], $field_type);
      $entity_type = $this->entityTypeManager()->getDefinition($this->entityTypeId);
      $route_parameters = [
        'entity_type' => $this->entityTypeId,
        'bundle' => $this->bundle,
        'selected_field_type' => $category_info->getPluginId(),
      ] + FieldUI::getRouteBundleParameter($entity_type, $this->bundle);
      $display_as_group = !($category_info instanceof FallbackFieldTypeCategory);
      $cleaned_class_name = Html::getClass($field_type['unique_identifier']);
      $field_type_options_radios[$id] = [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#attributes' => [
          'class' => ['field-option', 'use-ajax'],
          'role' => 'button',
          'tabindex' => '0',
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 1100,
            'title' => $this->t('Add field: @type', ['@type' => $category_info->getLabel()]),
          ]),
          'href' => Url::fromRoute("field_ui.field_storage_config_add_sub_{$this->entityTypeId}", $route_parameters)->toString(),
        ],
        '#weight' => $category_info->getWeight(),
        'thumb' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['field-option__thumb'],
          ],
          'icon' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['field-option__icon', $display_as_group ?
                "field-icon-{$field_type['category']}" : "field-icon-$cleaned_class_name",
              ],
            ],
          ],
        ],
        // Store some data we later need.
        '#data' => [
          '#group_display' => $display_as_group,
        ],
        'words' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['field-option__words'],
          ],
          'label' => [
            '#attributes' => [
              'class' => ['field-option__label'],
            ],
            '#type' => 'html_tag',
            '#tag' => 'span',
            '#value' => $category_info->getLabel(),
          ],
          'description' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['field-option__description'],
            ],
            '#markup' => $category_info->getDescription(),
          ],
        ],
      ];

      if ($libraries = $category_info->getLibraries()) {
        $field_type_options_radios[$id]['#attached']['library'] = $libraries;
      }
    }
    uasort($field_type_options_radios, [SortArray::class, 'sortByWeightProperty']);
    $form['add']['new_storage_type'] = $field_type_options_radios;
    $form['#attached']['library'][] = 'field_ui/drupal.field_ui';
    $form['#attached']['library'][] = 'field_ui/drupal.field_ui.manage_fields';
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

  /**
   * Creates a dummy field to set in temp store in order to build the edit form.
   *
   * @param string|null $entity_type_id
   *   The name of the entity type.
   * @param string|null $bundle
   *   The entity bundle.
   * @param string|null $field_name
   *   The field name.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The field instance edit form.
   */
  public function openModalForm(string $entity_type_id = NULL, string $bundle = NULL, string $field_name = NULL) {
    $form = [];
    if (!empty($field_name)) {
      $this->tempStore->delete("$entity_type_id:$field_name");
    }
    $form = $this->buildForm($form, $entity_type_id, $bundle);
    if ($this->isAjax()) {
      $response = new AjaxResponse();
      $dialog_options['modal'] = TRUE;
      $dialog_options['width'] = 1100;
      $response->addCommand(new OpenModalDialogCommand('Add field', $form, $dialog_options));
    }
    else {
      $response = $form;
    }

    return $response;
  }

}
