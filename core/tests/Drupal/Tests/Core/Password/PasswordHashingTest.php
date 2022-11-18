<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Password\PasswordTest.
 */

namespace Drupal\Tests\Core\Password;

use Drupal\Core\Password\LegacyPassword;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Password\PhpPassword;
use Drupal\Tests\UnitTestCase;

/**
 * Tests password hashing services.
 *
 * @coversDefaultClass \Drupal\Core\Password\PhpPassword
 * @group System
 */
class PasswordHashingTest extends UnitTestCase {

  /**
   * The current password hashing service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $hashingService;

  /**
   * The legacy hashing service.
   *
   * This service was used in Drupal 7 and Drupal < 8.3.0.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $legacyHashingService;

  /**
   * The plain-text password.
   *
   * @var string
   */
  protected $plainPassword;

  /**
   * A Drupal 6 (md5) hash migrated with legacy hashing service.
   *
   * This is a string migrated from Drupal 6 (or any system with md5 hashing)
   * either to Drupal 7 or to Drupal < 8.3.0. Such a string is build by hashing
   * an already md5 hashed password with the legacy service (used in Drupal 7,
   * < 8.3.0) and prefixed with 'U'.
   *
   * @var string
   */
  protected $md5ToLegacyHashedPassword;

  /**
   * A Drupal 6 (md5) hash migrated with current hashing service.
   *
   * This is a string migrated from Drupal 6 (or any system with md5 hashing) to
   * Drupal >= 8.3.0. Such a string is build by hashing an already md5 hashed
   * password with the current service (used in Drupal >= 8.3.0) password and
   * prefixed with 'U'.
   *
   * @var string
   */
  protected $md5HashedPassword;

  /**
   * A plain password hashed with the legacy service.
   *
   * This is a plain-text password hashed with the legacy hashing service, used
   * in Drupal 7 and Drupal < 8.3.0.
   *
   * @var string
   */
  protected $legacyHashedPassword;

  /**
   * A plain password hashed with the current service.
   *
   * This is a plain-text password hashed with the current hashing service, used
   * Drupal >= 8.3.0.
   *
   * @var string
   */
  protected $hashedPassword;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->legacyHashingService = new LegacyPassword(1);
    $this->hashingService = new PhpPassword(4, $this->legacyHashingService);

    $this->plainPassword = $this->randomMachineName();
    $md5_hash = md5($this->plainPassword);

