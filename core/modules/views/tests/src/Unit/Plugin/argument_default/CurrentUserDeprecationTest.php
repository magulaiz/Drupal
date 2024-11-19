<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Unit\Plugin\argument_default;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Plugin\views\argument_default\CurrentUser;

/**
 * Tests the deprecation notices of the current user default argument.
 *
 * @group legacy
 */
class CurrentUserDeprecationTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $container = new Container();
    $current_user = $this->prophesize(AccountProxyInterface::class);
    $container->set('current_user', $current_user->reveal());
    \Drupal::setContainer($container);
  }

  /**
   * Tests the deprecation in the constructor.
   */
  public function testConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\user\Plugin\views\argument_default\CurrentUser::__construct() without the $current_user argument is deprecated in drupal:11.2.0 and is required in drupal:12.0.0. See https://www.drupal.org/node/3347878');
    new CurrentUser([], '', []);
  }

}
