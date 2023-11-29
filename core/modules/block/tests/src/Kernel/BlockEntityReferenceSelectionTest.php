<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Kernel;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\field\Traits\EntityReferenceFieldCreationTrait;

/**
 * Tests block entity reference selection plugin.
 *
 * @group entity_reference
 */
class BlockEntityReferenceSelectionTest extends KernelTestBase {

  use EntityReferenceFieldCreationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'block',
    'block_test',
    'system',
    'user',
    'field',
    'entity_test',
  ];

  /**
   * The selection handler service.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface
   */
  protected SelectionInterface $selectionHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->get('theme_installer')->install(['stark']);
    $this->installEntitySchema('entity_test');

    $field_name = $this->randomMachineName();
    // Create new entity reference field for the entity_test field.
    // Block categories mentioned in the plugin settings are provided by
    // block_test module's multiple block plugins.
    $this->createEntityReferenceField('entity_test', 'entity_test', $field_name, $this->randomMachineName(), 'block', 'default:block', [
      'block_categories' => [
        'Form' => 1,
        'Context' => 1,
      ],
    ]);
    $field_config = FieldConfig::loadByName('entity_test', 'entity_test', $field_name);
    $this->selectionHandler = $this->container->get('plugin.manager.entity_reference_selection')->getSelectionHandler($field_config);

    $block_manager = $this->container->get('plugin.manager.block');
    foreach ($block_manager->getDefinitions() as $block_id => $definition) {
      $id = sprintf('stark_%s', $block_id);
      $block = Block::create([
        'id' => $id,
        'theme' => 'stark',
        'weight' => 00,
        'status' => TRUE,
        'region' => 'content',
        'plugin' => $block_id,
        'settings' => [
          'label' => (string) $definition['admin_label'],
          'provider' => 'system',
          'label_display' => FALSE,
        ],
        'visibility' => [],
      ]);
      $block->save();
    }
  }

  /**
   * Test the outcomes of Block selection entity reference plugin.
   *
   * @dataProvider providerTestCases
   */
  public function testBlockSelectionReference(string $match, string $match_operator, int $limit, array $items): void {
    $referenceable = $this->selectionHandler->getReferenceableEntities($match, $match_operator, $limit);
    foreach ($items as $item) {
      $this->assertContains($item, $referenceable['block']);
    }
  }

  /**
   * Provides test cases for ::testBlockSelectionReference() test.
   *
   * @return array[]
   */
  public function providerTestCases(): array {
    return [
      ['test', 'CONTAINS', 5, [
        'Test context-aware block',
        'Test context-aware block - no valid context options',
        'Test context-aware unsatisfied block',
        'Test form block caching',
        'Multiple forms test block',
      ],
      ],
      ['form', 'CONTAINS', 5, [
        'Multiple forms test block',
        'Test form block caching',
      ],
      ],
      ['block', 'ENDS_WITH', 5, [
        'Test context-aware unsatisfied block',
        'Test context-aware block',
        'Multiple forms test block',
      ],
      ],
      ['multiple', 'STARTS_WITH', 5, [
        'Multiple forms test block',
      ],
      ],
    ];
  }

}
