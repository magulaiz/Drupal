<?php

namespace Drupal\Tests\filter\Kernel;

use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests text format default configuration.
 *
 * @group filter
 */
class FilterDefaultConfigTest extends KernelTestBase {

  protected static $modules = ['system', 'user', 'filter', 'filter_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Install filter_test module, which ships with custom default format.
    $this->installConfig(['user', 'filter_test']);
  }

  /**
   * Tests installation of default formats.
   */
  public function testInstallation() {
    // Verify that the format was installed correctly.
    $format = FilterFormat::load('filter_test');
    $this->assertTrue((bool) $format);
    $this->assertEquals('filter_test', $format->id());
    $this->assertEquals('Test format', $format->label());
    $this->assertEquals(2, $format->get('weight'));

    // Verify that format default property values have been added/injected.
    $this->assertNotEmpty($format->uuid());

    // Verify enabled filters.
    $filters = $format->get('filters');
    $this->assertEquals(1, $filters['filter_html_escape']['status']);
    $this->assertEquals(-10, $filters['filter_html_escape']['weight']);
    $this->assertEquals('filter', $filters['filter_html_escape']['provider']);
    $this->assertEquals([], $filters['filter_html_escape']['settings']);
    $this->assertEquals(1, $filters['filter_autop']['status']);
    $this->assertEquals(0, $filters['filter_autop']['weight']);
    $this->assertEquals('filter', $filters['filter_autop']['provider']);
    $this->assertEquals([], $filters['filter_autop']['settings']);
    $this->assertEquals(1, $filters['filter_url']['status']);
    $this->assertEquals(0, $filters['filter_url']['weight']);
    $this->assertEquals('filter', $filters['filter_url']['provider']);
    $this->assertEquals(['filter_url_length' => 72], $filters['filter_url']['settings']);
  }

}
