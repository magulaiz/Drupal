<?php

namespace Drupal\dblog\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\dblog\DblogEntryStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Exposes log types to views module.
 *
 * @ViewsFilter("dblog_types")
 */
class DblogTypes extends InOperator {

  /**
   * The dblog entry storage service.
   *
   * @var \Drupal\dblog\DblogEntryStorageInterface
   */
  protected $dblogStorage;

  /**
   * Constructs a DblogTypes object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\dblog\DblogEntryStorageInterface $dblog_storage
   *   The dblog entry storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DblogEntryStorageInterface $dblog_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dblogStorage = $dblog_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('dblog')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueOptions = $this->dblogStorage->messageTypes();
    }
    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);
    $form['value']['#access'] = !empty($form['value']['#options']);
  }

}
