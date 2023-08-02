<?php

namespace Drupal\Tests\block\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests page title block.
 *
 * @group Block
 */
class PageTitleBlockTest extends BrowserTestBase {
  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['block', 'update', 'node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Add the page title block to the page.
    $this->drupalPlaceBlock('page_title_block', ['id' => 'stark_page_title']);

    // Create node type.
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    // Create administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer themes',
      'administer software updates',
      'administer nodes',
      'create article content',
      'edit any article content',
      'delete any article content',
      'delete any article content',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Data provider for testContextualizeTitle().
   *
   * @return array[][]
   *   The test cases.
   */
  public function providerTestContextualizeTitle() {
    return [
      'Stark theme' => [
        $this->defaultTheme,
        FALSE,
        'Update',
        'Extend',
      ],
      // For Claro theme the contextualize_title settings is enabled by default
      // hence the title will always be contextualized.
      'Claro theme' => [
        'claro',
        TRUE,
        'Extend',
        'Extend',
      ],
      'Olivero theme' => [
        'olivero',
        FALSE,
        'Update',
        'Extend',
      ],
    ];
  }

  /**
   * Check if the contextualized title is displayed.
   *
   * @dataProvider providerTestContextualizeTitle
   */
  public function testContextualizeTitle(string $theme, bool $contextualize_title_enabled, string $non_contextualized_title, string $contextualized_title) {
    if ($theme !== $this->defaultTheme) {
      $system_theme_config = $this->container->get('config.factory')
        ->getEditable('system.theme');
      $system_theme_config
        ->set('default', $theme)
        ->save();
      \Drupal::service('theme_installer')->install([$theme]);
    }
    $edit['admin_theme'] = $theme;
    $this->drupalGet('admin/appearance');
    $this->submitForm($edit, 'Save configuration');

    // Make sure the title shown is non-contextualized.
    $this->drupalGet('admin/modules/update');
    $this->assertSession()->elementTextEquals('css', 'h1', $non_contextualized_title);

    // Checking if the title block is configured for showing contextualized
    // title and if it's not then configure it.
    $this->drupalGet('admin/structure/block/manage/' . $theme . '_page_title');
    if ($contextualize_title_enabled) {
      $this->assertSession()->checkboxChecked('settings[contextualize_title]');
    }
    else {
      $this->assertSession()->checkboxNotChecked('settings[contextualize_title]');
      $this->submitForm(['settings[contextualize_title]' => TRUE], 'Save block');
    }

    // Make sure the title shown is contextualized.
    $this->drupalGet('admin/modules/update');
    $this->assertSession()->elementTextEquals('css', 'h1', $contextualized_title);
  }

  /**
   * Data provider for testContextualizeTitleOnNodeOperationPages().
   *
   * @return array[][]
   *   The test cases.
   */
  public function providerTestContextualizeTitleOnNodeOperationPages() {
    return [
      'node with random title' => [$this->randomMachineName(8)],
      'node with title set to 0' => ['0'],
    ];
  }

  /**
   * Tests if contextualized title displayed on all node operation pages.
   *
   * @dataProvider providerTestContextualizeTitleOnNodeOperationPages
   */
  public function testContextualizeTitleOnNodeOperationPages($node_title) {
    $settings = [
      'type' => 'article',
      'title' => $node_title,
    ];
    $node = $this->drupalCreateNode($settings);

    // Make sure the non-contextualized title is shown on all node operation
    // pages.
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->elementTextEquals('xpath', '//h1', $node_title);

    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertSession()->elementTextEquals('xpath', '//h1', "Edit Article $node_title");

    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->assertSession()->elementTextEquals('xpath', '//h1', "Are you sure you want to delete the content item $node_title?");

    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->assertSession()->elementTextEquals('xpath', '//h1', "Revisions for $node_title");

    // Configure title block to show contextualized title and.
    $this->drupalGet('admin/structure/block/manage/' . $this->defaultTheme . '_page_title');
    $this->submitForm(['settings[contextualize_title]' => TRUE], 'Save block');

    // Make sure the contextualized title is shown on all node operation pages.
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->elementTextEquals('xpath', '//h1', $node_title);

    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertSession()->elementTextEquals('xpath', '//h1', $node_title);

    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->assertSession()->elementTextEquals('xpath', '//h1', $node_title);

    $this->drupalGet('node/' . $node->id() . '/revisions');
    $this->assertSession()->elementTextEquals('xpath', '//h1', $node_title);

  }

}
