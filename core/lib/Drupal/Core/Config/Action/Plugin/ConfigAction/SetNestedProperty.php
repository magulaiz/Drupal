<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Action\Plugin\ConfigAction;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[ConfigAction(
  id: 'setProperties',
  admin_label: new TranslatableMarkup('Set property of a config entity'),
  entity_types: ['*'],
)]
final class SetProperties implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly ConfigManagerInterface $configManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get(ConfigManagerInterface::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    $entity = $this->configManager->loadConfigEntityByName($configName);
    assert($entity instanceof ConfigEntityInterface);

    assert(is_array($value));
    assert(!array_is_list($value));

    foreach ($values as $property_name => $value) {
      $parts = explode('.', $property_name);

      $property_value = $entity->get($parts[0]);
      if (count($parts) > 1) {
        if (!is_array($property_value)) {
          throw new ConfigActionException('This config action can only set nested values on arrays.');
        }
        NestedArray::setValue($property_value, array_slice($parts, 1), $value['value']);
      }
      else {
        $property_value = $value;
      }
      $entity->set($parts[0], $property_value);
    }
    $entity->save();
  }

}
