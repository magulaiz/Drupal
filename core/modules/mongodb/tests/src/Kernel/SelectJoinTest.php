<?php

namespace Drupal\Tests\mongodb\Kernel;

use Drupal\comment\CommentInterface;
use Drupal\comment\Entity\Comment;
use Drupal\comment\Entity\CommentType;
use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Tests select queries with joins.
 *
 * @group mongodb
 */
class SelectJoinTest extends EntityKernelTestBase {

  use CommentTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['comment', 'entity_test', 'user'];

  /**
   * Fields that only users with administer comments permissions can change.
   *
   * @var array
   */
  protected $administrativeFields = [
    'uid',
    'status',
    'created',
  ];

  /**
   * These fields are automatically managed and can not be changed by any user.
   *
   * @var array
   */
  protected $readOnlyFields = [
    'changed',
    'hostname',
    'cid',
    'thread',
  ];

  /**
   * These fields can be edited on create only.
   *
   * @var array
   */
  protected $createOnlyFields = [
    'uuid',
    'pid',
    'comment_type',
    'entity_id',
    'entity_type',
    'field_name',
  ];

  /**
   * These fields can only be edited by the admin or anonymous users if allowed.
   *
   * @var array
   */
  protected $contactFields = [
    'name',
    'mail',
    'homepage',
  ];

