<?php

namespace Drupal\content_moderation\Plugin\views\argument;

use Drupal\content_moderation\Plugin\views\ModerationStateJoinViewsHandlerTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler for moderation state.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("moderation_state_argument")
 */
class ModerationStateArgument extends ArgumentPluginBase implements ContainerFactoryPluginInterface {

  use ModerationStateJoinViewsHandlerTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The storage handler of the workflow entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $workflowStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityStorageInterface $workflow_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->workflowStorage = $workflow_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_type.manager')->getStorage('workflow')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $this->ensureMyTable();
    // Try to get static analysis to play more nicely with the query class.
    /** @var \Drupal\views\Plugin\views\query\Sql $view_query */
    $view_query = $this->view->query;

    // Convert arguments to array so that they can be added as conditions.
    $arguments = explode(',', $this->argument);

    // The values are strings composed of the workflow ID and the state ID, so
    // we need to create a complex WHERE condition.
    $field = $view_query->getConnection()->condition('OR');
    foreach ($arguments as $argument) {
      [$workflow_id, $state_id] = explode('-', $argument, 2);
      $and = $view_query->getConnection()->condition('AND');
      $and->condition("$this->tableAlias.workflow", $workflow_id, '=')
        ->condition("$this->tableAlias.$this->realField", $state_id, '=');
      $field->condition($and);
    }

    // Must satisfy the drupal-check gods.
    /** @var \Drupal\views\Plugin\views\query\Sql $this_query */
    $this_query = $this->query;
    $this_query->addWhere(0, $field);
  }

}
