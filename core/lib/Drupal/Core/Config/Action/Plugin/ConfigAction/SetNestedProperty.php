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

#[ConfigAction(
  id: 'setNested',
  admin_label: new TranslatableMarkup('Set nested property'),
  entity_types: ['*'],
)]
final class SetNestedProperty implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly ConfigManagerInterface $configManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    $entity = $this->configManager->loadConfigEntityByName($configName);
    assert($entity instanceof ConfigEntityInterface);

    assert(is_array($value));
    $values = array_is_list($value) ? $value : [$value];

    foreach ($values as $value) {
      $property_name = $value['property_name'];
      assert(is_string($property_name));

      // The property name has to be a nested property path.
      if (!str_contains($property_name, '.')) {
        throw new ConfigActionException("The setNested config action requires a nested property path.");
      }
      $parts = explode('.', $property_name);

      $property_value = $entity->get($parts[0]) ?? [];
      if (!is_array($property_value)) {
        throw new ConfigActionException('The setNested config action can only work on array values.');
      }

      NestedArray::setValue($property_value, array_slice($parts, 1), $value['value']);
      $entity->set($parts[0], $property_value);
    }
    $entity->save();
  }

}
