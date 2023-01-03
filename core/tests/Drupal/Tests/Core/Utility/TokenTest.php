<?php

namespace Drupal\Tests\Core\Utility;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;
use Drupal\Core\Utility\Token;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Utility\Token
 * @group Utility
 */
class TokenTest extends UnitTestCase {

  /**
   * The cache used for testing.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cache;

  /**
   * The language manager used for testing.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The module handler service used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The language interface used for testing.
   *
   * @var \Drupal\Core\Language\LanguageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $language;

  /**
   * The token service under test.
   *
   * @var \Drupal\Core\Utility\Token|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $token;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheTagsInvalidator;

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\Context\CacheContextsManager
   */
  protected $cacheContextManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cache = $this->createMock('\Drupal\Core\Cache\CacheBackendInterface');

    $this->languageManager = $this->createMock('Drupal\Core\Language\LanguageManagerInterface');

    $this->moduleHandler = $this->createMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->language = $this->createMock('\Drupal\Core\Language\LanguageInterface');

    $this->cacheTagsInvalidator = $this->createMock('\Drupal\Core\Cache\CacheTagsInvalidatorInterface');

    $this->renderer = $this->createMock('Drupal\Core\Render\RendererInterface');

    $this->token = new Token($this->moduleHandler, $this->cache, $this->languageManager, $this->cacheTagsInvalidator, $this->renderer);

    $container = new ContainerBuilder();
    $this->cacheContextManager = new CacheContextsManager($container, [
      'current_user',
      'custom_context',
    ]);
    $container->set('cache_contexts_manager', $this->cacheContextManager);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::getInfo
   */
  public function testGetInfo() {
    $token_info = [
      'types' => [
        'foo' => [
          'name' => $this->randomMachineName(),
        ],
      ],
    ];

    $this->language->expects($this->atLeastOnce())
      ->method('getId')
      ->willReturn($this->randomMachineName());

    $this->languageManager->expects($this->once())
      ->method('getCurrentLanguage')
      ->with(LanguageInterface::TYPE_CONTENT)
      ->willReturn($this->language);

    // The persistent cache must only be hit once, after which the info is
    // cached statically.
    $this->cache->expects($this->once())
      ->method('get');
    $this->cache->expects($this->once())
      ->method('set')
      ->with('token_info:' . $this->language->getId(), $token_info);

    $this->moduleHandler->expects($this->once())
      ->method('invokeAll')
      ->with('token_info')
      ->willReturn($token_info);
    $this->moduleHandler->expects($this->once())
      ->method('alter')
      ->with('token_info', $token_info);

    // Get the information for the first time. The cache should be checked, the
    // hooks invoked, and the info should be set to the cache should.
    $this->token->getInfo();
    // Get the information for the second time. The data must be returned from
    // the static cache, so the persistent cache must not be accessed and the
    // hooks must not be invoked.
    $this->token->getInfo();
  }

  /**
   * @covers ::replace
   */
  public function testReplaceWithAliasedTokens() {

    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->will($this->onConsecutiveCalls(
        ['[node:title]' => 'Giraffe'],
        ['[node:title]' => 'Elephant']
      ));

    $bubbleable_metadata = new BubbleableMetadata();

    $firstNode = $this->prophesize('Drupal\node\NodeInterface');
    $firstNode->getCacheTags()->willReturn(['node:1']);
    $firstNode->getCacheContexts()->willReturn([]);
    $firstNode->getCacheMaxAge()->willReturn(10);
    $firstNode = $firstNode->reveal();

    $secondNode = $this->prophesize('Drupal\node\NodeInterface');
    $secondNode->getCacheTags()->willReturn(['node:2']);
    $secondNode->getCacheContexts()->willReturn([]);
    $secondNode->getCacheMaxAge()->willReturn(10);
    $secondNode = $secondNode->reveal();

    $result = $this->token->replace('[node{firstNode}:title] and [node{secondNode}:title]', ['node{firstNode}' => $firstNode, 'node{secondNode}' => $secondNode], [], $bubbleable_metadata);
    $this->assertEquals('Giraffe and Elephant', $result);
  }

