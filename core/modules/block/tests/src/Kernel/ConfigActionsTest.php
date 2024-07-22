<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Kernel;

use Drupal\block\Entity\Block;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\block\Traits\BlockCreationTrait;

/**
 * @group image
 */
class ConfigActionsTest extends KernelTestBase {

  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'system'];

  private readonly ConfigActionManager $configActionManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->get(ThemeInstallerInterface::class)->install(['stark']);
    $this->configActionManager = $this->container->get('plugin.manager.config_action');
  }

  public function testConfigActions(): void {
    $block = $this->placeBlock('system_messages_block', ['theme' => 'stark']);
    $this->assertSame('content', $block->getRegion());
    $this->assertSame(0, $block->getWeight());

    $this->configActionManager->applyAction(
      'entity_method:block.block:setRegion',
      $block->getConfigDependencyName(),
      'highlighted',
    );
    $this->configActionManager->applyAction(
      'entity_method:block.block:setWeight',
      $block->getConfigDependencyName(),
      -10,
    );

    $block = Block::load($block->id());
    $this->assertSame('highlighted', $block->getRegion());
    $this->assertSame(-10, $block->getWeight());
  }

}
