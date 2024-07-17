<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Kernel;

// cspell:ignore inflector
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the config action system.
 *
 * @group config
 */
class ConfigActionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['config_test', 'block', 'system', 'path_alias'];

  /**
   * Reference valid configuration for placing a block.
   */
  protected $validBlock = [
    'theme' => 'olivero',
    'region' => 'content',
    'weight' => 0,
    'provider' => NULL,
    'id' => 'config_action_test',
    'plugin' => 'local_tasks_block',
    'settings' => [
      'id' => 'local_tasks_block',
      'label' => 'Additional tabs',
      'provider' => 'core',
      'primary' => FALSE,
      'secondary' => TRUE,
    ],
    'visibility' => [],
  ];

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceNull(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Empty configuration should trigger an error.
    try {
      $manager->applyAction('placeBlock', 'block.block.block_test', NULL);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.block_test cannot be created because no configuration was provided', $e->getMessage());
    }
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceNonArray(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Empty configuration should trigger an error.
    try {
      $manager->applyAction('placeBlock', 'block.block.block_test', 'Dummy string');
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.block_test cannot be created because provided configuration is not an array', $e->getMessage());
    }
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceNonBlock(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Non-block configuration should trigger an error.
    try {
      $manager->applyAction('placeBlock', 'config_test.dynamic.action_test', ['label' => 'Action test', 'protected_property' => '']);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Provided config entity config_test.dynamic.action_test is not a block', $e->getMessage());
    }
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceNoTheme(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');

    // Block configuration with no theme specified should trigger an error.
    $no_theme = $this->validBlock;
    unset($no_theme['theme']);
    try {
      $manager->applyAction('placeBlock', 'block.block.no_theme', $no_theme);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.no_theme cannot be created because of missing theme identifier', $e->getMessage());
    }
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceInvalidTheme(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Block configuration with invalid theme specified should trigger an error.
    $bad_theme = $this->validBlock;
    $bad_theme['theme'] = 'mongoose';
    try {
      $manager->applyAction('placeBlock', 'block.block.bad_theme', $bad_theme);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.bad_theme cannot be created because the specified theme is missing', $e->getMessage());
    }
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceMissingPlugin(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Block configuration with no plugin specified should trigger an error.
    $no_plugin = $this->validBlock;
    unset($no_plugin['plugin']);
    try {
      $manager->applyAction('placeBlock', 'block.block.no_plugin', $no_plugin);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.no_plugin requires a plugin value', $e->getMessage());
    }
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceBlockRegion(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Tests below require a theme installed.
    \Drupal::service('theme_installer')->install(['olivero']);

    // Block configuration with no region specified should trigger an error.
    $no_region = $this->validBlock;
    unset($no_region['region']);
    try {
      $manager->applyAction('placeBlock', 'block.block.no_region', $no_region);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.no_region cannot be created because a region must be specified', $e->getMessage());
    }

    // Block configuration with invalid region specified must trigger an error.
    $bad_region = $this->validBlock;
    $bad_region['id'] = 'bad_region';
    $bad_region['region'] = 'platypus';
    try {
      $manager->applyAction('placeBlock', 'block.block.bad_region', $bad_region);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.bad_region could not identify a valid region', $e->getMessage());
    }

    $bad_region['regions'] = ['nonexistent', 'bogus', 'nonsense'];
    try {
      $manager->applyAction('placeBlock', 'block.block.bad_region', $bad_region);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Block block.block.bad_region could not identify a valid region', $e->getMessage());
    }

    // Validate that the block can be placed with a valid backup region.
    $bad_region['regions'][] = 'content';
    $manager->applyAction('placeBlock', 'block.block.bad_region', $bad_region);
    $placed_blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties([
      'id' => 'bad_region',
    ]);
    $this->assertCount(1, $placed_blocks, 'There is 1 matching block entity');
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceBlockThemes(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Tests below require a theme installed.
    \Drupal::service('theme_installer')->install(['olivero', 'claro']);
    $config = \Drupal::configFactory()->getEditable('system.theme');
    $config->set('default', 'olivero')->save();

    // Validate that the block can be placed with 'default' theme specified.
    $default_theme_block = $this->validBlock;
    $default_theme_block['theme'] = 'default';
    $default_theme_block['id'] = 'default_theme_block';
    $manager->applyAction('placeBlock', 'block.block.default_theme_block', $default_theme_block);
    // ID will be automatically updated based on the default theme name.
    $placed_blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties([
      'id' => 'olivero_theme_block',
    ]);
    $this->assertCount(1, $placed_blocks, 'There is 1 matching block entity');
    $found_block = array_pop($placed_blocks);
    $this->assertSame('olivero', $found_block->get('theme'));

    // Validate that the block can be placed.
    $manager->applyAction('placeBlock', 'block.block.config_action_test', $this->validBlock);
    $placed_blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties([
      'id' => 'config_action_test',
    ]);
    $this->assertCount(1, $placed_blocks, 'There is 1 matching block entity');

    // Placing the same block again should not fail.
    $manager->applyAction('placeBlock', 'block.block.config_action_test', $this->validBlock);

    // Placing the same block again with a different theme should fail.
    $different_theme = $this->validBlock;
    $different_theme['theme'] = 'claro';
    try {
      $manager->applyAction('placeBlock', 'block.block.config_action_test', $different_theme);
      $this->fail('Expected theme exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Unable to place block block.block.config_action_test because a block with this name has been placed in a different theme', $e->getMessage());
    }
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceBlockOrder(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Tests below require a theme installed.
    \Drupal::service('theme_installer')->install(['olivero']);
    // Validate that placing blocks first and last works as expected.
    $first_block = $last_block = $this->validBlock;
    $last_block['weight'] = 'last';
    $last_block['id'] = 'config_action_last';
    $manager->applyAction('placeBlock', 'block.block.config_action_last', $last_block);
    $first_block['weight'] = 'first';
    $first_block['id'] = 'config_action_first';
    $manager->applyAction('placeBlock', 'block.block.config_action_first', $first_block);
    $placed_blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties([
      'theme' => 'olivero',
      'region' => 'content',
    ]);
    // These would be out of order if not for the weight keywords.
    uasort($placed_blocks, 'Drupal\block\Entity\Block::sort');
    $this->assertSame('config_action_first', array_key_first($placed_blocks));
    $this->assertSame('config_action_last', array_key_last($placed_blocks));
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceBlockIds(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Tests below require a theme installed.
    \Drupal::service('theme_installer')->install(['olivero']);

    // Validate that the block can be placed without an id, with a valid name.
    $no_id = $this->validBlock;
    unset($no_id['id']);
    // First, verify that it fails without an available fallback.
    try {
      $manager->applyAction('placeBlock', 'block.block.', $no_id);
      $this->fail('Expected exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Unable to determine a valid id for block block.block.', $e->getMessage());
    }
    // Next, verify that it can extract an id from the config name.
    $manager->applyAction('placeBlock', 'block.block.no_id', $no_id);
    $placed_blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties([
      'id' => 'no_id',
    ]);
    $this->assertCount(1, $placed_blocks, 'There is 1 matching block entity');
  }

  /**
   * @see \Drupal\Core\Config\Action\Plugin\ConfigAction\PlaceBlock
   */
  public function testPlaceBlockExisting(): void {
    /** @var \Drupal\Core\Config\Action\ConfigActionManager $manager */
    $manager = $this->container->get('plugin.manager.config_action');
    // Tests below require a theme installed.
    \Drupal::service('theme_installer')->install(['olivero']);

    // The following tests require the block to be placed already.
    $manager->applyAction('placeBlock', 'block.block.config_action_test', $this->validBlock);

    // Placing the same block again with a different plugin should fail.
    $different_plugin = $this->validBlock;
    $different_plugin['plugin'] = 'some_other_plugin';
    try {
      $manager->applyAction('placeBlock', 'block.block.config_action_test', $different_plugin);
      $this->fail('Expected plugin exception not thrown');
    }
    catch (ConfigActionException $e) {
      $this->assertSame('Unable to place block block.block.config_action_test because a block with this name has been placed but uses a different plugin', $e->getMessage());
    }

    // Placing the same block again with new visibility or settings should
    // update the block.
    $updated_block = $this->validBlock;
    $updated_block['visibility'] = [
      'request_path' => [
        'id' => 'request_path',
        'negate' => FALSE,
        'pages' => '<front>',
      ],
    ];
    $updated_block['settings']['primary'] = TRUE;
    $updated_block['settings']['secondary'] = FALSE;
    $manager->applyAction('placeBlock', 'block.block.config_action_test', $updated_block);
    $placed_blocks = \Drupal::entityTypeManager()->getStorage('block')->loadByProperties([
      'id' => 'config_action_test',
    ]);
    $this->assertCount(1, $placed_blocks, 'There is 1 matching block entity');
    $block = array_pop($placed_blocks);
    $block_visibility = $block->getVisibility();
    $this->assertSame('<front>', $block_visibility['request_path']['pages'], 'Block has the modified visibility');
    $block_settings = $block->get('settings');
    $this->assertTrue($block_settings['primary'], 'Block has modified settings');
  }

}