  /**
   * Tests basic functionality.
   */
  public function testEntityQuery() {
    $this->installConfig(['user', 'comment']);
    $this->installSchema('comment', ['comment_entity_statistics']);

    // Create a comment type.
    $comment_type = CommentType::create([
      'id' => 'comment',
      'label' => 'Default comments',
      'description' => 'Default comment field',
      'target_entity_type_id' => 'entity_test',
    ]);
    $comment_type->save();

    // An administrator user. No user exists yet, ensure that the first user
    // does not have UID 1.
    $comment_admin_user = $this->createUser([
      'administer comments',
      'access comments',
    ], 'admin', FALSE, ['uid' => 2]);

    // Two comment enabled users, one with edit access.
    $comment_enabled_user = $this->createUser([
      'post comments',
      'skip comment approval',
      'edit own comments',
      'access comments',
    ], 'enabled');

    $comment_no_edit_user = $this->createUser([
      'post comments',
      'skip comment approval',
      'access comments',
    ], 'no edit');

    // An unprivileged user.
    $comment_disabled_user = $this->createUser(['access content'], 'disabled');

    $role = Role::load(RoleInterface::ANONYMOUS_ID);
    $role->grantPermission('post comments')
      ->save();

//    \Drupal::service('account_switcher')->switchTo($comment_enabled_user);

    // Add two fields.
    $this->addDefaultCommentField('entity_test', 'entity_test', 'comment');
    $this->addDefaultCommentField('entity_test', 'entity_test', 'comment_other');

    // Create a comment against a test entity.
    $host = EntityTest::create();
    $host->user_id = $comment_enabled_user->id();
    $host->save();

    $host2 = EntityTest::create();
    $host2->user_id = $comment_no_edit_user->id();
    $host2->comment->status = CommentItemInterface::CLOSED;
    $host2->comment_other->status = CommentItemInterface::CLOSED;
    $host2->save();

    // Change the second field's anonymous contact setting.
    $instance = FieldConfig::loadByName('entity_test', 'entity_test', 'comment_other');
    // Default is 'May not contact', for this field - they may contact.
    $instance->setSetting('anonymous', CommentInterface::ANONYMOUS_MAY_CONTACT);
    $instance->save();

    // Create three "Comments". One is owned by our edit-enabled user.
    $comment1 = Comment::create([
      'entity_type' => 'entity_test',
      'name' => 'Tony',
      'hostname' => 'magic.example.com',
      'mail' => 'tonythemagicalpony@example.com',
      'subject' => 'Bruce the Mesopotamian moose',
      'entity_id' => $host->id(),
      'comment_type' => 'comment',
      'field_name' => 'comment',
      'pid' => 0,
      'uid' => 0,
      'status' => 1,
    ]);
    $comment1->save();
    $comment2 = Comment::create([
      'entity_type' => 'entity_test',
      'hostname' => 'magic.example.com',
      'subject' => 'Brian the messed up lion',
      'entity_id' => $host->id(),
      'comment_type' => 'comment',
      'field_name' => 'comment',
      'status' => 1,
      'pid' => 0,
      'uid' => $comment_enabled_user->id(),
    ]);
    $comment2->save();
    $comment3 = Comment::create([
      'entity_type' => 'entity_test',
      'hostname' => 'magic.example.com',
      // Unpublished.
      'status' => 0,
      'subject' => 'Gail the minke whale',
      'entity_id' => $host->id(),
      'comment_type' => 'comment',
      'field_name' => 'comment_other',
      'pid' => $comment2->id(),
      'uid' => $comment_no_edit_user->id(),
    ]);
    $comment3->save();
    // Note we intentionally don't save this comment so it remains 'new'.
    $comment4 = Comment::create([
      'entity_type' => 'entity_test',
      'hostname' => 'magic.example.com',
      // Unpublished.
      'status' => 0,
      'subject' => 'Daniel the Cocker-Spaniel',
      'entity_id' => $host->id(),
      'comment_type' => 'comment',
      'field_name' => 'comment_other',
      'pid' => 0,
      'uid' => $comment_admin_user->id(),
    ]);
    // Note we intentionally don't save this comment so it remains 'new'.
    $comment5 = Comment::create([
      'entity_type' => 'entity_test',
      'hostname' => 'magic.example.com',
      // Unpublished.
      'status' => 0,
      'subject' => 'Wally the Border Collie',
      // This one is closed for comments.
      'entity_id' => $host2->id(),
      'comment_type' => 'comment',
      'field_name' => 'comment_other',
      'pid' => 0,
      'uid' => $comment_admin_user->id(),
    ]);

    $this->assertTrue(TRUE);

    $connection = \Drupal::database();

//    $query = $connection->select('entity_test', 'e')
//      ->fields('e', ['id', 'uuid', 'user_id']);
//    $query->addJoin('LEFT', 'users', 'u', $query->joinCondition()->compare('e.user_id', 'u.uid'));
//    $query->addField('u', 'uuid', 'user_uuid');
//    $query->addField('u', 'user_translations.name', 'user_name');
//    $results = $query->execute()->fetchAll();
//
//    $this->assertCount(2, $results);
//    $this->assertSame($results[0]->id, $host->id());
//    $this->assertSame($results[0]->uuid, $host->uuid());
//    $this->assertSame($results[0]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[0]->user_uuid, $comment_enabled_user->uuid());
//    $this->assertSame($results[0]->user_name, [$comment_enabled_user->getAccountName()]);
//
//    $this->assertSame($results[1]->id, $host2->id());
//    $this->assertSame($results[1]->uuid, $host2->uuid());
//    $this->assertSame($results[1]->user_id, (string) $host2->getOwnerId());
//    $this->assertSame($results[1]->user_uuid, $comment_no_edit_user->uuid());
//    $this->assertSame($results[1]->user_name, [$comment_no_edit_user->getAccountName()]);
//
//
//    $query = $connection->select('comment', 'c')
//      ->fields('c', ['cid', 'uuid']);
//    $query->addJoin('LEFT', 'entity_test', 'e', $query->joinCondition()->compare('e.id', 'c.comment_translations.entity_id')->condition('e.user_id', 3));
//    $query->addField('e', 'uuid', 'entity_test_uuid');
//    $results = $query->execute()->fetchAll();
//
//    $this->assertCount(3, $results);
//    $this->assertSame($results[0]->cid, $comment1->id());
//    $this->assertSame($results[0]->uuid, $comment1->uuid());
//    $this->assertSame($results[0]->entity_test_uuid, $host->uuid());
//
//    $this->assertSame($results[1]->cid, $comment2->id());
//    $this->assertSame($results[1]->uuid, $comment2->uuid());
//    $this->assertSame($results[1]->entity_test_uuid, $host->uuid());
//
//    $this->assertSame($results[2]->cid, $comment3->id());
//    $this->assertSame($results[2]->uuid, $comment3->uuid());
//    $this->assertSame($results[2]->entity_test_uuid, $host->uuid());
//
//
//    $query = $connection->select('entity_test', 'e')
//      ->fields('e', ['id', 'uuid', 'user_id']);
//    $query->addJoin('INNER', 'comment', 'c', $query->joinCondition()->compare('e.id', 'c.comment_translations.entity_id')->condition('e.user_id', 3));
//    $query->addField('c', 'uuid', 'comment_uuid');
//    $results = $query->execute()->fetchAll();
//
//    $this->assertCount(3, $results);
//    $this->assertSame($results[0]->id, $host->id());
//    $this->assertSame($results[0]->uuid, $host->uuid());
//    $this->assertSame($results[0]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[0]->comment_uuid, $comment1->uuid());
//
//    $this->assertSame($results[1]->id, $host->id());
//    $this->assertSame($results[1]->uuid, $host->uuid());
//    $this->assertSame($results[1]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[1]->comment_uuid, $comment2->uuid());
//
//    $this->assertSame($results[2]->id, $host->id());
//    $this->assertSame($results[2]->uuid, $host->uuid());
//    $this->assertSame($results[2]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[2]->comment_uuid, $comment3->uuid());
//
//
//    $query = $connection->select('entity_test', 'e')
//      ->fields('e', ['id', 'uuid', 'user_id']);
//    $query->addJoin('LEFT', 'users', 'u', $query->joinCondition()->compare('e.user_id', 'u.uid'));
//    $query->addField('u', 'uuid', 'user_uuid');
//    $query->addField('u', 'user_translations.name', 'user_name');
//    $query->addJoin('LEFT', 'comment', 'c', $query->joinCondition()->compare('e.id', 'c.comment_translations.entity_id')->condition('e.user_id', 3));
//    $query->addField('c', 'uuid', 'comment_uuid');
//    $results = $query->execute()->fetchAll();
//
//    $this->assertCount(4, $results);
//    $this->assertSame($results[0]->id, $host->id());
//    $this->assertSame($results[0]->uuid, $host->uuid());
//    $this->assertSame($results[0]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[0]->user_uuid, $comment_enabled_user->uuid());
//    $this->assertSame($results[0]->user_name, [$comment_enabled_user->getAccountName()]);
//    $this->assertSame($results[0]->comment_uuid, $comment1->uuid());
//
//    $this->assertSame($results[1]->id, $host->id());
//    $this->assertSame($results[1]->uuid, $host->uuid());
//    $this->assertSame($results[1]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[1]->user_uuid, $comment_enabled_user->uuid());
//    $this->assertSame($results[1]->user_name, [$comment_enabled_user->getAccountName()]);
//    $this->assertSame($results[1]->comment_uuid, $comment2->uuid());
//
//    $this->assertSame($results[2]->id, $host->id());
//    $this->assertSame($results[2]->uuid, $host->uuid());
//    $this->assertSame($results[2]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[2]->user_uuid, $comment_enabled_user->uuid());
//    $this->assertSame($results[2]->user_name, [$comment_enabled_user->getAccountName()]);
//    $this->assertSame($results[2]->comment_uuid, $comment3->uuid());
//
//    $this->assertSame($results[3]->id, $host2->id());
//    $this->assertSame($results[3]->uuid, $host2->uuid());
//    $this->assertSame($results[3]->user_id, (string) $host2->getOwnerId());
//    $this->assertSame($results[3]->user_uuid, $comment_no_edit_user->uuid());
//    $this->assertSame($results[3]->user_name, [$comment_no_edit_user->getAccountName()]);
//    $this->assertNull($results[3]->comment_uuid);
//
//
//    $query = $connection->select('users', 'u')
//      ->fields('u', ['uid', 'uuid']);
//    $query->addJoin('LEFT', 'comment', 'c', $query->joinCondition()->compare('u.uid', 'c.comment_translations.uid'));
//    $query->addField('c', 'cid', 'comment_cid');
//    $query->addField('c', 'comment_translations.uid', 'comment_uid');
//    $query->addField('c', 'uuid', 'comment_uuid');
//    $query->addJoin('LEFT', 'entity_test', 'e', $query->joinCondition()->compare('e.entity_test__comment.entity_id', 'c.cid'));
//    $query->addField('e', 'id', 'entity_test_id');
//    $query->addField('e', 'uuid', 'entity_test_uuid');
//    $results = $query->execute()->fetchAll();
//
//    $this->assertCount(4, $results);
//    $this->assertSame($results[0]->uid, $comment_admin_user->id());
//    $this->assertSame($results[0]->uuid, $comment_admin_user->uuid());
//    $this->assertNull($results[0]->comment_cid);
//    $this->assertNull($results[0]->comment_uuid);
//    $this->assertNull($results[0]->entity_test_id);
//    $this->assertNull($results[0]->entity_test_uuid);
//
//    $this->assertSame($results[1]->uid, $comment_enabled_user->id());
//    $this->assertSame($results[1]->uuid, $comment_enabled_user->uuid());
//    $this->assertSame($results[1]->comment_cid, $comment2->id());
//    $this->assertSame($results[1]->comment_uid, $comment2->getOwnerId());
//    $this->assertSame($results[1]->comment_uuid, $comment2->uuid());
//    $this->assertSame($results[1]->entity_test_id, $host2->id());
//    $this->assertSame($results[1]->entity_test_uuid, $host2->uuid());
//
//    $this->assertSame($results[2]->uid, $comment_no_edit_user->id());
//    $this->assertSame($results[2]->uuid, $comment_no_edit_user->uuid());
//    $this->assertSame($results[2]->comment_cid, $comment3->id());
//    $this->assertSame($results[2]->comment_uid, $comment3->getOwnerId());
//    $this->assertSame($results[2]->comment_uuid, $comment3->uuid());
//    $this->assertNull($results[2]->entity_test_id);
//    $this->assertNull($results[2]->entity_test_uuid);
//
//    $this->assertSame($results[3]->uid, $comment_disabled_user->id());
//    $this->assertSame($results[3]->uuid, $comment_disabled_user->uuid());
//    $this->assertNull($results[3]->comment_cid);
//    $this->assertNull($results[3]->comment_uuid);
//    $this->assertNull($results[3]->entity_test_id);
//    $this->assertNull($results[3]->entity_test_uuid);
//
//    // Get the last role of the user $comment_enabled_user.
//    $comment_enabled_user_roles = $comment_enabled_user->getRoles();
//    $comment_enabled_user_role = end($comment_enabled_user_roles);
//
//    $query = $connection->select('entity_test', 'e')
//      ->fields('e', ['id', 'uuid', 'user_id']);
//    $query->addJoin('LEFT', 'users', 'u', $query->joinCondition()->compare('e.user_id', 'u.uid')->condition('u.user_translations.user_translations__roles.roles_target_id', $comment_enabled_user_role));
//    $query->addField('u', 'uuid', 'user_uuid');
//    $query->addField('u', 'user_translations.name', 'user_name');
//    $query->addField('u', 'user_translations.user_translations__roles.roles_target_id', 'user_role');
//    $results = $query->execute()->fetchAll();
//
//    $this->assertCount(2, $results);
//    $this->assertSame($results[0]->id, $host->id());
//    $this->assertSame($results[0]->uuid, $host->uuid());
//    $this->assertSame($results[0]->user_id, (string) $host->getOwnerId());
//    $this->assertSame($results[0]->user_uuid, $comment_enabled_user->uuid());
//    $this->assertSame($results[0]->user_name, $comment_enabled_user->getAccountName());
//    $this->assertSame($results[0]->user_role, $comment_enabled_user_role);
//
//    $this->assertSame($results[1]->id, $host2->id());
//    $this->assertSame($results[1]->uuid, $host2->uuid());
//    $this->assertSame($results[1]->user_id, (string) $host2->getOwnerId());
//    $this->assertNull($results[1]->user_uuid);
//    $this->assertNull($results[1]->user_name);
//    $this->assertNull($results[1]->user_role);
//
//
//    $query = $connection->select('users', 'u')
//      ->fields('u', ['uid', 'uuid']);
//    $query->addJoin('INNER', 'comment', 'c', $query->joinCondition()
//      ->compare('u.uid', 'c.comment_translations.uid')
//      ->condition('u.user_translations.user_translations__roles.roles_target_id', $comment_enabled_user_role)
//    );
//    $query->addField('c', 'cid', 'comment_cid');
//    $query->addField('c', 'comment_translations.uid', 'comment_uid');
//    $query->addField('c', 'uuid', 'comment_uuid');
//    $results = $query->execute()->fetchAll();
//
//    $this->assertCount(1, $results);
//    $this->assertSame($results[0]->uid, $comment_enabled_user->id());
//    $this->assertSame($results[0]->uuid, $comment_enabled_user->uuid());
//    $this->assertSame($results[0]->comment_cid, $comment2->id());
//    $this->assertSame($results[0]->comment_uid, $comment2->getOwnerId());
//    $this->assertSame($results[0]->comment_uuid, $comment2->uuid());

      // Get the last role of the user $comment_enabled_user.
      $comment_enabled_user_roles = $comment_enabled_user->getRoles();
dump($comment_enabled_user_roles);
      $query = $connection->select('entity_test', 'e')
        ->fields('e', ['id', 'uuid', 'user_id']);
      $query->addJoin('LEFT', 'users', 'u', $query->joinCondition()->compare('e.user_id', 'u.uid')->condition('u.user_translations.user_translations__roles.roles_target_id', $comment_enabled_user_roles, 'IN'));
      $query->addField('u', 'uuid', 'user_uuid');
      $query->addField('u', 'user_translations.name', 'user_name');
      $query->addField('u', 'user_translations.user_translations__roles.roles_target_id', 'user_role');
      $results = $query->execute()->fetchAll();
dump($results);


  }

}
