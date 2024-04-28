<?php

namespace Drupal\Tests\search\Functional;

use Drupal\Core\Database\Database;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Tests deleting content translation left no orphans in search index.
 *
 * @group search
 */
class SearchNodeTranslationDeleteTest extends BrowserTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'content_translation',
    'node',
    'search',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A page node for which to check translation delete.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $testNode;

  /**
   * Node search plugin.
   *
   * @var \Drupal\node\Plugin\Search\NodeSearch
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Enable content translation.
    $content_translation_manager = $this->container->get('content_translation.manager');
    $content_translation_manager->setEnabled('node', 'page', TRUE);

    // Create and log in user.
    $test_user = $this->drupalCreateUser([
      'administer search',
    ]);
    $this->drupalLogin($test_user);

    // Add a new language.
    ConfigurableLanguage::createFromLangcode('es')->save();

    // Create search index.
    $this->plugin = $this->container->get('plugin.manager.search')->createInstance('node_search');
  }

  /**
   * Create node, translation, then delete translation and check search index.
   */
  public function testLanguages() {
    // Create a page node with translation.
    $default_format = filter_default_format();
    $this->testNode = $this->drupalCreateNode([
      'title' => 'Node en',
      'type' => 'page',
      'body' => [['value' => $this->randomMachineName(32), 'format' => $default_format]],
      'langcode' => 'en',
    ]);

    // Add Spanish translation to the node.
    $translation = $this->testNode->addTranslation('es', ['title' => 'Node es']);
    $translation->body->value = $this->randomMachineName(32);
    $this->testNode->save();

    // Index nodes.
    $this->plugin->updateIndex();

    // Check database.
    $this->assertDatabaseCounts(2, 'node with 1 translation');
    // Visit the Search settings page and verify it says 100% indexed.
    $this->drupalGet('admin/config/search/pages');
    $this->assertSession()->pageTextContains('100% of the site has been indexed');
    // Check text in pages section of Search settings page.
    $this->assertSession()->pageTextContains('1 of 1 indexed');

    // Delete node translation.
    $this->testNode->removeTranslation('es');
    $this->testNode->save();

    // Index nodes.
    $this->plugin->updateIndex();
    // Run cron to remove orphan data from translation.
    $this->cronRun();

    // After deleting translation it, only one entry in {node_search}.
    // But there are 2 entries, this we need to fix.
    // Check database.
    $this->assertDatabaseCounts(1, 'node without translation');
    // Visit the Search settings page and verify it says 100% indexed.
    $this->drupalGet('admin/config/search/pages');
    $this->assertSession()->pageTextContains('100% of the site has been indexed');
    // Check text in pages section of Search settings page.
    $this->assertSession()->pageTextContains('1 of 1 indexed');
  }

  /**
   * Checks actual database counts of items in the search index.
   *
   * @param int $count_node
   *   Count of node items to assert.
   * @param string $message
   *   Message suffix to use.
   *
   * @internal
   */
  protected function assertDatabaseCounts(int $count_node, string $message): void {
    $connection = Database::getConnection();
    $results = $connection->select('search_dataset', 'i')
      ->fields('i', ['sid', 'langcode'])
      ->condition('type', 'node_search')
      ->execute()
      ->fetchCol();
    $this->assertCount($count_node, $results, 'Node count was ' . $count_node . ' for ' . $message);
  }

}
