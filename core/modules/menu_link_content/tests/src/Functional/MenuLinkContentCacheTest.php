<?php

declare(strict_types=1);

namespace Drupal\Tests\menu_link_content\Functional;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;
use Drupal\Tests\system\Functional\Cache\PageCacheTagsTestBase;
use Drupal\Tests\Traits\Core\PathAliasTestTrait;

/**
 * Ensures that the menu tree adapts to entity path alias changes.
 *
 * @group menu_link_content
 * @group path
 */
class MenuLinkContentCacheTest extends PageCacheTagsTestBase {

  use PathAliasTestTrait;

  /**
   * The user to test the feature.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $basicUser;

  /**
   * The entity to test the aliases logic.
   *
   * @var \Drupal\entity_test\Entity\EntityTest
   */
  protected EntityTest $entity;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'menu_link_content',
    'entity_test',
    'block',
    'menu_ui',
    'path_alias',
    'test_page_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = EntityTest::create();
    $this->entity->save();

    Menu::create([
      'id' => 'menu-test',
      'label' => 'Test menu',
      'description' => 'Description text',
    ])->save();

    $menu_link = MenuLinkContent::create([
      'title' => 'Menu link test',
      'provider' => 'menu_link_content',
      'menu_name' => 'menu-test',
      'link' => ['uri' => "entity:entity_test/{$this->entity->id()}"],
    ]);
    $menu_link->save();

    $this->drupalPlaceBlock('system_menu_block:menu-test');

    $this->basicUser = $this->drupalCreateUser(['view test entity']);
    $this->drupalLogin($this->basicUser);
  }

  /**
   * Tests the entity path aliasing changing.
   */
  public function testMenuLinkContentCache(): void {
    /** @var \Drupal\path_alias\AliasManagerInterface $alias_manager */
    $alias_manager = \Drupal::service('path_alias.manager');
    $test_page_url = Url::fromRoute('test_page_test.test_page');

    // Ensure that canonical URI is used when there is no alias.
    $this->verifyDynamicPageCache($test_page_url, 'MISS');
    $this->verifyDynamicPageCache($test_page_url, 'HIT');
    $this->assertSession()->linkByHrefExists('/entity_test/' . $this->entity->id());

    // Create alias and confirm that menu link is updated to point to alias.
    $alias = $this->createPathAlias('/entity_test/' . $this->entity->id(), '/foo');
    $alias_manager->cacheClear();
    $this->verifyDynamicPageCache($test_page_url, 'MISS');
    $this->verifyDynamicPageCache($test_page_url, 'HIT');
    $this->assertSession()->linkByHrefExists('foo');

    // Update alias and confirm that link is updated to point to alias.
    $alias->setAlias('/bar');
    $alias->save();
    $this->verifyDynamicPageCache($test_page_url, 'MISS');
    $this->verifyDynamicPageCache($test_page_url, 'HIT');
    $this->assertSession()->linkByHrefExists('bar');

    // Delete alias and confirm that link points to canonical URI.
    $alias->delete();
    $this->verifyDynamicPageCache($test_page_url, 'MISS');
    $this->verifyDynamicPageCache($test_page_url, 'HIT');
    $this->assertSession()->linkByHrefExists('/entity_test/' . $this->entity->id());
  }

}
