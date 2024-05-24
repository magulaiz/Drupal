<?php

namespace Drupal\config\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Fix empty core.menu.static_menu_link_overrides:definitions.*.parent value to NULL.
 */
class MenuParentUpdate implements EventSubscriberInterface {

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs a MenuParentUpdate object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Fix empty core.menu.static_menu_link_overrides:definitions.*.parent value to NULL.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onSave(ConfigCrudEvent $event) {
    $saved_config = $event->getConfig();
    if ($saved_config->getName() === 'core.menu.static_menu_link_overrides') {
      $all_overrides = $saved_config->get('definitions') ?: [];
      foreach ($all_overrides as $definition_key => $definition_value) {
        if ($definition_value['parent'] === '') {
          $saved_config->set('definitions.' . $definition_key . '.parent', NULL)->save();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[ConfigEvents::SAVE][] = ['onSave'];
    return $events;
  }

}
