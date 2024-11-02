<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Security;

use Drupal\Core\Security\Attribute\TrustedCallback;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Security\DoTrustedCallbackTrait;
use Drupal\Core\Security\UntrustedCallbackException;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Security\DoTrustedCallbackTrait
 * @group Security
 */
class DoTrustedCallbackTraitTest extends UnitTestCase {
  use DoTrustedCallbackTrait;

  /**
   * @covers ::doTrustedCallback
   * @dataProvider providerTestTrustedCallbacks
   * @group legacy
   */
  public function testTrustedCallbacks(callable $callback, $extra_trusted_interface = NULL, $deprecation = NULL): void {
    if ($deprecation) {
      $this->expectDeprecation($deprecation);
    }
    $return = $this->doTrustedCallback($callback, [], '%s is not trusted', TrustedCallbackInterface::THROW_EXCEPTION, $extra_trusted_interface);
    $this->assertSame('test', $return);
  }

  /**
   * Data provider for ::testTrustedCallbacks().
   */
  public static function providerTestTrustedCallbacks() {
    $closure = function () {
      return 'test';
    };

    $tests['closure'] = [$closure];
    $tests['object_attribute'] = [[new TrustedMethods(), 'attributeCallback'], TrustedInterface::class];
    $tests['subclass_attribute'] = [[new TrustedMethodsSubclass(), 'attributeCallback'], TrustedInterface::class, 'Discovery of overridden trusted methods is deprecated in drupal:11.1.0 and will throw an error from drupal:12.0.0. Add #[TrustedCallback] to the overridden method. See https://www.drupal.org/node/7654321'];
    $tests['static_array_attribute'] = [[TrustedMethods::class, 'attributeCallback'], TrustedInterface::class];
    $tests['extra_trusted_interface_object'] = [[new TrustedObject(), 'callback'], TrustedInterface::class];
    $tests['extra_trusted_interface_subclass'] = [[new TrustedSubclass(), 'callback'], TrustedInterface::class];
    $tests['extra_trusted_interface_static_string'] = ['\Drupal\Tests\Core\Security\TrustedObject::callback', TrustedInterface::class];
    $tests['extra_trusted_interface_static_array'] = [[TrustedObject::class, 'callback'], TrustedInterface::class];
    return $tests;
  }

  /**
   * @covers ::doTrustedCallback
   * @dataProvider providerTestUntrustedCallbacks
   */
  public function testUntrustedCallbacks(callable $callback, $extra_trusted_interface = NULL): void {
    $this->expectException(UntrustedCallbackException::class);
    $this->doTrustedCallback($callback, [], '%s is not trusted', TrustedCallbackInterface::THROW_EXCEPTION, $extra_trusted_interface);
  }

  /**
   * Data provider for ::testUntrustedCallbacks().
   */
  public static function providerTestUntrustedCallbacks(): array {
    $tests['TrustedCallbackInterface_object'] = [[new TrustedMethods(), 'unTrustedCallback'], TrustedInterface::class];
    $tests['TrustedCallbackInterface_static_string'] = ['\Drupal\Tests\Core\Security\TrustedMethods::unTrustedCallback', TrustedInterface::class];
    $tests['TrustedCallbackInterface_static_array'] = [[TrustedMethods::class, 'unTrustedCallback'], TrustedInterface::class];
    $tests['untrusted_object'] = [[new UntrustedObject(), 'callback'], TrustedInterface::class];
    $tests['untrusted_object_static_string'] = ['\Drupal\Tests\Core\Security\UntrustedObject::callback', TrustedInterface::class];
    $tests['untrusted_object_static_array'] = [[UntrustedObject::class, 'callback'], TrustedInterface::class];
    $tests['invokable_untrusted_object_static_array'] = [new InvokableUntrustedObject(), TrustedInterface::class];
    return $tests;
  }

  /**
   * @covers ::doTrustedCallback
   * @dataProvider providerTestDeprecatedTrustedCallbacks
   * @group legacy
   */
  public function testDeprecatedTrustedCallbacks($callback, $extra_trusted_interface = NULL): void {
    $this->expectDeprecation('Usage of the Drupal\Core\Security\TrustedCallbackInterface is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Instead, you should use \Drupal\Core\Security\Attribute\TrustedCallback attribute for the method. See https://www.drupal.org/node/3349470');
    $return = $this->doTrustedCallback($callback, [], '%s is not trusted', TrustedCallbackInterface::THROW_EXCEPTION, $extra_trusted_interface);
    $this->assertSame('test', $return);
  }

  /**
   * Data provider for ::testDeprecatedTrustedCallbacks().
   */
  public static function providerTestDeprecatedTrustedCallbacks(): array {
    $tests['TrustedCallbackInterface_object'] = [[new DeprecatedTrustedMethod(), 'callback'], TrustedInterface::class];
    $tests['TrustedCallbackInterface_static_string'] = ['\Drupal\Tests\Core\Security\DeprecatedTrustedMethod::callback', TrustedInterface::class];
    $tests['TrustedCallbackInterface_static_array'] = [[DeprecatedTrustedMethod::class, 'callback'], TrustedInterface::class];
    return $tests;
  }

  /**
   * @dataProvider errorTypeProvider
   */
  public function testException($callback): void {
    $this->expectException(UntrustedCallbackException::class);
    $this->expectExceptionMessage('Drupal\Tests\Core\Security\UntrustedObject::callback is not trusted');
    $this->doTrustedCallback($callback, [], '%s is not trusted');
  }

  /**
   * @dataProvider errorTypeProvider
   * @group legacy
   */
  public function testSilencedDeprecation($callback): void {
    $this->expectDeprecation('Drupal\Tests\Core\Security\UntrustedObject::callback is not trusted');
    $this->doTrustedCallback($callback, [], '%s is not trusted', TrustedCallbackInterface::TRIGGER_SILENCED_DEPRECATION);
  }

  /**
   * Data provider for tests of ::doTrustedCallback $error_type argument.
   */
  public static function errorTypeProvider() {
    $tests['untrusted_object'] = [[new UntrustedObject(), 'callback']];
    $tests['untrusted_object_static_string'] = ['Drupal\Tests\Core\Security\UntrustedObject::callback'];
    $tests['untrusted_object_static_array'] = [[UntrustedObject::class, 'callback']];
    return $tests;
  }

}

interface TrustedInterface {
}

class TrustedObject implements TrustedInterface {

  public static function callback() {
    return 'test';
  }

}

class TrustedSubclass extends TrustedObject {

  public static function callback(): string {
    return 'test';
  }

}

class UntrustedObject {

  public static function callback() {
    return 'test';
  }

}

class InvokableUntrustedObject {

  public function __invoke() {
    return 'test';
  }

}

class TrustedMethods {

  #[TrustedCallback]
  public static function attributeCallback() {
    return 'test';
  }

  public static function unTrustedCallback() {
    return 'test';
  }

}

class TrustedMethodsSubclass extends TrustedMethods {

  public static function attributeCallback(): string {
    return 'test';
  }

}

class DeprecatedTrustedMethod implements TrustedCallbackInterface {

  public static function trustedCallbacks() {
    return ['callback'];
  }

  public static function callback(): string {
    return 'test';
  }

}
