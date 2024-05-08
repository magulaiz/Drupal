<?php

declare(strict_types=1);

namespace Drupal\Tests\path\Unit\Field;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Tests\Core\Field\BaseFieldDefinitionTestBase;

/**
 * @group path
 */
#[CoversClass(\Drupal\Core\Field\BaseFieldDefinition::class)]
class PathFieldDefinitionTest extends BaseFieldDefinitionTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getPluginId() {
    return 'path';
  }

  /**
   * {@inheritdoc}
   */
  protected function getModuleAndPath() {
    return ['path', dirname(__DIR__, 4)];
  }

  public function testGetColumns() {
    $this->assertSame([], $this->definition->getColumns());
  }

}
