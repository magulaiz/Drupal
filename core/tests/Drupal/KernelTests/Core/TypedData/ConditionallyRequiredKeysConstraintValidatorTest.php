<?php

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the ConditionallyRequiredKeys validation constraint.
 *
 * @group Validation
 *
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\ConditionallyRequiredKeysConstraint
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\ConditionallyRequiredKeysConstraintValidator
 */
class ConditionallyRequiredKeysConstraintValidatorTest extends KernelTestBase {

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
      // Specify the constraint the constraint being tested builds on top of.
      ->addConstraint('RequiredKeys', '<infer>')
      // Specify the one constraint that is being tested.
      ->addConstraint('ConditionallyRequiredKeys', [
        'condition' => [
          'key' => 'admin_compact_mode',
          'value' => FALSE,
        ],
        'keys' => [
          'mail_notification',
        ],
      ]);
    $this->config->getDataDefinition()['mapping']['mail_notification']['requiredKey'] = FALSE;
  }

  /**
   * Tests the ConditionallyRequiredKeys constraint validator.
   */
  public function testValidation(): void {
    // Reference to the mapping in the schema, to allow adjusting it for testing
    // purposes.
    $mapping = $this->config->getDataDefinition()['mapping'];

    // Removing a conditionally required key-value pair should trigger a
    // validation error.
    $data = $this->config->getValue();
    unset($data['mail_notification']);
    $this->config->setValue($data);
    $violations = $this->config->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'mail_notification' is a conditionally required key.", (string) $violations->get(0)->getMessage());

    // Unless a key is conditionally required, and preventing the condition from
    // being met should remove the validation error.
    $this->config->set('admin_compact_mode', TRUE);
    $this->config->getDataDefinition()['mapping'] = $mapping;
    $violations = $this->config->validate();
    $this->assertCount(0, $violations);

    // But when a conditionally required key is present when its condition is
    // NOT met, the inverse error message should be triggered.
    $data = $this->config->getValue();
    $data['mail_notification'] = 'sofie@example.com';
    $this->config->setValue($data);
    $violations = $this->config->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'mail_notification' is an extraneous key.", (string) $violations->get(0)->getMessage());
  }

  /**
   * Tests exception is thrown for incorrect conditionally required keys.
   *
   * @testWith ["key"]
   *           ["value"]
   */
  public function testInvalidConditionallyRequiredKeys(string $key): void {
    $constraints = $this->config->getDataDefinition()->getConstraints();
    unset($constraints['ConditionallyRequiredKeys']['condition'][$key]);
    $this->config->getDataDefinition()->setConstraints($constraints);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage("The `condition` option for the mapping at system.site: is invalid. It must contain two key-value pairs: `key` containing a key in this mapping and `value` containing the value required at that key for the keys in `keys` to be required.");
    $this->config->validate();
  }

  /**
   * Tests exception is thrown when chaining conditionally required keys.
   */
  public function testConditionallyRequiredKeysMustDependOnRequiredKeys(): void {
    unset($this->config->getDataDefinition()['mapping']['mail_notification']['requiredKey']);

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage("The conditionally required key `mail_notification` exists in the mapping at system.site: but does not have `requiredKey: false` set. Add this, otherwise it cannot correctly behave as a conditionally required key.");
    $this->config->validate();
  }

}
