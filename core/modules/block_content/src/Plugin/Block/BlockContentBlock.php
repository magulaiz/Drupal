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

/**
 * Defines a generic block type.
 *
 * @Block(
 *  id = "block_content",
 *  admin_label = @Translation("Content block"),
 *  category = @Translation("Content block"),
 *  deriver = "Drupal\block_content\Plugin\Derivative\BlockContent"
 * )
 */
class BlockContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The block content entity.
   *
   * @var \Drupal\block_content\BlockContentInterface
   */
  protected $blockContent;

  /**
   * Constructs a new BlockContentBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   The Plugin Block Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which view access should be checked.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The URL generator.
   * @param \Drupal\block_content\BlockContentUuidLookup $uuidLookup
   *   The block content UUID lookup service.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entityDisplayRepository
   *   The entity display repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected BlockManagerInterface $blockManager, protected EntityTypeManagerInterface $entityTypeManager, protected AccountInterface $account, protected UrlGeneratorInterface $urlGenerator, protected BlockContentUuidLookup $uuidLookup, protected EntityDisplayRepositoryInterface $entityDisplayRepository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
    $block = $this->getEntity();
    if (!$block) {
      return $form;
    }
    $options = $this->entityDisplayRepository->getViewModeOptionsByBundle('block_content', $block->bundle());

    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('View mode'),
      '#description' => $this->t('Output the block in this view mode.'),
      '#default_value' => $this->configuration['view_mode'],
      '#access' => (count($options) > 1),
    ];
    $form['title']['#description'] = $this->t('The title of the block as shown to the user.');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Invalidate the block cache to update content block-based derivatives.
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
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
      $uuid = $this->getDerivativeId();
      if ($id = $this->uuidLookup->get($uuid)) {
        $this->blockContent = $this->entityTypeManager->getStorage('block_content')->load($id);
      }
    }
    return $this->blockContent;
  }

}
