<?php

namespace Drupal\Tests\language\Unit\Plugin\LanguageNegotiation;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationContentEntity;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Tests the LanguageNegotiationContentEntity plugin class.
 *
 * @group language
 * @coversDefaultClass \Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationContentEntity
 * @see \Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationContentEntity
 */
class LanguageNegotiationContentEntityTest extends UnitTestCase {

  use LanguageNegotiationFactoryTrait;

  /**
   * The language negotiation method plugin class.
   */
  const PLUGIN_CLASS = LanguageNegotiationContentEntity::class;

  /**
   * A mock LanguageManager object.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

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

    $container = new ContainerBuilder();

    $cache_contexts_manager = $this->getMockBuilder(CacheContextsManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $cache_contexts_manager->method('assertValidTokens')->willReturn(TRUE);
    $container->set('cache_contexts_manager', $cache_contexts_manager);

    $entityTypeManager = $this->getMockBuilder(EntityTypeManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $container->set('entity_type.manager', $entityTypeManager);

    \Drupal::setContainer($container);
  }

  /**
   * @covers ::getLangcode
   */
  public function testGetLangcode() {

    $languageNegotiationContentEntity = $this->createLanguageNegotiationPlugin();

    // Case 1: Empty request.
    $this->assertEquals(NULL, $languageNegotiationContentEntity->getLangcode());

    // Case 2: A request is available, but the languageManager is not set and
    // the static::QUERY_PARAMETER is not provided as a named parameter.
    $request = Request::create('/de/foo', 'GET');
    $request->query = new ParameterBag();
    $this->assertEquals(NULL, $languageNegotiationContentEntity->getLangcode($request));

    // Case 3: A request is available, the languageManager is set, but the
    // static::QUERY_PARAMETER is not provided as a named parameter.
    $languageNegotiationContentEntity->setLanguageManager($this->languageManager);
    $this->assertEquals(NULL, $languageNegotiationContentEntity->getLangcode($request));

    // Case 4: A request is available, the languageManager is set and the
    // static::QUERY_PARAMETER is provided as a named parameter.
    $expectedLangcode = 'de';
    $request->query->set(LanguageNegotiationContentEntity::QUERY_PARAMETER, $expectedLangcode);
    $this->assertEquals($expectedLangcode, $languageNegotiationContentEntity->getLangcode($request));

    // Case 5: A request is available, the languageManager is set and the
    // static::QUERY_PARAMETER is provided as a named parameter with a given
    // langcode that is not one of the system supported ones.
    $unknownLangcode = 'xx';
    $request->query->set(LanguageNegotiationContentEntity::QUERY_PARAMETER, $unknownLangcode);
    $this->assertNull($languageNegotiationContentEntity->getLangcode($request));
  }

}
