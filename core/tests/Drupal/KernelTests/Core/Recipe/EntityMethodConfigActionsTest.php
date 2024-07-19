<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Recipe;

use Drupal\block\Entity\Block;
use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\image\Entity\ImageStyle;
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

    $expected_values = array_key_exists('set', $config_actions)
      ? $config_actions
      : reset($config_actions);
    $entity = $storage->load('foo');
    foreach ($expected_values as ['property_name' => $name, 'value' => $value]) {
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

  public function testChangeFieldSettings(): void {
    $field = FieldConfig::loadByName('node', 'test', 'body');
    $this->assertTrue($field->isTranslatable());
    $this->assertFalse($field->isRequired());
    $this->assertTrue($field->getSetting('display_summary'));
    $this->assertFalse($field->getSetting('required_summary'));
    $this->assertEmpty($field->getDefaultValueLiteral());

    $recipe = <<<YAML
name: 'Set field label and description'
config:
  actions:
    field.field.node.*.body:
      setLabel: 'Not what you were expecting!'
      setDescription: "Any ol' nonsense can go here."
      setTranslatable: false
      setRequired: true
      setSettings:
        display_summary: false
        required_summary: true
      setDefaultValue:
        value: "Don't build a castle in a swamp."
YAML;
    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

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

  public function testContactFormEntityActions(): void {
    $this->enableModules(['contact']);
    $this->installConfig('contact');

    $form = ContactForm::load('personal');
    $this->assertSame('Your message has been sent.', $form->getMessage());
    $this->assertEmpty($form->getRecipients());
    $this->assertSame('/', $form->getRedirectUrl()->toString());
    $this->assertEmpty($form->getReply());
    $this->assertSame(0, $form->getWeight());

    $node = $this->createNode(['type' => 'test']);

    $recipe = <<<YAML
name: 'Change contact form'
config:
  actions:
    {$form->getConfigDependencyName()}:
      setMessage: 'Fly, little message!'
      setRecipients:
        - ben@deep.space
        - jake@deep.space
      setRedirectPath: {$node->toUrl()->toString()}
      setReply: "From hell's heart, I reply to thee."
      setWeight: -10
YAML;

    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $form = ContactForm::load($form->id());
    $this->assertSame('Fly, little message!', $form->getMessage());
    $this->assertSame(['ben@deep.space', 'jake@deep.space'], $form->getRecipients());
    $this->assertSame($node->toUrl()->toString(), $form->getRedirectUrl()->toString());
    $this->assertSame("From hell's heart, I reply to thee.", $form->getReply());
    $this->assertSame(-10, $form->getWeight());
  }

  public function testImageStyleEntityActions(): void {
    $this->enableModules(['image']);
    $this->installConfig('image');

    $style = ImageStyle::load('large');
    $this->assertCount(2, $style->getEffects());

    $recipe = <<<YAML
name: 'Change image style'
config:
  actions:
    {$style->getConfigDependencyName()}:
      addImageEffect:
        id: image_desaturate
        weight: 1
YAML;

    $recipe = $this->createRecipe($recipe);
    RecipeRunner::processRecipe($recipe);

    $this->assertCount(3, ImageStyle::load('large')->getEffects());
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
