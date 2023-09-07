<?php

namespace Drupal\block_content\Plugin\Block;

use Drupal\block_content\BlockContentUuidLookup;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

// cspell:ignore autocompleteclose

/**
 * Defines a generic block type.
 *
 * @Block(
 *  id = "block_content",
 *  admin_label = @Translation("Block Content Library"),
 *  category = @Translation("Content block")
 * )
 */
class BlockContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Plugin Block Manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Drupal account to use for checking for access to block.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The block content entity.
   *
   * @var \Drupal\block_content\BlockContentInterface
   */
  protected $blockContent;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The block content UUID lookup service.
   *
   * @var \Drupal\block_content\BlockContentUuidLookup
   */
  protected $uuidLookup;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a new BlockContentBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The Plugin Block Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which view access should be checked.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   * @param \Drupal\block_content\BlockContentUuidLookup $uuid_lookup
   *   The block content UUID lookup service.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BlockManagerInterface $block_manager, EntityTypeManagerInterface $entity_type_manager, AccountInterface $account, UrlGeneratorInterface $url_generator, BlockContentUuidLookup $uuid_lookup, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->blockManager = $block_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->account = $account;
    $this->urlGenerator = $url_generator;
    $this->uuidLookup = $uuid_lookup;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.block'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('url_generator'),
      $container->get('block_content.uuid_lookup'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_content_uuid' => '',
      'status' => TRUE,
      'info' => '',
      'view_mode' => 'full',
    ];
  }

  /**
   * Overrides \Drupal\Core\Block\BlockBase::blockForm().
   *
   * Adds body and description fields to the block configuration form.
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $block = '';

    if (isset($this->configuration['block_content_uuid'])) {
      $block = $this->getEntity();
    }

    // Adding an entity_autocomplete element to select the block content.
    $form['block_content_selection'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'block_content',
      '#tags' => TRUE,
      '#required' => TRUE,
      '#default_value' => $this->blockContent ?: '',
      '#selection_handler' => 'default',
      '#title' => $this->t('Block content'),
      '#description' => $this->t('Search by Block Content title.'),
      '#ajax' => [
        'callback' => [$this, 'loadBlockContent'],
        'wrapper' => 'view-mode-container',
        'event' => 'autocompleteclose',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Processing'),
        ],
      ],
    ];

    $form['view_mode_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'view-mode-container'],
    ];

    if ($block) {
      $options = $this->entityDisplayRepository->getViewModeOptionsByBundle('block_content', $block->bundle());

      $form['view_mode_container']['view_mode'] = [
        '#type' => 'select',
        '#options' => $options,
        '#title' => $this->t('View mode'),
        '#description' => $this->t('Output the block in this view mode.'),
        '#default_value' => $this->configuration['view_mode'],
        '#access' => (count($options) > 1),
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Invalidate the block cache to update content block-based derivatives.
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
    if (!isset($this->blockContent)) {
      $id = $form_state->getValue('block_content_selection');
      if ($id) {
        $id = $id[0]['target_id'];
        $block = $this->entityTypeManager->getStorage('block_content')->load($id);
        $this->configuration['block_content_uuid'] = 'block_content:' . $block->uuid();
      }
    }
    else {
      $this->configuration['block_content_uuid'] = 'block_content:' . $this->blockContent->uuid();
    }
    $this->blockManager->clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->getEntity()) {
      return $this->getEntity()->access('view', $account, TRUE);
    }
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($block = $this->getEntity()) {
      return $this->entityTypeManager->getViewBuilder($block->getEntityTypeId())->view($block, $this->configuration['view_mode']);
    }
    else {
      return [
        '#markup' => $this->t('Block with uuid %uuid does not exist. <a href=":url">Add content block</a>.', [
          '%uuid' => $this->getDerivativeId(),
          ':url' => $this->urlGenerator->generate('block_content.add_page'),
        ]),
        '#access' => $this->account->hasPermission('administer blocks'),
      ];
    }
  }

  /**
   * Loads the block content entity of the block.
   *
   * @return \Drupal\block_content\BlockContentInterface|null
   *   The block content entity.
   */
  protected function getEntity() {
    if (!isset($this->blockContent)) {
      if (!empty($this->configuration['block_content_uuid'])) {
        $uuid = explode(':', $this->configuration['block_content_uuid']);
        $blocks = $this->entityTypeManager->getStorage('block_content')->loadByProperties(['uuid' => $uuid[1]]);

        if (is_array($blocks)) {
          $this->blockContent = reset($blocks);
        }
      }
    }
    return $this->blockContent;
  }

  /**
   * Ajax callback for the block content entity_autocomplete.
   */
  public function loadBlockContent(array $form, FormStateInterface $form_state) {
    $id = $form_state->getValue(['settings', 'block_content_selection']);
    if ($id) {
      $id = $id[0]['target_id'];
      $this->blockContent = $this->entityTypeManager->getStorage('block_content')->load($id);
    }
  }

}
