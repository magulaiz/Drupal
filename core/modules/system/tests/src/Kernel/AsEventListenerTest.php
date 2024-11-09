<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel;

use Drupal\Component\EventDispatcher\Event;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that module classes using AsEventListener attribute.
 *
 * @group system
 */
class AsEventListenerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['event_listener_test'];

  public function testAsEventListenerSubscriber(): void {
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher */
    $dispatcher = $this->container->get('event_dispatcher');
    $dispatcher->dispatch(new Event());
    /** @var \Drupal\Core\State\StateInterface $state */
    $state = $this->container->get('state');
    $this->assertTrue($state->get('event_listener_test_invoke'));
    $this->assertTrue($state->get('event_listener_test_on_event'));
  }

}
