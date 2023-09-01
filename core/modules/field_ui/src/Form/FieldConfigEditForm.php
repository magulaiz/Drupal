<?php

namespace Drupal\field_ui\Form;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Render\Element;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\field_ui\FieldUI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for the field settings form.
 *
 * @internal
 */
class FieldConfigEditForm extends EntityForm {

  use FieldStorageCreationTrait;

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $entity;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The name of the entity type.
   *
   * @var string
   */
  protected string $entityTypeId;

  /**
   * The entity bundle.
   *
   * @var string
   */
  protected string $bundle;

  /**
   * Constructs a new FieldConfigDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The type data manger.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface|null $entityDisplayRepository
   *   The entity display repository.
   * @param \Drupal\Core\TempStore\PrivateTempStore|null $tempStore
   *   The private tempstore.
   * @param Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface|null $selectionManager
   *   The entity reference selection plugin manager.
   */
  public function __construct(
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    protected TypedDataManagerInterface $typedDataManager,
    protected ?EntityDisplayRepositoryInterface $entityDisplayRepository = NULL,
    protected ?PrivateTempStore $tempStore = NULL,
    protected ?SelectionPluginManagerInterface $selectionManager = NULL) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    if ($this->entityDisplayRepository === NULL) {
      @trigger_error('Calling FieldConfigEditForm::__construct() without the $entityDisplayRepository argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3383771', E_USER_DEPRECATED);
      $this->entityDisplayRepository = \Drupal::service('entity_display.repository');
    }
    if ($this->tempStore === NULL) {
      @trigger_error('Calling FieldConfigEditForm::__construct() without the $tempStore argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3383771', E_USER_DEPRECATED);
      $this->tempStore = \Drupal::service('tempstore.private')->get('field_ui');
    }
    if ($this->selectionManager === NULL) {
      @trigger_error('Calling FieldConfigEditForm::__construct() without the $selectionManager argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0.', E_USER_DEPRECATED);
      $this->selectionManager = \Drupal::service('plugin.manager.entity_reference_selection');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('typed_data_manager'),
      $container->get('entity_display.repository'),
      $container->get('tempstore.private')->get('field_ui'),
      $container->get('plugin.manager.entity_reference_selection')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['#parents'] = [];

    $field_storage = $this->entity->getFieldStorageDefinition();
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($this->entity->getTargetEntityTypeId());

    $form_title = $this->t('%field settings for %bundle', [
      '%field' => $this->entity->getLabel(),
      '%bundle' => $bundles[$this->entity->getTargetBundle()]['label'],
    ]);
    $form['#title'] = $form_title;

    if ($field_storage->isLocked()) {
      $form['locked'] = [
        '#markup' => $this->t('The field %field is locked and cannot be edited.', ['%field' => $this->entity->getLabel()]),
      ];
      return $form;
    }

    // Build the configurable field values.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->getLabel() ?: $field_storage->getName(),
      '#required' => TRUE,
      '#maxlength' => 255,
      '#weight' => -20,
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Help text'),
      '#default_value' => $this->entity->getDescription(),
      '#rows' => 5,
      '#description' => $this->t('Instructions to present to the user below this field on the editing form.<br />Allowed HTML tags: @tags', ['@tags' => FieldFilteredMarkup::displayAllowedTags()]) . '<br />' . $this->t('This field supports tokens.'),
      '#weight' => -10,
    ];

