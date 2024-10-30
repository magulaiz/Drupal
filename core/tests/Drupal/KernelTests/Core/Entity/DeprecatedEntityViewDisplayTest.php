<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;

/**
 * Tests deprecated entity view displays.
 *
 * @group Entity
 * @group legacy
 */
final class DeprecatedEntityViewDisplayTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'filter',
    'text',
    'options',
    'field',
    'user',
    'deprecated_entity_view_display_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system', 'node', 'filter']);
    // We create this manually rather than using the ContentTypeCreationTrait
    // because we don't want node_add_body_field() to be called, which will
    // create the entity view display we're trying to test.
    NodeType::create([
      'type' => 'page',
      'langcode' => 'en',
      'name' => 'Basic page',
      'description' => "Use <em>basic pages</em> for your static content, such as an 'About us' page.",
    ])->save();
    $this->installConfig('deprecated_entity_view_display_test');
  }

  /**
   * Tests deprecations.
   *
   * @covers \Drupal\Core\Entity\Entity\EntityViewDisplay::preCreate
   * @covers \Drupal\Core\Entity\Entity\EntityViewDisplay::__construct
   */
  public function testDeprecatedEntityViewDisplay(): void {
    $display_repository = $this->container->get(EntityDisplayRepositoryInterface::class);
    $this->expectDeprecation('Creating an entity view display without a value for pageDisplay is deprecated in drupal:11.1.0 and will be required in drupal:12.0.0. Update install configuration to add this key. See https://www.drupal.org/node/3484529');
    $this->expectDeprecation('Creating an entity view display without a value for pageDisplay is deprecated in drupal:11.1.0 and will be required in drupal:12.0.0. Update install configuration to add this key. See https://www.drupal.org/node/3484529');
    $default = $display_repository->getViewDisplay('node', 'page', 'default');
    self::assertFalse($default->hasPageDisplay());
    $full = $display_repository->getViewDisplay('node', 'page', 'full');
    self::assertTrue($full->hasPageDisplay());
    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => $this->randomMachineName(),
      'status' => TRUE,
    ]);

  }

}
