<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Functional;

use Drupal\Core\Config\Schema\SchemaIncompleteException;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests that blocks assigned to invalid regions will throw exception.
 *
 * @group block
 */
class BlockInvalidRegionTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['block', 'block_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Create an admin user.
    $admin_user = $this->drupalCreateUser([
      'administer site configuration',
      'access administration pages',
      'administer blocks',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests that blocks assigned to invalid regions throw exception.
   */
  public function testBlockInInvalidRegion(): void {
    // Enable a test block and place it in an invalid region.
    $block = $this->drupalPlaceBlock('test_html');
    $block_id = $block->id();
    $this->expectException(SchemaIncompleteException::class);
    $this->expectExceptionMessage("Schema errors for block.block.$block_id with the following errors: 0 [region] This is not a valid region for &lt;em class=&quot;placeholder&quot;&gt;stark&lt;/em&gt;.");
    \Drupal::configFactory()->getEditable('block.block.' . $block_id)->set('region', 'invalid_region')->save();
  }

}
