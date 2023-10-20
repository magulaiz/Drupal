<?php

declare(strict_types = 1);

namespace Drupal\KernelTests\Core\TypedData;

use Drupal\block\Entity\Block;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\ConstraintViolation;

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
    $this->config->getDataDefinition()
      // Remove all constraints defined by `block.block.*`'s schema.
      ->setConstraints([])
      // Specify the one constraint that is being tested.
      ->addConstraint('RequiredKeys', '<infer>');
    $this->config->get('settings')->getDataDefinition()
      // Remove all constraints defined by
      // `block.settings.system_branding_block`'s schema.
      ->setConstraints([])
      // Specify the one constraint that is being tested.
      ->addConstraint('RequiredKeys', '<infer>');
  }

  /**
   * Tests RequiredKeys constraint validator detecting optional keys.
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
   * Tests RequiredKeys constraint validator detecting missing required keys.
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
    // (unconditionally) required key. It is only required because all block
    // plugins are required to set it: see `type: block_settings`.
    // all block plugins support this key in their configuration.
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
   * Tests RequiredKeys detecting missing required keys.
   *
   * @see \Drupal\Core\Validation\Plugin\Validation\Constraint\RequiredKeysConstraint::$dynamicMessage
   */
  public function testConditionallyRequiredKeys(): void {
    // Start from the valid config.
    $this->assertEmpty($this->config->validate());

    // Then modify only one thing: remove the `use_site_name` setting.
    $data = $this->config->toArray();
    unset($data['settings']['use_site_name']);
    $this->config = $this->container->get('config.typed')
      ->createFromNameAndData('block.block.branding', $data);

    // Now 1 validation error should be triggered: one for the missing
    // required key. It is only conditionally required because not
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
   * Tests RequiredKeys constraint validator detecting unknown keys.
   *
   * @see \Drupal\Core\Validation\Plugin\Validation\Constraint\RequiredKeysConstraint::$unknownMessage
   */
  public function testExtraneousKeys(): void {
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
