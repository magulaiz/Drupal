<?php

namespace Drupal\Tests\system\Functional\System;

use Drupal\rest\Entity\RestResourceConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests to see if generator header is added.
 *
 * @group system
 */
class ResponseGeneratorTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['jsonapi', 'rest', 'user', 'basic_auth'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The currently logged-in user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    $account = $this->drupalCreateUser(['access content']);
    $this->currentUser = $account;
    $this->drupalLogin($account);
  }

  /**
   * Tests to see if generator header is added.
   */
  public function testGeneratorHeaderAdded() {
    [$version] = explode('.', \Drupal::VERSION, 2);
    $expectedGeneratorHeader = 'Drupal ' . $version . ' (https://www.drupal.org)';

    // Check to see if the header is added when viewing an HTML page.
    $this->drupalGet($this->currentUser->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Content-Type', 'text/html; charset=UTF-8');
    $this->assertSession()->responseHeaderEquals('X-Generator', $expectedGeneratorHeader);

    // Check to see if the header is also added for a non-successful response.
    $this->drupalGet('llama');
    $this->assertSession()->statusCodeEquals(404);
    $this->assertSession()->responseHeaderEquals('Content-Type', 'text/html; charset=UTF-8');
    $this->assertSession()->responseHeaderEquals('X-Generator', $expectedGeneratorHeader);

    // Enable cookie-based authentication for the entity:user REST resource.
    $resource_config = RestResourceConfig::load('entity.user');
    $configuration = $resource_config->get('configuration');
    $configuration['authentication'][] = 'cookie';
    $resource_config->set('configuration', $configuration)->save();
    $this->rebuildAll();

    // Check to see if the header is also added for a non-HTML request.
    $this->drupalGet($this->currentUser->toUrl()->setOption('query', ['_format' => 'json']));
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Content-Type', 'application/json');
    $this->assertSession()->responseHeaderEquals('X-Generator', $expectedGeneratorHeader);
  }

}
