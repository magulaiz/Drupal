<?php

declare(strict_types=1);

namespace Drupal\Tests\filter\Functional;

use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for filter_quote plugin.
 *
 * @group filter
 */
class FilterQuoteTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'filter',
    'filter_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * An administrative user account that can administer text formats.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    /** @var \Drupal\filter\FilterFormatInterface $filtered_html_format */
    $filtered_html_format = FilterFormat::load('filtered_html');
    /** @var \Drupal\filter\FilterFormatInterface $full_html_format */
    $full_html_format = FilterFormat::load('full_html');

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    $this->adminUser = $this->drupalCreateUser([
      'create article content',
      $full_html_format->getPermissionName(),
      $filtered_html_format->getPermissionName(),
      'access content',
    ]);

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Checks the behavior of a quote when filter_quote is enabled.
   */
  public function testFilterQuote(): void {
    // Check that a single line quote is wrapped in a <q> tag.
    $title = $this->randomMachineName();
    $quote_value = '<p>Single line quote should be wrapped in a "q" tag.</p>';
    $values = [
      'title[0][value]' => $title,
      'body[0][value]' => '<blockquote>' . $quote_value . '</blockquote>',
      'body[0][format]' => 'filtered_html',
    ];
    $this->drupalGet("node/add/article");
    $this->submitForm($values, 'Save');
    $node = $this->drupalGetNodeByTitle($title);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<q>' . $quote_value . '</q>');

    // Check that a multiple lines quote with <br> is wrapped in a
    // <blockquote> tag.
    $title = $this->randomMachineName();
    $quote_value = '<p>Multiple lines quote.<br>Should be wrapped in a "blockquote" tag.</p>';
    $values = [
      'title[0][value]' => $title,
      'body[0][value]' => '<blockquote>' . $quote_value . '</blockquote>',
      'body[0][format]' => 'filtered_html',
    ];
    $this->drupalGet("node/add/article");
    $this->submitForm($values, 'Save');
    $node = $this->drupalGetNodeByTitle($title);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<blockquote>' . $quote_value . '</blockquote>');

    // Check that a multiple lines quote with multiple <p> is wrapped in a
    // <blockquote> tag.
    $title = $this->randomMachineName();
    $quote_value = '<p>Also a multiple lines quote.</p><p>Should be wrapped in a "blockquote" tag too.</p>';
    $values = [
      'title[0][value]' => $title,
      'body[0][value]' => '<blockquote>' . $quote_value . '</blockquote>',
      'body[0][format]' => 'filtered_html',
    ];
    $this->drupalGet("node/add/article");
    $this->submitForm($values, 'Save');
    $node = $this->drupalGetNodeByTitle($title);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<blockquote>' . $quote_value . '</blockquote>');
  }

  /**
   * Checks the behavior of a quote when filter_quote is disabled.
   */
  public function testNoFilterQuote(): void {
    // Check that a single line quote is wrapped in a <blockquote> tag.
    $title = $this->randomMachineName();
    $quote_value = '<p>Single line quote should be wrapped in a "blockquote" tag.</p>';
    $values = [
      'title[0][value]' => $title,
      'body[0][value]' => '<blockquote>' . $quote_value . '</blockquote>',
      'body[0][format]' => 'full_html',
    ];
    $this->drupalGet("node/add/article");
    $this->submitForm($values, 'Save');
    $node = $this->drupalGetNodeByTitle($title);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<blockquote>' . $quote_value . '</blockquote>');

    // Check that a multiple lines quote with <br> is wrapped in a
    // <blockquote> tag.
    $title = $this->randomMachineName();
    $quote_value = '<p>Multiple lines quote.<br>Should be wrapped in a "blockquote" tag.</p>';
    $values = [
      'title[0][value]' => $title,
      'body[0][value]' => '<blockquote>' . $quote_value . '</blockquote>',
      'body[0][format]' => 'full_html',
    ];
    $this->drupalGet("node/add/article");
    $this->submitForm($values, 'Save');
    $node = $this->drupalGetNodeByTitle($title);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<blockquote>' . $quote_value . '</blockquote>');

    // Check that a multiple lines quote with multiple <p> is wrapped in a
    // <blockquote> tag.
    $title = $this->randomMachineName();
    $quote_value = '<p>Also a multiple lines quote.</p><p>Should be wrapped in a "blockquote" tag too.</p>';
    $values = [
      'title[0][value]' => $title,
      'body[0][value]' => '<blockquote>' . $quote_value . '</blockquote>',
      'body[0][format]' => 'full_html',
    ];
    $this->drupalGet("node/add/article");
    $this->submitForm($values, 'Save');
    $node = $this->drupalGetNodeByTitle($title);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseContains('<blockquote>' . $quote_value . '</blockquote>');
  }

}
