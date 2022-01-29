<?php

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

// cspell:ignore esque

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Heading
 * @group ckeditor5
 * @internal
 */
class HeadingsTest extends CKEditor5TestBase {

  /**
   * Test headings configuration.
   */
  public function testHeadingsPlugin() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->addNewTextFormat($page, $assert_session);
    $this->drupalGet('admin/config/content/formats/manage/ckeditor5');
    $this->assertHtmlEsqueFieldValueEquals('filters[filter_html][settings][allowed_html]', '<br> <p> <h2> <h3> <h4> <h5> <h6> <strong> <em>');

    $this->drupalGet('node/add');
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-heading-dropdown button'));

    $page->find('css', '.ck-heading-dropdown button')->click();

    // Get all the headings available in dropdown.
    $headings_dropdown = $page->findAll('css', '.ck-heading-dropdown li .ck-button__label');

    // Create array of available headings.
    $available_headings = [];
    foreach ($headings_dropdown as $item) {
      $available_headings[] = $item->getText();
    }

    $this->assertSame([
      'Paragraph',
      'Heading 2',
      'Heading 3',
      'Heading 4',
      'Heading 5',
      'Heading 6',
    ], $available_headings);

    $this->drupalGet('admin/config/content/formats/manage/ckeditor5');
    $this->assertTrue($page->hasUncheckedField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading1]'));
    $page->checkField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading1]');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertTrue($page->hasCheckedField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading1]'));
    $this->assertTrue($page->hasCheckedField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading2]'));
    $page->uncheckField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading2]');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertTrue($page->hasUncheckedField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading2]'));
    $this->assertTrue($page->hasCheckedField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading4]'));
    $page->uncheckField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading4]');
    $assert_session->assertWaitOnAjaxRequest();
    $this->assertTrue($page->hasUncheckedField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading4]'));
    $this->assertHtmlEsqueFieldValueEquals('filters[filter_html][settings][allowed_html]', '<br> <p> <h1> <h3> <h5> <h6> <strong> <em>');
    $this->assertTrue($page->hasUncheckedField('editor[settings][plugins][ckeditor5_heading][enabled_headings][heading4]'));

    $page->pressButton('Save configuration');

    $this->drupalGet('node/add');
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ck-heading-dropdown button'));

    $page->find('css', '.ck-heading-dropdown button')->click();

    // Get all the headings available in dropdown.
    $headings_dropdown = $page->findAll('css', '.ck-heading-dropdown li .ck-button__label');

    // Create array of available headings.
    $available_headings = [];
    foreach ($headings_dropdown as $item) {
      $available_headings[] = $item->getText();
    }

    $this->assertSame([
      'Paragraph',
      'Heading 1',
      'Heading 3',
      'Heading 5',
      'Heading 6',
    ], $available_headings);
  }

}
