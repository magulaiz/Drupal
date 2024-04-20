<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Component\Utility\Html;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationUrl;
use Drupal\Tests\block\Traits\BlockCreationTrait;
use Drupal\Tests\BrowserTestBase;

// cspell:ignore displaymessage scriptalertxsssubjectscript
// cspell:ignore testcontextawarenavigationblock

/**
 * Tests that the navigation block config UI exists and stores data correctly.
 *
 * @group navigation
 */
class NavigationBlockUiTest extends BrowserTestBase {

  use BlockCreationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'navigation',
    'navigation_test',
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
        'plugin_id' => 'system_menu_navigation_block:content',
        'settings' => ['region' => 'content', 'id' => 'content_menu'],
        'test_weight' => '-2',
      ],
      [
        'label' => 'Administration',
        'tr' => '5',
        'plugin_id' => 'system_menu_navigation_block:admin',
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

  /**
   * Tests the behavior of unsatisfied context-aware navigation blocks.
   */
  public function testContextAwareUnsatisfiedNavigationBlocks() {
    $this->drupalGet('/admin/config/user-interface/navigation-block');
    $this->clickLink('Place navigation block');
    // Verify that the context-aware test navigation block does not appear.
    $this->assertSession()->elementNotExists('xpath', '//tr[.//td/div[text()="Test context-aware unsatisfied navigation block"] and .//td[text()="Navigation test"] and .//td//a[contains(@href, "admin/config/user-interface/navigation-block/add/test_context_aware_unsatisfied")]]');

    $definition = \Drupal::service('plugin.manager.navigation_block')->getDefinition('test_context_aware_unsatisfied');
    $this->assertNotEmpty($definition, 'The context-aware test block does not exist.');
  }

  /**
   * Tests the behavior of context-aware navigation blocks.
   */
  public function testContextAwareNavigationBlocks() {
    $expected_text = '<div id="test_context_aware--username">' . \Drupal::currentUser()->getAccountName() . '</div>';
    $this->drupalGet('');
    $this->assertSession()->pageTextNotContains('Test context-aware block');
    $this->assertSession()->responseNotContains($expected_text);

    $navigation_block_url = 'admin/config/user-interface/navigation-block/add/test_context_aware';

    $this->drupalGet('/admin/config/user-interface/navigation-block');
    $this->clickLink('Place navigation block');
    $this->assertSession()->elementExists('xpath', '//tr[.//td/div[text()="Test context-aware navigation block"] and .//td[text()="Navigation test"] and .//td//a[contains(@href, "' . $navigation_block_url . '")]]');
    $definition = \Drupal::service('plugin.manager.navigation_block')->getDefinition('test_context_aware');
    $this->assertNotEmpty($definition, 'The context-aware test navigation block exists.');
    $edit = [
      'settings[label_display]' => TRUE,
      'settings[context_mapping][user]' => '@navigation_test.multiple_static_context:userB',
    ];
    $this->drupalGet($navigation_block_url);
    $this->submitForm($edit, 'Save navigation block');

    $this->drupalGet('');
    $this->assertSession()->pageTextContains('Test context-aware navigation block');
    $this->assertSession()->pageTextContains('User context found.');
    $this->assertSession()->responseContains($expected_text);

    // Test context mapping form element is not visible if there are no valid
    // context options for the navigation block (the
    // test_context_aware_no_valid_context_options navigation block has one
    // context defined which is not available for it on the Navigation Block
    // Layout interface).
    $this->drupalGet('admin/config/user-interface/navigation-block/add/test_context_aware_no_valid_context_options');
    $this->assertSession()->fieldNotExists('edit-settings-context-mapping-email');

    // Test context mapping allows empty selection for optional contexts.
    $this->drupalGet('admin/config/user-interface/navigation-block/manage/testcontextawarenavigationblock');
    $edit = [
      'settings[context_mapping][user]' => '',
    ];
    $this->submitForm($edit, 'Save navigation block');
    $this->drupalGet('');
    $this->assertSession()->pageTextContains('No context mapping selected.');
    $this->assertSession()->pageTextNotContains('User context found.');

    // Tests that conditions with missing context are not displayed.
    $this->drupalGet('admin/structure/block/manage/stark_testcontextawareblock');
    $this->assertSession()->responseNotContains('No existing type');
    $this->assertSession()->elementNotExists('xpath', '//*[@name="visibility[condition_test_no_existing_type][negate]"]');
  }

  /**
   * Tests that the NavigationBlockForm populates machine name correctly.
   */
  public function testMachineNameSuggestion() {
    // Check the form uses the raw machine name suggestion when no instance
    // already exists.
    $url = 'admin/config/user-interface/navigation-block/add/test_navigation_block_instantiation';
    $this->drupalGet($url);
    $this->assertSession()->fieldValueEquals('id', 'displaymessage');
    $this->drupalGet($url);
    $this->submitForm([], 'Save navigation block');
    $this->assertSession()->pageTextContains('The navigation block configuration has been saved.');

    // Now, check to make sure the form starts by auto-incrementing correctly.
    $this->drupalGet($url);
    $this->assertSession()->fieldValueEquals('id', 'displaymessage_2');
    $this->drupalGet($url);
    $this->submitForm([], 'Save navigation block');
    $this->assertSession()->pageTextContains('The navigation block configuration has been saved.');

    // And verify that it continues working beyond just the first two.
    $this->drupalGet($url);
    $this->assertSession()->fieldValueEquals('id', 'displaymessage_3');
  }

  /**
   * Tests the navigation block placement indicator.
   */
  public function testNavigationBlockPlacementIndicator() {
    // Test the navigation block placement indicator with using the domain as
    // URL language indicator. This causes destination query parameters to be
    // absolute URLs.
    \Drupal::service('module_installer')->install(['language', 'locale']);
    $this->container = \Drupal::getContainer();
    ConfigurableLanguage::createFromLangcode('it')->save();
    $config = $this->config('language.types');
    $config->set('negotiation.language_interface.enabled', [
      LanguageNegotiationUrl::METHOD_ID => -10,
    ]);
    $config->save();
    $config = $this->config('language.negotiation');
    $config->set('url.source', LanguageNegotiationUrl::CONFIG_DOMAIN);
    $config->set('url.domains', [
      'en' => \Drupal::request()->getHost(),
      'it' => 'it.example.com',
    ]);
    $config->save();

    // Select a basic test navigation block to be placed.
    $navigation_block = [];
    $navigation_block['id'] = $this->randomMachineName();

    // After adding a navigation block, it will indicate which navigation block
    // was just added.
    $this->drupalGet('admin/config/user-interface/navigation-block/add/test_navigation_block_instantiation');
    $this->submitForm($navigation_block, 'Save navigation block');
    $this->assertSession()->addressEquals('admin/config/user-interface/navigation-block?navigation-block-placement=' . Html::getClass($navigation_block['id']));

    // Resaving the navigation block page will remove the block placement
    // indicator.
    $this->submitForm([], 'Save navigation blocks');
    $this->assertSession()->addressEquals('admin/config/user-interface/navigation-block');

    // Place another navigation block and test the remove functionality works
    // with the navigation block placement indicator. Click the first
    // 'Place navigation block' link to bring up the list of navigation blocks
    // to place in the first available region.
    $this->clickLink('Place navigation block');
    // Select the first available navigation block, which is the
    // 'test_xss_title' plugin, with a default machine name
    // 'scriptalertxsssubjectscript' that is used for the
    // 'navigation-block-placement' querystring parameter.
    $this->clickLink('Place navigation block');
    $this->submitForm([], 'Save navigation block');
    $this->assertSession()->addressEquals('admin/config/user-interface/navigation-block?navigation-block-placement=scriptalertxsssubjectscript');

    // Removing a navigation block will remove the navigation block
    // placement indicator.
    $this->clickLink('Remove');
    $this->submitForm([], 'Remove');
    $this->assertSession()->addressEquals('admin/config/user-interface/navigation-block');
  }

  /**
   * Tests if validation errors are passed plugin form to the parent form.
   */
  public function testNavigationBlockValidateErrors() {
    $this->drupalGet('admin/config/user-interface/navigation-block/add/test_settings_validation');
    $this->submitForm([
      'settings[digits]' => 'abc',
    ], 'Save navigation block');

    $this->assertSession()->statusMessageContains('Only digits are allowed', 'error');
    $this->assertSession()->elementExists('xpath', '//div[contains(@class,"form-item-settings-digits")]/input[contains(@class,"error")]');
  }

  /**
   * Tests that users without permission are not able to view broken nav blocks.
   */
  public function testBrokenNavigationBlockVisibility() {
    $assert_session = $this->assertSession();

    $navigation_block = $this->drupalPlaceBlock('broken');

    // Ensure that broken block configuration can be accessed.
    $this->drupalGet('admin/config/user-interface/navigation-block/manage/' . $navigation_block->id());
    $assert_session->statusCodeEquals(200);

    // Login as an admin user to the site.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('');
    $assert_session->statusCodeEquals(200);
    // Check that this user can view the Broken Navigation Block message.
    $assert_session->pageTextContains('This block is broken or missing. You may be missing content or you might need to install the original module.');
    $this->drupalLogout();

    // Visit the same page as anonymous.
    $this->drupalGet('');
    $assert_session->statusCodeEquals(200);
    // Check that this user cannot view the Broken Navigation Block message.
    $assert_session->pageTextNotContains('This block is broken or missing. You may be missing content or you might need to install the original module.');

    // Visit same page as an authorized user that does not have access to
    // administer blocks.
    $this->drupalLogin($this->drupalCreateUser(['access administration pages']));
    $this->drupalGet('');
    $assert_session->statusCodeEquals(200);
    // Check that this user cannot view the Broken Block message.
    $assert_session->pageTextNotContains('This block is broken or missing. You may be missing content or you might need to install the original module.');
  }

}
