<?php

namespace Drupal\Tests\layout_builder\Functional;

use Drupal\block_content\Entity\BlockContentType;
use Drupal\layout_builder\Section;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Layout Builder inline blocks alters.
 *
 * @group layout_builder
 */
class LayoutBuilderInlineBlocksTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'views',
    'layout_builder',
    'layout_builder_views_test',
    'layout_test',
    'block',
    'block_content',
    'block_test',
    'contextual',
    'node',
    'layout_builder_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('local_tasks_block');

    // Create a basic and a custom block bundles.
    BlockContentType::create([
      'id' => 'basic',
      'label' => 'Basic',
    ])->save();

    BlockContentType::create([
      'id' => 'custom',
      'label' => 'Custom',
    ])->save();

    // Create two nodes.
    $this->createContentType([
      'type' => 'bundle_with_section_field',
      'name' => 'Bundle with section field',
    ]);
  }

  /**
   * Tests that we can create a custom block.
   */
  public function testLayoutBuilderChooseBlocksCanCreateCustomBlock() {
    // See layout_builder_test_plugin_filter_block__layout_builder_alter().
    $assert_session = $this->assertSession();

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'administer node display',
      'administer node fields',
      'create and edit custom blocks',
    ]));

    // From the manage display page, go to manage the layout.
    $this->drupalGet('admin/structure/types/manage/bundle_with_section_field/display/default');
    $this->submitForm(['layout[enabled]' => TRUE], 'Save');
    $assert_session->linkExists('Manage layout');
    $this->clickLink('Manage layout');

    // Add a new block.
    $this->clickLink('Add block');

    // Assert I can create a custom block.
    $assert_session->linkExists('Create custom block');
    $this->clickLink('Create custom block');

    // Assert I see both block types
    $assert_session->linkExists('Basic');
    $assert_session->linkExists('Custom');
  }

  /**
   * Tests that we can create the allowed custom blocks after alter.
   *
   * @see layout_builder_hooks_test_layout_builder_allowed_inline_blocks_alter
   */
  public function testLayoutBuilderInlineBlocksCanBeAlter() {
    $this->container->get('module_installer')->install(['layout_builder_hooks_test']);

    // See layout_builder_hooks_test_layout_builder_allowed_inline_blocks_alter().
    $assert_session = $this->assertSession();

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'administer node display',
      'administer node fields',
      'create and edit custom blocks',
    ]));

    // From the manage display page, go to manage the layout.
    $this->drupalGet('admin/structure/types/manage/bundle_with_section_field/display/default');
    $this->submitForm(['layout[enabled]' => TRUE], 'Save');
    $assert_session->linkExists('Manage layout');
    $this->clickLink('Manage layout');

    // Add a new block.
    $this->clickLink('Add block');

    // Assert I can create a custom block.
    $assert_session->linkExists('Create custom block');
    $this->clickLink('Create custom block');

    // Assert I see only the block types allowed.
    $assert_session->linkNotExists('Basic');
    $assert_session->linkExists('Custom');
  }

  /**
   * Tests that we don't see the create custom block link if the storage don't allow bundles.
   *
   * @see \Drupal\layout_builder_test\Plugin\SectionStorage\SimpleConfigSectionStorage::inlineBlocksAllowedInContext
   */
  public function testSimpleConfigBasedLayoutWithNoAllowedInlineBlocks() {
    $assert_session = $this->assertSession();

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'create and edit custom blocks',
    ]));

    // Prepare an object with a pre-existing section.
    $this->container->get('config.factory')->getEditable('layout_builder_test.test_simple_config.existing')
      ->set('sections', [(new Section('layout_twocol'))->toArray()])
      ->save();

    // The pre-existing section is found.
    $this->drupalGet('layout-builder-test-simple-config/existing');
    $this->clickLink('Add block');

    $assert_session->linkNotExists('Create custom block');
  }

}
