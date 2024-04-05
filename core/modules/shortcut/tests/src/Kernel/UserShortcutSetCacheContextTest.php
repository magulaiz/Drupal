<?php

declare(strict_types=1);

namespace Drupal\Tests\shortcut\Kernel;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\shortcut\Cache\Context\UserShortcutSetCacheContext;
use Drupal\shortcut\Entity\ShortcutSet;
use Drupal\shortcut\ShortcutSetInterface;
use Drupal\user\Entity\User;

/**
 * @coversDefaultClass \Drupal\shortcut\Cache\Context\UserShortcutSetCacheContext
 * @group Cache
 */
class UserShortcutSetCacheContextTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['link', 'shortcut', 'system', 'user'];

  /**
   * The shortcut set created for testing purposes.
   *
   * @var \Drupal\shortcut\ShortcutSetInterface
   */
  protected ShortcutSetInterface $shortcutSet;

  /**
   * User account created for testing purposes.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $user;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig('shortcut');
    $this->installEntitySchema('shortcut');
    $this->installEntitySchema('user');
    $this->installSchema('shortcut', 'shortcut_set_users');

    $this->shortcutSet = ShortcutSet::create([
      'id' => 'test-shortcut-set',
      'label' => 'Test',
    ]);
    $this->shortcutSet->save();

    $this->user = User::create([
      'name' => 'first',
      'mail' => 'first@example.com',
    ]);
    $this->user->save();

    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

  /**
   * Tests cache context behavior depending on shortcut set assignments.
   *
   * @covers ::getContext
   * @covers ::getCacheableMetadata
   */
  public function testCacheContext(): void {
    $shortcut_set_storage = $this->entityTypeManager->getStorage('shortcut_set');
    $context = new UserShortcutSetCacheContext($this->user, $this->entityTypeManager);

    // Default shortcut set is assigned by default.
    $this->assertSame('default', $context->getContext());
    $this->assertSame(['config:shortcut.set.default'], $context->getCacheableMetadata()->getCacheTags());

    // Context is updated once the user is assigned to a new one
    $shortcut_set_storage->assignUser($this->shortcutSet, $this->user);
    $this->assertSame('test-shortcut-set', $context->getContext());
    $this->assertSame(['config:shortcut.set.test-shortcut-set'], $context->getCacheableMetadata()->getCacheTags());

    // Back to default after removing assignment.
    $shortcut_set_storage->unassignUser($this->user);
    $this->assertSame('default', $context->getContext());
    $this->assertSame(['config:shortcut.set.default'], $context->getCacheableMetadata()->getCacheTags());

    // Reassign and confirm that bak to default after deletion of assignments.
    $shortcut_set_storage->assignUser($this->shortcutSet, $this->user);
    $this->assertSame('test-shortcut-set', $context->getContext());
    $this->assertSame(['config:shortcut.set.test-shortcut-set'], $context->getCacheableMetadata()->getCacheTags());

    $shortcut_set_storage->deleteAssignedShortcutSets($this->shortcutSet);
    $this->assertSame('default', $context->getContext());
    $this->assertSame(['config:shortcut.set.default'], $context->getCacheableMetadata()->getCacheTags());

    // Reassign and confirm that bak to default after deletion of shortcut set.
    $shortcut_set_storage->assignUser($this->shortcutSet, $this->user);
    $this->assertSame('test-shortcut-set', $context->getContext());
    $this->assertSame(['config:shortcut.set.test-shortcut-set'], $context->getCacheableMetadata()->getCacheTags());

    $this->shortcutSet->delete();
    $this->assertSame('default', $context->getContext());
    $this->assertSame(['config:shortcut.set.default'], $context->getCacheableMetadata()->getCacheTags());
  }

}
