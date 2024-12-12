<?php

declare(strict_types=1);

namespace Drupal\Core\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides an interface for the event dispatcher factory.
 */
interface EventDispatcherFactoryInterface {

  public static function createInstance(EventDispatcherFactoryStage $stage = EventDispatcherFactoryStage::PreBoot): EventDispatcherInterface;

  public function getInstance(): EventDispatcherInterface;

  public function getInstanceStage(): EventDispatcherFactoryStage;

}
