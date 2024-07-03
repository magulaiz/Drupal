<?php

namespace Drupal\config\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Fix empty core.menu.static_menu_link_overrides:definitions.*.parent value to NULL.
 */
class MenuParentUpdate implements EventSubscriberInterface {

  /**
   * Constructs a MenuParentUpdate object.
   */
  public function __construct(protected readonly ConfigFactoryInterface $configFactory, protected readonly RequestStack $requestStack) {
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
      $changed = FALSE;
      foreach ($all_overrides as $definition_key => $definition_value) {
        if ($definition_value['parent'] === '') {
          $saved_config->set('definitions.' . $definition_key . '.parent', NULL);
          $changed = TRUE;
        }
      }
      if ($changed) {
        $saved_config->save();
        if (!str_contains($this->requestStack->getMainRequest()->getBaseUrl(), 'update.php')) {
          @trigger_error("Using an empty 'parent' key is deprecated in drupal:11.1.0 and will be removed in drupal:12.0.0. See https://www.drupal.org/node/3458722", E_USER_DEPRECATED);
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