    $this->md5ToLegacyHashedPassword = 'U' . $this->legacyHashingService->hash($md5_hash);
    $this->md5HashedPassword = 'U' . $this->hashingService->hash($md5_hash);
    $this->legacyHashedPassword = $this->legacyHashingService->hash($this->plainPassword);
    $this->hashedPassword = $this->hashingService->hash($this->plainPassword);
  }

  /**
   * Tests if a password needs rehashing.
   *
   * @covers \Drupal\Core\Password\PhpPassword::needsRehash
   */
  public function testPasswordNeedsRehashing() {
    // Check that outdated hashes need rehashing.
    $this->assertTrue($this->hashingService->needsRehash($this->md5ToLegacyHashedPassword));
    $this->assertTrue($this->hashingService->needsRehash($this->md5HashedPassword));
    $this->assertTrue($this->hashingService->needsRehash($this->legacyHashedPassword));

    // Check that a text hashed with the current service doesn't need rehashing.
    $this->assertFalse($this->hashingService->needsRehash($this->hashedPassword));
  }

  /**
   * Tests password hashing.
   * Tests that plain-text password is verifying against all its hashes.
   *
   * @covers \Drupal\Core\Password\PhpPassword::check
   * @covers \Drupal\Core\Password\LegacyPassword::check
   */
  public function testPasswordHashing() {
    // Check that text hashed with current service is different than the others.
    $this->assertNotEquals($this->hashedPassword, $this->md5ToLegacyHashedPassword);
    $this->assertNotEquals($this->hashedPassword, $this->md5HashedPassword);
    $this->assertNotEquals($this->hashedPassword, $this->legacyHashedPassword);

    // Check that the plain-text password is verifying against all its hashes.
    // This is important because migrated and legacy hashes should be checked
    // with the user plain-text entered password on first login.
    $this->assertTrue($this->hashingService->check($this->plainPassword, $this->md5ToLegacyHashedPassword));
    $this->assertTrue($this->hashingService->check($this->plainPassword, $this->md5HashedPassword));
    $this->assertTrue($this->hashingService->check($this->plainPassword, $this->legacyHashedPassword));
  }

  /**
   * Tests that password needs rehashing when the cost changes.
   *
   * @covers \Drupal\Core\Password\PhpPassword::hash
   * @covers \Drupal\Core\Password\PhpPassword::check
   * @covers \Drupal\Core\Password\PhpPassword::needsRehash
   */
  public function testPasswordNeedsRehashingOnCostChange() {
    // Increment the cost from 4 to 5.
    $this->hashingService = new PhpPassword(5, $this->legacyHashingService);

    // Check that the hash needs rehashing after cost changes.
    $this->assertTrue($this->hashingService->needsRehash($this->hashedPassword));

    // Re-hash the password.
    $rehashed_password = $this->hashingService->hash($this->plainPassword);
    $this->assertNotEquals($rehashed_password, $this->hashedPassword);

    // Check that the new hash is up-to-date.
    $this->assertFalse($this->hashingService->needsRehash($rehashed_password));
    $this->assertTrue($this->hashingService->check($this->plainPassword, $rehashed_password));
  }

  /**
   * Tests that passwords longer than 512 bytes are not hashed.
   *
   * @covers \Drupal\Core\Password\PhpPassword::hash
   *
   * @dataProvider providerLongPasswords
   */
  public function testLongPassword($password, $allowed) {
    $hashed_password = $this->hashingService->hash($password);
    if ($allowed) {
      $this->assertNotFalse($hashed_password);
    }
    else {
      $this->assertFalse($hashed_password);
    }
  }

  /**
   * Provides the test cases for testLongPassword().
   *
   * @see ::testLongPassword()
   */
  public function providerLongPasswords() {
    // '512 byte long password is allowed.'
    $passwords['allowed'] = [str_repeat('x', PasswordInterface::PASSWORD_MAX_LENGTH), TRUE];
    // 513 byte long password is not allowed.
    $passwords['too_long'] = [str_repeat('x', PasswordInterface::PASSWORD_MAX_LENGTH + 1), FALSE];

    // Check a string of 3-byte UTF-8 characters, 510 byte long password is
    // allowed.
    $len = floor(PasswordInterface::PASSWORD_MAX_LENGTH / 3);
    $diff = PasswordInterface::PASSWORD_MAX_LENGTH % 3;
    $passwords['utf8'] = [str_repeat('€', $len), TRUE];
    // 512 byte long password is allowed.
    $passwords['ut8_extended'] = [$passwords['utf8'][0] . str_repeat('x', $diff), TRUE];

    // Check a string of 3-byte UTF-8 characters, 513 byte long password is
    // allowed.
    $passwords['utf8_too_long'] = [str_repeat('€', $len + 1), FALSE];

    return $passwords;
  }

  /**
   * Tests if legacy service hash count boundaries are enforced.
   *
   * @covers \Drupal\Core\Password\LegacyPassword::enforceLog2Boundaries
   */
  public function testWithinBounds() {
    $legacy_service = new FakeLegacyPassword();
    $this->assertEquals(LegacyPassword::MIN_HASH_COUNT, $legacy_service->enforceLog2Boundaries(1));
    $this->assertEquals(LegacyPassword::MAX_HASH_COUNT, $legacy_service->enforceLog2Boundaries(100));
  }

}

/**
 * A fake legacy hashing class service for tests.
 */
class FakeLegacyPassword extends LegacyPassword {

  public function __construct() {
    // Noop.
  }

  /**
   * Exposes this method as public for tests.
   */
  public function enforceLog2Boundaries($count_log2) {
    return parent::enforceLog2Boundaries($count_log2);
  }

}
