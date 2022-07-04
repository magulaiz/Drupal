<?php

namespace Drupal\KernelTests\Core\PathProcessor;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\language\Kernel\LanguageTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the path processors as a whole.
 *
 * @group Path
 */
class PathProcessorTest extends LanguageTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['path', 'language', 'user'];

  /**
   * Setup for the test.
   */
  protected function setUp() {
    parent::setUp();

    // Add a language.
    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Enable language negotiation.
    \Drupal::configFactory()
      ->getEditable('language.negotiation')
      ->set('url', [
        'source' => 'path_prefix',
        'prefixes' => [
          'en' => '',
          'fr' => 'fr',
        ],
      ])
      ->save();

    // Add language types configuration.
    \Drupal::configFactory()
      ->getEditable('language.types')
      ->set('negotiation.language_interface.enabled.language-url', 1)
      ->set('configurable', ['language_interface'])
      ->set('all', ['language_interface', 'language_content', 'language_url'])
      ->save();

    // This is required to register the Language path processor.
    // @todo Find better way.
    drupal_flush_all_caches();
  }

  /**
   * Tests whether the processor converts a path alias in different language.
   */
  public function testAliasInDifferentLanguageConverted() {
    /** @var \Drupal\Core\PathProcessor\PathProcessorManager $pathProcessor */
    $pathProcessor = \Drupal::service('path_processor_manager');

    // Test that the language path processor works.
    $langAlias = '/fr/user/login';
    $request = Request::create($langAlias);
    $processedPath = $pathProcessor->processInbound($langAlias, $request);

    $this->assertEquals('/user/login', $processedPath, 'The language prefix was removed.');

    $alias = '/some-alias';
    $path = '/some-path';

    // Create a new alias.
    \Drupal::service('path.alias_storage')->save($alias, $path, 'fr');

    // Attempt to process the alias and convert it.
    $langAlias = '/fr' . $alias;
    $request = Request::create($langAlias);
    $processedPath = $pathProcessor->processInbound($langAlias, $request);

    $this->assertEquals($path, $processedPath, 'The alias was converted to the real path.');
  }

}
