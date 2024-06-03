<?php

declare(strict_types=1);

namespace Drupal\FunctionalJavascriptTests\Ajax;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests CORS provided by Drupal.
 *
 * @see sites/default/default.services.yml
 * @see \Asm89\Stack\Cors
 * @see \Asm89\Stack\CorsService
 *
 * @group Http
 */
class CorsIntegrationTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ajax_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  public function testAjaxCorsSettings() {
    // Test default parameters.
    $cors_config = $this->container->getParameter('cors.config');
    $this->assertFalse($cors_config['enabled']);
    $this->assertFalse($cors_config['supportsCredentials']);

    // Enable CORS with some default options.
    $cors_config['enabled'] = TRUE;
    $cors_config['supportsCredentials'] = FALSE;

    $this->setContainerParameter('cors.config', $cors_config);
    $this->rebuildContainer();

    // Test ajaxCrossDomain is not set by default (supportsCredentials is false).
    $this->drupalGet('ajax-test/dialog');
    $settings = $this->getDrupalSettings();
    $this->assertArrayHasKey('withCredentials', $settings['ajaxCrossDomain'] ?? []);
    $this->assertFalse($cors_config['supportsCredentials'] ?? FALSE);
    $this->assertFalse($settings['ajaxCrossDomain']['withCredentials'] ?? FALSE);

    // Reset page cache
    $this->resetAll();

    // Test ajaxCrossDomain is set if supportsCredentials is set.
    $cors_config['supportsCredentials'] = TRUE;
    $this->setContainerParameter('cors.config', $cors_config);
    $this->rebuildContainer();

    // Cache bust the request, reload the test page.
    $this->drupalGet('ajax-test/dialog');
    $cors_config = $this->container->getParameter('cors.config');
    $settings = $this->getDrupalSettings();
    $this->assertArrayHasKey('withCredentials', $settings['ajaxCrossDomain'] ?? []);
    $this->assertTrue($cors_config['supportsCredentials'] ?? FALSE);
    $this->assertTrue($settings['ajaxCrossDomain']['withCredentials'] ?? FALSE);
  }

}
