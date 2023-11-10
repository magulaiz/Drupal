<?php

namespace Drupal\Tests\path_alias\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\language\Kernel\LanguageTestBase;
use Drupal\Tests\path_alias\Traits\PathAliasLanguageFallbackTestTrait;
use Drupal\Tests\Traits\Core\PathAliasTestTrait;

/**
 * Tests path alias language fallback functionality.
 *
 * @coversDefaultClass \Drupal\path_alias\AliasRepository
 *
 * @group path_alias
 */
class AliasLanguageFallbackTest extends LanguageTestBase {

  use PathAliasTestTrait,
    PathAliasLanguageFallbackTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'language',
    'path_alias',
    'path_alias_language_fallback_test',
  ];

  /**
   * The alias repository.
   *
   * @var \Drupal\path_alias\AliasRepositoryInterface
   */
  protected $aliasRepository;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('path_alias');

    $language = ConfigurableLanguage::createFromLangcode('af');
    $language->save();

    $this->aliasRepository = $this->container->get('path_alias.repository');
  }

  /**
   * Ensure aliases can be looked up with extended language fallbacks.
   *
   * @covers ::lookupByAlias
   * @see path_alias_language_fallback_test_language_fallback_candidates_path_alias_alter()
   * @see path_alias_language_fallback_test_query_path_alias_language_fallback_alter()
   */
  public function testLookupByAlias() {
    // Create an alias for a path in Afrikaans.
    $test_source = '/user/1';
    $test_alias = '/users/my-test-path';
    $this->createPathAlias($test_source, $test_alias, 'af');
    $this->assertNull($this->aliasRepository->lookupByAlias($test_alias, 'en'), 'No path alias is found if we specify English to the alias repository and Afrikaans is not a valid fallback candidate for English.');

    // Enable configured fallback from English to Afrikaans.
    $this->setPathAliasFallbackLanguage('af');
    $this->assertEquals($test_source, $this->aliasRepository->lookupByAlias($test_alias, 'en')['path'], 'The Afrikaans path alias is returned if we specify English to the alias repository and Afrikaans is a valid fallback candidate for English.');
    // Test that standard path lookup still works.
    $this->assertEquals($test_source, $this->aliasRepository->lookupByAlias($test_alias, 'af')['path'], 'Directly looking up the path alias in Afrikaans still works when Afrikaans is a fallback language.');

    // Create an identical alias in English, for a different source path.
    $en_source_path = '/user/2';
    $this->createPathAlias($en_source_path, $test_alias, 'en');
    $this->assertEquals($en_source_path, $this->aliasRepository->lookupByAlias($test_alias, 'en')['path'], 'The more specific English path alias is returned if we specify English to the alias repository.');

    // Check that no alias is found when none exists.
    $this->assertNull($this->aliasRepository->lookupByAlias('/this-alias-does-not-exist', 'en'), 'No alias is found when none exists.');
  }

  /**
   * Ensure looking up aliases for paths work with extended language fallbacks.
   *
   * @covers ::lookupBySystemPath
   * @see path_alias_language_fallback_test_language_fallback_candidates_path_alias_alter()
   * @see path_alias_language_fallback_test_query_path_alias_language_fallback_alter()
   */
  public function testLookupBySystemPath() {
    // Create an alias for a path in Afrikaans.
    $test_source = '/user/login';
    $test_alias = '/test-login-alias';
    $this->createPathAlias($test_source, $test_alias, 'af');
    $this->assertNull($this->aliasRepository->lookupBySystemPath($test_source, 'en'), 'No path alias is found if we specify English to the alias repository and Afrikaans is not a valid fallback candidate for English.');

    // Enable configured fallback from English to Afrikaans.
    $this->setPathAliasFallbackLanguage('af');
    $this->assertEquals($test_alias, $this->aliasRepository->lookupBySystemPath($test_source, 'en')['alias'], 'The Afrikaans path alias is returned if we specify English to the alias repository and Afrikaans is a valid fallback candidate for English.');
    // Test that standard path lookup still works.
    $this->assertEquals($test_alias, $this->aliasRepository->lookupBySystemPath($test_source, 'af')['alias'], 'Directly looking up the system path for an alias in Afrikaans still works when Afrikaans is a fallback language.');

    // Create an identical alias in English, for a different source path.
    $en_test_alias = '/test-english-login-alias';
    $this->createPathAlias($test_source, $en_test_alias, 'en');
    $this->assertEquals($en_test_alias, $this->aliasRepository->lookupBySystemPath($test_source, 'en')['alias'], 'The more specific English path alias is returned if we specify English to the alias repository.');

    // Check that no alias is found when none exists.
    $this->assertNull($this->aliasRepository->lookupBySystemPath('/this-alias-does-not-exist', 'en'), 'No alias is found when none exists.');
  }

}
