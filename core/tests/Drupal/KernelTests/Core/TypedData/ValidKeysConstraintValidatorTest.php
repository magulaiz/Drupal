<?php

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Tests the ValidKeys validation constraint.
 *
 * @group Validation
 *
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\ValidKeysConstraint
 * @covers \Drupal\Core\Validation\Plugin\Validation\Constraint\ValidKeysConstraintValidator
 */
class ValidKeysConstraintValidatorTest extends KernelTestBase {

  /**
   * Tests the ValidKeys constraint validator.
   */
  public function testValidation(): void {
    // Create a data definition that specifies certain allowed keys.
    $definition = MapDataDefinition::create('mapping')
      ->addConstraint('ValidKeys', ['north', 'south', 'west']);
    $definition['mapping'] = [
      'north' => ['type' => 'string'],
      'east' => ['type' => 'string'],
      'south' => ['type' => 'string'],
      'west' => ['type' => 'string'],
    ];
    // @todo Remove this line in https://www.drupal.org/project/drupal/issues/3403782
    $definition->setClass('Drupal\Core\Config\Schema\Mapping');

    /** @var \Drupal\Core\TypedData\TypedDataManagerInterface $typed_config */
    $typed_config = $this->container->get('config.typed');
    // @see \Drupal\Core\Config\TypedConfigManager::buildDataDefinition()
    // @see \Drupal\Core\TypedData\TypedDataManager::createDataDefinition()
    $definition->setTypedDataManager($typed_config);

    // Passing a non-array value should raise an exception.
    try {
      $typed_config->create(clone $definition, 2501)->validate();
      $this->fail('Expected an exception but none was raised.');
    }
    catch (UnexpectedTypeException $e) {
      $this->assertSame('Expected argument of type "array", "int" given', $e->getMessage());
    }

    // Empty arrays are valid.
    $this->assertCount(0, $typed_config->create(clone $definition, [])->validate());

    // Indexed arrays are never valid.
    $violations = $typed_config->create(clone $definition, ['north', 'south'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('Numerically indexed arrays are not allowed.', (string) $violations->get(0)->getMessage());

    // Arrays with automatically assigned keys, AND a valid key, should be
    // considered invalid overall.
    $violations = $typed_config->create(clone $definition, ['north', 'south' => 'west'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'0' is not a supported key.", (string) $violations->get(0)->getMessage());

    // Associative arrays with an invalid key should be invalid.
    $violations = $typed_config->create(clone $definition, ['north' => 'south', 'east' => 'west'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'east' is not a supported key.", (string) $violations->get(0)->getMessage());

    // If the array only contains the allowed keys, it's fine.
    $value = [
      'north' => 'Boston',
      'south' => 'Atlanta',
      'west' => 'San Francisco',
    ];
    $violations = $typed_config->create(clone $definition, $value)->validate();
    $this->assertCount(0, $violations);
  }

  /**
   * Tests that valid keys can be inferred from the data definition.
   */
  public function testValidKeyInference(): void {
    // Install the System module and its config so that we can test that the
    // validator infers the allowed keys from a defined schema.
    $this->enableModules(['system']);
    $this->installConfig('system');

    $config = $this->container->get('config.typed')
      ->get('system.site');
    $config->getDataDefinition()
      ->addConstraint('ValidKeys', '<infer>');

    $data = $config->getValue();
    $data['invalid-key'] = "There's a snake in my boots.";
    $config->setValue($data);
    $violations = $config->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'invalid-key' is not a supported key.", (string) $violations->get(0)->getMessage());

    // Ensure that ValidKeys will freak out if the option is not exactly
    // `<infer>`.
    $config->getDataDefinition()
      ->addConstraint('ValidKeys', 'infer');
    $this->expectExceptionMessage("'infer' is not a valid set of allowed keys.");
    $config->validate();
  }

}