  /**
   * @covers ::replace
   */
  public function testReplaceWithMultipleAliasedTokens() {

    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->will($this->onConsecutiveCalls(
        ['[node:oneField]' => 'filled', '[node:anotherField]' => 'red'],
        ['[node:oneField]' => 'unfilled', '[node:anotherField]' => 'brown'],
        ['[term:termField]' => 'square'],
        ['[term:termField]' => 'circle']
      ));

    $bubbleable_metadata = new BubbleableMetadata();

    $firstNode = $this->prophesize('Drupal\node\NodeInterface');
    $firstNode->getCacheTags()->willReturn(['node:1']);
    $firstNode->getCacheContexts()->willReturn([]);
    $firstNode->getCacheMaxAge()->willReturn(10);
    $firstNode = $firstNode->reveal();

    $secondNode = $this->prophesize('Drupal\node\NodeInterface');
    $secondNode->getCacheTags()->willReturn(['node:2']);
    $secondNode->getCacheContexts()->willReturn([]);
    $secondNode->getCacheMaxAge()->willReturn(10);
    $secondNode = $secondNode->reveal();

    $firstTerm = $this->prophesize('Drupal\taxonomy\TermInterface');
    $firstTerm->getCacheTags()->willReturn(['term:1']);
    $firstTerm->getCacheContexts()->willReturn([]);
    $firstTerm->getCacheMaxAge()->willReturn(10);

    $secondTerm = $this->prophesize('Drupal\taxonomy\TermInterface');
    $secondTerm->getCacheTags()->willReturn(['term:2']);
    $secondTerm->getCacheContexts()->willReturn([]);
    $secondTerm->getCacheMaxAge()->willReturn(10);

    $text_to_replace = "[node{firstNode}:oneField] [node{firstNode}:anotherField] [term{firstTerm}:termField] and [node{secondNode}:oneField] [node{secondNode}:anotherField] [term{secondTerm}:termField]";
    $token_data = [
      'node{firstNode}' => $firstNode,
      'node{secondNode}' => $secondNode,
      'term{firstTerm}' => $firstTerm,
      'term{secondTerm}' => $secondTerm,
    ];

    $result = $this->token->replace($text_to_replace, $token_data, [], $bubbleable_metadata);
    $this->assertEquals('filled red square and unfilled brown circle', $result);
  }

  /**
   * @covers ::replace
   */
  public function testReplaceMixingAliasedAndUnaliasedTokens() {

    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->will($this->onConsecutiveCalls(
        ['[term:termField]' => 'square'],
        ['[node:oneField]' => 'filled', '[node:anotherField]' => 'red'],
        ['[node:oneField]' => 'unfilled', '[node:anotherField]' => 'brown']
      ));

    $bubbleable_metadata = new BubbleableMetadata();

    $firstNode = $this->prophesize('Drupal\node\NodeInterface');
    $firstNode->getCacheTags()->willReturn(['node:1']);
    $firstNode->getCacheContexts()->willReturn([]);
    $firstNode->getCacheMaxAge()->willReturn(10);
    $firstNode = $firstNode->reveal();

    $secondNode = $this->prophesize('Drupal\node\NodeInterface');
    $secondNode->getCacheTags()->willReturn(['node:2']);
    $secondNode->getCacheContexts()->willReturn([]);
    $secondNode->getCacheMaxAge()->willReturn(10);
    $secondNode = $secondNode->reveal();

    $term = $this->prophesize('Drupal\taxonomy\TermInterface');
    $term->getCacheTags()->willReturn(['term:1']);
    $term->getCacheContexts()->willReturn([]);
    $term->getCacheMaxAge()->willReturn(10);

    $text_to_replace = "[node{firstNode}:oneField] [node{firstNode}:anotherField] [term:termField] and [node{secondNode}:oneField] [node{secondNode}:anotherField] triangle";
    $token_data = [
      'node{firstNode}' => $firstNode,
      'node{secondNode}' => $secondNode,
      'term' => $term,
    ];

    $result = $this->token->replace($text_to_replace, $token_data, [], $bubbleable_metadata);
    $this->assertEquals('filled red square and unfilled brown triangle', $result);
  }

  /**
   *
   * @covers ::replace
   */
  public function testReplaceWithBubbleableMetadataObject() {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn(['[node:title]' => 'hello world']);

    $bubbleable_metadata = new BubbleableMetadata();
    $bubbleable_metadata->setCacheContexts(['current_user']);
    $bubbleable_metadata->setCacheMaxAge(12);

    $node = $this->prophesize('Drupal\node\NodeInterface');
    $node->getCacheTags()->willReturn(['node:1']);
    $node->getCacheContexts()->willReturn(['custom_context']);
    $node->getCacheMaxAge()->willReturn(10);
    $node = $node->reveal();

    $result = $this->token->replace('[node:title]', ['node' => $node], [], $bubbleable_metadata);
    $this->assertEquals('hello world', $result);

    $this->assertEquals(['node:1'], $bubbleable_metadata->getCacheTags());
    $this->assertEquals([
      'current_user',
      'custom_context',
    ], $bubbleable_metadata->getCacheContexts());
    $this->assertEquals(10, $bubbleable_metadata->getCacheMaxAge());
  }

