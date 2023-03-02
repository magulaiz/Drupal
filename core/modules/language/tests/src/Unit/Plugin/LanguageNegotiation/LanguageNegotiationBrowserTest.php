<?php

namespace Drupal\Tests\language\Unit\Plugin\LanguageNegotiation;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Session\AccountInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ServerBag;

/**
 * Tests the LanguageNegotiationBrowser plugin class.
 *
 * @group language
 * @coversDefaultClass \Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationBrowser
 * @see \Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationBrowser
 */
class LanguageNegotiationBrowserTest extends LanguageNegotiationTestBase {

  /**
   * A mock LanguageManager object.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * A mock object implementing the AccountInterface.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function getPluginClass(): string {
    return LanguageNegotiationBrowser::class;
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    // Set up some languages to be used by the language-based path processor.
    $language_de = $this->createMock(LanguageInterface::class);
    $language_de->expects($this->any())
      ->method('getId')
      ->will($this->returnValue('de'));
    $language_en = $this->createMock(LanguageInterface::class);
    $language_en->expects($this->any())
      ->method('getId')
      ->will($this->returnValue('en'));
    $languages = [
      'de' => $language_de,
      'en' => $language_en,
    ];

    $language_manager = $this->getMockBuilder(ConfigurableLanguageManagerInterface::class)
      ->getMock();
    $language_manager->expects($this->any())
      ->method('getLanguages')
      ->will($this->returnValue($languages));
    $this->languageManager = $language_manager;

    $this->user = $this->getMockBuilder(AccountInterface::class)
      ->getMock();

    $cache_contexts_manager = $this->getMockBuilder(CacheContextsManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $cache_contexts_manager->method('assertValidTokens')->willReturn(TRUE);
    $kill_switch = $this->getMockBuilder(KillSwitch::class)->getMock();
    $kill_switch->expects($this->any())->method('trigger');
    $container = new ContainerBuilder();
    $container->set('cache_contexts_manager', $cache_contexts_manager);
    $container->set('page_cache_kill_switch', $kill_switch);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::getLangcode
   */
  public function testGetLangcode() {
    $languageNegotiationBrowser = $this->createLanguageNegotiationPlugin();

    // Case 1: LanguageManager not available.
    $this->assertEquals(NULL, $languageNegotiationBrowser->getLangcode());

    // Case 2: LanguageManager available, but no request given.
    $languageNegotiationBrowser->setLanguageManager($this->languageManager);
    $this->assertEquals(NULL, $languageNegotiationBrowser->getLangcode());

    // Case 3: LanguageManager available, a request is given, but
    // HTTP_ACCEPT_LANGUAGE is not provided.
    $request = Request::create('/de/foo', 'GET');
    $request->server = new ServerBag();
    $this->assertEquals(NULL, $languageNegotiationBrowser->getLangcode($request));

    // Case 4: LanguageManager available, a request with HTTP_ACCEPT_LANGUAGE is
    // provided, with a given langcode that is one of the system supported
    // languages.
    $expectedLangcode = 'de';
    $request->server->set('HTTP_ACCEPT_LANGUAGE', $expectedLangcode);
    $config = $this->getConfigFactoryStub(['language.mappings' => ['map' => []]]);
    $languageNegotiationBrowser->setConfig($config);
    $this->assertEquals($expectedLangcode, $languageNegotiationBrowser->getLangcode($request));

    // Case 5: LanguageManager available, a request with HTTP_ACCEPT_LANGUAGE is
    // provided, with an unknown langcode.
    $unknownLangcode = 'xx';
    $request->server->set('HTTP_ACCEPT_LANGUAGE', $unknownLangcode);
    $this->assertFalse($languageNegotiationBrowser->getLangcode($request));
  }

}
