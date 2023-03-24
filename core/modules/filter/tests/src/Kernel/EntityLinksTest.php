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
   * Tests the entity_links filter for entities with translations.
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
    $expected_template = '<a data-entity-type="entity_test_mul" data-entity-uuid="%s" href="%s">Link text</a>';
    $expected_cacheability = (new CacheableMetadata())
      ->setCacheTags(['entity_test_mul:1'])
      ->setCacheContexts([])
      ->setCacheMaxAge(Cache::PERMANENT);

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

    // Test the cases of input without href or with a typical href (only path).
    foreach ($expected_hrefs as $langcode => $expected_alias) {
      $expected = sprintf($expected_template, $entity->uuid(), $expected_alias);

      // The expected href is generated.
      $processed = $this->filter->process(
        '<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '">Link text</a>',
        $langcode
      );
      $this->assertSame([], $this->logger->records);
      $this->assertSame($expected, $processed->getProcessedText(), $langcode);
      $this->assertEquals($expected_cacheability, CacheableMetadata::createFromObject($processed));

      // The existing href is overwritten with the expected value.
      $processed = $this->filter->process(
        '<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '" href="something">Link text</a>',
        $langcode
      );
      $this->assertSame([], $this->logger->records);
      $this->assertSame($expected, $processed->getProcessedText());
      $this->assertEquals($expected_cacheability, CacheableMetadata::createFromObject($processed));
    }

    // Test the cases of input with a customized href: query string + fragment.
    foreach ($expected_hrefs as $langcode => $expected_alias) {
      $expected = sprintf($expected_template, $entity->uuid(), $expected_alias . '?query=string#fragment');

      // The existing href is overwritten with the expected value, but query
      // string and fragment are retained.
      $processed = $this->filter->process(
        '<a data-entity-type="entity_test_mul" data-entity-uuid="' . $entity->uuid() . '" href="something?query=string#fragment">Link text</a>',
        $langcode,
      );
      $this->assertSame([], $this->logger->records);
      $this->assertSame($expected, $processed->getProcessedText());
      $this->assertEquals($expected_cacheability, CacheableMetadata::createFromObject($processed));
    }
  }

  /**
   * Tests the entity_links filter for file entities.
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

    $processed = $this->filter->process(
      '<a data-entity-type="file" data-entity-uuid="' . $file->uuid() . '" href="something?query=string#fragment">Link text</a>',
      'en',
    );
    $this->assertSame([], $this->logger->records);
    $this->assertSame(
      sprintf('<a data-entity-type="file" data-entity-uuid="%s" href="%s?query=string#fragment">Link text</a>', $file->uuid(), $file->createFileUrl(TRUE)),
      $processed->getProcessedText(),
    );
    $this->assertEquals(
      (new CacheableMetadata())
        ->setCacheTags(['file:1'])
        ->setCacheContexts([])
        ->setCacheMaxAge(Cache::PERMANENT),
      CacheableMetadata::createFromObject($processed)
    );
  }

}
