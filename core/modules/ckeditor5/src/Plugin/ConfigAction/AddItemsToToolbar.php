<?php

namespace Drupal\ckeditor5\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[ConfigAction(
  id: 'editor:addItemsToToolbar',
  admin_label: new TranslatableMarkup('Add items to a CKEditor 5 toolbar'),
  entity_types: ['editor'],
)]
final class AddItemToToolbar implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly ConfigActionPluginInterface $decorated,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $decorated = $container->get('plugin.manager.config_action')
      ->createInstance('editor:addItemToToolbar');

    return new static($decorated);
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $values): void {
    assert(is_array($values) && array_is_list($values));

    foreach ($values as $value) {
      $this->decorated->apply($configName, $value);
    }
  }

}
