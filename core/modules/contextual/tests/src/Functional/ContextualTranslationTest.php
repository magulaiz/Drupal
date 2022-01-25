<?php

namespace Drupal\tests\contextual\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\contextual\Traits\ContextualTestTrait;

/**
 * Tests contextual link translation.
 *
 * @group contextual
 */
class ContextualTranslationTest extends BrowserTestBase {

  use ContextualTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'contextual',
    'language',
    'locale',
    'node',
    'system',
  ];

  /**
   * The admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The locale storage.
   *
   * @var \Drupal\locale\StringStorageInterface
   */
  protected $localeStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->languageManager = $this->container->get('language_manager');
    $this->localeStorage = $this->container->get('locale.storage');

    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');

    $this->adminUser = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($this->adminUser);

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    ConfigurableLanguage::createFromLangcode('nl')->save();
    $this->rebuildContainer();

    // Enable the 'Account administration pages' language detection.
    $this->drupalGet('admin/config/regional/language/detection');
    $this->submitForm(['language_interface[enabled][language-user-admin]' => TRUE], 'Save settings');
  }

  /**
   * Tests that contextual links are shown in the preferred admin language.
   */
  public function testContextualLinksPreferredAdminLanguage() {
    // Create a node and visit the translated page so new translation labels
    // are added.
    $nl_language = $this->languageManager->getLanguage('nl');
    $node1 = $this->drupalCreateNode(['type' => 'page']);
    $this->drupalGet($node1->toUrl('canonical', ['language' => $nl_language]));

    // Add a translation for the 'Edit' string.
    $edit_translation = $this->randomMachineName();
    $this->drupalGet('admin/config/regional/translate');
    $this->submitForm(['string' => 'Edit', 'langcode' => 'nl'], 'Filter');
    $textarea = current($this->xpath('//textarea'));
    $lid = (string) $textarea->getAttribute('name');
    $this->submitForm([$lid => $edit_translation], 'Save translations');

    $ids = [
      'node:node=' . $node1->id() . ':changed=' . $node1->getChangedTime() . '&langcode=nl',
    ];
    // Render the contextual links. The 'Edit' label should be shown in the
    // custom language.
    $response = $this->renderContextualLinks($ids, 'node', ['language' => $nl_language]);
    $json = Json::decode((string) $response->getBody());
    $this->assertSame('<ul class="contextual-links"><li class="entitynodeedit-form"><a href="' . base_path() . 'nl/node/1/edit">' . $edit_translation . '</a></li><li class="entitynodedelete-form"><a href="' . base_path() . 'nl/node/1/delete">Delete</a></li></ul>', $json[$ids[0]]);

    // Configure a preferred admin language.
    $this->adminUser->set('preferred_admin_langcode', 'en');
    $this->adminUser->save();

    // Render the contextual links. The edit label should be shown in the
    // preferred admin language.
    $response = $this->renderContextualLinks($ids, 'node', ['language' => $nl_language]);
    $json = Json::decode((string) $response->getBody());
    $this->assertSame('<ul class="contextual-links"><li class="entitynodeedit-form"><a href="' . base_path() . 'nl/node/1/edit">Edit</a></li><li class="entitynodedelete-form"><a href="' . base_path() . 'nl/node/1/delete">Delete</a></li></ul>', $json[$ids[0]]);
  }

}
