<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Recipe;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
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
    'filter',
    'layout_builder',
    'layout_discovery',
    'node',
    'system',
    'text',
    'user',
  ];

  private readonly ConfigActionManager $configActionManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('filter');
    $this->installConfig('node');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->createContentType(['type' => 'test']);

    $this->container->get(EntityDisplayRepositoryInterface::class)
      ->getViewDisplay('node', 'test', 'full')
      ->save();

    $this->configActionManager = $this->container->get('plugin.manager.config_action');
  }

  public function testSetSingleThirdPartySetting(): void {
    $this->configActionManager->applyAction(
      'entity_method:core.entity_view_display:setThirdPartySetting',
      'core.entity_view_display.node.test.full',
      [
        'module' => 'layout_builder',
        'key' => 'enabled',
        'value' => TRUE,
      ],
    );

    /** @var \Drupal\Core\Config\Entity\ThirdPartySettingsInterface $display */
    $display = $this->container->get(EntityDisplayRepositoryInterface::class)
      ->getViewDisplay('node', 'test', 'full');
    $this->assertTrue($display->getThirdPartySetting('layout_builder', 'enabled'));
  }

  public function testSetMultipleThirdPartySettings(): void {
    $this->configActionManager->applyAction(
      'entity_method:core.entity_view_display:setThirdPartySettings',
      'core.entity_view_display.node.test.full',
      [
        [
          'module' => 'layout_builder',
          'key' => 'enabled',
          'value' => TRUE,
        ],
        [
          'module' => 'layout_builder',
          'key' => 'allow_custom',
          'value' => TRUE,
        ],
      ],
    );

    /** @var \Drupal\Core\Config\Entity\ThirdPartySettingsInterface $display */
    $display = $this->container->get(EntityDisplayRepositoryInterface::class)
      ->getViewDisplay('node', 'test', 'full');
    $this->assertTrue($display->getThirdPartySetting('layout_builder', 'enabled'));
    $this->assertTrue($display->getThirdPartySetting('layout_builder', 'allow_custom'));
  }

  /**
   * @testWith ["set", {"property_name": "protected_property", "value": "Here be sandworms..."}]
   *   ["setMultiple", [{"property_name": "protected_property", "value": "Here be sandworms..."}, {"property_name": "label", "value": "New face"}]]
   */
  public function testSet(string $action_name, array $value): void {
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

    $this->configActionManager->applyAction(
      "entity_method:config_test.dynamic:$action_name",
      'config_test.dynamic.foo',
      $value,
    );

    $expected_values = array_is_list($value) ? $value : reset($value);
    $entity = $storage->load('foo');
    foreach ($expected_values as ['property_name' => $name, 'value' => $value]) {
      $this->assertSame($value, $entity->get($name));
    }
  }

  /**
   * @testWith [true, "setStatus", false, false]
   *   [false, "setStatus", true, true]
   *   [true, "disable", [], false]
   *   [false, "enable", [], true]
   */
  public function testSetStatus(bool $initial_status, string $action_name, array|bool $value, bool $expected_status): void {
    $storage = $this->container->get(EntityTypeManagerInterface::class)
      ->getStorage('config_test');

    $entity = $storage->create([
      'id' => 'foo',
      'label' => 'Behold!',
      'status' => $initial_status,
    ]);
    $this->assertSame($initial_status, $entity->status());
    $entity->save();

    $this->configActionManager->applyAction(
      "entity_method:config_test.dynamic:$action_name",
      'config_test.dynamic.foo',
      $value,
    );

    $this->assertSame($expected_status, $storage->load('foo')->status());
  }

  public function testConfigurableLanguageEntityActions(): void {
    $this->enableModules(['language']);
    $this->installConfig('language');

    $language = ConfigurableLanguage::load('en');
    $this->assertSame('English', $language->getName());
    $this->assertSame(0, $language->getWeight());

    $recipe = <<<YAML
name: 'Change configurable language'
config:
  actions:
    {$language->getConfigDependencyName()}:
      setName: "Wacky language"
      setWeight: 39
YAML;

    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $language = ConfigurableLanguage::load('en');
    $this->assertSame('Wacky language', $language->getName());
    $this->assertSame(39, $language->getWeight());
  }

  public function testRemoveComponentFromDisplay(): void {
    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $repository */
    $repository = $this->container->get(EntityDisplayRepositoryInterface::class);

    $form_display = $repository->getFormDisplay('node', 'test');
    $this->assertIsArray($form_display->getComponent('uid'));

    $view_display = $repository->getViewDisplay('node', 'test');
    $this->assertIsArray($view_display->getComponent('body'));
    $this->assertIsArray($view_display->getComponent('links'));

    // The `hideComponent` action is an alias for `removeComponent`, proving
    // that entity methods can be aliased.
    $recipe = <<<YAML
name: 'Hide display components'
config:
  actions:
    {$form_display->getConfigDependencyName()}:
      hideComponent: uid
    {$view_display->getConfigDependencyName()}:
      hideComponents:
        - body
        - links
YAML;

    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $this->assertNull($repository->getFormDisplay('node', 'test')->getComponent('uid'));
    $view_display = $repository->getViewDisplay('node', 'test');
    $this->assertNull($view_display->getComponent('body'));
    $this->assertNull($view_display->getComponent('links'));

    // `removeComponent` should not be a valid action name, even though it's the
    // name of the underlying method.
    $this->expectException(PluginNotFoundException::class);
    $this->expectExceptionMessage('The "entity_form_display:removeComponent" plugin does not exist.');
    $this->container->get('plugin.manager.config_action')
      ->applyAction('entity_form_display:removeComponent', $form_display->getConfigDependencyName(), 'uid');
  }

}
