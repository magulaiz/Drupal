<?php

declare(strict_types=1);

namespace Drupal\Tests\path\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\WaitTerminateTestTrait;

/**
 * Tests modifying path aliases from the UI.
 *
 * @group path
 */
class PathAliasTest extends PathTestBase {

  use WaitTerminateTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['path'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create test user and log in.
    $web_user = $this->drupalCreateUser([
      'create page content',
      'edit own page content',
      'administer url aliases',
      'create url aliases',
      'access content overview',
    ]);
    $this->drupalLogin($web_user);

    // The \Drupal\path_alias\AliasPrefixList service performs cache clears
    // after Drupal has flushed the response to the client. We use
    // WaitTerminateTestTrait to wait for Drupal to do this before continuing.
    $this->setWaitForTerminate();
  }

  /**
   * Tests the path cache.
   */
  public function testPathCache(): void {
    // Create test node.
    $node1 = $this->drupalCreateNode();

    // Create alias.
    $edit = [];
    $edit['path[0][value]'] = '/node/' . $node1->id();
    $edit['alias[0][value]'] = '/' . $this->randomMachineName(8);
    $this->drupalGet('admin/config/search/path/add');
    $this->submitForm($edit, 'Save');

    // Check the path alias prefix list cache.
    $prefix_list = \Drupal::cache('bootstrap')->get('path_alias_prefix_list');
    $this->assertTrue($prefix_list->data['node']);
    $this->assertFalse($prefix_list->data['admin']);

    // Visit the system path for the node and confirm a cache entry is
    // created.
    \Drupal::cache('data')->deleteAll();
    // Make sure the path is not converted to the alias.
    $this->drupalGet(trim($edit['path[0][value]'], '/'), ['alias' => TRUE]);
    $this->assertNotEmpty(\Drupal::cache('data')->get('preload-paths:' . $edit['path[0][value]']), 'Cache entry was created.');

    // Visit the alias for the node and confirm a cache entry is created.
    \Drupal::cache('data')->deleteAll();
    // @todo Remove this once https://www.drupal.org/node/2480077 lands.
    Cache::invalidateTags(['rendered']);
    $this->drupalGet(trim($edit['alias[0][value]'], '/'));
    $this->assertNotEmpty(\Drupal::cache('data')->get('preload-paths:' . $edit['path[0][value]']), 'Cache entry was created.');
  }

}
