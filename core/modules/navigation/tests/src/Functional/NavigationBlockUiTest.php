<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\BrowserTestBase;

// cspell:ignore displaymessage scriptalertxsssubjectscript
// cspell:ignore testcontextawarenavigationblock

/**
 * Tests that the navigation block UI exists and stores data correctly.
 *
 * @group navigation
 */
class NavigationBlockUiTest extends BrowserTestBase {

  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'navigation',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The submitted navigation block values used by this test.
   *
   * @var array
   */
  protected $navigationBlockValues;

  /**
   * An administrative user to configure the test environment.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Create and log in an administrative user.
    $this->adminUser = $this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
      'access navigation',
    ]);
    $this->drupalLogin($this->adminUser);

    // Enable some test blocks.
    $this->navigationBlockValues = [
      [
        'label' => 'Shortcuts',
        'tr' => '3',
        'plugin_id' => 'shortcuts',
        'settings' => ['region' => 'content', 'id' => 'shortcuts'],
        'test_weight' => '2',
      ],
      [
        'label' => 'Content',
        'tr' => '4',
        'plugin_id' => 'navigation_menu:content',
        'settings' => ['region' => 'content', 'id' => 'content_menu'],
        'test_weight' => '-2',
      ],
      [
        'label' => 'Administration',
        'tr' => '5',
        'plugin_id' => 'navigation_menu:admin',
        'settings' => ['region' => 'content', 'id' => 'administration_menu'],
        'test_weight' => '-1',
      ],
    ];
  }

  /**
   * Tests navigation block admin page exists and functions correctly.
   */
  public function testNavigationBlockAdminUiPage() {
    // Visit the navigation blocks admin ui.
    $this->drupalGet('/admin/config/user-interface/navigation-block');
    // Look for the navigation blocks table.
    $this->assertSession()->elementExists('xpath', "//table[@id='navigation-blocks']");
    // Look for test navigation blocks in the table.
    $edit = [];
    foreach ($this->navigationBlockValues as $values) {
      $this->assertSession()->elementTextEquals('xpath', '//*[@id="navigation-blocks"]/tbody/tr[' . $values['tr'] . ']/td[1]/text()', $values['label']);
      // Look for a test navigation block weight select form element.
      $this->assertSession()->fieldExists('navigation_blocks[' . $values['settings']['id'] . '][weight]');
      // Change the test navigation block's weight.
      $edit['navigation_blocks[' . $values['settings']['id'] . '][weight]'] = $values['test_weight'];
    }
    $this->drupalGet('/admin/config/user-interface/navigation-block');
    $this->submitForm($edit, 'Save navigation blocks');

    foreach ($this->navigationBlockValues as $values) {
      // Check if the weight settings changes have persisted.
      $this->assertTrue($this->assertSession()->optionExists(str_replace('_', '-', 'edit-navigation-blocks-' . $values['settings']['id'] . '-weight'), $values['test_weight'])->isSelected());
    }
  }

}
