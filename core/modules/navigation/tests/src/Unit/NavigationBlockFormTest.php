<?php

namespace Drupal\Tests\navigation\Unit;

use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\navigation\Entity\NavigationBlock;
use Drupal\navigation\Form\NavigationBlockForm;
use Drupal\navigation\NavigationBlockPluginBase;
use Drupal\navigation\NavigationBlockRepository;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\navigation\Form\NavigationBlockForm
 * @group navigation
 */
class NavigationBlockFormTest extends UnitTestCase {

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $conditionManager;

  /**
   * The navigation block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $storage;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $language;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The mocked context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $contextHandler;

  /**
   * The mocked context repository.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $contextRepository;

  /**
   * The plugin form manager.
   *
   * @var \Drupal\Core\Plugin\PluginFormFactoryInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $pluginFormFactory;

  /**
   * The navigation block repository.
   *
   * @var \Drupal\navigation\NavigationBlockRepositoryInterface
   */
  protected $navigationBlockRepository;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->conditionManager = $this->createMock('Drupal\Core\Executable\ExecutableManagerInterface');
    $this->language = $this->createMock('Drupal\Core\Language\LanguageManagerInterface');
    $this->contextRepository = $this->createMock('Drupal\Core\Plugin\Context\ContextRepositoryInterface');

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->storage = $this->createMock('Drupal\Core\Config\Entity\ConfigEntityStorageInterface');
    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->storage);

    $this->pluginFormFactory = $this->prophesize(PluginFormFactoryInterface::class);

    $this->contextHandler = $this->createMock('Drupal\Core\Plugin\Context\ContextHandlerInterface');
    $this->navigationBlockRepository = new NavigationBlockRepository($this->entityTypeManager, $this->contextHandler);
  }

  /**
   * Mocks a navigation block with a navigation block plugin.
   *
   * @param string $machine_name
   *   The machine name of the navigation block plugin.
   *
   * @return \Drupal\navigation\NavigationBlockInterface|\PHPUnit\Framework\MockObject\MockObject
   *   The mocked navigation block.
   */
  protected function getNavigationBlockMockWithMachineName($machine_name) {
    $plugin = $this->createMock(NavigationBlockPluginBase::class);
    $plugin->expects($this->any())
      ->method('getMachineNameSuggestion')
      ->willReturn($machine_name);

    $navigation_block = $this->createMock(NavigationBlock::class);
    $navigation_block->expects($this->any())
      ->method('getPlugin')
      ->willReturn($plugin);
    return $navigation_block;
  }

  /**
   * Tests the unique machine name generator.
   *
   * @see \Drupal\block\BlockForm::getUniqueMachineName()
   */
  public function testGetUniqueMachineName() {
    $navigation_blocks = [];

    $navigation_blocks['test'] = $this->getNavigationBlockMockWithMachineName('test');
    $navigation_blocks['other_test'] = $this->getNavigationBlockMockWithMachineName('other_test');
    $navigation_blocks['other_test_1'] = $this->getNavigationBlockMockWithMachineName('other_test');
    $navigation_blocks['other_test_2'] = $this->getNavigationBlockMockWithMachineName('other_test');

    $query = $this->createMock('Drupal\Core\Entity\Query\QueryInterface');
    $query->expects($this->exactly(5))
      ->method('condition')
      ->willReturn($query);

    $query->expects($this->exactly(5))
      ->method('execute')
      ->willReturn(['test', 'other_test', 'other_test_1', 'other_test_2']);

    $this->storage->expects($this->exactly(5))
      ->method('getQuery')
      ->willReturn($query);

    $navigation_block_form = new NavigationBlockForm($this->entityTypeManager, $this->conditionManager, $this->contextRepository, $this->language, $this->pluginFormFactory->reveal(), $this->navigationBlockRepository);

    // Ensure that the navigation block with just one other instance gets
    // the next available name suggestion.
    $this->assertEquals('test_2', $navigation_block_form->getUniqueMachineName($navigation_blocks['test']));

    // Ensure that the navigation block with already three instances (_0, _1, _
    // 2) gets the 4th available name.
    $this->assertEquals('other_test_3', $navigation_block_form->getUniqueMachineName($navigation_blocks['other_test']));
    $this->assertEquals('other_test_3', $navigation_block_form->getUniqueMachineName($navigation_blocks['other_test_1']));
    $this->assertEquals('other_test_3', $navigation_block_form->getUniqueMachineName($navigation_blocks['other_test_2']));

    // Ensure that a navigation block without an instance yet gets the
    // suggestion as unique machine name.
    $last_block = $this->getNavigationBlockMockWithMachineName('last_test');
    $this->assertEquals('last_test', $navigation_block_form->getUniqueMachineName($last_block));
  }

}
