<?php

declare(strict_types=1);

namespace Drupal\system\Tests\TypedData;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Options\SimpleOptionsProviderBase;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests using option providers.
 *
 * @group TypedData
 */
class OptionsProviderTest extends KernelTestBase {

  /**
   * Returns a sample options array.
   *
   * @return int[]
   *   A sample options array.
   */
  public static function getOptions() {
    return [
      1 => 1,
      2 => 2,
      3 => 3,
    ];
  }

  /**
   * Tests using a callable options provider.
   */
  public function testCallableOptionsProvider() {
    $definition = DataDefinition::create('int')
      ->setOptionsProviderDefinition(self::class . '::getOptions');

    $provider = $definition->getOptionsProvider();
    $this->assertNotNull($provider);
    $this->assertEquals($provider->getPossibleOptions(), self::getOptions());
    $this->assertEquals($provider->getPossibleValues(), array_keys(self::getOptions()));
    $this->assertEquals($provider->getSettableOptions(), self::getOptions());
    $this->assertEquals($provider->getSettableValues(), array_keys(self::getOptions()));
  }

  /**
   * Tests using a simple options provider.
   */
  public function testSimpleOptionsProviderBase() {
    $definition = DataDefinition::create('int')
      ->setOptionsProviderDefinition(SimpleOptionsProvider::class);
    $expected = [1 => 1];

    $provider = $definition->getOptionsProvider();
    $this->assertNotNull($provider);
    $this->assertEquals($provider->getPossibleOptions(), $expected);
    $this->assertEquals($provider->getPossibleValues(), array_keys($expected));
    $this->assertEquals($provider->getSettableOptions(), $expected);
    $this->assertEquals($provider->getSettableValues(), array_keys($expected));
  }

  // @todo Test definition aware and dependent options providers.

}

/**
 * Helper class for testing simple option providers.
 */
class SimpleOptionsProvider extends SimpleOptionsProviderBase {

  /**
   * {@inheritdoc}
   */
  public function getPossibleOptions(?AccountInterface $account = NULL) {
    return [1 => 1];
  }

}
