<?php

namespace Drupal\Tests\content_moderation\Functional;

use Drupal\Core\Url;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;

/**
 * Tests general content moderation workflow for nodes.
 *
 * @group content_moderation
 */
class ModerationStateNodeTest extends ModerationStateTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'content_translation',
    'content_moderation',
    'block',
    'block_content',
    'node',
    'entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
    $this->createContentTypeFromUi('Moderated content', 'moderated_content', TRUE);
    $this->grantUserPermissionToCreateContentOfType($this->adminUser, 'moderated_content');
  }

  /**
   * Tests creating and deleting content.
   */
  public function testCreatingContent() {
    $this->drupalGet('node/add/moderated_content');
    $this->submitForm([
      'title[0][value]' => 'moderated content',
      'moderation_state[0][state]' => 'draft',
    ], 'Save');
    $node = $this->getNodeByTitle('moderated content');
    if (!$node) {
      $this->fail('Test node was not saved correctly.');
    }
    $this->assertEquals('draft', $node->moderation_state->value);

    $path = 'node/' . $node->id() . '/edit';
    // Set up published revision.
    $this->drupalGet($path);
    $this->submitForm(['moderation_state[0][state]' => 'published'], 'Save');
    \Drupal::entityTypeManager()->getStorage('node')->resetCache([$node->id()]);
    /** @var \Drupal\node\NodeInterface $node */
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->assertTrue($node->isPublished());
    $this->assertEquals('published', $node->moderation_state->value);

    // Verify that the state field is not shown.
    $this->assertSession()->pageTextNotContains('Published');

    // Delete the node.
    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
    $this->assertSession()->pageTextContains('The Moderated content moderated content has been deleted.');

    // Disable content moderation.
    $edit['bundles[moderated_content]'] = FALSE;
    $this->drupalGet('admin/config/workflow/workflows/manage/editorial/type/node');
    $this->submitForm($edit, 'Save');
    // Ensure the parent environment is up-to-date.
    // @see content_moderation_workflow_insert()
    \Drupal::service('entity_type.bundle.info')->clearCachedBundles();
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();

    // Create a new node.
    $this->drupalGet('node/add/moderated_content');
    $this->submitForm(['title[0][value]' => 'non-moderated content'], 'Save');

    $node = $this->getNodeByTitle('non-moderated content');
    if (!$node) {
      $this->fail('Non-moderated test node was not saved correctly.');
    }
    $this->assertFalse($node->hasField('moderation_state'));
  }

  /**
   * Tests edit form destinations.
   */
  public function testFormSaveDestination() {
    // Create new moderated content in draft.
    $this->drupalGet('node/add/moderated_content');
    $this->submitForm([
      'title[0][value]' => 'Some moderated content',
      'body[0][value]' => 'First version of the content.',
      'moderation_state[0][state]' => 'draft',
    ], 'Save');

    $node = $this->drupalGetNodeByTitle('Some moderated content');
    $edit_path = sprintf('node/%d/edit', $node->id());

    // After saving, we should be at the canonical URL and viewing the first
    // revision.
    $this->assertSession()->addressEquals(Url::fromRoute('entity.node.canonical', ['node' => $node->id()]));
    $this->assertSession()->pageTextContains('First version of the content.');

    // Create a new draft; after saving, we should still be on the canonical
    // URL, but viewing the second revision.
    $this->drupalGet($edit_path);
    $this->submitForm([
      'body[0][value]' => 'Second version of the content.',
      'moderation_state[0][state]' => 'draft',
    ], 'Save');
    $this->assertSession()->addressEquals(Url::fromRoute('entity.node.canonical', ['node' => $node->id()]));
    $this->assertSession()->pageTextContains('Second version of the content.');

    // Make a new published revision; after saving, we should be at the
    // canonical URL.
    $this->drupalGet($edit_path);
    $this->submitForm([
      'body[0][value]' => 'Third version of the content.',
      'moderation_state[0][state]' => 'published',
    ], 'Save');
    $this->assertSession()->addressEquals(Url::fromRoute('entity.node.canonical', ['node' => $node->id()]));
    $this->assertSession()->pageTextContains('Third version of the content.');

    // Make a new pending revision; after saving, we should be on the "Latest
    // version" tab.
    $this->drupalGet($edit_path);
    $this->submitForm([
      'body[0][value]' => 'Fourth version of the content.',
      'moderation_state[0][state]' => 'draft',
    ], 'Save');
    $this->assertSession()->addressEquals(Url::fromRoute('entity.node.latest_version', ['node' => $node->id()]));
    $this->assertSession()->pageTextContains('Fourth version of the content.');
  }

  /**
   * Tests pagers aren't broken by content_moderation.
   */
  public function testPagers() {
    // Create 51 nodes to force the pager.
    foreach (range(1, 51) as $delta) {
      Node::create([
        'type' => 'moderated_content',
        'uid' => $this->adminUser->id(),
        'title' => 'Node ' . $delta,
        'status' => 1,
        'moderation_state' => 'published',
      ])->save();
    }
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/content');
    $element = $this->cssSelect('nav.pager li.is-active a');
    $url = $element[0]->getAttribute('href');
    $query = [];
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $this->assertEquals(0, $query['page']);
  }

  /**
   * Tests the workflow when a user has no Content Moderation permissions.
   */
  public function testNoContentModerationPermissions() {
    $session_assert = $this->assertSession();

    // Create a user with quite advanced node permissions but no content
    // moderation permissions.
    $limited_user = $this->createUser([
      'administer nodes',
      'bypass node access',
    ]);
    $this->drupalLogin($limited_user);

    // Check the user can see the content entity form, but can't see the
    // moderation state select or save the entity form.
    $this->drupalGet('node/add/moderated_content');
    $session_assert->statusCodeEquals(200);
    $session_assert->fieldNotExists('moderation_state[0][state]');
    $this->submitForm([
      'title[0][value]' => 'moderated content',
    ], 'Save');
    $session_assert->pageTextContains('You do not have access to transition from Draft to Draft');
  }

  /**
   * Tests that canceling a user retains the default revision.
   */
  public function testUserCancel() {

    // Enable additional languages.
    ConfigurableLanguage::createFromLangcode('es')->save();
    // Enable translation for the current entity type and ensure the change is
    // picked up.
    \Drupal::service('content_translation.manager')->setEnabled('node', 'moderated_content', TRUE);

    // Set the user cancel default method.
    $this->config('user.settings')
      ->set('cancel_method', 'user_cancel_reassign')
      ->save();

    // Add permissions to admin of cancel account.
    $role_ids = $this->adminUser->getRoles();
    $role_id = reset($role_ids);
    $role = Role::load($role_id);
    $role->grantPermission('administer users');
    $role->grantPermission('view any unpublished content');
    $role->save();

    // Create the first user.
    $web_user = $this->drupalCreateUser([
      'view any unpublished content',
      'access content overview',
      'use editorial transition create_new_draft',
      'use editorial transition publish',
      'use editorial transition archive',
      'use editorial transition archived_draft',
      'use editorial transition archived_published',
    ]);

    $this->grantUserPermissionToCreateContentOfType($web_user, 'moderated_content');

    // Create a second user.
    $second_web_user = $this->drupalCreateUser([
      'view any unpublished content',
      'access content overview',
      'use editorial transition create_new_draft',
      'use editorial transition publish',
      'use editorial transition archive',
      'use editorial transition archived_draft',
      'use editorial transition archived_published',
      'translate any entity',
    ]);

    $this->grantUserPermissionToCreateContentOfType($second_web_user, 'moderated_content');

    $this->drupalLogin($web_user);

    // Create the first revision of the content.
    // The author will be "web_user".
    $this->drupalGet('node/add/moderated_content');
    $this->submitForm([
      'title[0][value]' => 'First version of the content en.',
      'moderation_state[0][state]' => 'published',
    ], 'Save');

    $node = $this->getNodeByTitle('First version of the content en.');
    if (!$node) {
      $this->fail('Test node was not saved correctly.');
    }
    $this->assertEquals('published', $node->moderation_state->value);

    // After saving, we should be at the canonical URL and viewing the first
    // revision.
    $this->assertSession()
      ->addressEquals(Url::fromRoute('entity.node.canonical', ['node' => $node->id()]));
    $this->assertSession()->pageTextContains('First version of the content en.');

    $this->drupalLogin($second_web_user);

    $translation_path = sprintf('node/%d/translations/add/en/es', $node->id());
    // Create a second revision.
    $this->drupalGet($translation_path);
    $this->submitForm([
      'title[0][value]' => 'First version of the content es.',
      'moderation_state[0][state]' => 'published',
    ], 'Save');

    $this->assertSession()
      ->addressEquals(Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['langcode' => 'es']));
    $this->assertSession()->pageTextContains('First version of the content es.');

    // Check that the revision number is right.
    $this->assertUserNodeCount($web_user->id(), 1, 'en', 'node_field_data');
    $this->assertUserNodeCount($second_web_user->id(), 1, 'es', 'node_field_data');

    $this->assertUserNodeCount($web_user->id(), 2, 'en', 'node_field_revision');
    $this->assertUserNodeCount($second_web_user->id(), 1, 'es', 'node_field_revision');

    // Cancel "web_user" account.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('user/' . $web_user->id() . '/cancel');
    $this->submitForm([], 'Confirm');

    // Confirm user deletion.
    $this->assertSession()
      ->pageTextContains("Account {$web_user->getAccountName()} has been deleted.");

    // Check that the revision of first user was assigned to the anonymous user.
    $this->assertUserNodeCount($web_user->id(), 0, 'en', 'node_field_data');
    $this->assertUserNodeCount(0, 1, 'en', 'node_field_data');
    $this->assertUserNodeCount($second_web_user->id(), 1, 'es', 'node_field_data');

    $this->assertUserNodeCount($web_user->id(), 0, 'en', 'node_field_revision');
    $this->assertUserNodeCount(0, 2, 'en', 'node_field_revision');
    $this->assertUserNodeCount($second_web_user->id(), 1, 'es', 'node_field_revision');

    // Check content as anonymous.
    $this->drupalLogout();

    \Drupal::entityTypeManager()->getStorage('node')->resetCache([$node->id()]);

    // Check default language.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($node->id());
    $this->drupalGet($node->toUrl('canonical', ['language' => $node->language()])->toString());
    $this->assertSession()->pageTextContains('First version of the content en.');

    // Check that the author, previously "web_user", now is anonymous.
    $this->assertEquals(0, $node->uid->entity->id());
    $this->assertEquals('First version of the content en.', $node->title->value);

    // Check that the translation revision still have the right users "second_web_user".
    $translation = $node->getTranslation('es');

    // Check translation.
    $this->drupalGet('es/node/' . $node->id());
    $this->assertSession()->pageTextContains('First version of the content es.');
    $this->assertEquals('First version of the content es.', $translation->title->value);
    $this->assertEquals($second_web_user->id(), $translation->uid->entity->id(), 'Check user on translation.');
  }

  /**
   * Assert count by langcode and uid.
   */
  private function assertUserNodeCount(int $uid, int $count, string $langcode, string $table): void {
    $query = \Drupal::database()->select($table, 'n');
    $result = $query
      ->fields('n')
      ->condition('uid', $uid)
      ->condition('langcode', $langcode)
      ->countQuery()
      ->execute();

    $this->assertEquals($count, $result->fetchField(), 'User with uid ' . $uid . 'should have ' . $count . ' rows on table ' . $table . ' and language ' . $langcode);
  }

}
