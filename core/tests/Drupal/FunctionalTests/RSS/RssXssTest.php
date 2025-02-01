<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Test;

use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests that dangerous tags in RSS feeds are escaped.
 *
 * @group node
 */
class RssXssTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['filter', 'editor', 'node', 'views'];

  /**
   * Tests XSS functionality with in the RSS feed.
   */
  public function testRssXss(): void {
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    $field_storage = [
      'field_name' => 'test_field',
      'entity_type' => 'node',
      'type' => 'text',
    ];
    FieldStorageConfig::create($field_storage)->save();
    $field = [
      'field_name' => $field_storage['field_name'],
      'entity_type' => 'node',
      'bundle' => 'article',
    ];
    FieldConfig::create($field)->save();

    // Assign display properties for the 'rss' view mode.
    \Drupal::service('entity_display.repository')
      ->getViewDisplay('node', 'article', 'rss')
      ->setComponent($field_storage['field_name'])
      ->setComponent('body')
      ->save();

    $trusted_html_format = FilterFormat::create([
      'format' => 'trusted_format',
      'name' => 'Text format for trusted users',
      'weight' => 1,
      'filters' => [
        'filter_html' => [
          'status' => 1,
          'settings' => [
            'allowed_html' => '<h2> <h3> <h4> <h5> <h6> <p> <br> <strong> <a> <embed> <div> <img src>',
          ],
        ],
      ],
    ]);
    $trusted_html_format->save();
    $web_user = $this->drupalCreateUser([
      'create article content',
      'edit any article content',
      'use text format trusted_format',
    ]);
    $this->drupalLogin($web_user);

    // Dangerous script tag.
    $xss = '<script>alert("xss")</script>';
    $title = $xss . 'Confirm title.';
    $body = $xss . '<img src="test" />Confirm body text.';
    $plain_text = $xss . 'Confirm <div>plain</div> text.';

    $settings = [
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body,
        'format' => 'trusted_format',
      ],
      'test_field' => [
        'value' => $plain_text,
      ],
    ];
    $this->drupalCreateNode($settings);

    $this->drupalGet('rss.xml');
    // Ensure XSS was filtered appropriately upstream.
    $this->assertSession()->responseNotContains($xss);
    // Ensure the created page loads with content.
    $this->assertSession()->responseContains('Confirm title.');
    // The div should be escaped from plain text.
    $this->assertSession()->responseNotContains('Confirm <div>plain</div> text.');
    // The image should be allowed by the format.
    $this->assertSession()->responseContains('<img src="test">Confirm body text.');
    $this->assertSession()->responseContains('Confirm &lt;div&gt;plain&lt;/div&gt; text.');
  }

}
