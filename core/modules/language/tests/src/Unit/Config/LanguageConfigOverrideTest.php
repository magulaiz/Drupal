<?php

declare(strict_types=1);

namespace Drupal\Tests\language\Unit\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\language\Config\LanguageConfigOverride;
use Drupal\Tests\UnitTestCase;

/**
 * @group Config
 * @group language
 */
#[CoversClass(\Drupal\language\Config\LanguageConfigOverride::class)]
class LanguageConfigOverrideTest extends UnitTestCase {

  /**
   * Language configuration override.
   *
   * @var \Drupal\language\Config\LanguageConfigOverride
   */
  protected $configTranslation;

  /**
   * Storage.
   *
   * @var \Drupal\Core\Config\StorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $storage;

  /**
   * Event Dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $eventDispatcher;

  /**
   * Typed Config.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $typedConfig;

  /**
   * The mocked cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cacheTagsInvalidator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->storage = $this->createMock('Drupal\Core\Config\StorageInterface');
    $this->eventDispatcher = $this->createMock('Symfony\Contracts\EventDispatcher\EventDispatcherInterface');
    $this->typedConfig = $this->createMock('\Drupal\Core\Config\TypedConfigManagerInterface');
    $this->configTranslation = new LanguageConfigOverride('config.test', $this->storage, $this->typedConfig, $this->eventDispatcher);
    $this->cacheTagsInvalidator = $this->createMock('Drupal\Core\Cache\CacheTagsInvalidatorInterface');

    $container = new ContainerBuilder();
    $container->set('cache_tags.invalidator', $this->cacheTagsInvalidator);
    \Drupal::setContainer($container);
  }

  public function testSaveNew() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')
      ->with(['config:config.test']);
    $this->assertTrue($this->configTranslation->isNew());
    $this->configTranslation->save();
  }

  public function testSaveExisting() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')
      ->with(['config:config.test']);
    $this->configTranslation->initWithData([]);
    $this->configTranslation->save();
  }

  public function testDelete() {
    $this->cacheTagsInvalidator->expects($this->once())
      ->method('invalidateTags')
      ->with(['config:config.test']);
    $this->configTranslation->initWithData([]);
    $this->configTranslation->delete();
  }

}
