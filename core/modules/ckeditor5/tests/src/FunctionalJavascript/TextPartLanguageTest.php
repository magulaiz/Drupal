<?php

namespace Drupal\Tests\ckeditor5\FunctionalJavascript;

use Drupal\Core\Language\LanguageManager;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Language
 * @group ckeditor5
 * @internal
 */
class TextPartLanguageTest extends CKEditor5TestBase {

  /**
   * Test for plugin Language of parts.
   */
  public function testLanguagePlugin() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->createNewTextFormat($page, $assert_session);
    // Press arrow down key to add the button to the active toolbar.
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ckeditor5-toolbar-item-textPartLanguage'));
    $this->triggerKeyUp('.ckeditor5-toolbar-item-textPartLanguage', 'ArrowDown');
    $assert_session->assertWaitOnAjaxRequest();

    // Test for "United Nations' official languages" option.
    $languages = LanguageManager::getUnitedNationsLanguageList();
    $this->languagePluginTestHelper($page, $assert_session, $languages, 'un');

    // Test for "All 95 languages" option.
    $this->drupalGet('admin/config/content/formats/manage/ckeditor5');
    $languages = LanguageManager::getStandardLanguageList();
    $this->languagePluginTestHelper($page, $assert_session, $languages, 'all');
  }

  /**
   * Validate the available languages on the basis of selected language option.
   */
  public function languagePluginTestHelper($page, $assert_session, $predefined_languages, $option) {
    $this->assertNotEmpty($assert_session->waitForElement('css', 'a[href^="#edit-editor-settings-plugins-ckeditor5-language"]'));

    // Set correct value.
    $vertical_tab_link = $page->find('xpath', "//ul[contains(@class, 'vertical-tabs__menu')]/li/a[starts-with(@href, '#edit-editor-settings-plugins-ckeditor5-language')]");
    $vertical_tab_link->click();
    $page->selectFieldOption('editor[settings][plugins][ckeditor5_language][language_list]', $option);
    $assert_session->assertWaitOnAjaxRequest();
    $page->pressButton('Save configuration');

    // Validate plugin on node add page.
    $this->drupalGet('node/add');
    $this->assertNotEmpty($assert_session->waitForText('Choose language'));

    // Click on the dropdown button.
    $page->find('css', '.ck-text-fragment-language-dropdown button')->click();

    // Get all the languages available in dropdown.
    $current_languages = $page->findAll('css', '.ck-text-fragment-language-dropdown li .ck-button__label');

    // Remove "Remove language" element from current languages.
    array_shift($current_languages);

    // Create array of full language name.
    $languages = [];
    foreach ($current_languages as $item) {
      $languages[] = $item->getText();
    }
    // Return the values from a single column.
    $predefined_languages = array_column($predefined_languages, 0);

    // Sort on full language name.
    asort($predefined_languages);

    $this->assertSame(array_values($predefined_languages), $languages);
  }

}
