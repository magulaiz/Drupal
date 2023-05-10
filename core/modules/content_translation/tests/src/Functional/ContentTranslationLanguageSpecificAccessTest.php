<?php

namespace Drupal\Tests\content_translation\Functional;

use Drupal\Core\Url;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\WebAssert;

/**
 * Tests language-specific access check on content entity translations.
 *
 * @group content_translation
 */
class ContentTranslationLanguageSpecificAccessTest extends ContentTranslationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'content_translation',
    'content_translation_language_access_test',
    'entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->state = $this->container->get('state');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->entityStorage = $entity_type_manager->getStorage($this->entityTypeId);
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatorPermissions() {
    $permissions = parent::getTranslatorPermissions();

    // Allow translator to access the entity edit form.
    $permissions[] = 'administer entity_test content';

    return $permissions;
  }

  /**
   * Sets language-specific access results for current entity type.
   *
   * @param string $language
   *   The language code.
   * @param bool[] $access
   *   The list of access results per entity operation. Possible values of every
   *   item:
   *   - FALSE: access is forbidden,
   *   - TRUE: access is allowed,
   *   - NULL: neutral.
   */
  protected function setLanguageAccessResult(string $language, array $access) {
    $state_key = implode('.', [
      'content_translation_language_access_test',
      $this->entityTypeId,
      $language,
    ]);
    $this->state->set($state_key, $access);
  }

  /**
   * Tests translation overview in case of language-specific access results.
   */
  public function testLanguageSpecificAccess() {
    // Create an entity with a translation.
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity_id = $this->createEntity([], 'en');
    $this->entityStorage->resetCache();
    $entity = $this->entityStorage->load($entity_id);
    $translation = $entity->addTranslation('it', $entity->toArray());
    $translation->save();

    // Forbid access to the original entity, but keep it unset for translation.
    // In order to access the translation overview, user must also be able to
    // view it.
    $this->setLanguageAccessResult('en', [
      'view' => TRUE,
      'update' => FALSE,
      'delete' => FALSE,
    ]);

    $entity_edit_url = $entity->toUrl('edit-form');
    $entity_edit_url->setOption('language', ConfigurableLanguage::load('en'));
    $translation_edit_url = $translation->toUrl('edit-form');
    $translation_edit_url->setOption('language', ConfigurableLanguage::load('it'));

    // The edit URL in default language is not accessible.
    $this->drupalGet($entity_edit_url->toString());
    $this->assertSession()
      ->statusCodeEquals(403);

    // The edit URL in translation language is accessible.
    $this->drupalGet($translation_edit_url->toString());
    $this->assertSession()
      ->statusCodeEquals(200);

    // The translation overview must show no link for the original entity and
    // link to the edit form of the translation.
    $this->drupalGet($entity->toUrl('drupal:content-translation-overview'));
    $this->assertSession()
      ->statusCodeEquals(200);
    $this->assertTranslationLinkNotExists($entity_edit_url);
    $this->assertTranslationLinkExists($translation_edit_url);
  }

  /**
   * Asserts that link with exact URL exists on the page.
   *
   * @param \Drupal\Core\Url $url
   *   The URL to test for.
   */
  protected function assertTranslationLinkExists(Url $url) {
    $session = $this->assertSession();
    $xpath = $this->buildTranslationLinkXPathQuery($session, $url);
    $session->elementExists('xpath', $xpath);
  }

  /**
   * Asserts that link with exact URL doesn't exist on the page.
   *
   * @param \Drupal\Core\Url $url
   *   The URL to test for.
   */
  protected function assertTranslationLinkNotExists(Url $url) {
    $session = $this->assertSession();
    $xpath = $this->buildTranslationLinkXPathQuery($session, $url);
    $session->elementNotExists('xpath', $xpath);
  }

  /**
   * Builds XPath query for a link with passed URL.
   *
   * @param \Drupal\Tests\WebAssert $session
   *   The WebAssert.
   * @param \Drupal\Core\Url $url
   *   The URL.
   *
   * @return string
   *   The XPath query.
   */
  protected function buildTranslationLinkXPathQuery(WebAssert $session, Url $url) {
    return $session->buildXPathQuery(
      '//a[@href=:href]',
      [':href' => $url->toString()]
    );
  }

}
