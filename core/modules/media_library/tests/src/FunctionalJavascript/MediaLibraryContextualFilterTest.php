<?php

declare(strict_types=1);

namespace Drupal\Tests\media_library\FunctionalJavascript;

use Drupal\media\Entity\Media;
use Drupal\views\Views;

/**
 * Tests the media library view with contextual filters.
 *
 * @group media_library
 */
class MediaLibraryContextualFilterTest extends MediaLibraryTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['views_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $assert_session = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    // Create an admin user to update the view.
    $adminUser = $this->createUser([], 'admin user', TRUE);
    // Add media author as a contextual filter to the widget display.
    $this->drupalLogin($adminUser);
    $this->addMediaAuthorContextualFilterToDisplay('media_library', 'widget');
    $this->addMediaAuthorContextualFilterToDisplay('media_library', 'widget_table');
  }

  /**
   * Test contextual filters in the media library.
   */
  public function testMediaLibraryContextualFilter(): void {
    $assert_session = $this->assertSession();
    $session = $this->getSession();

    // Create users for adding media and using the media library.
    $permissions = [
      'access administration pages',
      'access content',
      'create basic_page content',
      'create type_one media',
      'view media',
    ];
    $user1 = $this->createUser($permissions, 'user 1');
    $user2 = $this->createUser($permissions, 'user 2');

    // Login with user 1 and create media items.
    $this->drupalLogin($user1);
    Media::create([
      'name' => 'Mosquito',
      'bundle' => 'type_one',
      'field_media_test' => 'Mosquito',
      'status' => TRUE,
      'uid' => $user1->id(),
    ])->save();
    Media::create([
      'name' => 'Ant',
      'bundle' => 'type_one',
      'field_media_test' => 'Ant',
      'status' => TRUE,
      'uid' => $user1->id(),
    ])->save();

    // Login with user 2 and create media items.
    $this->drupalLogin($user2);
    Media::create([
      'name' => 'Bear',
      'bundle' => 'type_one',
      'field_media_test' => 'Bear',
      'status' => TRUE,
      'uid' => $user2->id(),
    ])->save();
    Media::create([
      'name' => 'Horse',
      'bundle' => 'type_one',
      'field_media_test' => 'Horse',
      'status' => TRUE,
      'uid' => $user2->id(),
    ])->save();

    // Visit a node create page with user 2.
    $this->drupalGet('node/add/basic_page');
    $this->openMediaLibraryForField('field_unlimited_media');
    $this->assertElementExistsAfterWait('css', '.js-media-library-item');
    // Verify number of items on initial load of the media library widget.
    $this->waitForElementsCount('css', '#media-library-view .views-row', 2);

    // Switch to the table widget.
    $this->switchToMediaLibraryTable();
    // Verify number of items again.
    $this->waitForElementsCount('css', '#media-library-view .media-library-item', 2);

    // Switch back to the grid display.
    $this->switchToMediaLibraryGrid();
    // Verify number of items again.
    $this->waitForElementsCount('css', '#media-library-view .views-row', 2);
  }

  /**
   * Add the media author contextual filter to the media library view widgets.
   *
   * @param string $view_name
   *   The view name.
   * @param string $display_id
   *   The display ID.
   */
  protected function addMediaAuthorContextualFilterToDisplay($view_name, $display_id): void {
    $assert_session = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    $this->drupalGet("admin/structure/views/view/{$view_name}/edit/{$display_id}");
    $assert_session->elementExists('css', '#views-add-argument')->click();
    $assert_session->waitForElementVisible('css', '.views-ui-dialog');
    $assert_session->elementExists('css', 'input[name="name[media_field_data.uid]"]')->check();
    $page->find('css', '.ui-dialog-buttonset button:contains("Apply (this display)")')->press();
    $assert_session->waitForElementVisible('css', 'input[name="options[default_action]"]');
    $page->selectFieldOption('Provide default value', 'default');
    $assert_session->waitForElementVisible('css', '[data-drupal-selector="edit-options-default-argument-type"]');
    $page->selectFieldOption('options[default_argument_type]', 'current_user');
    $page->find('css', '.ui-dialog-buttonset button:contains("Apply (this display)")')->press();
    $assert_session->waitForElementRemoved('css', '.views-ui-dialog');
    $this->submitForm([], 'Save');
  }

}