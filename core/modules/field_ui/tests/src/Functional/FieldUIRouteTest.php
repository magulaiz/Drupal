<?php

namespace Drupal\Tests\field_ui\Functional;

use Drupal\Core\Entity\Entity\EntityFormMode;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the functionality of the Field UI route subscriber.
 *
 * @group field_ui
 */
class FieldUIRouteTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var string[]
   */
  protected static $modules = ['block', 'entity_test', 'field_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->rootUser);
    $this->drupalPlaceBlock('page_title_block', ['weight' => -10]);
    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Ensures that entity types with bundles do not break following entity types.
   */
  public function testFieldUIRoutes() {
    $this->drupalGet('entity_test_no_id/structure/entity_test_no_id/fields');
    $this->assertSession()->pageTextContains('No fields are present yet.');

    $this->drupalGet('admin/config/people/accounts/fields');
    $this->assertSession()->titleEquals('Manage fields: User | Drupal');
    $this->assertLocalTasks();

    // Test manage display tabs and titles.
    $this->drupalGet('admin/config/people/accounts/display/compact');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalGet('admin/config/people/accounts/display');
    $this->assertSession()->titleEquals('Manage display: User | Drupal');
    $this->assertLocalTasks();

    $edit = ['display_modes_custom[compact]' => TRUE];
    $this->submitForm($edit, 'Save');
    $this->drupalGet('admin/config/people/accounts/display/compact');
    $this->assertSession()->titleEquals('Manage display: User | Drupal');
    $this->assertLocalTasks();

    // Test manage form display tabs and titles.
    $this->drupalGet('admin/config/people/accounts/form-display/register');
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalGet('admin/config/people/accounts/form-display');
    $this->assertSession()->titleEquals('Manage form display: User | Drupal');
    $this->assertLocalTasks();

    $edit = ['display_modes_custom[register]' => TRUE];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('admin/config/people/accounts/form-display/register');
    $this->assertSession()->titleEquals('Manage form display: User | Drupal');
    $this->assertLocalTasks();
    // Test that default secondary tab is in first position.
    $this->assertSession()->elementsCount('xpath', "//ul/li[1]/a[contains(text(), 'Default')]", 1);

    // Create new view mode and verify it's available on the Manage Display
    // screen after enabling it.
    EntityViewMode::create([
      'id' => 'user.test',
      'label' => 'Test',
      'targetEntityType' => 'user',
    ])->save();
    $this->container->get('router.builder')->rebuildIfNeeded();

    $edit = ['display_modes_custom[test]' => TRUE];
    $this->drupalGet('admin/config/people/accounts/display');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->linkExists('Test');

    // Create new form mode and verify it's available on the Manage Form
    // Display screen after enabling it.
    EntityFormMode::create([
      'id' => 'user.test',
      'label' => 'Test',
      'targetEntityType' => 'user',
    ])->save();
    $this->container->get('router.builder')->rebuildIfNeeded();

    $edit = ['display_modes_custom[test]' => TRUE];
    $this->drupalGet('admin/config/people/accounts/form-display');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->linkExists('Test');
  }

  /**
   * Asserts that local tasks exists.
   *
   * @internal
   */
  public function assertLocalTasks(): void {
    $this->assertSession()->linkExists('Settings');
    $this->assertSession()->linkExists('Manage fields');
    $this->assertSession()->linkExists('Manage display');
    $this->assertSession()->linkExists('Manage form display');
  }

  /**
   * Asserts that admin routes are correctly marked as such.
   */
  public function testAdminRoute() {
    $route = \Drupal::service('router.route_provider')->getRouteByName('entity.entity_test.field_ui_fields');
    $is_admin = \Drupal::service('router.admin_context')->isAdminRoute($route);
    $this->assertTrue($is_admin, 'Admin route correctly marked for "Manage fields" page.');
  }

  /**
   * Tests titles of admin routes.
   */
  public function testBundleEntityTitles() {
    $entity = EntityTest::create([
      'name' => 'entity_test',
      'entity_type' => 'entity_test',
    ]);
    $entity->save();
    $entity_type_manager = \Drupal::entityTypeManager();
    $node_type = $entity_type_manager->getStorage('entity_test')->load($entity->id());
    $node_type_label = $node_type->label();

    // Create teaser view mode for test entity.
    $entity_view_mode = EntityViewMode::create([
      'id' => 'entity_test.teaser',
      'label' => 'Teaser',
      'targetEntityType' => 'entity_test',
    ]);
    $entity_view_mode->save();
    $edit = ['display_modes_custom[teaser]' => TRUE];
    $this->drupalGet('entity_test/structure/entity_test/display');
    $this->submitForm($edit, 'Save');

    $user_entity_type_label = $this->container->get('entity_type.manager')
      ->getStorage('user')->getEntityType()->getLabel();
    /** @var \Drupal\Core\Entity\EntityViewModeInterface $compact_display_mode */
    $compact_display_mode = EntityViewMode::load('user.compact');

    $this->drupalGet('admin/config/people/accounts/display');
    $edit = ['display_modes_custom[compact]' => TRUE];
    $this->submitForm($edit, 'Save');

    // Entities having bundles (e.g. 'node', 'taxonomy_term').
    $path = 'entity_test/structure/entity_test';
    $args = [
      '@bundle' => $node_type_label,
    ];
    $titles = [
      "$path/fields" => (string) t('Manage fields: Entity Test Bundle'),
      "$path/fields/add-field" => (string) t('Add field to Entity Test Bundle'),
      "$path/form-display" => (string) t('Manage form display: Entity Test Bundle'),
      "$path/form-display/default" => (string) t('Manage form display: Entity Test Bundle'),
      "$path/display" => (string) t('Manage display: Entity Test Bundle'),
      "$path/display/default" => (string) t('Manage display: Entity Test Bundle'),
      "$path/display/teaser" => (string) t('Manage display: Entity Test Bundle'),
    ];
    // Entities without bundles (e.g. 'user').
    $path = 'admin/config/people/accounts';
    $args = ['@entity' => $user_entity_type_label];
    $titles += [
      "$path/fields" => (string) t('Manage fields: @entity', $args),
      "$path/fields/add-field" => (string) t('Add field to @entity', $args),
      "$path/form-display" => (string) t('Manage form display: @entity', $args + ['@mode' => (string) t('Default')]),
      "$path/form-display/default" => (string) t('Manage form display: @entity', $args + ['@mode' => (string) t('Default')]),
      "$path/display" => (string) t('Manage display: @entity', $args + ['@mode' => (string) t('Default')]),
      "$path/display/default" => (string) t('Manage display: @entity', $args + ['@mode' => (string) t('Default')]),
      "$path/display/compact" => (string) t('Manage display: @entity', $args + ['@mode' => $compact_display_mode->label()]),
    ];

    foreach ($titles as $path => $title) {
      $this->drupalGet($path);
      $this->assertSession()->pageTextContains($title);
    }
  }

}
