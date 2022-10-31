<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Menu;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Tests the behavior of the linkset controller in multilingual setup.
 *
 * @group decoupled_menus
 *
 * @see https://tools.ietf.org/html/draft-ietf-httpapi-linkset-00
 */
final class LinksetControllerMultiLingualTest extends LinksetControllerTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'basic_auth',
    'link',
    'path_alias',
    'path',
    'user',
    'menu_link_content',
    'node',
    'page_cache',
    'dynamic_page_cache',
    'language',
  ];

  /**
   * An HTTP kernel.
   *
   * Used to send a test request to the controller under test and validate its
   * response.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * A user account to author test content.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $authorAccount;

  /**
   * Test set up.
   *
   * Installs necessary database schemas, then creates test content and menu
   * items. The concept of this set up is to replicate a typical site's menus.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setUp(): void {
    parent::setUp();
    // Create Basic page node type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    // Add some custom languages.
    foreach (['aa', 'bb', 'cc', 'dd'] as $index => $language_code) {
      ConfigurableLanguage::create([
        'id' => $language_code,
        'label' => $this->randomMachineName(),
        'weight' => $index,
      ])->save();
    }
    // Setting up an admin user to configure multi-lingual settings.
    $admin_user = $this->drupalCreateUser([
      'view own unpublished content',
      'administer languages',
      'administer content types',
      'access administration pages',
      'create page content',
      'edit own page content',
    ]);
    $this->drupalLogin($admin_user);
    // Enable URL language detection and selection.
    $this->drupalGet('/admin/config/regional/language/detection');
    $this->submitForm([
      'language_interface[enabled][language-url]' => TRUE,
      'language_interface[enabled][language-selected]' => TRUE,
    ], 'Save settings');

    // Set prefixes to aa, bb, and cc.
    $this->drupalGet('/admin/config/regional/language/detection/url');
    $this->submitForm([
      'prefix[aa]' => 'aa',
      'prefix[bb]' => 'bb',
      'prefix[cc]' => 'cc',
      'prefix[dd]' => '',
    ], 'Save configuration');

    $this->drupalGet('/admin/config/regional/language/detection/selected');
    $this->submitForm(['edit-selected-langcode' => 'dd'], 'Save configuration');

    // Set default language code for content type page to 'dd'.
    ContentLanguageSettings::loadByEntityTypeBundle('node', 'page')
      ->setDefaultLangcode('dd')
      ->setLanguageAlterable(TRUE)
      ->save();
    // Set default language code to for menu_link_content 'dd'.
    ContentLanguageSettings::loadByEntityTypeBundle('menu_link_content', 'menu_link_content')
      ->setDefaultLangcode('dd')
      ->setLanguageAlterable(TRUE)
      ->save();
    $this->config('system.feature_flags')
      ->set('linkset_endpoint', TRUE)
      ->save(TRUE);
    // Using rebuildIfNeeded here to implicitly test that router is only rebuilt
    // when necessary.
    \Drupal::service('router.builder')->rebuildIfNeeded();
    $this->drupalLogout();

    $permissions = [
      'view own unpublished content',
    ];
    $this->authorAccount = $this->setUpCurrentUser([
      'name' => 'author',
      'pass' => 'authorPass',
    ], $permissions);

    // Generate some data which we can test against.
    $home_page_link = $this->createMenuItem([
      'title' => 'Home',
      'description' => 'Links to the home page.',
      'link' => 'internal:/<front>',
      'weight' => 0,
      'menu_name' => 'main',
    ]);

    // Multilingual test.
    $multi_lingual_node = $this->createNode([
      'nid' => 1,
      'title' => 'A multi-lingual-node',
      'type' => 'page',
      'path' => '/multi-lingual-node',
    ]);
    $multi_lingual_menu_item = $this->createMenuItem([
      'title' => 'A multi-lingual-node',
      'link' => 'entity:node/' . (int) $multi_lingual_node->id(),
      'menu_name' => 'main',
      'weight' => $home_page_link->getWeight() + 1,
    ]);
    foreach (['aa', 'bb', 'cc'] as $language_code) {
      $multi_lingual_menu_item->addTranslation($language_code, [
        'title' => $language_code . '|' . 'A multi-lingual-node',
      ]);
      $multi_lingual_menu_item->save();
    }
    // Multilingual Menu item with missing language using `entity:` route.
    $multi_lingual_node = $this->createNode([
      'nid' => 2,
      'title' => 'A multi-lingual-node',
      'type' => 'page',
      'path' => '/multi-lingual-node-two',
    ]);
    $multi_lingual_menu_item = $this->createMenuItem([
      'title' => 'Second multi-lingual-node',
      'link' => 'entity:node/' . (int) $multi_lingual_node->id(),
      'menu_name' => 'main',
      'weight' => $home_page_link->getWeight() + 2,
    ]);
    foreach (['aa', 'bb'] as $language_code) {
      $multi_lingual_menu_item->addTranslation($language_code, [
        'title' => $language_code . '|' . 'Second multi-lingual-node',
      ]);
      $multi_lingual_menu_item->save();
    }
    // Multilingual Menu item with missing language using `internal` route.
    $multi_lingual_node = $this->createNode([
      'nid' => 3,
      'title' => 'A multi-lingual-node',
      'type' => 'page',
      'path' => '/multi-lingual-node-three',
    ]);
    $multi_lingual_menu_item = $this->createMenuItem([
      'title' => 'Third multi-lingual-node',
      'link' => 'internal:/node/' . (int) $multi_lingual_node->id(),
      'menu_name' => 'main',
      'weight' => $home_page_link->getWeight() + 3,
    ]);
    foreach (['aa', 'bb'] as $language_code) {
      $multi_lingual_menu_item->addTranslation($language_code, [
        'title' => $language_code . '|' . 'Third multi-lingual-node',
      ]);
      $multi_lingual_menu_item->save();
    }
    $this->httpKernel = $this->container->get('http_kernel');
  }

  /**
   * Test core functions of the linkset for multilingual behaviour.
   *
   * @throws \Exception
   */
  public function testBasicMultilingualFunctions() {
    foreach (['aa', 'bb', 'cc', 'dd'] as $language_code) {
      $expected_linkset = $this->getReferenceLinksetDataFromFile(__DIR__ . '/../../../fixtures/linkset/linkset-menu-main-multilingual-' . $language_code . '.json');
      $response = $this->doRequest('GET', Url::fromUri('base:/' . $language_code . '/system/menu/main/linkset'));
      $this->assertSame($expected_linkset, Json::decode((string) $response->getBody()));
    }
  }

  /**
   * Test core functions of the linkset for multilingual behaviour.
   *
   * @throws \Exception
   */
  public function testDefaultMultilingualFunctions() {
    $expected_linkset = $this->getReferenceLinksetDataFromFile(__DIR__ . '/../../../fixtures/linkset/linkset-menu-main-multilingual-default.json');
    $response = $this->doRequest('GET', Url::fromUri('base:/system/menu/main/linkset'));
    $this->assertSame($expected_linkset, Json::decode((string) $response->getBody()));
  }

}
