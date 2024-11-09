<?php

namespace Drupal\views\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\ViewEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Config action for removing item from display option.
 */
#[ConfigAction(
  id: 'view:removeItemFromDisplayOption',
  admin_label: new TranslatableMarkup('Views remove item from display option'),
  entity_types: ['view'],
)]
class ViewsRemoveItemFromDisplayOption implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Constructs instance of ViewsAddItemToDisplayOption.
   *
   * @param \Drupal\Core\Config\ConfigManagerInterface $configManager
   *   The configuration manager.
   */
  public function __construct(
    protected readonly ConfigManagerInterface $configManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($container->get('config.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    if (empty($value)) {
      throw new ConfigActionException(sprintf('View %s cannot be updated because no option settings were provided', $configName));
    }
    if (empty($value['option'])) {
      throw new ConfigActionException('No view display option provided');
    }
    $option = $value['option'];
    if (empty($value['item'])) {
      throw new ConfigActionException(sprintf('No view display %s item name provided', $option));
    }
    $item = $value['item'];
    // Load the views executable.
    $entity = $this->configManager->loadConfigEntityByName($configName);
    if (empty($entity)) {
      throw new ConfigActionException(sprintf('View %s does not exist', $configName));
    }
    if (!$entity instanceof ViewEntityInterface) {
      throw new ConfigActionException(sprintf('%s is not view', $configName));
    }
    $view = $entity->getExecutable();
    $display_id = 'default';
    if (!empty($value['display_id'])) {
      $display_id = $value['display_id'];
    }
    $override = FALSE;
    if (!empty($value['override'])) {
      $override = TRUE;
    }
    $view->setDisplay($display_id);
    $option_settings = $view->displayHandlers->get($display_id)->getOption($option);
    if (!empty($option_settings[$item])) {
      unset($option_settings[$item]);
      if ($override) {
        $view->displayHandlers->get($display_id)->overrideOption($option, $option_settings);
      }
      else {
        $view->displayHandlers->get($display_id)->setOption($option, $option_settings);
      }
      $errors = $view->validate();
      if (!empty($errors)) {
        throw new ConfigActionException(sprintf('Validation of the view ended with following errors: %s', implode(', ', $errors)));
      }
      $view->save();
    }
  }

}
