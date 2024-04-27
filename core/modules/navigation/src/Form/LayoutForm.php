<?php

declare(strict_types=1);

namespace Drupal\navigation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL) {
    $form['#attributes']['class'][] = 'layout-builder-form';
    $form['layout_builder'] = [
      '#type' => 'layout_builder',
      '#section_storage' => $section_storage,
    ];
    $form['#attached']['library'][] = 'navigation/navigation.layoutBuilder';

    $this->sectionStorage = $section_storage;
    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ] + $this->buildActionsElement([]);

    $form['actions']['footer'] = [
      '#type' => 'html_tag',
      '#tag' => 'footer',
      '#weight' => 50,
    ];
    $form['actions']['footer']['content'] = [
      '#theme' => 'links',
      '#links' => [
      'first_link' => [
        'title' => t('Goto Frontpage'),
        'url' =>  Url::fromRoute('<front>'),
      ],
      'second_link' => [
        'title' => t('Goto Administration'),
        'url' => Url::fromRoute('system.admin'),
      ],
    ],
      '#attributes' => ['class' => ['links-inline']],
      '#heading' => [
        'text' => 'Layout Complete — Proceed',
        'attributes' => ['class' => 'proceed-links']
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->sectionStorage->save();
    $this->saveTasks($form_state, new TranslatableMarkup('Saved navigation blocks'));
  }

}
