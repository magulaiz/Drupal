<?php

declare(strict_types=1);

namespace Drupal\Tests\path_alias\Kernel;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\KernelTests\KernelTestBase;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\path_alias\Entity\PathAlias;
use Prophecy\Argument;

/**
 * @group path_alias
 */
#[CoversClass(\Drupal\path_alias\Entity\PathAlias::class)]
class PathHooksTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['path_alias'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('path_alias');
  }

  /**
   * Tests that the PathAlias entity clears caches correctly.
   */
  public function testPathHooks() {
    $path_alias = PathAlias::create([
      'path' => '/' . $this->randomMachineName(),
      'alias' => '/' . $this->randomMachineName(),
    ]);

    // Check \Drupal\path_alias\Entity\PathAlias::postSave() for new path alias
    // entities.
    $alias_manager = $this->prophesize(AliasManagerInterface::class);
    $alias_manager->cacheClear(Argument::any())->shouldBeCalledTimes(1);
    $alias_manager->cacheClear($path_alias->getPath())->shouldBeCalledTimes(1);
    \Drupal::getContainer()->set('path_alias.manager', $alias_manager->reveal());
    $path_alias->save();

    $new_source = '/' . $this->randomMachineName();

    // Check \Drupal\path_alias\Entity\PathAlias::postSave() for existing path
    // alias entities.
    $alias_manager = $this->prophesize(AliasManagerInterface::class);
    $alias_manager->cacheClear(Argument::any())->shouldBeCalledTimes(2);
    $alias_manager->cacheClear($path_alias->getPath())->shouldBeCalledTimes(1);
    $alias_manager->cacheClear($new_source)->shouldBeCalledTimes(1);
    \Drupal::getContainer()->set('path_alias.manager', $alias_manager->reveal());
    $path_alias->setPath($new_source);
    $path_alias->save();

    // Check \Drupal\path_alias\Entity\PathAlias::postDelete().
    $alias_manager = $this->prophesize(AliasManagerInterface::class);
    $alias_manager->cacheClear(Argument::any())->shouldBeCalledTimes(1);
    $alias_manager->cacheClear($new_source)->shouldBeCalledTimes(1);
    \Drupal::getContainer()->set('path_alias.manager', $alias_manager->reveal());
    $path_alias->delete();
  }

}
