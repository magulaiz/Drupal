<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Recipe;

use Drupal\block\Entity\Block;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\media\Entity\MediaType;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * @group Recipe
 */
class EntityMethodConfigActionsTest extends KernelTestBase {

  use BlockCreationTrait;
  use ContentTypeCreationTrait;
  use NodeCreationTrait;
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

  public function testChangeFieldSettings(): void {
    $field = FieldConfig::loadByName('node', 'test', 'body');
    $this->assertTrue($field->isTranslatable());
    $this->assertFalse($field->isRequired());
    $this->assertTrue($field->getSetting('display_summary'));
    $this->assertFalse($field->getSetting('required_summary'));
    $this->assertEmpty($field->getDefaultValueLiteral());

    $this->configActionManager->applyAction(
      'entity_method:field.field:setLabel',
      $field->getConfigDependencyName(),
      'Not what you were expecting!',
    );
    $this->configActionManager->applyAction(
      'entity_method:field.field:setDescription',
      $field->getConfigDependencyName(),
      "Any ol' nonsense can go here.",
    );
    $this->configActionManager->applyAction(
      'entity_method:field.field:setTranslatable',
      $field->getConfigDependencyName(),
      FALSE,
    );
    $this->configActionManager->applyAction(
      'entity_method:field.field:setRequired',
      $field->getConfigDependencyName(),
      TRUE,
    );
    $this->configActionManager->applyAction(
      'entity_method:field.field:setSettings',
      $field->getConfigDependencyName(),
      [
        'display_summary' => FALSE,
        'required_summary' => TRUE,
      ],
    );
    $this->configActionManager->applyAction(
      'entity_method:field.field:setDefaultValue',
      $field->getConfigDependencyName(),
      [
        'value' => "Don't build a castle in a swamp.",
      ],
    );

    $field = FieldConfig::loadByName('node', 'test', 'body');
    $this->assertNotEmpty($field);
    $this->assertSame('Not what you were expecting!', $field->getLabel());
    $this->assertSame("Any ol' nonsense can go here.", $field->getDescription());
    $this->assertFalse($field->isTranslatable());
    $this->assertTrue($field->isRequired());
    $this->assertFalse($field->getSetting('display_summary'));
    $this->assertTrue($field->getSetting('required_summary'));
    $this->assertSame([['value' => "Don't build a castle in a swamp."]], $field->getDefaultValueLiteral());
  }

  public function testBlockEntityActions(): void {
    $this->enableModules(['block']);
    $this->container->get(ThemeInstallerInterface::class)->install(['stark']);

    $block = $this->placeBlock('system_messages_block', ['theme' => 'stark']);
    $this->assertSame('content', $block->getRegion());
    $this->assertSame(0, $block->getWeight());

    $recipe = <<<YAML
name: 'Change block setup'
config:
  actions:
    {$block->getConfigDependencyName()}:
      setRegion: highlighted
      setWeight: -10
YAML;
    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $block = Block::load($block->id());
    $this->assertSame('highlighted', $block->getRegion());
    $this->assertSame(-10, $block->getWeight());
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

  public function testMediaTypeEntityActions(): void {
    $this->container->get('module_installer')->install(['media_test_type']);

    $media_type = MediaType::load('test');
    $this->assertSame('Test type.', $media_type->getDescription());
    $this->assertSame(['metadata_attribute' => 'field_attribute_config_test'], $media_type->getFieldMap());

    $recipe = <<<YAML
name: 'Change media type'
config:
  actions:
    {$media_type->getConfigDependencyName()}:
      setDescription: 'Changed by a recipe...'
      setFieldMap:
        foo: baz
YAML;

    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $media_type = MediaType::load('test');
    $this->assertSame('Changed by a recipe...', $media_type->getDescription());
    $this->assertSame(['foo' => 'baz'], $media_type->getFieldMap());
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

  public function testNodeTypeEntityActions(): void {
    $node_type = NodeType::load('test');

    $this->assertTrue($node_type->shouldCreateNewRevision());
    $this->assertSame(DRUPAL_OPTIONAL, $node_type->getPreviewMode());
    $this->assertTrue($node_type->displaySubmitted());

    $recipe = <<<YAML
name: 'Change content type'
config:
  actions:
    {$node_type->getConfigDependencyName()}:
      setNewRevision: false
      setPreviewMode: 2
      setDisplaySubmitted: false
YAML;

    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $node_type = NodeType::load('test');
    $this->assertFalse($node_type->shouldCreateNewRevision());
    $this->assertSame(DRUPAL_REQUIRED, $node_type->getPreviewMode());
    $this->assertFalse($node_type->displaySubmitted());
  }

}