    $form['required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required field'),
      '#default_value' => $this->entity->isRequired(),
      '#weight' => -5,
    ];

    // Create an arbitrary entity object (used by the 'default value' widget).
    $ids = (object) [
      'entity_type' => $this->entity->getTargetEntityTypeId(),
      'bundle' => $this->entity->getTargetBundle(),
      'entity_id' => NULL,
    ];
    $form['#entity'] = _field_create_entity_from_ids($ids);
    $items = $this->getTypedData($this->buildEntity($form, $form_state), $form['#entity']);
    $item = $items->first() ?: $items->appendItem();

    $item_class = 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem';
    /** @var \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager */
    $field_type_manager = \Drupal::service('plugin.manager.field.field_type');
    $class = $field_type_manager->getPluginClass($this->entity->getType());
    if ($class === $item_class || is_subclass_of($class, $item_class)) {
      if ($target_type = $field_storage->getSetting('target_type') ?? $this->tempStore->get($this->entity->getTargetEntityTypeId() . ':' . $this->entity->getName())['field_storage']->getSetting('target_type')) {
        $field_storage->setSetting('target_type', $target_type);
        $item->getFieldDefinition()->setSetting('target_type', $target_type);
        [$current_handler] = explode(':', $item->getFieldDefinition()->getSetting('handler'), 2);
        $item->getFieldDefinition()
          ->setSetting('handler', $this->selectionManager->getPluginId($target_type, $current_handler));
      }
      $item->getFieldDefinition()->setSetting('handler_settings', []);
    }
    $form['field_storage'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Field Storage'),
      '#weight' => -15,
      '#tree' => TRUE,
    ];
    $form['field_storage']['subform'] = [
      '#parents' => ['field_storage', 'subform'],
    ];
    $subform_state = SubformState::createForSubform($form['field_storage']['subform'], $form, $form_state);
    $field_storage_form = $this->entityTypeManager->getFormObject('field_storage_config', 'edit');
    $field_storage_form->setEntity($field_storage);
    $form['field_storage']['subform'] = $field_storage_form->buildForm($form['field_storage']['subform'], $subform_state, $this->entity->id());
    $current_field_storage = $field_storage_form->buildEntity($form['field_storage']['subform'], $subform_state);
    unset($form['field_storage']['subform']['actions']);
