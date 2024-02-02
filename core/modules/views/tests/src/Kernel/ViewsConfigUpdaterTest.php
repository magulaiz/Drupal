<?php

namespace Drupal\Tests\views\Kernel;

use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
<<<<<<< HEAD
=======
use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Drupal\Tests\responsive_image\Functional\ViewsIntegrationTest;
>>>>>>> upstream/11.x
use Drupal\views\ViewsConfigUpdater;

/**
 * @coversDefaultClass \Drupal\views\ViewsConfigUpdater
 *
 * @group Views
 * @group legacy
 */
class ViewsConfigUpdaterTest extends ViewsKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'views_config_entity_test',
<<<<<<< HEAD
    'field',
    'file',
    'image',
=======
    'entity_test',
    'breakpoint',
    'field',
    'file',
    'image',
    'responsive_image',
    'responsive_image_test_module',
>>>>>>> upstream/11.x
  ];

  /**
   * @covers ::needsResponsiveImageLazyLoadFieldUpdate
   */
  public function testNeedsResponsiveImageLazyLoadFieldUpdate(): void {
    $config_updater = $this->container
      ->get('class_resolver')
      ->getInstanceFromDefinition(ViewsConfigUpdater::class);
<<<<<<< HEAD
=======
    assert($config_updater instanceof ViewsConfigUpdater);
>>>>>>> upstream/11.x

    FieldStorageConfig::create([
      'field_name' => 'user_picture',
      'entity_type' => 'user',
      'type' => 'image',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'user',
      'field_name' => 'user_picture',
      'file_directory' => 'pictures/[date:custom:Y]-[date:custom:m]',
      'bundle' => 'user',
    ])->save();
<<<<<<< HEAD
=======

    // Create a responsive image style.
    ResponsiveImageStyle::create([
      'id' => ViewsIntegrationTest::RESPONSIVE_IMAGE_STYLE_ID,
      'label' => 'Foo',
      'breakpoint_group' => 'responsive_image_test_module',
    ]);
    // Create an image field to be used with a responsive image formatter.
    FieldStorageConfig::create([
      'type' => 'image',
      'entity_type' => 'entity_test',
      'field_name' => 'bar',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'field_name' => 'bar',
    ])->save();

    $test_view = $this->loadTestView('views.view.test_responsive_images');
    $needs_update = $config_updater->needsResponsiveImageLazyLoadFieldUpdate($test_view);
    $test_view->save();
    $this->assertTrue($needs_update);

    $default_display = $test_view->getDisplay('default');
    self::assertEquals('eager', $default_display['display_options']['fields']['bar']['settings']['image_loading']['attribute']);
  }

  /**
   * @covers ::needsRenderedEntityFieldUpdate
   */
  public function testNeedsRenderedEntityFieldUpdate(): void {
    $config_updater = $this->container
      ->get('class_resolver')
      ->getInstanceFromDefinition(ViewsConfigUpdater::class);
    assert($config_updater instanceof ViewsConfigUpdater);
    $test_view = $this->loadTestView('views.view.test_entity_field_renderered_entity');
    $needs_update = $config_updater->needsRenderedEntityFieldUpdate($test_view);
    $test_view->save();
    $this->assertTrue($needs_update);

    $displays = [
      'default',
      'page_1',
      'page_2',
      'page_3',
      'page_4',
      'page_5',
      'page_6',
    ];
    foreach ($displays as $display) {
      $display = $test_view->getDisplay($display);
      self::assertEmpty($display['cache_metadata']['tags']);
    }
>>>>>>> upstream/11.x
  }

  /**
   * Loads a test view.
   *
   * @param string $view_id
   *   The view config ID.
   *
   * @return \Drupal\views\ViewEntityInterface
   *   A view entity object.
   */
  protected function loadTestView($view_id) {
    // We just instantiate the test view from the raw configuration, as it may
    // not be possible to save it, due to its faulty schema.
    $config_dir = $this->getModulePath('views') . '/tests/fixtures/update';
    $file_storage = new FileStorage($config_dir);
    $values = $file_storage->read($view_id);
    /** @var \Drupal\views\ViewEntityInterface $test_view */
    $test_view = $this->container
      ->get('entity_type.manager')
      ->getStorage('view')
      ->create($values);
    return $test_view;
  }

