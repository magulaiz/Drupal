<?php

namespace Drupal\Tests\media_library\Functional;

use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\media\Entity\Media;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests the media library widget in a multilingual context.
 *
 * @group media_library
 */
class MediaLibraryWidgetTranslationTest extends BrowserTestBase {

  use EntityReferenceTestTrait;
  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_translation',
    'media_library',
    'media_test_source',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that validation is bypassed for hidden, untranslated media fields.
   */
  public function testValidateUntranslatedHiddenMediaField(): void {
    // Create a test media type and media item.
    $media_type = $this->createMediaType('test');
    $media = Media::create([
      'bundle' => $media_type->id(),
      'field_media_test' => $this->randomString(),
    ]);
    $media->save();

    // Create a test content type with a media field.
    $node_type = $this->drupalCreateContentType()->id();
    $this->createEntityReferenceField('node', $node_type, 'field_media', 'Media', 'media');
    $this->container->get('entity_display.repository')
      ->getFormDisplay('node', $node_type)
      ->setComponent('field_media', [
        'type' => 'media_library_widget',
      ])
      ->save();

    // Add a second language to the site and enable it for the test content
    // type, ensuring that untranslatable fields are hidden on the content
    // type's translation form.
    ConfigurableLanguage::createFromLangcode('fr')->save();
    ContentLanguageSettings::loadByEntityTypeBundle('node', $node_type)
      ->setThirdPartySetting('content_translation', 'enabled', TRUE)
      ->setThirdPartySetting('content_translation', 'bundle_settings', [
        'untranslatable_fields_hide' => TRUE,
      ])
      ->save();

    // Make the media field required, but untranslatable.
    /** @var \Drupal\field\Entity\FieldConfig $field */
    $field = FieldConfig::loadByName('node', $node_type, 'field_media');
    $field->setRequired(TRUE)->setTranslatable(FALSE)->save();

    // Create a test node in the default language, with the required media field
    // referencing our test media item.
    $account = $this->drupalCreateUser([
      "edit own $node_type content",
      'translate editable entities',
    ]);
    $this->drupalLogin($account);

    $node = $this->drupalCreateNode([
      'type' => $node_type,
      'field_media' => $media->id(),
    ]);

    // Ensure the media field is visible in the default translation.
    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session = $this->assertSession();
    $assert_session->statusCodeEquals(200);
    $assert_session->buttonExists('Add media');

    // Visit the form to add a translation of the node.
    $url = Url::fromRoute('entity.node.content_translation_add', [
      'source' => $node->language()->getId(),
      'target' => 'fr',
      'node' => $node->id(),
    ]);
    $this->drupalGet($url);

    // We've hidden untranslatable fields, so we should not see the media field.
    $assert_session->statusCodeEquals(200);
    $assert_session->buttonNotExists('Add media');

    // We should see a warning about hidden, untranslatable fields.
    $assert_session->pageTextContains('Fields that apply to all languages are hidden to avoid conflicting changes.');

    // We should be able to save the translation successfully, even though we
    // haven't made any changes to the media field.
    $this->getSession()->getPage()->pressButton('Save (this translation)');
    $assert_session->pageTextNotContains('Media field is required.');
  }

}
