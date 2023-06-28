<?php

namespace Drupal\field_ui\Form;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Render\Element;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\Url;
use Drupal\field\FieldConfigInterface;
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
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

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
   * Constructs a new FieldConfigDeleteForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entityDisplayRepository
   *   The entity display repository.
   * @param \Drupal\Component\Plugin\PluginManagerBase $pluginManager
   *   The widget plugin manager.
   * @param Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selectionManager
   *   The entity reference selection plugin manager.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info, PrivateTempStoreFactory $temp_store_factory, protected TypedDataManagerInterface $typedDataManager, protected EntityDisplayRepositoryInterface $entityDisplayRepository, protected PluginManagerBase $pluginManager, protected SelectionPluginManagerInterface $selectionManager) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->tempStore = $temp_store_factory->get('field_ui');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('tempstore.private'),
      $container->get('typed_data_manager'),
      $container->get('entity_display.repository'),
      $container->get('plugin.manager.field.widget'),
      $container->get('plugin.manager.entity_reference_selection')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

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
    // Create an instance of the field item. This needs to be done manually when
    // creating a new field because it hasn't been attached to the entity at
    // this point.
    if (!$this->entity->isNew()) {
      $items = $form['#entity']->get($this->entity->getName());
    }
    else {
      $entity_adapter = EntityAdapter::createFromEntity($form['#entity']);
      $items = $this->typedDataManager->create($this->entity, NULL, $this->entity->getName(), $entity_adapter);
    }
    $item = $items->first() ?: $items->appendItem();

    $item_class = 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem';
    /** @var \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager */
    $field_type_manager = \Drupal::service('plugin.manager.field.field_type');
    $class = $field_type_manager->getPluginClass($this->entity->getType());
    if ($class === $item_class || is_subclass_of($class, $item_class)) {
      $item->getFieldDefinition()->setSetting('target_type', $field_storage->getSetting('target_type'));
      $item->getFieldDefinition()->setSetting('handler', 'default:' . $field_storage->getSetting('target_type'));
      $item->getFieldDefinition()->setSetting('handler_settings', []);
    }
    $form['field_storage'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Field Storage'),
      '#weight' => -15,
      '#tree' => TRUE,
    ];
    $form['field_storage']['subform'] = [];
    $subform_state = SubformState::createForSubform($form['field_storage']['subform'], $form, $form_state);
    $field_storage_form = $this->entityTypeManager->getFormObject('field_storage_config', 'edit');
    $field_storage_form->setEntity($field_storage);
    $form['field_storage']['subform'] = $field_storage_form->buildForm($form['field_storage']['subform'], $subform_state, $this->entity->id());
    unset($form['field_storage']['subform']['actions']);
    if (isset($form['field_storage']['subform']['cardinality_container'])) {
      $form['field_storage']['subform']['cardinality_container']['#parents'] = [
        'field_storage',
        'subform',
      ];
    }
    $form['field_storage']['subform']['field_storage_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update settings'),
      '#limit_validation_errors' => [],
      '#process' => [[static::class, 'processFieldStorageSubmit']],
      '#submit' => [[$this, 'fieldStorageSubmit']],
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

    $temp_storage = $this->tempStore->get($this->currentUser()->id() . ':' . $this->entity->getTargetEntityTypeId() . ':' . $this->entity->getName());
    if (isset($temp_storage['default_options']['entity_form_display']['default']['type']) && $temp_storage['default_options']['entity_form_display']['default']['type']) {
      $this->initFormDefaultValues($form_state, $temp_storage['default_options']['entity_form_display']['default']['type']);
    }

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
        $has_default_value = $this->hasAnyElementDefaultValue($element);
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

    return $form;
  }

  /**
   * Initializes form default widget.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $widget_name
   *   Default form widget name.
   */
  protected function initFormDefaultValues(FormStateInterface $form_state, $widget_name) {
    if (!$form_state->has('default_value_widget')) {
      $widget = $this->pluginManager->getInstance([
        'field_definition' => $this->entity,
        'configuration' =>
          ['type' => $widget_name],
      ]);
      $form_state->set('default_value_widget', $widget);
    }
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
   * A function to check if element contains elements with #default_value.
   *
   * @param array $element
   *   An element to check.
   *
   * @return bool
   */
  private function hasAnyElementDefaultValue(array $element) {
    $has_default_value = FALSE;
    foreach (Element::children($element) as $child) {
      if (isset($element[$child]['#default_value']) && $element[$child]['#default_value']) {
        $has_default_value = TRUE;
        break;
      }
      if (Element::children($element[$child])) {
        return $this->hasAnyElementDefaultValue($element[$child]);
      }
    }

    return $has_default_value;
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
      $ids = (object) [
        'entity_type' => $this->entity->getTargetEntityTypeId(),
        'bundle' => $this->entity->getTargetBundle(),
        'entity_id' => NULL,
      ];
      $form['#entity'] = _field_create_entity_from_ids($ids);
      $entity_adapter = EntityAdapter::createFromEntity($form['#entity']);
      $item = $this->typedDataManager->create($this->entity);
      $item->setContext('entity', $entity_adapter);
      if ($form_state->getValue(['settings', 'handler_settings']) !== $item->getFieldDefinition()->getSetting('handler_setting')) {
        $item->getFieldDefinition()->setSetting('handler_settings', $form_state->getValue(['settings', 'handler_settings']));
      }
      $item->defaultValuesFormValidate($form['default_value'], $form, $form_state);
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
      $ids = (object) [
        'entity_type' => $this->entity->getTargetEntityTypeId(),
        'bundle' => $this->entity->getTargetBundle(),
        'entity_id' => NULL,
      ];
      $form['#entity'] = _field_create_entity_from_ids($ids);
      $entity_adapter = EntityAdapter::createFromEntity($form['#entity']);
      $items = $this->typedDataManager->create($this->entity, NULL, $this->entity->getName(), $entity_adapter);
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
    $temp_storage = $this->tempStore->get($this->currentUser()->id() . ':' . $this->entity->getTargetEntityTypeId() . ':' . $this->entity->getName());
    if ($temp_storage && $temp_storage['field_storage']->isNew()) {
      // Save field storage.
      $temp_storage['field_storage']->save();
    }
    // Save field config.
    $this->entity->save();
    if ($temp_storage) {
      // Configure the display modes.
      $this->entityTypeId = $temp_storage['field_values']['entity_type'];
      $this->bundle = $temp_storage['field_values']['bundle'];
      $this->configureEntityFormDisplay($temp_storage['field_values']['field_name'], $temp_storage['default_options']['entity_form_display'] ?? []);
      $this->configureEntityViewDisplay($temp_storage['field_values']['field_name'], $temp_storage['default_options']['entity_view_display'] ?? []);
      // Delete the temp store entry.
      $this->tempStore->delete($this->currentUser()->id() . ':' . $this->entity->getTargetEntityTypeId() . ':' . $this->entity->getName());
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

  public static function processFieldStorageSubmit(array $element, FormStateInterface $form_state, &$complete_form) {
    $element['#limit_validation_errors'] = [array_slice($element['#parents'], 0, -1)];
    return $element;
  }

  public function fieldStorageSubmit(&$form, FormStateInterface $form_state) {
    $entity = $form_state->getFormObject()->getEntity();
    /** @var \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager */
    $field_type_manager = \Drupal::service('plugin.manager.field.field_type');
    $class = $field_type_manager->getPluginClass($this->entity->getType());
    $item_class = 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem';
    if ($class === $item_class || is_subclass_of($class, $item_class)) {
      $parents = array_slice($form_state->getTriggeringElement()['#parents'], 0, -1);
      array_push($parents, 'settings', 'target_type');
      $new_target_type = $form_state->getValue($parents);
      $entity->getFieldStorageDefinition()->setSetting('target_type', $new_target_type);
      [$current_handler] = explode(':', $entity->getSetting('handler'), 2);
      $entity->setSetting('handler', $this->selectionManager->getPluginId($new_target_type, $current_handler));
      // @see field_field_storage_config_update
      $entity->setSetting('handler_settings', []);
    }
    else {
      $parents = array_slice($form_state->getTriggeringElement()['#parents'], 0, -1);
      $parents[] = 'settings';
      $entity->getFieldStorageDefinition()->setSettings($form_state->getValue($parents));
    }
    $entity->getFieldStorageDefinition()->set('cardinality', $form_state->getValue(['field_storage', 'subform', 'cardinality_number']));
    // The default value widget needs to be regenerated.
    $field_storage = &$form_state->getStorage();
    unset($field_storage['default_value_widget']);
    $form_state->setRebuild();
  }

}
