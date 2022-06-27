<?php

namespace Drupal\Tests\file\Functional;

use Drupal\Core\Language\LanguageManager;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the widget for media track files.
 *
 * @group file
 */
class FileMediaTrackWidgetTest extends FileFieldTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test media track widget element.
   */
  public function testWidgetElement() {
    /** @var \Drupal\Core\Language\LanguageManagerInterface $languageManager */
    $languageManager = $this->container->get('language_manager');

    // Add a media_track field to the article content type.
    $defaults_field_name = mb_strtolower($this->randomMachineName());
    $this->createMediaTrackField($defaults_field_name, 'node', 'article', ['cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED]);

    // Add a second field with alternate configuration for the widget's kind and
    // srclang settings.
    $alternate_config_field_name = mb_strtolower($this->randomMachineName());
    $this->createMediaTrackField(
      $alternate_config_field_name,
      'node',
      'article',
      ['cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED],
      [
        'languages' => 'all',
        'kinds' => ['chapters' => 'chapters'],
      ]
    );
    $file = $this->getTestFile('webvtt');

    $this->drupalGet('node/add/article');
    $this->assertSession()->elementExists('xpath', '//div[contains(@class, "field--type-media-track")]');

    // Check for default allowed extensions.
    $this->assertSession()->pageTextContains('Allowed types: vtt.');

    // Upload a VTT file to both fields.
    $edit = [];
    $edit['files[' . $defaults_field_name . '_0][]'] = $this->container->get('file_system')->realpath($file->getFileUri());
    $this->submitForm($edit, "{$defaults_field_name}_0_upload_button");

    $edit = [];
    $edit['files[' . $alternate_config_field_name . '_0][]'] = $this->container->get('file_system')->realpath($file->getFileUri());
    $this->submitForm($edit, "{$alternate_config_field_name}_0_upload_button");

    // Verify the additional details elements are present for the uploaded file.
    $this->assertSession()->fieldExists($defaults_field_name . '[0][label]');
    $this->assertSession()->selectExists($defaults_field_name . '[0][srclang]');
    $srclang_options = $this
      ->getSession()
      ->getPage()
      ->findAll('css', 'select[name="' . $defaults_field_name . '[0][srclang]"] option');
    $this->assertCount(count($languageManager->getLanguages()), $srclang_options);
    $this->assertSession()->optionExists($defaults_field_name . '[0][srclang]', $languageManager->getDefaultLanguage()->getName());

    $this->assertSession()->selectExists($defaults_field_name . '[0][kind]');
    $this->assertSession()->optionExists($defaults_field_name . '[0][kind]', 'Captions');
    $this->assertSession()->optionExists($defaults_field_name . '[0][kind]', 'Subtitles');
    $this->assertSession()->optionNotExists($defaults_field_name . '[0][kind]', 'Chapters');
    $this->assertSession()->fieldExists($defaults_field_name . '[0][default]');

    // Test alternative configurations for languages, and kinds options.
    $srclang_options = $this
      ->getSession()
      ->getPage()
      ->findAll('css', 'select[name="' . $alternate_config_field_name . '[0][srclang]"] option');
    $this->assertCount(count(LanguageManager::getStandardLanguageList()), $srclang_options);
    $this->assertSession()->optionExists($alternate_config_field_name . '[0][srclang]', 'English');
    $this->assertSession()->optionExists($alternate_config_field_name . '[0][srclang]', 'Lolspeak');

    $this->assertSession()->optionNotExists($alternate_config_field_name . '[0][kind]', 'Subtitles');
    $this->assertSession()->optionExists($alternate_config_field_name . '[0][kind]', 'Chapters');
  }

}
