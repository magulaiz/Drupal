<?php

namespace Drupal\layout_builder\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenOffCanvasDialogCommand;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\layout_builder\Context\LayoutBuilderContextTrait;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\LayoutBuilderHighlightTrait;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionComponentTrait;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for applying visibility conditions to a block.
 *
 * @internal
 *   Form classes are internal.
 */
class BlockVisibilityForm extends FormBase {

  use AjaxFormHelperTrait;
  use LayoutBuilderContextTrait;
  use LayoutBuilderHighlightTrait;
  use LayoutRebuildTrait;
  use SectionComponentTrait;

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\SectionStorageInterface
   */
  protected $sectionStorage;

  /**
   * The layout section delta.
   *
   * @var int
   */
  protected $delta;

  /**
   * The uuid of the block component.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * Constructs a BlockVisibilityForm object.
   *
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   *   The condition plugin manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   */
  public function __construct(ExecutableManagerInterface $condition_manager, FormBuilderInterface $form_builder, LayoutTempstoreRepositoryInterface $layout_tempstore_repository) {
    $this->conditionManager = $condition_manager;
    $this->formBuilder = $form_builder;
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.condition'),
      $container->get('form_builder'),
      $container->get('layout_builder.tempstore_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_builder_block_visibility';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $uuid = NULL) {
    $this->sectionStorage = $section_storage;
    $this->delta = $delta;
    $this->uuid = $uuid;

    // Any visibility conditions that have already been added to the block.
    $visibility_conditions_applied_to_block = $this->getCurrentComponent()->get('visibility') ?: [];

    // Visibility condition types that can be added to a block.
    $conditions_available_to_block = [];
    foreach ($this->conditionManager->getFilteredDefinitions('layout_builder', $this->getAvailableContexts($section_storage)) as $plugin_id => $definition) {
      $conditions_available_to_block[$plugin_id] = $definition['label'];
    }

    $items = [];
    foreach ($visibility_conditions_applied_to_block as $visibility_id => $configuration) {
      /** @var \Drupal\Core\Condition\ConditionInterface $condition */
      $condition = $this->conditionManager->createInstance($configuration['id'], $configuration);
      $options = [
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
          'data-dialog-renderer' => 'off_canvas',
          'data-outside-in-edit' => TRUE,
        ],
      ];
      $items[$visibility_id] = [
        'label' => $this->t('<strong>@condition_name:</strong> @condition_summary', [
          '@condition_name' => $condition->getPluginDefinition()['label'],
          '@condition_summary' => $condition->summary(),
        ]),
        'edit' => [
          'data' => [
            '#type' => 'link',
            '#title' => $this->t('Edit'),
            '#url' => Url::fromRoute('layout_builder.add_visibility', $this->getParameters($visibility_id), $options),
          ],
        ],
        'delete' => [
          'data' => [
            '#type' => 'link',
            '#title' => $this->t('Delete'),
            '#url' => Url::fromRoute('layout_builder.delete_visibility', $this->getParameters($visibility_id), $options),
          ],
        ],
      ];
    }

    if ($items) {
      $form['visibility'] = [
        '#prefix' => '<div class="configured-conditions">',
        '#suffix' => '</div>',
        '#theme' => 'table',
        '#rows' => $items,
        '#caption' => $this->t('Configured Conditions'),
      ];
    }

    // Determines if multiple conditions should be applied with 'and' or 'or'.
    $form['operator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Operator'),
      '#options' => [
        'and' => $this->t('And'),
        'or' => $this->t('Or'),
      ],
      '#default_value' => $this->getCurrentComponent()->get('visibility_operator') ?: 'and',
      // This field is not necessary until multiple conditions are added.
      '#access' => count($items) > 1,
    ];

    // This is a submit button that only appears once two or more visibility
    // conditions are present. This submit button appears so the user can
    // update the visibility operator, a setting that impacts the entire block.
    // This is different than the default submit button/handler for this form,
    // which is used to add a visibility condition to the block.
    $form['update_operator'] = [
      '#type' => 'submit',
      '#access' => count($items) > 1,
      '#value' => $this->t('Update operator'),
      '#submit' => ['::updateOperator'],
    ];

    $form['add'] = [
      '#type' => 'details',
      '#title' => $this->t('Add a visibility condition'),
      '#weight' => 20,
    ];
    $form['add']['conditions'] = [
      '#theme' => 'item_list',
      '#items' => [],
    ];
    foreach ($conditions_available_to_block as $condition_id => $condition_label) {
      $parameters = $this->getParameters($condition_id);

      $form['add']['conditions']['#items'][$condition_id] = [
        '#type' => 'link',
        '#title' => $condition_label,
        '#url' => Url::fromRoute('layout_builder.add_visibility', $parameters, [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
            'data-outside-in-edit' => TRUE,
          ],
        ]),
      ];
    }

    $form['#attributes']['data-layout-builder-target-highlight-id'] = $this->blockUpdateHighlightId($this->uuid);

    if ($this->isAjax()) {
      $form['update_operator']['#ajax']['callback'] = '::ajaxSubmit';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    // The submit was triggered by the "update operator" button, just
    // rebuild the layout UI and close the dialog.
    return $this->rebuildAndClose($this->sectionStorage);
  }

  /**
   * Submit handler for updating just the visibility operator.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function updateOperator(array $form, FormStateInterface $form_state) {
    $operator_value = $form_state->getValue('operator');
    $component = $this->getCurrentComponent();
    $component->set('visibility_operator', $operator_value);
    $this->layoutTempstoreRepository->set($this->sectionStorage);
    $form_state->setRedirectUrl($this->sectionStorage->getLayoutBuilderUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $parameters = $this->getParameters($form_state->getValue('condition'));
    $operator = $form_state->getValue('operator');
    $parameters['operator'] = $operator === 'or' ? $operator : 'and';
    $url = new Url('layout_builder.add_visibility', $parameters);
    $form_state->setRedirectUrl($url);
  }

  /**
   * Gets the parameters needed for the various Url() and form invocations.
   *
   * @param string $visibility_id
   *   The ID of the visibility plugin.
   *
   * @return array
   *   List of URL parameters.
   */
  protected function getParameters($visibility_id) {
    return [
      'section_storage_type' => $this->sectionStorage->getStorageType(),
      'section_storage' => $this->sectionStorage->getStorageId(),
      'delta' => $this->delta,
      'uuid' => $this->uuid,
      'plugin_id' => $visibility_id,
    ];
  }

  /**
   * Provides a title callback.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The original delta of the section.
   * @param string $uuid
   *   The UUID of the block being updated.
   *
   * @return string
   *   The title for the block visibility form.
   */
  public function title(SectionStorageInterface $section_storage, $delta, $uuid) {
    $block_label = $section_storage
      ->getSection($delta)
      ->getComponent($uuid)
      ->getPlugin()
      ->label();

    return $this->t('Configure visibility rules for the @block_label block', ['@block_label' => $block_label]);
  }

}
