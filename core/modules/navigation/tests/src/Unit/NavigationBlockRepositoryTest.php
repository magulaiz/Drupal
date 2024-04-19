<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Unit;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\navigation\NavigationBlockInterface;
use Drupal\navigation\NavigationBlockRepository;
use Drupal\navigation\NavigationBlockRepositoryInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\navigation\NavigationBlockRepository
 * @group navigation
 */
class NavigationBlockRepositoryTest extends UnitTestCase {

  /**
   * The navigation block repository instance.
   *
   * @var \Drupal\navigation\NavigationBlockRepositoryInterface
   */
  protected NavigationBlockRepositoryInterface $navigationBlockRepository;

  /**
   * The navigation block storage mock instance.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $navigationBlockStorage;

  /**
   * The context handler mock instance.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $contextHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->contextHandler = $this->createMock('Drupal\Core\Plugin\Context\ContextHandlerInterface');
    $this->navigationBlockStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject $entity_type_manager */
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->navigationBlockStorage);

    $this->navigationBlockRepository = new NavigationBlockRepository($entity_type_manager, $this->contextHandler);
  }

  /**
   * Tests the retrieval of navigation block entities.
   *
   * @covers ::getVisibleBlocksPerRegion
   *
   * @dataProvider providerBlocksConfig
   */
  public function testGetVisibleNavigationBlocksPerRegion(array $blocks_config, array $expected_blocks) {
    $navigation_blocks = [];
    foreach ($blocks_config as $block_id => $block_config) {
      $block = $this->createMock(NavigationBlockInterface::class);
      $block->expects($this->once())
        ->method('access')
        ->willReturn($block_config[0]);
      $block->expects($block_config[0] ? $this->atLeastOnce() : $this->never())
        ->method('getRegion')
        ->willReturn($block_config[1]);
      $block->expects($this->any())
        ->method('label')
        ->willReturn($block_id);
      $block->expects($this->any())
        ->method('getWeight')
        ->willReturn($block_config[2]);
      $navigation_blocks[$block_id] = $block;
    }

    $this->navigationBlockStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn($navigation_blocks);
    $result = [];
    $cacheable_metadata = [];
    foreach ($this->navigationBlockRepository->getVisibleBlocksPerRegion($cacheable_metadata) as $region => $resulting_blocks) {
      $result[$region] = [];
      foreach ($resulting_blocks as $plugin_id => $block) {
        $result[$region][] = $plugin_id;
      }
    }
    $this->assertEquals($expected_blocks, $result);
  }

  /**
   * Data provider for testGetVisibleNavigationBlocksPerRegion().
   */
  public static function providerBlocksConfig() {
    $blocks_config = [
      'block1' => [
        AccessResult::allowed(), NavigationBlockRepositoryInterface::REGION_FOOTER, 0,
      ],
      // Test a block without access.
      'block2' => [
        AccessResult::forbidden(), NavigationBlockRepositoryInterface::REGION_CONTENT, 0,
      ],
      // Test some blocks in the same region with specific weight.
      'block4' => [
        AccessResult::allowed(), NavigationBlockRepositoryInterface::REGION_CONTENT, 5,
      ],
      'block3' => [
        AccessResult::allowed(), NavigationBlockRepositoryInterface::REGION_CONTENT, 5,
      ],
      'block5' => [
        AccessResult::allowed(), NavigationBlockRepositoryInterface::REGION_CONTENT, -5,
      ],
    ];

    $test_cases = [];
    $test_cases[] = [$blocks_config,
      [
        NavigationBlockRepositoryInterface::REGION_FOOTER => ['block1'],
        NavigationBlockRepositoryInterface::REGION_CONTENT => ['block5', 'block3', 'block4'],
      ],
    ];
    return $test_cases;
  }

  /**
   * Tests the retrieval of navigation block entities that are context-aware.
   *
   * @covers ::getVisibleBlocksPerRegion
   */
  public function testGetVisibleNavigationBlocksPerRegionWithContext() {
    $navigation_block = $this->createMock('Drupal\navigation\NavigationBlockInterface');
    $navigation_block->expects($this->once())
      ->method('access')
      ->willReturn(AccessResult::allowed()->addCacheTags(['config:navigation.navigation_block.block_id']));
    $navigation_block->expects($this->once())
      ->method('getRegion')
      ->willReturn(NavigationBlockRepositoryInterface::REGION_CONTENT);
    $blocks['block_id'] = $navigation_block;

    $this->navigationBlockStorage->expects($this->once())
      ->method('loadMultiple')
      ->willReturn($blocks);
    $result = [];
    $cacheable_metadata = [];
    foreach ($this->navigationBlockRepository->getVisibleBlocksPerRegion($cacheable_metadata) as $region => $resulting_blocks) {
      $result[$region] = [];
      foreach ($resulting_blocks as $plugin_id => $navigation_block) {
        $result[$region][] = $plugin_id;
      }
    }
    $expected = [
      NavigationBlockRepositoryInterface::REGION_CONTENT => [
        'block_id',
      ],
      NavigationBlockRepositoryInterface::REGION_FOOTER => [],
    ];
    $this->assertSame($expected, $result);

    // Assert that the cacheable metadata from the navigation block access
    // results was collected.
    $this->assertEquals(['config:navigation.navigation_block.block_id'], $cacheable_metadata[NavigationBlockRepositoryInterface::REGION_CONTENT]->getCacheTags());
  }

}
