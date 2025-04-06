<?php

namespace Drupal\Core\Config\Development;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\Schema\SchemaIncompleteException;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Psr\Log\LoggerInterface;

/**
 * Listens to the config save event and warns about invalid schema.
 */
class LenientConfigSchemaChecker extends ConfigSchemaChecker {

  /**
   * The messenger service to display the warning.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger to save the warning.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs the ConfigSchemaChecker object.
   *
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_manager
   *   The typed config manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service to display the warning.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger to save the warning.
   * @param string[] $exclude
   *   An array of config object names that are excluded from schema checking.
   */
  public function __construct(TypedConfigManagerInterface $typed_manager, MessengerInterface $messenger, LoggerInterface $logger, array $exclude = []) {
    parent::__construct($typed_manager, $exclude);
    $this->messenger = $messenger;
    $this->logger = $logger;
  }

  /**
   * Checks that configuration complies with its schema on config save.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The configuration event.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    try {
      parent::onConfigSave($event);
    }
    catch (SchemaIncompleteException $exception) {
      $this->messenger->addWarning($exception->getMessage());
      $this->logger->warning($exception->getMessage());
    }
  }

}
