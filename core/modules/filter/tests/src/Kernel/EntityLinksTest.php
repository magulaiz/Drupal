<?php

declare(strict_types=1);

namespace Drupal\Tests\filter\Kernel;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Language\LanguageInterface;
use Drupal\entity_test\Entity\EntityTestMul;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\filter\FilterPluginCollection;
use Drupal\filter\FilterProcessResult;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\Traits\Core\PathAliasTestTrait;

/**
 * @coversDefaultClass \Drupal\filter\Plugin\Filter\EntityLinks
 * @group ckeditor5
 * @todo ⚠️ CHANGE THE GROUP TO "FILTER" ONCE WE RUN THE ENTIRE CORE TEST SUITE ⚠️
 */
class EntityLinksTest extends KernelTestBase {

  use PathAliasTestTrait;

  /**
   * The entity_links filter.
   *
   * @var \Drupal\filter\Plugin\Filter\EntityLinks
   */
  protected $filter;

  /**
   * The test logger.
   *
   * @var \ColinODell\PsrTestLogger\TestLogger
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'entity_test',
    'path',
    'path_alias',
    'language',
    'file',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('entity_test_mul');
    $this->installEntitySchema('file');
    $this->installEntitySchema('path_alias');

    // Add Swedish, Danish and Finnish.
    ConfigurableLanguage::createFromLangcode('sv')->save();
    ConfigurableLanguage::createFromLangcode('da')->save();
    ConfigurableLanguage::createFromLangcode('fi')->save();

    /** @var \Drupal\Component\Plugin\PluginManagerInterface $manager */
    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filter = $bag->get('entity_links');

    // Add test logger to the 'filter' channel to assert no exceptions occurred.
    $this->logger = new TestLogger();
    $this->container->get('logger.factory')
      ->get('filter')
      ->addLogger($this->logger);
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    // Undo what the parent did, to allow testing path aliases in kernel tests.
    $container->getDefinition('path_alias.path_processor')
      ->addTag('path_processor_inbound')
      ->addTag('path_processor_outbound');
  }

  /**
   * @covers ::process
   */
  public function test(): void {
    $expected_aliases = [
      'da' => '/foo-da',
      'en' => '/foo-en',
      'fi' => '/foo-fi',
      'sv' => '/foo-sv',
    ];
    $expected_hrefs = $expected_aliases + [
      LanguageInterface::LANGCODE_DEFAULT => '/foo-en',
      // @todo The following 3 arguably should fall back to "/foo-en" as well, but don't. This is a known bug in EntityRepository::getTranslationFromContext(): https://www.drupal.org/project/drupal/issues/3061761
      LanguageInterface::LANGCODE_NOT_APPLICABLE => '/foo-da',
      LanguageInterface::LANGCODE_NOT_SPECIFIED => '/foo-da',
      LanguageInterface::LANGCODE_SITE_DEFAULT => '/foo-da',
    ];

    // Create an entity and add translations to that.
    /** @var \Drupal\entity_test\Entity\EntityTestMul $entity */
    $entity = EntityTestMul::create(['name' => $this->randomMachineName()]);
    $entity->addTranslation('sv', ['name' => $this->randomMachineName(), 'langcode' => 'sv']);
    $entity->addTranslation('da', ['name' => $this->randomMachineName(), 'langcode' => 'da']);
    $entity->addTranslation('fi', ['name' => $this->randomMachineName(), 'langcode' => 'fi']);
    $entity->save();

    // Assert the entity has a translation for every expected language.
    $this->assertSame(array_keys($expected_aliases), array_keys($entity->getTranslationLanguages()));

    // Create per-translation URL aliases.
    $canonical_url = $entity->toUrl()->toString(TRUE)->getGeneratedUrl();
    foreach ($expected_aliases as $langcode => $alias) {
      $this->createPathAlias($canonical_url, $alias, $langcode);
    }

    foreach ($expected_hrefs as $langcode => $expected_alias) {
      $expected_result = (new FilterProcessResult())
        ->setProcessedText('<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '" href="' . $expected_alias . '">Link text</a>')
        ->setCacheTags(['entity_test_mul:1'])
        ->setCacheContexts([])
        ->setCacheMaxAge(Cache::PERMANENT);

      // The expected href is generated.
      $this->assertFilterProcessResult(
        '<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '">Link text</a>',
        $langcode,
        $expected_result
      );
      // The existing href is overwritten with the expected value.
      $this->assertFilterProcessResult(
        '<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '" href="something">Link text</a>',
        $langcode,
        $expected_result
      );

      // The existing href is overwritten, but its customized query string and
      // fragment remain unchanged.
      $this->assertFilterProcessResult(
        '<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '" href="something?query=string#fragment">Link text</a>',
        $langcode,
        (new FilterProcessResult())
          ->setProcessedText('<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '" href="' . $expected_alias . '?query=string#fragment">Link text</a>')
          ->setCacheTags(['entity_test_mul:1'])
          ->setCacheContexts([])
          ->setCacheMaxAge(Cache::PERMANENT)
      );
    }
  }

  /**
   * @covers ::getUrl
   */
  public function testFileEntity(): void {
    $file = File::create([
      'uid' => 1,
      'filename' => 'druplicon.txt',
      'uri' => 'public://druplicon.txt',
      'filemime' => 'text/plain',
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    $file->save();

    $this->assertFilterProcessResult(
      '<a data-entity-type="file" data-entity-uuid="' . $file->uuid() . '" href="something?query=string#fragment">Link text</a>',
      'en',
      (new FilterProcessResult())
        ->setProcessedText(sprintf('<a data-entity-type="file" data-entity-uuid="%s" href="%s?query=string#fragment">Link text</a>', $file->uuid(), $file->createFileUrl(TRUE)))
        ->setCacheTags(['file:1'])
        ->setCacheContexts([])
        ->setCacheMaxAge(Cache::PERMANENT)
    );
  }

  /**
   * Asserts an input string + langcode yield the expected FilterProcessResult.
   *
   * @param string $input
   *   The text string to be filtered.
   * @param string $langcode
   *   The language code of the text to be filtered.
   * @param \Drupal\filter\FilterProcessResult $expected_result
   *   The expected filtered result.a
   */
  private function assertFilterProcessResult(string $input, string $langcode, FilterProcessResult $expected_result): void {
    $result = $this->filter->process($input, $langcode);
    // No exceptions should have occurred.
    $this->assertSame([], $this->logger->records);
    // Assert both the processed text and the associated cacheability.
    $this->assertSame($expected_result->getProcessedText(), $result->getProcessedText());
    $this->assertEquals(CacheableMetadata::createFromObject($expected_result), CacheableMetadata::createFromObject($result));
  }

}
