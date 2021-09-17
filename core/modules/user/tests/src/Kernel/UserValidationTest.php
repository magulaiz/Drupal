<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Render\Element\Email;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Verify that user validity checks behave as designed.
 *
 * @group user
 */
class UserValidationTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['field', 'user', 'system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('system', ['sequences']);

    // Make sure that the default roles exist.
    $this->installConfig(['user']);

  }

  /**
   * Tests user name validation.
   */
  public function testUsernames() {
    // cSpell:disable
    $test_cases = [
      // '<username>' => ['<description>', 'assert<testName>'].
      'foo'                    => ['Valid username', 'assertNull'],
      'FOO'                    => ['Valid username', 'assertNull'],
      'Foo O\'Bar'             => ['Valid username', 'assertNull'],
      'foo@bar'                => ['Valid username', 'assertNull'],
      'foo@example.com'        => ['Valid username', 'assertNull'],
      // invalid domains are allowed in usernames.
      'foo@-example.com'       => ['Valid username', 'assertNull'],
      'þòøÇßªř€'               => ['Valid username', 'assertNull'],
      // '+' symbol is allowed.
      'foo+bar'                => ['Valid username', 'assertNull'],
      // runes.
      'ᚠᛇᚻ᛫ᛒᛦᚦ'                => ['Valid UTF8 username', 'assertNull'],
      ' foo'                   => ['Invalid username that starts with a space', 'assertNotNull'],
      'foo '                   => ['Invalid username that ends with a space', 'assertNotNull'],
      'foo  bar'               => ['Invalid username that contains 2 spaces \'&nbsp;&nbsp;\'', 'assertNotNull'],
      ''                       => ['Invalid empty username', 'assertNotNull'],
      'foo/'                   => ['Invalid username containing invalid chars', 'assertNotNull'],
      // NULL.
      'foo' . chr(0) . 'bar'   => ['Invalid username containing chr(0)', 'assertNotNull'],
      // CR.
      'foo' . chr(13) . 'bar'  => ['Invalid username containing chr(13)', 'assertNotNull'],
      str_repeat('x', UserInterface::USERNAME_MAX_LENGTH + 1) => ['Invalid excessively long username', 'assertNotNull'],
    ];
    // cSpell:enable
    foreach ($test_cases as $name => $test_case) {
      list($description, $test) = $test_case;
      $result = user_validate_name($name);
      $this->$test($result, $description . ' (' . $name . ')');
    }
  }

  /**
   * Runs entity validation checks.
   */
  public function testValidation() {
    $user = User::create([
      'name' => 'test',
      'mail' => 'test@example.com',
    ]);
    $violations = $user->validate();
    $this->assertCount(0, $violations, 'No violations when validating a default user.');

    // Only test one example invalid name here, the rest is already covered in
    // the testUsernames() method in this class.
    $name = $this->randomMachineName(61);
    $user->set('name', $name);
    $violations = $user->validate();
    $this->assertCount(1, $violations, 'Violation found when name is too long.');
    $this->assertEquals('name', $violations[0]->getPropertyPath());
    $this->assertEquals("The username <em class=\"placeholder\">$name</em> is too long: it must be <em class=\"placeholder\">60</em> characters or less.", $violations[0]->getMessage());

    // Create a second test user to provoke a name collision.
    $user2 = User::create([
      'name' => 'existing',
      'mail' => 'existing@example.com',
    ]);
    $user2->save();
    $user->set('name', 'existing');
    $violations = $user->validate();
    $this->assertCount(1, $violations, 'Violation found on name collision.');
    $this->assertEquals('name', $violations[0]->getPropertyPath());
    $this->assertEquals('The username <em class="placeholder">existing</em> is already taken.', $violations[0]->getMessage());

    // Make the name valid.
    $user->set('name', $this->randomMachineName());

    $user->set('mail', 'invalid');
    $violations = $user->validate();
    $this->assertCount(1, $violations, 'Violation found when email is invalid');
    $this->assertEquals('mail.0.value', $violations[0]->getPropertyPath());
    $this->assertEquals('This value is not a valid email address.', $violations[0]->getMessage());

    $mail = $this->randomMachineName(Email::EMAIL_MAX_LENGTH - 11) . '@example.com';
    $user->set('mail', $mail);
    $violations = $user->validate();
    // @todo There are two violations because EmailItem::getConstraints()
    //   overlaps with the implicit constraint of the 'email' property type used
    //   in EmailItem::propertyDefinitions(). Resolve this in
    //   https://www.drupal.org/node/2023465.
    $this->assertCount(2, $violations, 'Violations found when email is too long');
    $this->assertEquals('mail.0.value', $violations[0]->getPropertyPath());
    $this->assertEquals(t('%name: the email address can not be longer than @max characters.', ['%name' => $user->get('mail')->getFieldDefinition()->getLabel(), '@max' => Email::EMAIL_MAX_LENGTH]), $violations[0]->getMessage());
    $this->assertEquals('mail.0.value', $violations[1]->getPropertyPath());
    $this->assertEquals('This value is not a valid email address.', $violations[1]->getMessage());

    // Provoke an email collision with an existing user.
    $user->set('mail', 'existing@example.com');
    $violations = $user->validate();
    $this->assertCount(1, $violations, 'Violation found when email already exists.');
    $this->assertEquals('mail', $violations[0]->getPropertyPath());
    $this->assertEquals('The email address <em class="placeholder">existing@example.com</em> is already taken.', $violations[0]->getMessage());
    $user->set('mail', NULL);
    $violations = $user->validate();
    $this->assertCount(1, $violations, 'Email addresses may not be removed');
    $this->assertEquals('mail', $violations[0]->getPropertyPath());
    $this->assertEquals("{$user->getFieldDefinition('mail')->getLabel()} field is required.", $violations[0]->getMessage());
    $user->set('mail', 'someone@example.com');

    $user->set('timezone', $this->randomString(33));
    $this->assertLengthViolation($user, 'timezone', 32, 2, 1);
    $user->set('timezone', 'invalid zone');
    $this->assertAllowedValuesViolation($user, 'timezone');
    $user->set('timezone', NULL);

    $user->set('init', 'invalid');
    $violations = $user->validate();
    $this->assertCount(1, $violations, 'Violation found when init email is invalid');
    $user->set('init', NULL);

    $user->set('langcode', 'invalid');
    $this->assertAllowedValuesViolation($user, 'langcode');
    $user->set('langcode', NULL);

    // Only configurable langcodes are allowed for preferred languages.
    $user->set('preferred_langcode', Language::LANGCODE_NOT_SPECIFIED);
    $this->assertAllowedValuesViolation($user, 'preferred_langcode');
    $user->set('preferred_langcode', NULL);

    $user->set('preferred_admin_langcode', Language::LANGCODE_NOT_SPECIFIED);
    $this->assertAllowedValuesViolation($user, 'preferred_admin_langcode');
    $user->set('preferred_admin_langcode', NULL);

    Role::create(['id' => 'role1'])->save();
    Role::create(['id' => 'role2'])->save();

    // Test cardinality of user roles.
    $user = User::create([
      'name' => 'role_test',
      'mail' => 'test@example.com',
      'roles' => ['role1', 'role2'],
    ]);
    $violations = $user->validate();
    $this->assertCount(0, $violations);

    $user->roles[1]->target_id = 'unknown_role';
    $violations = $user->validate();
    $this->assertCount(1, $violations);
    $this->assertEquals('roles.1.target_id', $violations[0]->getPropertyPath());
    $this->assertEquals('The referenced entity (<em class="placeholder">user_role</em>: <em class="placeholder">unknown_role</em>) does not exist.', $violations[0]->getMessage());
  }

  /**
   * Verifies that a length violation exists for the given field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object to validate.
   * @param string $field_name
   *   The field that violates the maximum length.
   * @param int $length
   *   Number of characters that was exceeded.
   * @param int $count
   *   (optional) The number of expected violations. Defaults to 1.
   * @param int $expected_index
   *   (optional) The index at which to expect the violation. Defaults to 0.
   */
  protected function assertLengthViolation(EntityInterface $entity, $field_name, $length, $count = 1, $expected_index = 0) {
    $violations = $entity->validate();
    $this->assertCount($count, $violations, "Violation found when $field_name is too long.");
    $this->assertEquals("{$field_name}.0.value", $violations[$expected_index]->getPropertyPath());
    $field_label = $entity->get($field_name)->getFieldDefinition()->getLabel();
    $this->assertEquals("<em class=\"placeholder\">$field_label</em>: may not be longer than $length characters.", $violations[$expected_index]->getMessage());
  }

  /**
   * Verifies that an AllowedValues violation exists for the given field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object to validate.
   * @param string $field_name
   *   The name of the field to verify.
   */
  protected function assertAllowedValuesViolation(EntityInterface $entity, $field_name) {
    $violations = $entity->validate();
    $this->assertCount(1, $violations, "Allowed values violation for $field_name found.");
    $this->assertEquals($field_name === 'langcode' ? "{$field_name}.0" : "{$field_name}.0.value", $violations[0]->getPropertyPath());
    $this->assertEquals('The value you selected is not a valid choice.', $violations[0]->getMessage());
  }

}
