<?php

namespace Drupal\Tests\config_translation\Functional;

<<<<<<< HEAD
use Drupal\Component\Utility\Html;
=======
>>>>>>> upstream/11.x
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;

// cspell:ignore viewsviewfiles

/**
 * Translate settings and entities to various languages.
 *
 * @group config_translation
 * @group #slow
 */
class ConfigTranslationUiTest extends ConfigTranslationUiTestBase {

  /**
   * Tests the account settings translation interface.
   *
   * This is the only special case so far where we have multiple configuration
   * names involved building up one configuration translation form. Test that
   * the translations are saved for all configuration names properly.
   */
  public function testAccountSettingsConfigurationTranslation() {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/config/people/accounts');
    $this->assertSession()->linkExists('Translate account settings');

    $this->drupalGet('admin/config/people/accounts/translate');
    $this->assertSession()->linkExists('Translate account settings');
    $this->assertSession()->linkByHrefExists('admin/config/people/accounts/translate/fr/add');

    // Update account settings fields for French.
    $edit = [
      'translation[config_names][user.settings][anonymous]' => 'Anonyme',
      'translation[config_names][user.mail][status_blocked][subject]' => 'Testing, your account is blocked.',
      'translation[config_names][user.mail][status_blocked][body]' => 'Testing account blocked body.',
    ];

    $this->drupalGet('admin/config/people/accounts/translate/fr/add');
    $this->submitForm($edit, 'Save translation');

    // Make sure the changes are saved and loaded back properly.
    $this->drupalGet('admin/config/people/accounts/translate/fr/edit');
    foreach ($edit as $key => $value) {
      // Check the translations appear in the right field type as well.
      $this->assertSession()->fieldValueEquals($key, $value);
    }
    // Check that labels for email settings appear.
    $this->assertSession()->pageTextContains('Account cancellation confirmation');
    $this->assertSession()->pageTextContains('Password recovery');
  }

