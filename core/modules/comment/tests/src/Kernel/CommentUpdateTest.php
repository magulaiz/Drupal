<?php

declare(strict_types=1);

namespace Drupal\Tests\comment\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests updates for comment module.
 *
 * @group comment
 */
class CommentUpdateTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'comment',
    'user',
    'field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('comment');
  }

  /**
   * Tests that comment_update_10200 hook updates the size of comment fields.
   */
  public function test10200UpdateUpdatesSubjectFieldLength(): void {

    $connection = $this->container->get('database');

    // Simulate the old spec with the old length.
    $connection->schema()->changeField('comment_field_data', 'subject', 'subject', [
      'type' => 'varchar',
      'length' => 64,
    ]);

    // Run the update hook.
    $sandbox = [];
    include_once \Drupal::service('extension.list.module')->getPath('comment') . '/comment.install';
    comment_update_10200($sandbox);

    // Put the fields in an array keyed by field name.
    $fields = [];
    foreach ($connection->query('DESC {comment_field_data}')->fetchAll() as $field) {
      $fields[$field->Field] = $field;
    }

    // Check that the subject field has been updated.
    $this->assertEquals('varchar(255)', $fields['subject']->Type);
  }

}