//    $this->addAjaxCallBacks($form['field_storage']['subform']);

    if (isset($form['field_storage']['subform']['cardinality_container'])) {
      $form['field_storage']['subform']['cardinality_container']['#parents'] = [
        'field_storage',
        'subform',
      ];
    }
    $form['field_storage']['subform']['field_storage_submit'] = [
      '#type' => 'submit',
      '#name' => 'field_storage_submit',
      '#value' => $this->t('Update settings'),
      '#limit_validation_errors' => [],
      '#process' => [[static::class, 'processFieldStorageSubmit']],
      '#submit' => [[$this, 'fieldStorageSubmit']],
      '#ajax' => [
        'callback' => [$this, 'showUpdated'],
      ],
    ];
    // Add field settings for the field type and a container for third party
    // settings that modules can add to via hook_form_FORM_ID_alter().
    $form['settings'] = [
      '#tree' => TRUE,
      '#weight' => 10,
    ];
    $form['settings'] += $item->fieldSettingsForm($form, $form_state);
    $form['third_party_settings'] = [
      '#tree' => TRUE,
      '#weight' => 11,
    ];

    // Create a new instance of typed data for the field to ensure that default
    // value widget is always rendered from a clean state.
    $current_field_config = $this->buildEntity($form, $form_state);
    $reflector = new \ReflectionObject($current_field_config);
    $property = $reflector->getProperty('fieldStorage');
    $property->setValue($current_field_config, clone $current_field_storage);
    $items = $this->getTypedData($current_field_config, $form['#entity']);

    // Add handling for default value.
    if ($element = $items->defaultValuesForm($form, $form_state)) {
      $has_required = $this->hasAnyRequired($element);

      $element = array_merge($element, [
        '#type' => 'details',
        '#title' => $this->t('Default value'),
        '#open' => TRUE,
        '#tree' => TRUE,
        '#description' => $this->t('The default value for this field, used when creating new content.'),
        '#weight' => 12,
      ]);

      if (!$has_required) {
        $has_default_value = count($this->entity->getDefaultValue($form['#entity'])) > 0;
        $element['#states'] = [
          'invisible' => [
            ':input[name="set_default_value"]' => ['checked' => FALSE],
          ],
        ];
        $form['set_default_value'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Set default value'),
          '#default_value' => $has_default_value,
          '#description' => $this->t('Provide a pre-filled value for the editing form.'),
          '#weight' => $element['#weight'],
        ];
      }

      $form['default_value'] = $element;
    }
    $form['#prefix'] = '<div id="field-combined" >';
    $form['#suffix'] = '</div>';
    return $form;
  }

  /**
   * Callback for relaoding the form.
   */
  public function showUpdated($form, FormStateInterface &$form_state) {
    return $form;
  }

  /**
   * A function to check if element contains any required elements.
   *
   * @param array $element
   *   An element to check.
   *
   * @return bool
   */
  private function hasAnyRequired(array $element) {
    $has_required = FALSE;
    foreach (Element::children($element) as $child) {
      if (isset($element[$child]['#required']) && $element[$child]['#required']) {
        $has_required = TRUE;
        break;
      }
      if (Element::children($element[$child])) {
        return $this->hasAnyRequired($element[$child]);
      }
    }

    return $has_required;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Save settings');

    if (!$this->entity->isNew()) {
      $target_entity_type = $this->entityTypeManager->getDefinition($this->entity->getTargetEntityTypeId());
      $route_parameters = [
        'field_config' => $this->entity->id(),
      ] + FieldUI::getRouteBundleParameter($target_entity_type, $this->entity->getTargetBundle());
      $url = new Url('entity.field_config.' . $target_entity_type->id() . '_field_delete_form', $route_parameters);

      if ($this->getRequest()->query->has('destination')) {
        $query = $url->getOption('query');
        $query['destination'] = $this->getRequest()->query->get('destination');
        $url->setOption('query', $query);
      }
      $actions['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#url' => $url,
        '#access' => $this->entity->access('delete'),
        '#attributes' => [
          'class' => ['button', 'button--danger'],
        ],
      ];
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if (isset($form['default_value']) && (!isset($form['set_default_value']) || $form_state->getValue('set_default_value'))) {
      // Make sure that the default value form is validated using the field
      // configuration that was just submitted. Do not update $this->entity as
      // the field configuration may contain invalid values at this point.
      $field_config = $this->buildEntity($form, $form_state);
      $items = $this->getTypedData($field_config, $form['#entity']);
      $items->defaultValuesFormValidate($form['default_value'], $form, $form_state);
    }

    $field_storage_form = $this->entityTypeManager->getFormObject('field_storage_config', 'edit');
    $field_storage_form->setEntity($this->entity->getFieldStorageDefinition());
    $field_storage_form->validateForm($form['field_storage']['subform'], SubformState::createForSubform($form['field_storage']['subform'], $form, $form_state));
  }

    /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Handle the default value.
    $default_value = [];
    if (isset($form['default_value']) && (!isset($form['set_default_value']) || $form_state->getValue('set_default_value'))) {
      $items = $this->getTypedData($this->entity, $form['#entity']);
      $default_value = $items->defaultValuesFormSubmit($form['default_value'], $form, $form_state);
    }
    $this->entity->setDefaultValue($default_value);

    $field_storage_form = $this->entityTypeManager->getFormObject('field_storage_config', 'edit');
    $field_storage_form->setEntity($this->entity->getFieldStorageDefinition());
    $field_storage_form->submitForm($form['field_storage']['subform'], SubformState::createForSubform($form['field_storage']['subform'], $form, $form_state));
    $field_storage_form->save($form['field_storage']['subform'], SubformState::createForSubform($form['field_storage']['subform'], $form, $form_state));
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $temp_storage = $this->tempStore->get($this->entity->getTargetEntityTypeId() . ':' . $this->entity->getName());
    if ($this->entity->isNew()) {
      try {
        $temp_storage['field_storage']->save();
      }
      catch (EntityStorageException $e) {
        $this->tempStore->delete($this->entity->getTargetEntityTypeId() . ':' . $this->entity->getName());
        $form_state->setRedirectUrl(FieldUI::getOverviewRouteInfo($this->entity->getTargetEntityTypeId(), $this->entity->getTargetBundle()));
        $this->messenger()->addError($this->t('An error occurred while saving the field: @error', ['@error' => $e->getMessage()]));
        return;
      }
    }
    // Save field config.
    $this->entity->save();
    if (isset($form_state->getStorage()['default_options'])) {
      $default_options = $form_state->getStorage()['default_options'];
      // Configure the default display modes.
      $this->entityTypeId = $temp_storage['field_config_values']['entity_type'];
      $this->bundle = $temp_storage['field_config_values']['bundle'];
      $this->configureEntityFormDisplay($temp_storage['field_config_values']['field_name'], $default_options['entity_form_display'] ?? []);
      $this->configureEntityViewDisplay($temp_storage['field_config_values']['field_name'], $default_options['entity_view_display'] ?? []);
      // Delete the temp store entry.
      $this->tempStore->delete($this->entity->getTargetEntityTypeId() . ':' . $this->entity->getName());
    }

    $this->messenger()->addStatus($this->t('Saved %label configuration.', ['%label' => $this->entity->getLabel()]));

    $request = $this->getRequest();
    if (($destinations = $request->query->all('destinations')) && $next_destination = FieldUI::getNextDestination($destinations)) {
      $request->query->remove('destinations');
      $form_state->setRedirectUrl($next_destination);
    }
    else {
      $form_state->setRedirectUrl(FieldUI::getOverviewRouteInfo($this->entity->getTargetEntityTypeId(), $this->entity->getTargetBundle()));
    }
  }

  /**
   * The _title_callback for the field settings form.
   *
   * @param \Drupal\field\FieldConfigInterface $field_config
   *   The field.
   *
   * @return string
   *   The label of the field.
   */
  public function getTitle(FieldConfigInterface $field_config) {
    return $field_config->label();
  }

  /**
   * Gets typed data object for the field.
   *
   * @param \Drupal\field\FieldConfigInterface $field_config
   *   The field configuration.
   * @param \Drupal\Core\Entity\FieldableEntityInterface $parent
   *   The parent entity that the field is attached to.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   */
  private function getTypedData(FieldConfigInterface $field_config, FieldableEntityInterface $parent): TypedDataInterface {
    $entity_adapter = EntityAdapter::createFromEntity($parent);
    return $this->typedDataManager->create($field_config, $field_config->getDefaultValue($parent), $field_config->getName(), $entity_adapter);
  }

  /**
   * Process handler for subform submit.
   */
  public static function processFieldStorageSubmit(array $element, FormStateInterface $form_state, &$complete_form) {
    $element['#limit_validation_errors'] = [array_slice($element['#parents'], 0, -1)];
    $complete_form['#limit_validation_errors'] = [array_slice($element['#parents'], 0, -1)];
    return $element;
  }

  /**
   * Submit handler for subform submit.
   */
  public function fieldStorageSubmit(&$form, FormStateInterface $form_state) {
    $field_storage = $this->entity->getFieldStorageDefinition();
    /** @var \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager */
    $field_type_manager = \Drupal::service('plugin.manager.field.field_type');
    $class = $field_type_manager->getPluginClass($this->entity->getType());
    $item_class = 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem';
    if ($class === $item_class || is_subclass_of($class, $item_class)) {
      $parents = array_slice($form_state->getTriggeringElement()['#parents'], 0, -1);
      array_push($parents, 'settings', 'target_type');
      $new_target_type = $form_state->getValue($parents);
      $field_storage->setSetting('target_type', $new_target_type);
      if ($handler = $this->entity->getSetting('handler')) {
        [$current_handler] = explode(':', $handler, 2);
        // The handler will need to change. Can entity reference
        // @see field_field_storage_config_update()
        // We can't really save but could we override \Drupal\field\Entity\FieldConfig::save() to call save on the regular storage but
        // call on a new starage that always saves to the tempstore. Then we could call
        // $this->entity->save() which fire all hooks needed.
        // This might now work because field_field_storage_config_update() uses
        // `$field = FieldConfig::loadByName($field_storage->getTargetEntityTypeId(), $bundle, $field_storage->getName());`
        // which would load the actual field config and save it. Other contrib
        // modules may also do this.
        $this->entity->setSetting('handler', $this->selectionManager->getPluginId($new_target_type, $current_handler));
        // @see field_field_storage_config_update
        $this->entity->setSetting('handler_settings', []);
      }
    }

    // The default value widget needs to be regenerated.
    $form_storage = &$form_state->getStorage();
    unset($form_storage['default_value_widget']);
    $form_state->setRebuild();
  }

  /**
   * Add Ajax callback for all inputs.
   *
   * @param array $form
   */
  private function addAjaxCallBacks(array &$form): void {
    /** @var \Drupal\Core\Render\ElementInfoManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.element_info');
    $children = Element::children($form);
    foreach ($children as $key) {
      $child_is_input = FALSE;
      $child = &$form[$key];
      if (isset($child['#type'])) {
        $element_info = $element_manager->getInfo($child['#type']);
        if (!empty($element_info['#input'])) {
          $child_is_input = TRUE;
          $child['#ajax'] = [
            'trigger_as' => ['name' => 'op'],
            'wrapper' => 'field-combined',
            'event' => 'change',
          ];
        }
      }
      if (!$child_is_input) {
        $this->addAjaxCallBacks($child);
      }
    }
  }

}
