<?php

namespace Drupal\Tests\comment\Functional;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\system\Functional\Cache\AssertPageCacheContextsAndTagsTrait;
use Drupal\user\Entity\Role;

/**
 * Tests comments as part of an RSS feed.
 *
 * @group comment
 */
class CommentRssTest extends CommentTestBase {

  use AssertPageCacheContextsAndTagsTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['views'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Setup the rss view display.
    EntityViewDisplay::create([
      'status' => TRUE,
      'targetEntityType' => 'node',
      'bundle' => 'article',
      'mode' => 'rss',
      'content' => ['links' => ['weight' => 100]],
    ])->save();
  }

  /**
   * Tests comments as part of an RSS feed.
   */
  public function testCommentRss() {
    // Find comment in RSS feed.
    $this->drupalLogin($this->webUser);
    $this->postComment($this->node, $this->randomMachineName(), $this->randomMachineName());
    $this->drupalGet('rss.xml');

    $cache_contexts = [
      'languages:language_interface',
      'theme',
      'url.site',
      'user',
      'timezone',
    ];
    $this->assertCacheContexts($cache_contexts);

    $cache_context_tags = Cache::mergeTags(\Drupal::service('cache_contexts_manager')->convertTokensToKeys($cache_contexts)->getCacheTags(), [
      'config:views.view.frontpage',
      'node:1',
      'node_list',
      'node_view',
      'user:3',
    ]);
    foreach (Role::loadMultiple($this->webUser->getRoles()) as $role) {
      $cache_context_tags = Cache::mergeTags($cache_context_tags, $role->getCacheTags());
    }
    $this->assertCacheTags($cache_context_tags);

    $raw = '<comments>' . $this->node->toUrl('canonical', ['fragment' => 'comments', 'absolute' => TRUE])->toString() . '</comments>';
    $this->assertSession()->responseContains($raw);

    // Hide comments from RSS feed and check presence.
    $this->node->set('comment', CommentItemInterface::HIDDEN);
    $this->node->save();
    $this->drupalGet('rss.xml');
    $this->assertSession()->responseNotContains($raw);
  }

}
