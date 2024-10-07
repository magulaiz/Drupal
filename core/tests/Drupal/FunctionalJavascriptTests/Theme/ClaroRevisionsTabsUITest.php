<?php

declare(strict_types=1);

namespace Drupal\FunctionalJavascriptTests\Theme;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\Node;

/**
 * Runs tests on Revisions UI using Claro.
 *
 * @group claro
 */
class ClaroRevisionsTabsUITest extends WebDriverTestBase {

  /**
   * An array of node revisions.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $nodes;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);

    // Create initial node.
    $node = $this->drupalCreateNode();

    $nodes = [];

    // Get original node.
    $nodes[] = clone $node;

    // Create two revisions.
    $revision_count = 2;
    for ($i = 0; $i < $revision_count; $i++) {

      // Create revision with a random title and body and update variables.
      $node->title = $this->randomMachineName();
      $node->body = [
        'value' => $this->randomMachineName(32),
        'format' => filter_default_format(),
      ];
      $node->setNewRevision();

      $node->save();

      // Make sure we get revision information.
      $node = Node::load($node->id());
      $nodes[] = clone $node;
    }

    $this->nodes = $nodes;

    // Create the test user and log in.
    $admin_user = $this->drupalCreateUser([
      'access administration pages',
      'view the administration theme',
      'administer nodes',
      'edit any page content',
      'view page revisions',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests Revisions UI displays local tasks tabs.
   */
  public function testRevisionsUiTabsExist(): void {

    $this->drupalGet('node/' . $this->nodes[0]->id() . '/revisions');
    $assert_session = $this->assertSession();
    $assert_session->elementExists('css', 'ul.tabs.tabs--primary.clearfix');
    $assert_session->pageContains('node/' . $this->nodes[0]->id() . '/view');
    $assert_session->pageContains('node/' . $this->nodes[0]->id() . '/edit');
    $assert_session->pageContains('node/' . $this->nodes[0]->id() . '/delete');
    $assert_session->pageContains('node/' . $this->nodes[0]->id() . '/revisions');
  }

}
