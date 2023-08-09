<?php

declare(strict_types=1);

namespace Drupal\Tests\file\Unit\Upload;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Upload\FileUploadAccessCheck;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests the FileUploadAccessCheck.
 *
 * @group file
 * @coversDefaultClass \Drupal\file\Upload\FileUploadAccessCheck
 */
class FileUploadAccessCheckTest extends UnitTestCase {

  /**
   * The access check under test.
   */
  protected FileUploadAccessCheck $accessCheck;

  /**
   * The entity field manager.
   */
  protected EntityFieldManagerInterface|MockObject $entityFieldManager;

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface|MockObject $entityTypeManager;

  /**
   * The field definition.
   */
  protected FieldDefinitionInterface|MockObject $fieldDefinition;

  /**
   * The entity access control handler.
   */
  protected EntityAccessControlHandlerInterface|MockObject $entityAccessControlHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityAccessControlHandler = $this->createMock(EntityAccessControlHandlerInterface::class);

    $this->entityFieldManager = $this->createMock(EntityFieldManagerInterface::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);

    $entityType = $this->createMock(EntityTypeInterface::class);
    $entityType->expects($this->any())
      ->method('hasKey')
      ->with('bundle')
      ->willReturn(TRUE);

    $this->entityTypeManager->expects($this->any())
      ->method('getDefinition')
      ->with('foo_type')
      ->willReturn($entityType);

    $this->entityTypeManager->expects($this->any())
      ->method('getAccessControlHandler')
      ->with('foo_type')
      ->willReturn($this->entityAccessControlHandler);

    $this->fieldDefinition = $this->createMock(FieldDefinitionInterface::class);
    $this->fieldDefinition->expects($this->any())
      ->method('getSetting')
      ->with('target_type')
      ->willReturn('file');

    $this->accessCheck = new FileUploadAccessCheck($this->entityFieldManager, $this->entityTypeManager);
  }

  /**
   * @covers ::access
   */
  public function testAccessFailedNoFieldDefinition(): void {
    $fieldDefinitions = [];
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->withAnyParameters()
      ->willReturn($fieldDefinitions);

    $account = $this->createMock(AccountInterface::class);
    $result = $this->accessCheck->access($account, 'foo_type', 'bar_bundle', 'whiz_field');

    $this->assertTrue($result->isForbidden());
  }

  /**
   * @covers ::access
   */
  public function testAccessFailedWrongTargetType(): void {
    $fieldDefinition = $this->createMock(FieldDefinitionInterface::class);
    $fieldDefinition->expects($this->once())
      ->method('getSetting')
      ->with('target_type')
      ->willReturn('foo');

    $fieldDefinitions = [
      'whiz_field' => $fieldDefinition,
    ];
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->withAnyParameters()
      ->willReturn($fieldDefinitions);

    $account = $this->createMock(AccountInterface::class);
    $result = $this->accessCheck->access($account, 'foo_type', 'bar_bundle', 'whiz_field');

    $this->assertTrue($result->isForbidden());
  }

  /**
   * @covers ::access
   */
  public function testAccessFailedNoCreateAccess(): void {
    $account = $this->createMock(AccountInterface::class);
    $fieldDefinitions = [
      'whiz_field' => $this->fieldDefinition,
    ];
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->withAnyParameters()
      ->willReturn($fieldDefinitions);

    $this->entityAccessControlHandler->expects($this->once())
      ->method('createAccess')
      ->withAnyParameters()
      ->willReturn(AccessResult::forbidden("test entity create access fail"));

    $this->entityAccessControlHandler->expects($this->once())
      ->method('fieldAccess')
      ->withAnyParameters()
      ->willReturn(AccessResult::allowed());

    $result = $this->accessCheck->access($account, 'foo_type', 'bar_bundle', 'whiz_field');

    $this->assertTrue($result->isForbidden());
  }

  /**
   * @covers ::access
   */
  public function testAccessFailedNoFieldAccess(): void {
    $account = $this->createMock(AccountInterface::class);
    $fieldDefinitions = [
      'whiz_field' => $this->fieldDefinition,
    ];
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->withAnyParameters()
      ->willReturn($fieldDefinitions);

    $this->entityAccessControlHandler->expects($this->once())
      ->method('createAccess')
      ->withAnyParameters()
      ->willReturn(AccessResult::forbidden("test entity create access fail"));

    $this->entityAccessControlHandler->expects($this->once())
      ->method('fieldAccess')
      ->withAnyParameters()
      ->willReturn(AccessResult::forbidden("test field edit access fail"));

    $result = $this->accessCheck->access($account, 'foo_type', 'bar_bundle', 'whiz_field');

    $this->assertTrue($result->isForbidden());
  }

  /**
   * @covers ::access
   */
  public function testAccessSuccess(): void {
    $account = $this->createMock(AccountInterface::class);
    $fieldDefinitions = [
      'whiz_field' => $this->fieldDefinition,
    ];
    $this->entityFieldManager->expects($this->once())
      ->method('getFieldDefinitions')
      ->withAnyParameters()
      ->willReturn($fieldDefinitions);

    $this->entityAccessControlHandler->expects($this->once())
      ->method('createAccess')
      ->withAnyParameters()
      ->willReturn(AccessResult::allowed());

    $this->entityAccessControlHandler->expects($this->once())
      ->method('fieldAccess')
      ->withAnyParameters()
      ->willReturn(AccessResult::allowed());

    $result = $this->accessCheck->access($account, 'foo_type', 'bar_bundle', 'whiz_field');

    $this->assertTrue($result->isAllowed());
  }

}
