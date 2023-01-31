<?php

namespace Drupal\Tests\user\Kernel\Views;

use Drupal\views\Views;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\Plugin\views\argument_default\User as ArgumentDefault;

/**
 * Tests the user ID from route views default argument.
 *
 * @group user
 *
 * @see \Drupal\user\Plugin\views\argument_default\User
 */
class UserIdFromRouteViewsDefaultArgumentTest extends UserKernelTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_argument_default_user_id_from_route_context'];

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE): void {
    parent::setUp($import_test_views);
    $this->setupPermissionTestData();
  }

  /**
   * Tests that right user ID is returned from the user entity in the route.
   */
  public function testgetArgumentUserInterface() {
    $current_route = \Drupal::service('current_route_match')->getCurrentRouteMatch();
    $user_1 = User::load($this->users[0]->id());
    $user_2 = User::load($this->users[1]->id());
    $view = Views::getView('test_argument_default_user_id_from_route_context');

    $view->setDisplay();
    $this->executeView($view);
    $uid = 'not null';
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertNull($uid, 'Null uid should be fetched from the route.');

    $current_route->getParameters()->set('user_entity', $user_1);
    $view->setDisplay();
    $this->executeView($view);
    $uid_1 = $user_1->id();
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertEquals($uid_1, $uid, "Expected $uid_1 equals actual $uid.");

    $current_route->getParameters()->remove('user_entity');
    $view->setDisplay();
    $this->executeView($view);
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertNull($uid, 'Null uid should be fetched from the route.');

    $current_route->getParameters()->set('user_entity', $user_2);
    $view->setDisplay();
    $this->executeView($view);
    $uid_2 = $user_2->id();
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertEquals($uid_2, $uid, "Expected $uid_2 equals actual $uid.");

    $view->destroy();
  }

  /**
   * Tests that right user ID is returned from the node entity in the route.
   */
  public function testgetArgumentEntityOwnerInterface() {
    $this->installEntitySchema('node');
    $current_route = \Drupal::service('current_route_match')->getCurrentRouteMatch();
    $view = Views::getView('test_argument_default_user_id_from_route_context');
    $user_1 = User::load($this->users[0]->id());
    $user_2 = User::load($this->users[1]->id());

    $node_1 = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node_1->setOwner($user_1);
    $node_1->save();

    $node_2 = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
    ]);
    $node_2->setOwner($user_2);
    $node_2->save();

    $view->setDisplay();
    $this->executeView($view);
    $uid = 'not null';
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertNull($uid, 'Null uid should be fetched from the route.');

    $current_route->getParameters()->set('node_entity', $node_1);
    $view->setDisplay();
    $this->executeView($view);
    $uid_1 = $node_1->getOwnerId();
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertEquals($uid_1, $uid, "Expected $uid_1 equals actual $uid.");

    $current_route->getParameters()->remove('node_entity');
    $view->setDisplay();
    $this->executeView($view);
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertNull($uid, 'Null uid should be fetched from the route.');

    $current_route->getParameters()->set('node_entity', $node_2);
    $view->setDisplay();
    $this->executeView($view);
    $uid_2 = $node_2->getOwnerId();
    foreach ($view->argument as $argument) {
      if (($plugin = $argument->getPlugin('argument_default')) && $plugin instanceof ArgumentDefault) {
        $uid = $plugin->getArgument();
      }
    }
    $this->assertEquals($uid_2, $uid, "Expected $uid_2 equals actual $uid.");

    $view->destroy();
  }

}
