<?php

namespace Drupal\Tests\views\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\views\Exception\InvalidViewsDataException;
use Drupal\views\ManyToOneHelper;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Plugin\views\join\JoinPluginBase;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\Plugin\ViewsHandlerManager;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewsData;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\views\ManyToOneHelper
 * @group views
 */
class ManyToOneHelperTest extends UnitTestCase {

  /**
   * @covers ::addTable
   */
  public function testAddTable(): void {
    $view = $this->prophesize(ViewExecutable::class);
    $join = $this->prophesize(JoinPluginBase::class);
    $query = $this->prophesize(Sql::class);

    $join->leftTable = 'base_table';
    $join->table = 'base_table';

    $query->addTable('table', 'relationship', Argument::any(), NULL)
      ->willReturn('alias');

    $query->relationships = [
      'relationship' => [
        'base' => 'base_table',
      ],
    ];

    $many_to_one_helper = $this->getManyToOneHelperWithRelationship($join, $view, $query);
    $this->assertEquals('alias', $many_to_one_helper->addTable());
  }

  /**
   * @covers ::addTable
   */
  public function testAddTableWithNonExistingTable(): void {
    $container = new ContainerBuilder();
    $views_data = $this->prophesize(ViewsData::class);
    // Test a non-existent table.
    $views_data->get('non_existing_left_table')->willReturn('');
    $container->set('views.views_data', $views_data->reveal());
    \Drupal::setContainer($container);

    $view = $this->prophesize(ViewExecutable::class);
    $join = $this->prophesize(JoinPluginBase::class);
    $query = $this->prophesize(Sql::class);

    $join->leftTable = 'non_existing_left_table';
    $join->table = 'base_table';

    $query->addTable('table', 'relationship', Argument::any(), NULL)
      ->willReturn('alias');

    $query->relationships = [
      'relationship' => [
        'base' => 'base_table',
      ],
    ];

    $many_to_one_helper = $this->getManyToOneHelperWithRelationship($join, $view, $query);
    $this->expectException(InvalidViewsDataException::class);

    // Test that an exception is thrown when using a non-existing table.
    $many_to_one_helper->addTable();
  }

  /**
   * @covers ::addTable
   */
  public function testAddTableWithCorruptData(): void {
    $join = $this->prophesize(JoinPluginBase::class);
    $join->leftTable = 'corrupt_left_table';
    $join->table = 'corrupt_left_table';

    $container = new ContainerBuilder();
    $views_data = $this->prophesize(ViewsData::class);
    // Test a non-existent table.
    $views_data->get('corrupt_left_table')->willReturn(['table' => ['join' => ['base_table' => []]]]);

    $views_join_plugin_manager = $this->prophesize(ViewsHandlerManager::class);
    $views_join_plugin_manager->createInstance(Argument::any(), Argument::any())->willReturn($join->reveal());

    $container->set('views.views_data', $views_data->reveal());
    $container->set('plugin.manager.views.join', $views_join_plugin_manager->reveal());

    \Drupal::setContainer($container);

    $view = $this->prophesize(ViewExecutable::class);
    $query = $this->prophesize(Sql::class);

    $query->addTable('table', 'relationship', Argument::any(), NULL)
      ->willReturn('alias');

    $query->relationships = [
      'relationship' => [
        'base' => 'base_table',
      ],
    ];

    $many_to_one_helper = $this->getManyToOneHelperWithRelationship($join, $view, $query);

    $this->expectException(InvalidViewsDataException::class);

    // Test that an exception is thrown when an infinite loop is caused.
    $many_to_one_helper->addTable();
  }

  /**
   * Builds a ManyToOneHelper with a relationship handler.
   *
   * @param \Prophecy\Prophecy\ObjectProphecy $join
   *   The join to use for the relationship.
   * @param \Prophecy\Prophecy\ObjectProphecy $view
   *   The View to use in the handler.
   * @param \Prophecy\Prophecy\ObjectProphecy $query
   *   The query to use in the handler.
   *
   * @return \Drupal\views\ManyToOneHelper
   *   Returns a ManyToOneHelper.
   */
  protected function getManyToOneHelperWithRelationship(ObjectProphecy $join, ObjectProphecy $view, ObjectProphecy $query): ManyToOneHelper {
    $handler = $this->prophesize(FilterPluginBase::class);
    $handler->getJoin()->willReturn($join->reveal());
    $handler->relationship = 'relationship';
    $handler->table = 'table';
    $handler->field = 'field';
    $handler->value = 'value';
    $handler->view = $view->reveal();
    $handler->query = $query->reveal();
    return new ManyToOneHelper($handler->reveal());
  }

}
