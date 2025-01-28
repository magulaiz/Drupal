<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests that dangerous tags in the node title are escaped.
 *
 * @group node
 */
class RSSXSSTest extends NodeTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['filter', 'editor', 'node', 'views'];

  /**
   * Tests XSS functionality with a node entity in the RSS feed.
   */
  public function testNodeTitleXSS(): void {
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

    $full_html_format = FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
      'weight' => 1,
      'filters' => [
        'filter_html' => [
          'status' => 1,
          'settings' => [
            'allowed_html' => '<h2> <h3> <h4> <h5> <h6> <p> <br> <strong> <a> <embed>',
          ],
        ],
      ],
    ]);
    $full_html_format->save();
    $web_user = $this->drupalCreateUser([
      'create article content',
      'edit any article content',
      'use text format full_html',
    ]);
    $this->drupalLogin($web_user);

    // Dangerous script tag.
    $xss = '<script>alert("xss")</script>';
    $title = $xss . 'Confirm title.';
    $body = $xss . 'Confirm body text.';
    $plain_text = $xss . 'Confirm plain text.';

    $settings = [
      'type' => 'article',
      'title' => $title,
      'body' => [
        'value' => $body,
        'format' => 'full_html',
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
    $this->assertSession()->responseContains('Confirm body text.');
    $this->assertSession()->responseContains('Confirm plain text.');
  }

}
