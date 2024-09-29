<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Entity;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for adding/editing link suggesters.
 *
 * @internal
 */
class EntityLinkSuggesterForm extends EntityForm {

  /**
   * EntityLinkSuggesterForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   */
  public function __construct(
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_type_manager,
    protected readonly EntityTypeBundleInfoInterface $entityTypeBundleInfo,
  ) {
    $this->setModuleHandler($module_handler);
    $this->setEntityTypeManager($entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    assert($this->entity instanceof EntityLinkSuggester);

    $form['admin_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrative label'),
      '#description' => $this->t('Not displayed to content authors.'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => [EntityLinkSuggester::class, 'load'],
        'source' => ['admin_label'],
      ],
    ];

    $entity_type_labels_default_sort = [];
    $linkable_entity_types = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if (!$this->entity->isLinkableEntityType($entity_type)) {
        continue;
      }
      $linkable_entity_types[$entity_type_id] = $entity_type;
      $entity_type_labels_default_sort[$entity_type_id] = $entity_type->getCollectionLabel();
    }

    // Ensure the checkboxes are presented in alphabetical order rather than the
    // arbitrary order that the entity type manager returns entity types.
    natcasesort($entity_type_labels_default_sort);

    $enabled_entity_types = $this->entity->getEntityTypes();
    // When nothing is configured, link suggestions will be provided for all
    // linkable entity types (and all bundles for each). Visualize this to the
    // user, this form should deal with complexity, not the user's mental model.
    // @see `core.entity_link_suggestions.*:entity_types`
    if ($enabled_entity_types === NULL) {
      foreach (array_keys($entity_type_labels_default_sort) as $entity_type_id) {
        $enabled_entity_types[$entity_type_id] = [
          'entity_type' => $entity_type_id,
          'bundles' => NULL,
        ];
      }
    }

    // Generate weights that correspond to the ordering; these weights are not
    // actually stored.
    $weights = array_combine(
      array_keys($enabled_entity_types),
      range(0, count($enabled_entity_types) - 1),
    );
    $weight_delta = round(count($enabled_entity_types) / 2);

