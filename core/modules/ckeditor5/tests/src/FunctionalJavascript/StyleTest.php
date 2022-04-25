<?php

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Style
 * @group ckeditor5
 * @internal
 */
class StyleTest extends CKEditor5TestBase {

  /**
   * @covers \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Style::buildConfigurationForm
   */
  public function testStyleSettingsForm() {
    $this->drupalLogin($this->drupalCreateUser(['administer filters']));

    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->createNewTextFormat($page, $assert_session);
    $assert_session->assertWaitOnAjaxRequest();

    // The Style plugin settings form should not be present.
    $assert_session->elementNotExists('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-style"]');

    $this->assertNotEmpty($assert_session->waitForElement('css', '.ckeditor5-toolbar-item-style'));
    $this->triggerKeyUp('.ckeditor5-toolbar-item-style', 'ArrowDown');
    $assert_session->assertWaitOnAjaxRequest();

    // The Style plugin settings form should now be present and should have no
    // styles configured.
    $page->clickLink('Style');
    $this->assertNotNull($assert_session->waitForElementVisible('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-style-styles"]'));

    $javascript = <<<JS
      const allowedTags = document.querySelector('[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-style-styles"]');
      allowedTags.value = 'p.foo.bar  | Foobar paragraph';
      allowedTags.dispatchEvent(new Event('input'));
JS;
    $this->getSession()->executeScript($javascript);

    // Immediately save the configuration. Intentionally do nothing that would
    // trigger an AJAX rebuild.
    $page->pressButton('Save configuration');

    // Verify that the configuration was saved.
    $this->drupalGet('admin/config/content/formats/manage/ckeditor5');
    $page->clickLink('Style');
    $this->assertNotNull($styles_textarea = $assert_session->waitForElementVisible('css', '[data-drupal-selector="edit-editor-settings-plugins-ckeditor5-style-styles"]'));

    $this->assertSame("p.foo.bar|Foobar paragraph\n", $styles_textarea->getValue());
    $allowed_html_field = $assert_session->fieldExists('filters[filter_html][settings][allowed_html]');
    $this->assertStringContainsString('<p class="foo bar">', $allowed_html_field->getValue());
  }

}