<<<<<<< HEAD
  /**
   * @covers ::needsEntityLinkUrlUpdate
   */
  public function testNeedsEntityLinkUrlUpdate() {
    $test_view = $this->loadTestView('views.view.node_link_update_test');
    $this->configUpdater->setDeprecationsEnabled(FALSE);
    $needs_update = $this->configUpdater->needsEntityLinkUrlUpdate($test_view);
    $this->assertTrue($needs_update);
  }

  /**
   * @covers ::needsEntityLinkUrlUpdate
   */
  public function testNeedsEntityLinkUrlUpdateDeprecation() {
    $this->expectDeprecation('The entity link url update for the "node_link_update_test" view is deprecated in drupal:9.0.0 and is removed from drupal:10.0.0. Module-provided Views configuration should be updated to accommodate the changes described at https://www.drupal.org/node/2857891.');
    $test_view = $this->loadTestView('views.view.node_link_update_test');
    $needs_update = $this->configUpdater->needsEntityLinkUrlUpdate($test_view);
    $this->assertTrue($needs_update);
  }

  /**
   * @covers ::needsOperatorDefaultsUpdate
   */
  public function testNeedsOperatorUpdateDefaults() {
    $test_view = $this->loadTestView('views.view.test_exposed_filters');
    $this->configUpdater->setDeprecationsEnabled(FALSE);
    $needs_update = $this->configUpdater->needsOperatorDefaultsUpdate($test_view);
    $this->assertTrue($needs_update);
  }

  /**
   * @covers ::needsOperatorDefaultsUpdate
   */
  public function testNeedsOperatorDefaultsUpdateDeprecation() {
    $this->expectDeprecation('The operator defaults update for the "test_exposed_filters" view is deprecated in drupal:9.0.0 and is removed from drupal:10.0.0. Module-provided Views configuration should be updated to accommodate the changes described at https://www.drupal.org/node/2869168.');
    $test_view = $this->loadTestView('views.view.test_exposed_filters');
    $needs_update = $this->configUpdater->needsOperatorDefaultsUpdate($test_view);
    $this->assertTrue($needs_update);
  }

  /**
   * @covers ::needsImageLazyLoadFieldUpdate
   */
  public function testNeedsImageLazyLoadFieldUpdate() {
    $test_view = $this->loadTestView('views.view.test_user_multi_value');
    $needs_update = $this->configUpdater->needsImageLazyLoadFieldUpdate($test_view);
    $this->assertTrue($needs_update);
  }

  /**
   * @covers ::needsMultivalueBaseFieldUpdate
   */
  public function testNeedsFieldNamesForMultivalueBaseFieldsUpdate() {
    $test_view = $this->loadTestView('views.view.test_user_multi_value');
    $this->configUpdater->setDeprecationsEnabled(FALSE);
    $needs_update = $this->configUpdater->needsMultivalueBaseFieldUpdate($test_view);
    $this->assertTrue($needs_update);
  }

  /**
   * @covers ::needsMultivalueBaseFieldUpdate
   */
  public function testNeedsFieldNamesForMultivalueBaseUpdateFieldsDeprecation() {
    $this->expectDeprecation('The multivalue base field update for the "test_user_multi_value" view is deprecated in drupal:9.0.0 and is removed from drupal:10.0.0. Module-provided Views configuration should be updated to accommodate the changes described at https://www.drupal.org/node/2900684.');
    $test_view = $this->loadTestView('views.view.test_user_multi_value');
    $needs_update = $this->configUpdater->needsMultivalueBaseFieldUpdate($test_view);
    $this->assertTrue($needs_update);
  }

  /**
   * @covers ::updateAll
   */
  public function testUpdateAll() {
    $this->expectDeprecation('The entity link url update for the "node_link_update_test" view is deprecated in drupal:9.0.0 and is removed from drupal:10.0.0. Module-provided Views configuration should be updated to accommodate the changes described at https://www.drupal.org/node/2857891.');
    $this->expectDeprecation('The operator defaults update for the "test_exposed_filters" view is deprecated in drupal:9.0.0 and is removed from drupal:10.0.0. Module-provided Views configuration should be updated to accommodate the changes described at https://www.drupal.org/node/2869168.');
    $this->expectDeprecation('The multivalue base field update for the "test_user_multi_value" view is deprecated in drupal:9.0.0 and is removed from drupal:10.0.0. Module-provided Views configuration should be updated to accommodate the changes described at https://www.drupal.org/node/2900684.');
    $view_ids = [
      'views.view.node_link_update_test',
      'views.view.test_exposed_filters',
      'views.view.test_user_multi_value',
    ];

    foreach ($view_ids as $view_id) {
      $test_view = $this->loadTestView($view_id);
      $this->assertTrue($this->configUpdater->updateAll($test_view), "View $view_id should be updated.");
    }

    // @todo Improve this in https://www.drupal.org/node/3121008.
  }

=======
>>>>>>> upstream/11.x
}