    $header = [
      'enabled' => $this->t('Provide link suggestions for entity type'),
      'bundles' => $this->t('Bundles'),
      'weight' => $this->t('Weight'),
    ];
    $form['entity_types'] = [
      // @todo Move to asset library.
      '#prefix' => Markup::create('<style>td:first-child input[type=checkbox]:not(:checked) ~ label {  text-decoration: line-through; }</style>'),
      '#type' => 'table',
      '#header' => $header,
      '#title' => $this->t('Allowed entity types'),
      '#caption' => [
        '#markup' => '<p>' . $this->t('Select entity types to provide link suggestions for, in what order, and which bundles for each entity type should be included.') . '</p>',
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'entity-type-weight',
        ],
      ],
    ];

    // Now generate all checkboxes: start with the the actually stored entity
    // types, while respecting their order. Then append all the unchecked entity
    // types, in the default sort order.
    $checkbox_order = array_keys($enabled_entity_types + $entity_type_labels_default_sort);
    foreach ($checkbox_order as $entity_type_id) {
      $entity_type = $linkable_entity_types[$entity_type_id];
      $bundles_for_entity_type = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      $row = [
        '#attributes' => ['class' => ['draggable']],
        'enabled' => [
          '#title' => $linkable_entity_types[$entity_type_id]->isCommonReferenceTarget()
            ? $this->t("@label <small>(commonly linked)</small>", ['@label' => $entity_type->getCollectionLabel()])
            : $entity_type->getCollectionLabel(),
          '#type' => 'checkbox',
          '#default_value' => array_key_exists($entity_type_id, $enabled_entity_types),
          '#label_attributes' => [
            'style' => 'font-size: revert;',
          ],
        ],
        'bundles' => $entity_type->getBundleEntityType() === NULL
          ? [
            // An entity type without bundles cannot make a selection, the
            // value is expected to be NULL.
            // @see `core.entity_link_suggestions.*:entity_types.[%key].bundles`.
            '#type' => 'value',
            '#value' => NULL,
          ]
          : [
            '#title' => $this->t('Included %bundles', [
              '%bundles' => $this->entityTypeManager
                ->getDefinition($entity_type->getBundleEntityType())
                ->getPluralLabel(),
            ]),
            '#type' => 'checkboxes',
            '#options' => array_combine(
              array_keys($bundles_for_entity_type),
              array_column($bundles_for_entity_type, 'label')
            ),
            // When not configured yet or when the entity type's `bundles` is
            // is explicitly set to NULL, all bundles are included.
            '#default_value' => !array_key_exists($entity_type_id, $enabled_entity_types) || $enabled_entity_types[$entity_type_id]['bundles'] === NULL
              ? array_keys($bundles_for_entity_type)
              : $enabled_entity_types[$entity_type_id]['bundles'],
            '#states' => [
              'visible' => [
                ':input[name="entity_types[' . $entity_type_id . '][enabled]"]' => ['checked' => TRUE],
              ],
            ],
          ],
        '#weight' => $weights[$entity_type_id] ?? 1000,
        'weight' => [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @title', ['@title' => $entity_type->getCollectionLabel()]),
          '#title_display' => 'invisible',
          '#default_value' => $weights[$entity_type_id] ?? 1000,
          '#attributes' => ['class' => ['entity-type-weight']],
          '#delta' => $weight_delta,
        ],
      ];
      $form['entity_types'][$entity_type_id] = $row;
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $entity->set('id', $values['id']);
    $entity->set('admin_label', $values['admin_label']);
    $entity_types = [];
    foreach ($values['entity_types'] as $entity_type_id => $subvalues) {
      // @see \Drupal\Core\Render\Element\Checkboxes::getCheckedCheckboxes()
      if ($subvalues['enabled'] === 0) {
        continue;
      }
      $bundles = $subvalues['bundles'];
      // Entity types without bundles submit NULL.
      if ($bundles !== NULL) {
        $bundles = Checkboxes::getCheckedCheckboxes($bundles);
        // When all bundles are included, revert back to NULL.
        // @see `core.entity_link_suggestions.*:entity_types.[%key].bundles`.
        if (count($bundles) === count($this->entityTypeBundleInfo->getBundleInfo($entity_type_id))) {
          $bundles = NULL;
        }
      }
      $entity_types[$entity_type_id] = [
        'entity_type' => $entity_type_id,
        'bundles' => $bundles,
      ];
    }

    // When all entity types and bundles are enabled, revert back to NULL.
    // @see `core.entity_link_suggestions.*:entity_types`.
    $linkable_entity_types = array_filter(
      $this->entityTypeManager->getDefinitions(),
      fn (EntityTypeInterface $e) => $this->entity->isLinkableEntityType($e)
    );
    $column = array_filter(array_column($entity_types, 'bundles'));
    if (count($linkable_entity_types) == count($entity_types) && count($column) === 0) {
      $entity_types = NULL;
    }

    $entity->set('entity_types', $entity_types);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->entity->isNew() ? $this->t('Create') : $this->t('Update');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();
    $this->messenger()->addStatus(match ($status) {
      SAVED_NEW => $this->t('Created new link suggester %name.', ['%name' => $this->entity->label()]),
      SAVED_UPDATED => $this->t('Updated link suggester %name.', ['%name' => $this->entity->label()]),
    });

    $form_state->setValue('id', $this->entity->id());
    $form_state->set('id', $this->entity->id());

    $redirect_url = NULL;
    // If a destination is specified, that serves as the cancel link.
    if ($this->getRequest()->query->has('destination')) {
      $options = UrlHelper::parse($this->getRequest()->query->get('destination'));
      // @todo Revisit this in https://www.drupal.org/node/2418219.
      try {
        $redirect_url = Url::fromUserInput('/' . ltrim($options['path'], '/'), $options);
      }
      catch (\InvalidArgumentException) {
        // Suppress the exception and fall back to the form's cancel URL.
      }
    }
    if (!$redirect_url) {
      $redirect_url = $this->entity->toUrl('collection');
    }
    $form_state->setRedirectUrl($redirect_url);

    return $status;
  }

}
