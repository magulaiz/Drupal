<?php

declare(strict_types=1);

namespace Drupal\Tests\content_moderation\Kernel;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\content_moderation\Entity\ContentModerationState;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group content_moderation
 */
#[CoversClass(\Drupal\content_moderation\ContentModerationStateAccessControlHandler::class)]
class ContentModerationStateAccessControlHandlerTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'content_moderation',
    'workflows',
    'user',
  ];

  /**
   * The content_moderation_state access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $accessControlHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('content_moderation_state');
    $this->installEntitySchema('user');
    $this->accessControlHandler = $this->container->get('entity_type.manager')->getAccessControlHandler('content_moderation_state');
  }

  public function testHandler() {
    $entity = ContentModerationState::create([]);
    $this->assertFalse($this->accessControlHandler->access($entity, 'view'));
    $this->assertFalse($this->accessControlHandler->access($entity, 'update'));
    $this->assertFalse($this->accessControlHandler->access($entity, 'delete'));
    $this->assertFalse($this->accessControlHandler->createAccess());
  }

}
