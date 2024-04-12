<?php

declare(strict_types=1);

namespace Drupal\navigation;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a class to build a listing of navigation_block entities.
 */
class NavigationBlockListBuilder extends ConfigEntityListBuilder implements FormInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new BlockListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, FormBuilderInterface $form_builder, MessengerInterface $messenger) {
    parent::__construct($entity_type, $storage);

    $this->formBuilder = $form_builder;
    $this->messenger = $messenger;
    $this->limit = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('form_builder'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   The navigation_block list as a renderable array.
   */
  public function render(Request $request = NULL) {
    $this->request = $request;
    return $this->formBuilder->getForm($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'navigation_block_admin_display_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'core/drupal.tableheader';
    $form['#attached']['library'][] = 'navigation/navigation_block';
    $form['#attached']['library'][] = 'navigation/navigation_block.admin';
    $form['#attributes']['class'][] = 'clearfix';

    // Build the form tree.
    $form['navigation_blocks'] = $this->buildNavigationBlocksForm();

    $form['actions'] = [
      '#tree' => FALSE,
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save navigation blocks'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Builds the main "Navigation blocks" portion of the form.
   *
   * @return array
   *   The navigation blocks form.
   */
  protected function buildNavigationBlocksForm(): array {
    // Build navigation blocks first for each region.
    $navigation_blocks = [];
    $entities = $this->load();
    /** @var \Drupal\navigation\NavigationBlockInterface[] $entities */
    foreach ($entities as $entity_id => $entity) {
      $definition = $entity->getPlugin()->getPluginDefinition();
      $navigation_blocks[$entity->getRegion()][$entity_id] = [
        'label' => $entity->label(),
        'entity_id' => $entity_id,
        'weight' => $entity->getWeight(),
        'entity' => $entity,
        'category' => $definition['category'],
        'status' => $entity->status(),
      ];
    }

    $form = [
      '#type' => 'table',
      '#header' => [
        $this->t('Navigation block'),
        $this->t('Category'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#attributes' => [
        'id' => 'navigation-blocks',
      ],
    ];

    // Weights range from -delta to +delta, so delta should be at least half
    // of the amount of navigation blocks present. This makes sure all
    // navigation blocks in the same region get a unique weight.
    $weight_delta = round(count($entities) / 2);

    $placement = FALSE;
    if ($this->request->query->has('navigation-block-placement')) {
      $placement = $this->request->query->get('navigation-block-placement');
      $form['#attached']['drupalSettings']['navigationBlockPlacement'] = $placement;
      // Remove the navigation block placement from the current request so
      // that it is not passed on to any redirect destinations.
      $this->request->query->remove('navigation-block-placement');
    }

    // Loop over each region and build blocks.
    $regions = [
      NavigationBlockRepositoryInterface::REGION_CONTENT => $this->t('Content'),
    ];
    foreach ($regions as $region => $title) {
      // NOTE: If we ever return to multiple regions, we need to add back a
      // tabledrag match action to allow movement between regions.
      $form['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'navigation-block-weight',
        'subgroup' => 'navigation-block-weight-' . $region,
      ];

      $form['region-' . $region] = [
        '#attributes' => [
          'class' => ['region-title', 'region-title-' . $region],
          'no_striping' => TRUE,
        ],
      ];
      $form['region-' . $region]['title'] = [
        '#theme_wrappers' => [
          'container' => [
            '#attributes' => ['class' => 'region-title__action'],
          ],
        ],
        '#type' => 'link',
        '#title' => $this->t('Place navigation block', ['%region' => $title]),
        '#url' => Url::fromRoute('navigation_block.admin_library', [], ['query' => ['region' => $region]]),
        '#wrapper_attributes' => [
          'colspan' => 4,
        ],
        '#attributes' => [
          'class' => ['use-ajax', 'button', 'button--small'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 880,
          ]),
        ],
      ];

      $form['region-' . $region . '-message'] = [
        '#attributes' => [
          'class' => [
            'region-message',
            'region-' . $region . '-message',
            empty($navigation_blocks[$region]) ? 'region-empty' : 'region-populated',
          ],
        ],
      ];
      $form['region-' . $region . '-message']['message'] = [
        '#markup' => '<em>' . $this->t('No navigation blocks in this region') . '</em>',
        '#wrapper_attributes' => [
          'colspan' => 4,
        ],
      ];

      if (isset($navigation_blocks[$region])) {
        foreach ($navigation_blocks[$region] as $info) {
          $entity_id = $info['entity_id'];

          $form[$entity_id] = [
            '#attributes' => [
              'class' => ['draggable'],
            ],
          ];
          $form[$entity_id]['#attributes']['class'][] = $info['status'] ? 'navigation-block-enabled' : 'navigation-block-disabled';
          if ($placement && $placement == Html::getClass($entity_id)) {
            $form[$entity_id]['#attributes']['class'][] = 'color-success';
            $form[$entity_id]['#attributes']['class'][] = 'js-navigation-block-placed';
          }
          $form[$entity_id]['info'] = [
            '#wrapper_attributes' => [
              'class' => ['navigation-block'],
            ],
          ];
          // Ensure that the label is always rendered as plain text. Render
          // array #plain_text key is essentially treated same as @ placeholder
          // in translatable markup.
          if ($info['status']) {
            $form[$entity_id]['info']['#plain_text'] = $info['label'];
          }
          else {
            $form[$entity_id]['info']['#markup'] = $this->t('@label (disabled)', ['@label' => $info['label']]);
          }

          $form[$entity_id]['type'] = [
            '#plain_text' => $info['category'],
          ];
          $form[$entity_id]['weight'] = [
            '#type' => 'weight',
            '#default_value' => $info['weight'],
            '#delta' => $weight_delta,
            '#title' => $this->t('Weight for @navigation_block navigation block', ['@navigation_block' => $info['label']]),
            '#title_display' => 'invisible',
            '#attributes' => [
              'class' => ['navigation-block-weight', 'navigation-block-weight-' . $region],
            ],
          ];
          $form[$entity_id]['operations'] = $this->buildOperations($info['entity']);
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity): array {
    $operations = parent::getDefaultOperations($entity);

    if (isset($operations['edit'])) {
      $operations['edit']['title'] = $this->t('Configure');
    }

    if (isset($operations['delete'])) {
      $operations['delete']['title'] = $this->t('Remove');
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (empty($form_state->getValue('navigation_blocks'))) {
      $form_state->setErrorByName('navigation_blocks', $this->t('No navigation block settings to update.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $navigation_blocks = $form_state->getValue('navigation_blocks');
    $entities = $this->storage->loadMultiple(array_keys($navigation_blocks));
    /** @var \Drupal\navigation\NavigationBlockInterface[] $entities */
    foreach ($entities as $entity_id => $entity) {
      $entity_values = $form_state->getValue(['navigation_blocks', $entity_id]);
      $entity->setWeight((int) $entity_values['weight']);
      $entity->setRegion($entity_values['region'] ?? NavigationBlockRepositoryInterface::REGION_CONTENT);
      $entity->save();
    }
    $this->messenger->addStatus($this->t('The navigation block settings have been updated.'));
  }

}
