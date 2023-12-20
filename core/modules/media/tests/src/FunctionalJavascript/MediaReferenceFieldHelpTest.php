<?php

declare(strict_types=1);

namespace Drupal\Tests\media\FunctionalJavascript;

/**
 * Tests related to media reference fields.
 *
 * @group media
 */
class MediaReferenceFieldHelpTest extends MediaJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'media',
    'media_library',
  ];

  /**
   * Tests our custom help texts when creating a field.
   *
   * @see media_form_field_ui_field_storage_add_form_alter()
   */
  public function testFieldCreationHelpText() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $type = $this->drupalCreateContentType([
      'type' => 'foo',
    ]);
    $this->drupalGet("/admin/structure/types/manage/{$type->id()}/fields/add-field");

    $field_groups = [
      'File upload',
      'Media',
    ];

    $help_text = 'Use Media reference fields for most files, images, audio, videos, and remote media. Use File or Image reference fields when creating your own media types, or for legacy files and images created before installing the Media module.';

    // Choose a boolean field, none of the description containers should be
    // visible.
    $page->clickLink('Boolean');
    $assert_session->pageTextNotContains($help_text);
    $page->pressButton('Back');

    // Select each of the Reference, File upload field groups and verify their
    // descriptions are now visible and match the expected text.
    foreach ($field_groups as $field_group) {
      $this->clickLink($field_group);
      $assert_session->pageTextContains($help_text);
      $page->pressButton('Back');
    }
  }

}
