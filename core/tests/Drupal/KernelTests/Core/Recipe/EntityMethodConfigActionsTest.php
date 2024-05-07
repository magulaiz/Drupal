<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Recipe;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;

/**
 * @group Recipe
 */
class EntityMethodConfigActionsTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use RecipeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config_test',
    'field',
    'layout_builder',
    'layout_discovery',
    'node',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('node');
    $this->createContentType(['type' => 'test']);

    $this->container->get(EntityDisplayRepositoryInterface::class)
      ->getViewDisplay('node', 'test', 'full')
      ->save();
  }

  public function testSetSingleThirdPartySetting(): void {
    $recipe = <<<YAML
name: Third-party setting
config:
  actions:
    core.entity_view_display.node.test.full:
      setThirdPartySetting:
        module: layout_builder
        key: enabled
        value: true
YAML;
    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    /** @var \Drupal\Core\Config\Entity\ThirdPartySettingsInterface $display */
    $display = $this->container->get(EntityDisplayRepositoryInterface::class)
      ->getViewDisplay('node', 'test', 'full');
    $this->assertTrue($display->getThirdPartySetting('layout_builder', 'enabled'));
  }

  public function testSetMultipleThirdPartySettings(): void {
    $recipe = <<<YAML
name: Third-party setting
config:
  actions:
    core.entity_view_display.node.test.full:
      setThirdPartySettings:
        -
          module: layout_builder
          key: enabled
          value: true
        -
          module: layout_builder
          key: allow_custom
          value: true
YAML;
    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    /** @var \Drupal\Core\Config\Entity\ThirdPartySettingsInterface $display */
    $display = $this->container->get(EntityDisplayRepositoryInterface::class)
      ->getViewDisplay('node', 'test', 'full');
    $this->assertTrue($display->getThirdPartySetting('layout_builder', 'enabled'));
    $this->assertTrue($display->getThirdPartySetting('layout_builder', 'allow_custom'));
  }

  /**
   * @testWith [{"set": {"property_name": "protected_property", "value": "Here be sandworms..."}}]
   *   [{"setMultiple": [{"property_name": "protected_property", "value": "Here be sandworms..."}, {"property_name": "label", "value": "New face"}]}]
   */
  public function testSet(array $config_actions): void {
    $storage = $this->container->get(EntityTypeManagerInterface::class)
      ->getStorage('config_test');

    $entity = $storage->create([
      'id' => 'foo',
      'label' => 'Behold!',
      'protected_property' => 'Here be dragons...',
    ]);
    $this->assertSame('Behold!', $entity->get('label'));
    $this->assertSame('Here be dragons...', $entity->get('protected_property'));
    $entity->save();

    $recipe = $this->createRecipe([
      'name' => 'Set a value',
      'config' => [
        'actions' => [
          'config_test.dynamic.foo' => $config_actions,
        ],
      ],
    ]);
    RecipeRunner::processRecipe($recipe);

    $entity = $storage->load('foo');
    foreach ($config_actions as ['property_name' => $name, 'value' => $value]) {
      $this->assertSame($value, $entity->get($name));
    }
  }

  /**
   * @testWith [true, {"setStatus": false}, false]
   *   [false, {"setStatus": true}, true]
   *   [true, {"disable": []}, false]
   *   [false, {"enable": []}, true]
   */
  public function testSetStatus(bool $initial_status, array $actions, bool $expected_status): void {
    $storage = $this->container->get(EntityTypeManagerInterface::class)
      ->getStorage('config_test');

    $entity = $storage->create([
      'id' => 'foo',
      'label' => 'Behold!',
      'status' => $initial_status,
    ]);
    $this->assertSame($initial_status, $entity->status());
    $entity->save();

    $recipe = $this->createRecipe([
      'name' => 'Change config entity status',
      'config' => [
        'actions' => [
          'config_test.dynamic.foo' => $actions,
        ],
      ],
    ]);
    RecipeRunner::processRecipe($recipe);

    $this->assertSame($expected_status, $storage->load('foo')->status());
  }

  public function testSetFieldLabelAndDescription(): void {
    $recipe = <<<YAML
name: 'Set field label and description'
config:
  actions:
    field.field.node.*.body:
      setLabel: 'Not what you were expecting!'
      setDescription: "Any ol' nonsense can go here."
YAML;
    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $field = FieldConfig::loadByName('node', 'test', 'body');
    $this->assertNotEmpty($field);
    $this->assertSame('Not what you were expecting!', $field->getLabel());
    $this->assertSame("Any ol' nonsense can go here.", $field->getDescription());
  }

}
