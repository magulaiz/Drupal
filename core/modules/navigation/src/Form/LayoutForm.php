<?php

declare(strict_types=1);

namespace Drupal\navigation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\layout_builder\Form\LayoutBuilderEntityFormTrait;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for configuring navigation blocks.
 *
 * @internal
 */
final class LayoutForm extends FormBase {

  use LayoutBuilderEntityFormTrait {
    buildActions as buildActionsElement;
    saveTasks as saveTasks;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId(): string {
    return 'navigation_layout';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'navigation_layout';
  }

  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\SectionStorageInterface
   */
  protected $sectionStorage;

  /**
   * Constructs a new LayoutForm.
   */
  public function __construct(protected LayoutTempstoreRepositoryInterface $layoutTempstoreRepository) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('layout_builder.tempstore_repository')
    );
  }

  /**
   * Handles switching the configuration type selector.
   */
  public function addMoreAjax($form, FormStateInterface $form_state) {
    if ($form_state::hasAnyErrors()) {
      return $form;
    }

    $this->handleFormElementsVisibility($form, FALSE);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?SectionStorageInterface $section_storage = NULL) {
    $form['#prefix'] = '<div id="js-config-form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attributes']['class'][] = 'layout-builder-form';
    $this->sectionStorage = $section_storage;

    $form['layout_builder'] = [
      '#type' => 'layout_builder',
      '#section_storage' => $section_storage,
    ];
    $form['#attached']['library'][] = 'navigation/navigation.layoutBuilder';

    $form['actions'] = [
      'enable_edition' => [
        '#name' => 'enable_edition',
        '#type' => 'submit',
        '#value' => $this->t('Enable Edit mode'),
        '#ajax' => [
          'callback' => '::addMoreAjax',
          'wrapper' => 'js-config-form-wrapper',
          'effect' => 'fade',
        ],
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ] + $this->buildActionsElement([]);

    $this->handleFormElementsVisibility($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $button = $form_state->getTriggeringElement();
    if ($button['#name'] && $button['#name'] !== 'enable_edition') {
      $this->sectionStorage->save();
      $this->saveTasks($form_state, new TranslatableMarkup('Saved navigation blocks'));
    }
  }

  /**
   * Handles visibility of the form elements based on the edit mode status.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   */
  protected function handleFormElementsVisibility(array &$form, $edit_mode_enabled = TRUE): array {
    foreach (Element::children($form['actions']) as $action) {
      $edit_action_access = isset($form['actions'][$action]['#name']) && $form['actions'][$action]['#name'] === 'enable_edition';
      $form['actions'][$action]['#access'] = $edit_mode_enabled ? $edit_action_access : !$edit_action_access;
    }
    $form['layout_builder']['#access'] = !$edit_mode_enabled;
    return $form;
  }

}
