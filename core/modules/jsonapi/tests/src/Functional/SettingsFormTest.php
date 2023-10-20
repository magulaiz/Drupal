<?php

namespace Drupal\Tests\jsonapi\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @covers \Drupal\jsonapi\Form\JsonApiSettingsForm
 * @group jsonapi
 */
class SettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['jsonapi'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the JSON:API settings form.
   */
  public function testSettingsForm(): void {
    $account = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/config/services/jsonapi');

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('read_only', 0);
    $page->pressButton('Save configuration');
    $this->assertFalse($this->config('jsonapi.settings')->get('read_only'));

    $page->selectFieldOption('read_only', 1);
    $page->pressButton('Save configuration');
    print_r($page->getContent());
    $this->container->get('config.factory')->clearStaticCache();
    $this->assertTrue($this->config('jsonapi.settings')->get('read_only'));
  }

}