  /**
   * @covers ::replace
   */
  public function testReplaceWithHookTokensWithBubbleableMetadata() {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturnCallback(function ($hook_name, $args) {
        $cacheable_metadata = $args[4];
        $cacheable_metadata->addCacheContexts(['custom_context']);
        $cacheable_metadata->addCacheTags(['node:1']);
        $cacheable_metadata->setCacheMaxAge(10);

        return ['[node:title]' => 'hello world'];
      });

    $node = $this->prophesize('Drupal\node\NodeInterface');
    $node->getCacheContexts()->willReturn([]);
    $node->getCacheTags()->willReturn([]);
    $node->getCacheMaxAge()->willReturn(14);
    $node = $node->reveal();

    $bubbleable_metadata = new BubbleableMetadata();
    $bubbleable_metadata->setCacheContexts(['current_user']);
    $bubbleable_metadata->setCacheMaxAge(12);

    $result = $this->token->replace('[node:title]', ['node' => $node], [], $bubbleable_metadata);
    $this->assertEquals('hello world', $result);
    $this->assertEquals(['node:1'], $bubbleable_metadata->getCacheTags());
    $this->assertEquals([
      'current_user',
      'custom_context',
    ], $bubbleable_metadata->getCacheContexts());
    $this->assertEquals(10, $bubbleable_metadata->getCacheMaxAge());
  }

  /**
   * @covers ::replace
   */
  public function testReplaceWithHookTokensAlterWithBubbleableMetadata() {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn([]);

    $this->moduleHandler->expects($this->any())
      ->method('alter')
      ->willReturnCallback(function ($hook_name, array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {
        $replacements['[node:title]'] = 'hello world';
        $bubbleable_metadata->addCacheContexts(['custom_context']);
        $bubbleable_metadata->addCacheTags(['node:1']);
        $bubbleable_metadata->setCacheMaxAge(10);
      });

    $node = $this->prophesize('Drupal\node\NodeInterface');
    $node->getCacheContexts()->willReturn([]);
    $node->getCacheTags()->willReturn([]);
    $node->getCacheMaxAge()->willReturn(14);
    $node = $node->reveal();

    $bubbleable_metadata = new BubbleableMetadata();
    $bubbleable_metadata->setCacheContexts(['current_user']);
    $bubbleable_metadata->setCacheMaxAge(12);

    $result = $this->token->replace('[node:title]', ['node' => $node], [], $bubbleable_metadata);
    $this->assertEquals('hello world', $result);
    $this->assertEquals(['node:1'], $bubbleable_metadata->getCacheTags());
    $this->assertEquals([
      'current_user',
      'custom_context',
    ], $bubbleable_metadata->getCacheContexts());
    $this->assertEquals(10, $bubbleable_metadata->getCacheMaxAge());
  }

  /**
   * @covers ::resetInfo
   */
  public function testResetInfo() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')
      ->with(['token_info']);

    $this->token->resetInfo();
  }

  /**
   * @covers ::replace
   * @dataProvider providerTestReplaceEscaping
   */
  public function testReplaceEscaping($string, array $tokens, $expected) {
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturnCallback(function ($type, $args) {
        return $args[2];
      });

    $result = $this->token->replace($string, $tokens);
    $this->assertIsString($result);

    $this->assertEquals($expected, $result);
  }

  /**
   * Provides Test Data for replace.
   */
  public function providerTestReplaceEscaping() {
    $data = [];

    // No tokens. The first argument to Token::replace() should not be escaped.
    $data['no-tokens'] = ['muh', [], 'muh'];
    $data['html-in-string'] = ['<h1>Giraffe</h1>', [], '<h1>Giraffe</h1>'];

    $data['html-in-string-quote'] = ['<h1>Giraffe"</h1>', [], '<h1>Giraffe"</h1>'];

    $data['simple-placeholder-with-plain-text'] = ['<h1>[node:nodeField]</h1>', ['[node:nodeField]' => 'Giraffe"'], '<h1>' . Html::escape('Giraffe"') . '</h1>'];

    $data['simple-placeholder-with-plain-text'] = ['<h1>[node:nodeField] - [node:nodeField2]</h1>', ['[node:nodeField]' => 'Giraffe"', '[node:nodeField2]' => 'Giraffe2'], '<h1>' . Html::escape('Giraffe" - Giraffe2') . '</h1>'];

    $data['simple-placeholder-with-safe-html'] = [
      '<h1>[token:meh]</h1>',
      ['[token:meh]' => Markup::create('<em>Emphasized</em>')],
      '<h1><em>Emphasized</em></h1>',
    ];

    return $data;
  }

  /**
   * @covers ::replacePlain
   */
  public function testReplacePlain() {
    $this->setupSiteTokens();
    $base = 'Wow, great "[site:name]" has a slogan "[site:slogan]"';
    $plain = $this->token->replacePlain($base);
    $this->assertEquals($plain, 'Wow, great "Your <best> buys" has a slogan "We are best"');
  }

  /**
   * Sets up the token library to return site tokens.
   */
  protected function setupSiteTokens() {
    // The site name is plain text, but the slogan is markup.
    $tokens = [
      '[site:name]' => 'Your <best> buys',
      '[site:slogan]' => Markup::Create('We are <b>best</b>'),
    ];

    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn($tokens);
  }

}
