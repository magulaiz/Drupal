<?php

namespace Drupal\field_ui\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
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
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field_ui\FieldUI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for the "field storage" add page.
 *
 * @internal
 */
class FieldStorageAddSubfieldForm extends FormBase {
  use AjaxFormHelperTrait;
  use AjaxHelperTrait;

  /**
   * The name of the selected field type.
   *
   * @var string
   */
  protected $selectedFieldType;

   /**
   * The name of the selected field type.
   *
   * @var string
   */
  protected $selectedFieldStorageType;

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
   * ID for the field stored in temp store.
   *
   * @var string
   */
  protected $fieldTempStoreKey;

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
  public function buildForm(array $form, FormStateInterface $form_state, $selected_field_type = NULL, $entity_type_id = NULL, $bundle = NULL, $field_type_options = NULL) {
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
      $display_as_group = !($category_info instanceof FallbackFieldTypeCategory);
      $field_type_options_radios[$id] = [
        // Store some data we later need.
        '#data' => [
          '#group_display' => $display_as_group,
        ],
      ];
    }

    $form['no_js_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Change field'),
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['js-hide'],
      ],
      '#submit' => [[static::class, 'rebuildForm']],
    ];
    // @todo Maybe rename this since the 'Continue' button lives in here now and its not just group fields.
    $form['group_field_options_wrapper'] = [
      '#prefix' => '<div id="group-field-options-wrapper" class="group-field-options-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['actions'] = ['#type' => 'actions'];

    $form['group_field_options_wrapper']['field_name_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#size' => 30,
    // '#required' => TRUE,
      '#maxlength' => 255,
      '#weight' => -20,
    ];

    $field_prefix = $this->config('field_ui.settings')->get('field_prefix');
    $form['group_field_options_wrapper']['field_name'] = [
      '#type' => 'machine_name',
      '#field_prefix' => $field_prefix,
      '#size' => 15,
      '#description' => $this->t('A unique machine-readable name containing letters, numbers, and underscores.'),
          // Calculate characters depending on the length of the field prefix
          // setting. Maximum length is 32.
      '#maxlength' => FieldStorageConfig::NAME_MAX_LENGTH - strlen($field_prefix),
      '#machine_name' => [
        'source' => ['group_field_options_wrapper', 'field_name_label'],
        'exists' => [$this, 'fieldNameExists'],
      ],
      '#required' => FALSE,
    ];
    // Set the selected field to the form state by checking
    // the checked attribute.
    $selected_field_storage_type = NULL;
    if (isset($selected_field_type)) {
      $this->selectedFieldType = $selected_field_type;
      $group_display = $field_type_options_radios[$selected_field_type]['#data']['#group_display'];
      if ($group_display) {
        $form['group_field_options_wrapper']['label'] = [
          '#type' => 'label',
          '#title' => t('Choose an option below'),
          '#required' => TRUE,
        ];
        $form['group_field_options_wrapper']['fields'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['group-field-options'],
          ],
        ];

        foreach ($unique_definitions[$selected_field_type] as $option_key => $option) {
          $radio_element = [
            '#type' => 'radio',
            '#theme_wrappers' => ['form_element__new_storage_type'],
            '#title' => $option['label'],
            '#description' => [
              '#theme' => 'item_list',
              '#items' => $unique_definitions[$selected_field_type][$option_key]['description'],
            ],
            // @todo Try removing id.
            '#id' => Html::getClass($option['unique_identifier']),
            '#weight' => $option['weight'],
            '#parents' => ['group_field_options_wrapper'],
            '#attributes' => [
              'class' => ['field-option-radio'],
              'data-once' => 'field-click-to-select',
              'checked' => $this->getRequest()->request->get('group_field_options_wrapper') !== NULL && $this->getRequest()->request->get('group_field_options_wrapper') == $option_key,
            ],
            '#wrapper_attributes' => [
              'class' => ['js-click-to-select', 'subfield-option'],
            ],
            '#ajax' => [
              'callback' => [$this, 'showFieldsCallback'],
              'event' => 'updateOptions',
              'wrapper' => 'group-field-options-wrapper',
              'progress' => 'none',
              'disable-refocus' => TRUE,
            ],
            '#variant' => 'field-suboption',
          ];
          $radio_element['#return_value'] = $option['unique_identifier'];
          if ((string) $option['unique_identifier'] === 'entity_reference') {
            $radio_element['#title'] = 'Other';
            $radio_element['#weight'] = 10;
          }
          $group_field_options[$option['unique_identifier']] = $radio_element;
        }
        uasort($group_field_options, [SortArray::class, 'sortByWeightProperty']);
        $form['group_field_options_wrapper']['fields'] += $group_field_options;

        // Set the variable as the currently checked option.
        foreach ($group_field_options as $option) {
          if ($option['#attributes']['checked']) {
            $selected_field_storage_type = $option['#return_value'];
            $this->selectedFieldStorageType = $selected_field_storage_type;
            break;
          }
        }
      }

      // Create a random string as the field name, so we can create a dummy
      // entity in order to create the field storage settings. Inside the edit
      // form, a new entity with the user inputted field name will get created
      // that is saved.
      // @see \Drupal\field_ui\Form\FieldConfigEdit::validateForm
      $this->fieldTempStoreKey = '_' . uniqid();

      $entity_type = $this->entityTypeManager->getDefinition($this->entityTypeId);
      $route_parameters_back = [] + FieldUI::getRouteBundleParameter($entity_type, $this->bundle);
      $form['actions']['previous'] = [
        '#type' => 'link',
        '#title' => $this->t('Change field'),
        '#url' => Url::fromRoute("field_ui.field_storage_config_add_$entity_type_id", $route_parameters_back),
        '#attributes' => [
          'class' => ['button', 'button--primary', 'use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => '85vw',
          ]),
        ],
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
        '#submit' => ['::submitForm'],
        '#attributes' => [
          'class' => ['button', 'button--primary', 'use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => '85vw',
          ]),
        ],
      ];
      if ($this->isAjax()) {
        $form['actions']['submit']['#ajax']['callback'] = '::ajaxSubmit';
        // @todo static::ajaxSubmit() requires data-drupal-selector to be the same
        //   between the various Ajax requests. A bug in
        //   \Drupal\Core\Form\FormBuilder prevents that from happening unless
        //   $form['#id'] is also the same. Normally, #id is set to a unique HTML
        //   ID via Html::getUniqueId(), but here we bypass that in order to work
        //   around the data-drupal-selector bug. This is okay so long as we
        //   assume that this form only ever occurs once on a page. Remove this
        //   workaround in https://www.drupal.org/node/2897377.
        $form['#id'] = Html::getId($form_state->getBuildInfo()['form_id']);
      }
      // Hide the continue button until the sub-field is selected.
      if (isset($group_field_options) && !array_key_exists($form_state->getValue('group_field_options_wrapper'), $group_field_options)) {
        $form['group_field_options_wrapper']['submit']['#attributes']['class'][] = 'js-hide';
      }

    }
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
      // @todo Remove below workarounds needed for modal functionality.
      'core/drupal.machine-name',
      'core/drupal.states',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateAddNew($form, $form_state);
  }

  /**
   * Validates the 'add new field' case.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\field_ui\Form\FieldStorageAddForm::validateForm()
   */
  protected function validateAddNew(array $form, FormStateInterface $form_state) {
    // Validate if any information was provided in the 'add new field' case.
    // Missing label.
    if (!$form_state->getValue('field_name_label')) {
      $form_state->setErrorByName('label', $this->t('Add new field: you need to provide a label.'));
    }

    // Missing field name.
    if (!$form_state->getValue('field_name')) {
      $form_state->setErrorByName('field_name', $this->t('Add new field: you need to provide a machine name for the field.'));
    }
    // Field name validation.
    else {
      $field_name = $form_state->getValue('field_name');

      // Add the field prefix.
      $field_name = $this->configFactory->get('field_ui.settings')->get('field_prefix') . $field_name;
      $form_state->setValueForElement($form['group_field_options_wrapper']['field_name'], $field_name);
    }
  }

  /**
   * Checks if a field machine name is taken.
   *
   * @param string $value
   *   The machine name, not prefixed.
   * @param array $element
   *   An array containing the structure of the 'field_name' element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   Whether or not the field machine name is taken.
   */
  public function fieldNameExists($value, $element, FormStateInterface $form_state) {
    // Add the field prefix.
    $field_name = $this->configFactory->get('field_ui.settings')->get('field_prefix') . $value;

    $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($this->entityTypeId);
    return isset($field_storage_definitions[$field_name]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $var = 1;
    // No-op since form is routed to the below controller.
    // @see \Drupal\field_ui\Controller\FieldTempStoreController::setTempStore
  }

  /**
   * Callback for displaying fields after a group has been selected.
   */
  public function showFieldsCallback($form, FormStateInterface &$form_state) {
    return $form['group_field_options_wrapper'];
  }

  /**
   * Callback to rebuild form.
   */
  public static function rebuildForm($form, FormStateInterface &$form_state) {
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $entity_type = $this->entityTypeManager->getDefinition($this->entityTypeId);
    $route_parameters = [
      'entity_type' => $this->entityTypeId,
      'field_storage_type' => $this->selectedFieldStorageType ?? $this->selectedFieldType,
      'field_temp_store_key' => $this->fieldTempStoreKey,
      'field_label' => $form_state->getValue('field_name_label'),
      'field_machine_name' => $form_state->getValue('field_name'),
    ] + FieldUI::getRouteBundleParameter($entity_type, $this->bundle);
    $url = URL::fromRoute("field_ui.field_storage_entity_add_{$this->entityTypeId}", $route_parameters);
    if ($url) {
      $command = new RedirectCommand($url->setAbsolute()->toString());
    }
    else {
      // Settings Tray always provides a destination.
      throw new \Exception("No destination provided by form");
    }
    $response = new AjaxResponse();
    return $response->addCommand($command);
  }

}
