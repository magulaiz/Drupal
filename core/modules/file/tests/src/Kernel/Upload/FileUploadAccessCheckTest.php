<?php

declare(strict_types=1);

namespace Drupal\Tests\file\Kernel\Upload;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Upload\FileUploadAccessCheck;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\file\Functional\FileFieldCreationTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the FileUploadAccessCheck.
 *
 * @group file
 * @coversDefaultClass \Drupal\file\Upload\FileUploadAccessCheck
 */
class FileUploadAccessCheckTest extends KernelTestBase {

  use ContentTypeCreationTrait;
  use EntityReferenceTestTrait;
  use FileFieldCreationTrait;
  use NodeCreationTrait;
  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'field',
    'file',
    'filter',
    'node',
    'system',
    'text',
    'user',
  ];

  /**
   * The article editor.
   */
  protected AccountInterface $articleEditor;

  /**
   * The page editor.
   */
  protected AccountInterface $pageEditor;

  /**
   * The editor.
   */
  protected AccountInterface $editor;

  /**
   * The no access user.
   */
  protected AccountInterface $noAccessUser;

  /**
   * The article.
   */
  protected NodeInterface $article;

  /**
   * The file upload access check under test.
   */
  protected FileUploadAccessCheck $accessCheck;

  /**
   * An associative array of accounts keyed by account name.
   */
  protected array $accounts;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Add the entity schemas.
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    // Add the additional table schemas.
    $this->installSchema('node', ['node_access']);
    $this->installSchema('user', ['users_data']);

    $this->installConfig(['node', 'filter']);

    $this->createContentType(['type' => 'lorem']);
    $this->createContentType(['type' => 'article']);
    $this->createContentType(['type' => 'page']);

    $this->createFileField('field_file', 'node', 'article', [], ['file_extensions' => 'txt']);
    $this->createEntityReferenceField(
      'node',
      'article',
      'field_relationships',
      'Relationship',
      'node',
      'default',
      ['target_bundles' => ['article']],
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    );

    $this->accounts['article_editor'] = $this->createUser([
      'access content',
      'create article content',
      'edit any article content',
    ], 'article_editor');

    $this->accounts['page_editor'] = $this->createUser([
      'access content',
      'create page content',
      'edit any page content',
    ], 'page_editor');

    $this->accounts['super_editor'] = $this->createUser([
      'bypass node access',
    ], 'super editor');

    $this->accounts['no_access'] = $this->createUser([], 'no_access');

    $this->article = $this->createNode(['type' => 'article']);

    $this->accessCheck = $this->container->get('file.upload_access_check');
  }

  /**
   * @covers ::access
   * @dataProvider accessProvider
   */
  public function testAccess(string $accountName, string $entityTypeId, ?string $bundle, string $fieldName, string $expectedResultType, ?string $expectedReason = NULL): void {
    $account = $this->accounts[$accountName];
    $actualResult = $this->accessCheck->access($account, $entityTypeId, $bundle, $fieldName);
    $this->assertInstanceOf($expectedResultType, $actualResult);
    if ($actualResult instanceof AccessResultReasonInterface) {
      $this->assertEquals($expectedReason, $actualResult->getReason());
    }
  }

  /**
   * Data provider for ::testAccess.
   */
  public function accessProvider(): array {
    return [
      'article editor can create an article file field' => [
        'article_editor',
        'node',
        'article',
        'field_file',
        AccessResultAllowed::class,
      ],
      'article editor cannot create a file field on an unknown type' => [
        'article_editor',
        'foo',
        'bar',
        'baz',
        AccessResultForbidden::class,
        'The "foo" entity type does not exist.',
      ],
      'article editor cannot create an unknown field' => [
        'article_editor',
        'node',
        'article',
        'field_foobar',
        AccessResultForbidden::class,
        '"field_foobar" does not exist',
      ],
      'article editor cannot create a non-file field' => [
        'article_editor',
        'node',
        'article',
        'field_relationships',
        AccessResultForbidden::class,
        '"field_relationships" is not a file field',
      ],
      'page editor cannot create a file field on an article' => [
        'page_editor',
        'node',
        'article',
        'field_file',
        AccessResultNeutral::class,
      ],
      'super editor can create a file field on an article' => [
        'super_editor',
        'node',
        'article',
        'field_file',
        AccessResultAllowed::class,
      ],
      'no access user can cannot create a file field on an article' => [
        'no_access',
        'node',
        'article',
        'field_file',
        AccessResultForbidden::class,
        'The \'access content\' permission is required.',
      ],
    ];
  }

}
