<?php

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the RequiredKeys validation constraint.
 *
 * @group Validation
 *
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\RequiredKeysConstraint
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\RequiredKeysConstraintValidator
 */
class RequiredKeysConstraintValidatorTest extends KernelTestBase {

  /**
   * The config under test.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Install the System module and its config so that we can test that the
    // validator infers the allowed keys from a defined schema.
    $this->enableModules(['system']);
    $this->installConfig('system');

    $this->config = $this->container->get('config.typed')
      ->get('system.site');
    $this->config->getDataDefinition()
      // Remove all constraints defined by `system.site`'s schema.
      ->setConstraints([])
      // Specify the one constraint that is being tested.
      ->addConstraint('RequiredKeys', '<infer>');
  }

  /**
   * Tests the RequiredKeys constraint validator.
   */
  public function testValidation(): void {
    // Reference to the mapping in the schema, to allow adjusting it for testing
    // purposes.
    $mapping = $this->config->getDataDefinition()['mapping'];

    // Removing two key-value pair should trigger two validation errors.
    $data = $this->config->getValue();
    unset($data['name']);
    unset($data['mail_notification']);
    $this->config->setValue($data);
    $violations = $this->config->validate();
    $this->assertCount(2, $violations);
    $this->assertSame("'name' is a required key.", (string) $violations->get(0)->getMessage());
    $this->assertSame("'mail_notification' is a required key.", (string) $violations->get(1)->getMessage());

    // Unless a key is explicitly marked as optional.
    $mapping['name']['requiredKey'] = FALSE;
    $this->config->getDataDefinition()['mapping'] = $mapping;
    $violations = $this->config->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'mail_notification' is a required key.", (string) $violations->get(0)->getMessage());

    // Unless a key is conditionally required.
    $mapping['mail_notification']['requiredKeyIf'] = [
      'path' => 'admin_compact_mode',
      'value' => TRUE,
    ];
    $this->config->getDataDefinition()['mapping'] = $mapping;
    $violations = $this->config->validate();
    $this->assertCount(0, $violations);

    // Unless a key is conditionally required.
    $mapping['mail_notification']['requiredKeyIf'] = [
      'path' => 'admin_compact_mode',
      'value' => TRUE,
    ];
    $this->config->getDataDefinition()['mapping'] = $mapping;
    $violations = $this->config->validate();
    $this->assertCount(0, $violations);
  }

  /**
   * Tests exception is thrown if the option is not exactly `<infer>`.
   */
  public function testOnlyOneValidOption(): void {
    $this->config->getDataDefinition()
      ->addConstraint('RequiredKeys', 'infer');
    $this->expectException(\DomainException::class);
    $this->expectExceptionMessage("Only '<infer>' is allowed.");
    $this->config->validate();
  }

  /**
   * Tests exception is thrown for incorrect conditionally required keys.
   *
   * @testWith ["path"]
   *           ["value"]
   */
  public function testInvalidConditionallyRequiredKeys(string $key): void {
    $mapping = $this->config->getDataDefinition()['mapping'];
    $mapping['mail_notification']['requiredKeyIf'] = [
      'path' => 'admin_compact_mode',
      'value' => TRUE,
    ];
    // Make this invalid based on $key.
    unset($mapping['mail_notification']['requiredKeyIf'][$key]);
    $this->config->getDataDefinition()['mapping'] = $mapping;

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage("`requiredKeyIf` must contain two key-value pairs: `path` containing a property path string and `value` containing the value required at that property path for this key to be required.");
    $this->config->validate();
  }

  /**
   * Tests exception is thrown when chaining conditionally required keys.
   */
  public function testConditionallyRequiredKeysMustDependOnRequiredKeys(): void {
    $mapping = $this->config->getDataDefinition()['mapping'];
    $mapping['mail_notification']['requiredKeyIf'] = [
      'path' => 'admin_compact_mode',
      'value' => TRUE,
    ];
    $mapping['admin_compact_mode']['requiredKeyIf'] = [
      'path' => 'uuid',
      'value' => TRUE,
    ];
    $this->config->getDataDefinition()['mapping'] = $mapping;

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage("Conditionally required keys must depend only on unconditionally required keys. The dependency of `system.site:mail_notification` on `system.site:admin_compact_mode` violates this.");
    $this->config->validate();
  }

}
