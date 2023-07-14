<?php

declare(strict_types = 1);

namespace Drupal\KernelTests\Core\Config;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests validation of certain elements common to all config.
 *
 * @group config
 * @group Validation
 */
class SimpleConfigValidationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
  }

  public function testDefaultConfigHashValidation(): void {
    $config = $this->config('system.site');
    $this->assertFalse($config->isNew());
    $data = $config->get();
    $this->assertNotEmpty($data['_core']['default_config_hash']);

    /** @var \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager */
    $typed_config_manager = $this->container->get('config.typed');

    // If the default_config_hash is NULL, it should be an error.
    $data['_core']['default_config_hash'] = NULL;
    $violations = $typed_config_manager->createFromNameAndData($config->getName(), $data)
      ->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('_core.default_config_hash', $violations[0]->getPropertyPath());
    $this->assertSame('This value should not be null.', (string) $violations[0]->getMessage());

    $data['_core']['default_config_hash'] = 'Ever seen an evil hash?';
    $violations = $typed_config_manager->createFromNameAndData($config->getName(), $data)
      ->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('_core.default_config_hash', $violations[0]->getPropertyPath());
    $this->assertSame('This value is not valid.', (string) $violations[0]->getMessage());

    $data['_core']['default_config_hash'] = 'abc123';
    $data['_core']['invalid_key'] = 'Hello';
    $violations = $typed_config_manager->createFromNameAndData($config->getName(), $data)
      ->validate();
    $this->assertCount(1, $violations);
    $this->assertSame('_core', $violations[0]->getPropertyPath());
    $this->assertSame("'invalid_key' is not a supported key.", (string) $violations[0]->getMessage());
  }

}