  /**
   * Tests source and target language edge cases.
   */
  public function testSourceAndTargetLanguage() {
    $this->drupalLogin($this->adminUser);

    // Loading translation page for not-specified language (und)
    // should return 403.
    $this->drupalGet('admin/config/system/site-information/translate/und/add');
    $this->assertSession()->statusCodeEquals(403);

    // Check the source language doesn't have 'Add' or 'Delete' link and
    // make sure source language edit goes to original configuration page
    // not the translation specific edit page.
    $this->drupalGet('admin/config/system/site-information/translate');
    $this->assertSession()->linkByHrefNotExists('admin/config/system/site-information/translate/en/edit');
    $this->assertSession()->linkByHrefNotExists('admin/config/system/site-information/translate/en/add');
    $this->assertSession()->linkByHrefNotExists('admin/config/system/site-information/translate/en/delete');
    $this->assertSession()->linkByHrefExists('admin/config/system/site-information');

    // Translation addition to source language should return 403.
    $this->drupalGet('admin/config/system/site-information/translate/en/add');
    $this->assertSession()->statusCodeEquals(403);

    // Translation editing in source language should return 403.
    $this->drupalGet('admin/config/system/site-information/translate/en/edit');
    $this->assertSession()->statusCodeEquals(403);

    // Translation deletion in source language should return 403.
    $this->drupalGet('admin/config/system/site-information/translate/en/delete');
    $this->assertSession()->statusCodeEquals(403);

    // Set default language of site information to not-specified language (und).
    $this->config('system.site')
      ->set('langcode', LanguageInterface::LANGCODE_NOT_SPECIFIED)
      ->save();

    // Make sure translation tab does not exist on the configuration page.
    $this->drupalGet('admin/config/system/site-information');
    $this->assertSession()->linkByHrefNotExists('admin/config/system/site-information/translate');

    // If source language is not specified, translation page should be 403.
    $this->drupalGet('admin/config/system/site-information/translate');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Tests plural source elements in configuration translation forms.
   */
  public function testPluralConfigStringsSourceElements() {
    $this->drupalLogin($this->adminUser);

    // Languages to test, with various number of plural forms.
    $languages = [
      'vi' => ['plurals' => 1, 'expected' => [TRUE, FALSE, FALSE, FALSE]],
      'fr' => ['plurals' => 2, 'expected' => [TRUE, TRUE, FALSE, FALSE]],
      'sl' => ['plurals' => 4, 'expected' => [TRUE, TRUE, TRUE, TRUE]],
    ];

    foreach ($languages as $langcode => $data) {
      // Import a .po file to add a new language with a given number of plural forms
      $name = \Drupal::service('file_system')->tempnam('temporary://', $langcode . '_') . '.po';
      file_put_contents($name, $this->getPoFile($data['plurals']));
      $this->drupalGet('admin/config/regional/translate/import');
      $this->submitForm([
        'langcode' => $langcode,
        'files[file]' => $name,
      ], 'Import');

      // Change the config langcode of the 'files' view.
      $config = \Drupal::service('config.factory')->getEditable('views.view.files');
      $config->set('langcode', $langcode);
      $config->save();

      // Go to the translation page of the 'files' view.
      $translation_url = 'admin/structure/views/view/files/translate/en/add';
      $this->drupalGet($translation_url);

      // Check if the expected number of source elements are present.
      foreach ($data['expected'] as $index => $expected) {
        if ($expected) {
          $this->assertSession()->responseContains('edit-source-config-names-viewsviewfiles-display-default-display-options-fields-count-format-plural-string-' . $index);
        }
        else {
          $this->assertSession()->responseNotContains('edit-source-config-names-viewsviewfiles-display-default-display-options-fields-count-format-plural-string-' . $index);
        }
      }
    }
  }

  /**
   * Tests translation of plural strings with multiple plural forms in config.
   */
  public function testPluralConfigStrings() {
    $this->drupalLogin($this->adminUser);

    // First import a .po file with multiple plural forms.
    // This will also automatically add the 'sl' language.
    $name = \Drupal::service('file_system')->tempnam('temporary://', "sl_") . '.po';
    file_put_contents($name, $this->getPoFile(4));
    $this->drupalGet('admin/config/regional/translate/import');
    $this->submitForm([
      'langcode' => 'sl',
      'files[file]' => $name,
    ], 'Import');

    // Translate the files view, as this one uses numeric formatters.
    $description = 'Singular form';
    $field_value = '1 place';
    $field_value_plural = '@count places';
    $translation_url = 'admin/structure/views/view/files/translate/sl/add';
    $this->drupalGet($translation_url);

    // Make sure original text is present on this page, in addition to 2 new
    // empty fields.
    $this->assertSession()->pageTextContains($description);
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][0]', $field_value);
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][1]', $field_value_plural);
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][2]', '');
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][3]', '');

    // Then make sure it also works.
    $edit = [
      'translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][0]' => $field_value . ' SL',
      'translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][1]' => $field_value_plural . ' 1 SL',
      'translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][2]' => $field_value_plural . ' 2 SL',
      'translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][3]' => $field_value_plural . ' 3 SL',
    ];
    $this->drupalGet($translation_url);
    $this->submitForm($edit, 'Save translation');

    // Make sure the values have changed.
    $this->drupalGet($translation_url);
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][0]', "$field_value SL");
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][1]', "$field_value_plural 1 SL");
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][2]', "$field_value_plural 2 SL");
    $this->assertSession()->fieldValueEquals('translation[config_names][views.view.files][display][default][display_options][fields][count][format_plural_string][3]', "$field_value_plural 3 SL");
  }

  /**
   * Tests translation storage in locale storage.
   */
  public function testLocaleDBStorage() {
    // Enable import of translations. By default this is disabled for automated
    // tests.
    $this->config('locale.settings')
      ->set('translation.import_enabled', TRUE)
      ->set('translation.use_source', LOCALE_TRANSLATION_USE_SOURCE_LOCAL)
      ->save();

    $this->drupalLogin($this->adminUser);

    $langcode = 'xx';
    $name = $this->randomMachineName(16);
    $edit = [
      'predefined_langcode' => 'custom',
      'langcode' => $langcode,
      'label' => $name,
      'direction' => Language::DIRECTION_LTR,
    ];
    $this->drupalGet('admin/config/regional/language/add');
    $this->submitForm($edit, 'Add custom language');

    // Make sure there is no translation stored in locale storage before edit.
    $translation = $this->getTranslation('user.settings', 'anonymous', 'fr');
    $this->assertEmpty($translation);

    // Add custom translation.
    $edit = [
      'translation[config_names][user.settings][anonymous]' => 'Anonyme',
    ];
    $this->drupalGet('admin/config/people/accounts/translate/fr/add');
    $this->submitForm($edit, 'Save translation');

    // Make sure translation stored in locale storage after saved language
    // specific configuration translation.
    $translation = $this->getTranslation('user.settings', 'anonymous', 'fr');
    $this->assertEquals('Anonyme', $translation->getString());

    // revert custom translations to base translation.
    $edit = [
      'translation[config_names][user.settings][anonymous]' => 'Anonymous',
    ];
    $this->drupalGet('admin/config/people/accounts/translate/fr/edit');
    $this->submitForm($edit, 'Save translation');

    // Make sure there is no translation stored in locale storage after revert.
    $translation = $this->getTranslation('user.settings', 'anonymous', 'fr');
    $this->assertEquals('Anonymous', $translation->getString());
  }

  /**
   * Tests the single language existing.
   */
  public function testSingleLanguageUI() {
    $this->drupalLogin($this->adminUser);

    // Delete French language
    $this->drupalGet('admin/config/regional/language/delete/fr');
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains('The French (fr) language has been removed.');

    // Change default language to Tamil.
    $edit = [
      'site_default_language' => 'ta',
    ];
    $this->drupalGet('admin/config/regional/language');
    $this->submitForm($edit, 'Save configuration');
    $this->assertSession()->pageTextContains('Configuration saved.');

    // Delete English language
    $this->drupalGet('admin/config/regional/language/delete/en');
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains('The English (en) language has been removed.');

    // Visit account setting translation page, this should not
    // throw any notices.
    $this->drupalGet('admin/config/people/accounts/translate');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests the config_translation_info_alter() hook.
   */
  public function testAlterInfo() {
    $this->drupalLogin($this->adminUser);

    $this->container->get('state')->set('config_translation_test_config_translation_info_alter', TRUE);
    $this->container->get('plugin.manager.config_translation.mapper')->clearCachedDefinitions();

    // Check if the translation page does not have the altered out settings.
    $this->drupalGet('admin/config/people/accounts/translate/fr/add');
    $this->assertSession()->pageTextContains('Name');
    $this->assertSession()->pageTextNotContains('Account cancellation confirmation');
    $this->assertSession()->pageTextNotContains('Password recovery');
  }

  /**
   * Tests the sequence data type translation.
   */
  public function testSequenceTranslation() {
    $this->drupalLogin($this->adminUser);
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');

    $expected = [
      'kitten',
      'llama',
      'elephant',
    ];
    $actual = $config_factory
      ->getEditable('config_translation_test.content')
      ->get('animals');
    $this->assertEquals($expected, $actual);

    $edit = [
      'translation[config_names][config_translation_test.content][content][value]' => '<p><strong>Hello World</strong> - FR</p>',
      'translation[config_names][config_translation_test.content][animals][0]' => 'kitten - FR',
      'translation[config_names][config_translation_test.content][animals][1]' => 'llama - FR',
      'translation[config_names][config_translation_test.content][animals][2]' => 'elephant - FR',
    ];
    $this->drupalGet('admin/config/media/file-system/translate/fr/add');
    $this->submitForm($edit, 'Save translation');

    $this->container->get('language.config_factory_override')
      ->setLanguage(new Language(['id' => 'fr']));

    $expected = [
      'kitten - FR',
      'llama - FR',
      'elephant - FR',
    ];
    $actual = $config_factory
      ->get('config_translation_test.content')
      ->get('animals');
    $this->assertEquals($expected, $actual);
  }

<<<<<<< HEAD
  /**
   * Tests text_format translation.
   */
  public function testTextFormatTranslation() {
    $this->drupalLogin($this->adminUser);
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');

    $expected = [
      'value' => '<p><strong>Hello World</strong></p>',
      'format' => 'plain_text',
    ];
    $actual = $config_factory
      ->get('config_translation_test.content')
      ->getOriginal('content', FALSE);
    $this->assertEquals($expected, $actual);

    $translation_base_url = 'admin/config/media/file-system/translate';
    $this->drupalGet($translation_base_url);

    // 'Add' link should be present for French translation.
    $translation_page_url = "$translation_base_url/fr/add";
    $this->assertSession()->linkByHrefExists($translation_page_url);

    $this->drupalGet($translation_page_url);

    // Assert that changing the text format is not possible, even for an
    // administrator.
    $this->assertSession()->fieldNotExists('translation[config_names][config_translation_test.content][content][format]');

    // Update translatable fields.
    $edit = [
      'translation[config_names][config_translation_test.content][content][value]' => '<p><strong>Hello World</strong> - FR</p>',
    ];

    // Save language specific version of form.
    $this->drupalGet($translation_page_url);
    $this->submitForm($edit, 'Save translation');

    // Get translation and check we've got the right value.
    $expected = [
      'value' => '<p><strong>Hello World</strong> - FR</p>',
      'format' => 'plain_text',
    ];
    $this->container->get('language.config_factory_override')
      ->setLanguage(new Language(['id' => 'fr']));
    $actual = $config_factory
      ->get('config_translation_test.content')
      ->get('content');
    $this->assertEquals($expected, $actual);

    // Change the text format of the source configuration and verify that the
    // text format of the translation does not change because that could lead to
    // security vulnerabilities.
    $config_factory
      ->getEditable('config_translation_test.content')
      ->set('content.format', 'full_html')
      ->save();

    $actual = $config_factory
      ->get('config_translation_test.content')
      ->get('content');
    // The translation should not have changed, so re-use $expected.
    $this->assertEquals($expected, $actual);

    // Because the text is now in a text format that the translator does not
    // have access to, the translator should not be able to translate it.
    $translation_page_url = "$translation_base_url/fr/edit";
    $this->drupalLogin($this->translatorUser);
    $this->drupalGet($translation_page_url);
    $this->assertDisabledTextarea('edit-translation-config-names-config-translation-testcontent-content-value');
    $this->submitForm([], 'Save translation');
    // Check that submitting the form did not update the text format of the
    // translation.
    $actual = $config_factory
      ->get('config_translation_test.content')
      ->get('content');
    $this->assertEquals($expected, $actual);

    // The administrator must explicitly change the text format.
    $this->drupalLogin($this->adminUser);
    $edit = [
      'translation[config_names][config_translation_test.content][content][format]' => 'full_html',
    ];
    $this->drupalGet($translation_page_url);
    $this->submitForm($edit, 'Save translation');
    $expected = [
      'value' => '<p><strong>Hello World</strong> - FR</p>',
      'format' => 'full_html',
    ];
    $actual = $config_factory
      ->get('config_translation_test.content')
      ->get('content');
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests field translation for node fields.
   */
  public function testNodeFieldTranslation() {
    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();

    $field_name = 'translatable_field';
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'text',
    ]);

    $field_storage->setSetting('translatable_storage_setting', 'translatable_storage_setting');
    $field_storage->save();
    $field = FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'bundle' => 'article',
    ]);
    $field->save();

    $this->drupalLogin($this->translatorUser);

    $this->drupalGet("/entity_test/structure/article/fields/node.article.$field_name/translate");
    $this->clickLink('Add');

    $form_values = [
      'translation[config_names][field.field.node.article.translatable_field][description]' => 'FR Help text.',
      'translation[config_names][field.field.node.article.translatable_field][label]' => 'FR label',
    ];
    $this->submitForm($form_values, 'Save translation');
    $this->assertSession()->pageTextContains('Successfully saved French translation.');

    // Check that the translations are saved.
    $this->clickLink('Add');
    $this->assertSession()->responseContains('FR label');
  }

  /**
   * Test translation save confirmation message.
   */
  public function testMenuTranslationWithoutChange() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/menu/manage/main/translate/tyv/add');
    $this->submitForm([], 'Save translation');
    $this->assertSession()->pageTextContains('Tuvan translation was not added. To add a translation, you must modify the configuration.');

    $this->drupalGet('admin/structure/menu/manage/main/translate/tyv/add');
    $edit = [
      'translation[config_names][system.menu.main][label]' => 'Main navigation Translation',
      'translation[config_names][system.menu.main][description]' => 'Site section links Translation',
    ];
    $this->submitForm($edit, 'Save translation');
    $this->assertSession()->pageTextContains('Successfully saved Tuvan translation.');
  }

  /**
   * Gets translation from locale storage.
   *
   * @param $config_name
   *   Configuration object.
   * @param $key
   *   Translation configuration field key.
   * @param $langcode
   *   String language code to load translation.
   *
   * @return bool|mixed
   *   Returns translation if exists, FALSE otherwise.
   */
  protected function getTranslation($config_name, $key, $langcode) {
    $settings_locations = $this->localeStorage->getLocations(['type' => 'configuration', 'name' => $config_name]);
    $this->assertNotEmpty($settings_locations, "$config_name should have configuration locations.");

    if (!empty($settings_locations)) {
      $source = $this->container->get('config.factory')->get($config_name)->get($key);
      $source_string = $this->localeStorage->findString(['source' => $source, 'type' => 'configuration']);
      $this->assertNotEmpty($source_string, "$config_name.$key should have a source string.");

      if (!empty($source_string)) {
        $conditions = [
          'lid' => $source_string->lid,
          'language' => $langcode,
        ];
        $translations = $this->localeStorage->getTranslations($conditions + ['translated' => TRUE]);
        return reset($translations);
      }
    }
    return FALSE;
  }

  /**
   * Sets site name and slogan for default language, helps in tests.
   *
   * @param string $site_name
   *   The site name.
   * @param string $site_slogan
   *   The site slogan.
   */
  protected function setSiteInformation($site_name, $site_slogan) {
    $edit = [
      'site_name' => $site_name,
      'site_slogan' => $site_slogan,
    ];
    $this->drupalGet('admin/config/system/site-information');
    $this->submitForm($edit, 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
  }

  /**
   * Get server-rendered contextual links for the given contextual link ids.
   *
   * @param array $ids
   *   An array of contextual link ids.
   * @param string $current_path
   *   The Drupal path for the page for which the contextual links are rendered.
   *
   * @return string
   *   The response body.
   */
  protected function renderContextualLinks($ids, $current_path) {
    $post = [];
    for ($i = 0; $i < count($ids); $i++) {
      $post['ids[' . $i . ']'] = $ids[$i];
    }
    return $this->drupalPostWithFormat('contextual/render', 'json', $post, ['query' => ['destination' => $current_path]]);
  }

  /**
   * Asserts that a textarea with a given ID has been disabled from editing.
   *
   * @param string $id
   *   The HTML ID of the textarea.
   *
   * @internal
   */
  protected function assertDisabledTextarea(string $id): void {
    $textarea = $this->assertSession()->fieldDisabled($id);
    $this->assertSame('textarea', $textarea->getTagName());
    $this->assertSame('This field has been disabled because you do not have sufficient permissions to edit it.', $textarea->getText());
    // Make sure the text format select is not shown.
    $select_id = str_replace('value', 'format--2', $id);
    $xpath = $this->assertSession()->buildXPathQuery('//select[@id=:id]', [':id' => $select_id]);
    $this->assertSession()->elementNotExists('xpath', $xpath);
  }

  /**
   * Helper function that returns a .po file with a given number of plural forms.
   */
  public function getPoFile($plurals) {
    $po_file = [];

    $po_file[1] = <<< EOF
msgid ""
msgstr ""
"Project-Id-Version: Drupal 8\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=1; plural=0;\\n"
EOF;

    $po_file[2] = <<< EOF
msgid ""
msgstr ""
"Project-Id-Version: Drupal 8\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=2; plural=(n>1);\\n"
EOF;

    $po_file[4] = <<< EOF
msgid ""
msgstr ""
"Project-Id-Version: Drupal 8\\n"
"MIME-Version: 1.0\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
"Plural-Forms: nplurals=4; plural=(((n%100)==1)?(0):(((n%100)==2)?(1):((((n%100)==3)||((n%100)==4))?(2):3)));\\n"
EOF;

    return $po_file[$plurals];
  }

=======
>>>>>>> upstream/11.x
}
