<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Functional;

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
   * Tests XSS functionality with a node entity.
   */
  public function testNodeTitleXSS(): void {
    // Prepare a user to do the stuff.
    $full_html_format = FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
      'weight' => 1,
      'filters' => [
        // A filter of the FilterInterface::TYPE_HTML_RESTRICTOR type.
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
    // Change formatter for 'default' mode, check that the field is displayed
    // accordingly in 'rss' mode.

    $xss = '<script>alert("xss")</script>';
    $body = $xss . 'Confirm body text.';

    $settings = [
      'type' => 'article',
      'body' => [
        'value' => $body,
        'format' => 'full_html',
      ],
    ];
    $node = $this->drupalCreateNode($settings);

    $this->drupalGet('rss.xml');
    $res = $this->getSession()->getPage()->getContent();
    $this->assertSession()->responseNotContains($xss);
    $this->assertSession()->responseContains('Confirm body text');
  }

}
