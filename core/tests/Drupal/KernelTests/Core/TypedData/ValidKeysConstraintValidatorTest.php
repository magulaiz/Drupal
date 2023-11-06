<?php

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\block\Entity\Block;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\ConstraintViolation;
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
    // Install the Block module and create a Block config entity, so that we can
    // test that the validator infers the required keys from a defined schema.
    $this->enableModules(['system', 'block']);
    /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
    $theme_installer = $this->container->get('theme_installer');
    $theme_installer->install(['stark']);
    $block = Block::create([
      'id' => 'branding',
      'plugin' => 'system_branding_block',
      'theme' => 'stark',
      'status' => TRUE,
      'settings' => [
        'use_site_logo' => TRUE,
        'use_site_name' => TRUE,
        'use_site_slogan' => TRUE,
        'label_display' => FALSE,
        // TRICKY: these 4 are inherited from `type: block_settings`.
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
        'context_mapping' => [],
      ],
    ]);
    $block->save();

    $this->config = $this->container->get('config.typed')
      ->get('block.block.branding');
  }

  /**
   * Tests ValidKeys constraint validator detecting missing required keys.
   *
   * @see \Drupal\Core\Validation\Plugin\Validation\Constraint\ValidKeysConstraint::$requiredKeyMessage
   */
  public function testRequiredKeys(): void {
    // Start from the valid config.
    $this->assertEmpty($this->config->validate());

    // Then modify only one thing: remove the `label_display` setting.
    $data = $this->config->toArray();
    unset($data['settings']['label_display']);
    $this->config = $this->container->get('config.typed')
      ->createFromNameAndData('block.block.branding', $data);

    // Now 1 validation error should be triggered: one for the missing
    // (statically) required key. It is only required because all block plugins
    // are required to set it: see `type: block_settings`.
    // @see \Drupal\system\Plugin\Block\SystemBrandingBlock::defaultConfiguration()
    // @see \Drupal\system\Plugin\Block\SystemPoweredByBlock::defaultConfiguration()
    $this->assertSame(
      [
        "'label_display' is a required key.",
      ],
      array_map(
        fn (ConstraintViolation $v) => (string) $v->getMessage(),
        iterator_to_array($this->config->validate()),
      )
    );
  }

  /**
   * Tests ValidKeys constraint validator detecting unknown keys.
   *
   * @see \Drupal\Core\Validation\Plugin\Validation\Constraint\ValidKeysConstraint::$dynamicInvalidKeyMessage
   */
  public function testUnknownKeys(): void {
    // Start from the valid config.
    $this->assertEmpty($this->config->validate());

    // Then modify only one thing: the block plugin that is being used.
    $data = $this->config->toArray();
    $data['plugin'] = 'system_powered_by_block';
    $this->config = $this->container->get('config.typed')
      ->createFromNameAndData('block.block.branding', $data);

    // Now 3 validation errors should be triggered: one for each of the settings
    // that exist in the "branding" block but not the "powered by" block.
    // @see \Drupal\system\Plugin\Block\SystemBrandingBlock::defaultConfiguration()
    // @see \Drupal\system\Plugin\Block\SystemPoweredByBlock::defaultConfiguration()
    $this->assertSame(
      [
        "'use_site_logo' is an unknown key because plugin is system_powered_by_block (see config schema type block.settings.*).",
        "'use_site_name' is an unknown key because plugin is system_powered_by_block (see config schema type block.settings.*).",
        "'use_site_slogan' is an unknown key because plugin is system_powered_by_block (see config schema type block.settings.*).",
      ],
      array_map(
        fn (ConstraintViolation $v) => (string) $v->getMessage(),
        iterator_to_array($this->config->validate()),
      )
    );
  }

  /**
   * Tests ValidKeys detecting missing dynamically required keys.
   *
   * @see \Drupal\Core\Validation\Plugin\Validation\Constraint\ValidKeysConstraint::$dynamicRequiredKeyMessage
   */
  public function testDynamicallyRequiredKeys(): void {
    // Start from the valid config.
    $this->assertEmpty($this->config->validate());

    // Then modify only one thing: remove the `use_site_name` setting.
    $data = $this->config->toArray();
    unset($data['settings']['use_site_name']);
    $this->config = $this->container->get('config.typed')
      ->createFromNameAndData('block.block.branding', $data);

    // Now 1 validation error should be triggered: one for the missing
    // required key. It is only dynamically required because not
    // all block plugins support this key in their configuration.
    // @see \Drupal\system\Plugin\Block\SystemBrandingBlock::defaultConfiguration()
    // @see \Drupal\system\Plugin\Block\SystemPoweredByBlock::defaultConfiguration()
    $this->assertSame(
      [
        "'use_site_name' is a required key because plugin is system_branding_block (see config schema type block.settings.system_branding_block).",
      ],
      array_map(
        fn (ConstraintViolation $v) => (string) $v->getMessage(),
        iterator_to_array($this->config->validate()),
      )
    );
  }

  /**
   * Tests the ValidKeys constraint validator.
   */
  public function testValidation(): void {
    // Create a data definition that specifies certain allowed keys.
    $definition = DataDefinition::create('any')
      ->addConstraint('ValidKeys', ['north', 'south', 'west']);

    /** @var \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data */
    $typed_data = $this->container->get('typed_data_manager');

    // Passing a non-array value should raise an exception.
    try {
      $typed_data->create($definition, 2501)->validate();
      $this->fail('Expected an exception but none was raised.');
    }
    catch (UnexpectedTypeException $e) {
      $this->assertSame('Expected argument of type "array", "int" given', $e->getMessage());
    }

    // Empty arrays are valid.
    $this->assertCount(0, $typed_data->create($definition, [])->validate());

    // Indexed arrays are never valid.
    $violations = $typed_data->create($definition, ['north', 'south'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('Numerically indexed arrays are not allowed.', (string) $violations->get(0)->getMessage());

    // Arrays with automatically assigned keys, AND a valid key, should be
    // considered invalid overall.
    $violations = $typed_data->create($definition, ['north', 'south' => 'west'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'0' is not a supported key.", (string) $violations->get(0)->getMessage());

    // Associative arrays with an invalid key should be invalid.
    $violations = $typed_data->create($definition, ['north' => 'south', 'east' => 'west'])->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'east' is not a supported key.", (string) $violations->get(0)->getMessage());

    // If the array only contains the allowed keys, it's fine.
    $value = [
      'north' => 'Boston',
      'south' => 'Atlanta',
      'west' => 'San Francisco',
    ];
    $violations = $typed_data->create($definition, $value)->validate();
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

  /**
   * Tests ValidKeys constraint validator detecting optional keys.
   */
  public function testMarkedAsOptional(): void {
    $violations = $this->config->validate();
    $this->assertCount(0, $violations);

    // Reference to the mapping in the schema, to allow adjusting it for testing
    // purposes.
    $mapping = $this->config->getDataDefinition()['mapping'];

    // Removing a key-value pair should trigger a validation error.
    $data = $this->config->getValue();
    unset($data['status']);
    $this->config->setValue($data);
    $violations = $this->config->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("'status' is a required key.", (string) $violations->get(0)->getMessage());

    // Unless a key is explicitly marked as optional.
    $mapping['status']['requiredKey'] = FALSE;
    $this->config->getDataDefinition()['mapping'] = $mapping;
    $violations = $this->config->validate();
    $this->assertCount(0, $violations);
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
