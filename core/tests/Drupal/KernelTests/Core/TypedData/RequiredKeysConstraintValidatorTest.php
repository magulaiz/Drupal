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

    // Removing a key-value pair should trigger a validation error.
    $data = $this->config->getValue();
    unset($data['name']);
    $this->config->setValue($data);
    $violations = $this->config->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'name' is a required key.", (string) $violations->get(0)->getMessage());

    // Unless a key is explicitly marked as optional.
    $mapping['name']['requiredKey'] = FALSE;
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
   * Tests exception is thrown when `requiredKey` is anything but `false`.
   *
   * @testWith [true]
   *           ["false"]
   *           ["true"]
   *           [""]
   *           [null]
   */
  public function testExceptionWhenInvalidRequiredKey(mixed $value): void {
    $mapping = $this->config->getDataDefinition()['mapping'];
    $mapping['mail_notification']['requiredKey'] = $value;
    $this->config->getDataDefinition()['mapping'] = $mapping;

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage("The `requiredKey` flag must either be omitted or have `false` as the value.");
    $this->config->validate();
  }

}
