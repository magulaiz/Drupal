<?php

namespace Drupal\field_ui\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FallbackFieldTypeCategory;
use Drupal\Core\Field\FieldTypeCategoryManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\Url;
use Drupal\field_ui\FieldUI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for the "field storage" add page.
 *
 * @internal
 */
class FieldStorageAddForm extends FormBase {

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The field type plugin manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypePluginManager;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new FieldStorageAddForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   (optional) The entity field manager.
   * @param \Drupal\Core\TempStore\PrivateTempStore|null $tempStore
   *   The private tempstore.
   * @param \Drupal\Core\Field\FieldTypeCategoryManagerInterface|null $fieldTypeCategoryManager
   *   The field type category plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FieldTypePluginManagerInterface $field_type_plugin_manager, ConfigFactoryInterface $config_factory, EntityFieldManagerInterface $entity_field_manager, protected ?PrivateTempStore $tempStore = NULL, protected ?FieldTypeCategoryManagerInterface $fieldTypeCategoryManager = NULL) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldTypePluginManager = $field_type_plugin_manager;
    $this->configFactory = $config_factory;
    $this->entityFieldManager = $entity_field_manager;
    if ($this->tempStore === NULL) {
      @trigger_error('Calling FieldStorageAddForm::__construct() without the $tempStore argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3383719', E_USER_DEPRECATED);
      $this->tempStore = \Drupal::service('tempstore.private')->get('field_ui');
    }
    if ($this->fieldTypeCategoryManager === NULL) {
      @trigger_error('Calling FieldStorageAddForm::__construct() without the $fieldTypeCategoryManager argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3375740', E_USER_DEPRECATED);
      $this->fieldTypeCategoryManager = \Drupal::service('plugin.manager.field.field_type_category');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'field_ui_field_storage_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('config.factory'),
      $container->get('entity_field.manager'),
      $container->get('tempstore.private')->get('field_ui'),
      $container->get('plugin.manager.field.field_type_category'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL, $bundle = NULL) {
    if (!$form_state->get('entity_type_id')) {
      $form_state->set('entity_type_id', $entity_type_id);
    }
    if (!$form_state->get('bundle')) {
      $form_state->set('bundle', $bundle);
    }
    $this->entityTypeId = $form_state->get('entity_type_id');
    $this->bundle = $form_state->get('bundle');

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
      $entity_type = $this->entityTypeManager->getDefinition($this->entityTypeId);
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
    // Place the 'translatable' property as an explicit value so that contrib
    // modules can form_alter() the value for newly created fields. By default
    // we create field storage as translatable so it will be possible to enable
    // translation at field level.
    $form['translatable'] = [
      '#type' => 'value',
      '#value' => TRUE,
    ];
    $form['#attached']['library'] = [
      'field_ui/drupal.field_ui',
      'field_ui/drupal.field_ui.manage_fields',
      'core/drupal.ajax',
      'core/drupal.dialog.ajax',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Can also be removed.
    if (!$form_state->getValue('new_storage_type')) {
      $form_state->setErrorByName('new_storage_type', $this->t('You need to select a field type.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No-op since form is routed to the below controller.
    // @see \Drupal\field_ui\Controller\FieldTempStoreController::setTempStore
  }

  /**
   * Callback to rebuild form.
   */
  public static function rebuildForm($form, FormStateInterface &$form_state) {
    $form_state->setRebuild();
  }

}
