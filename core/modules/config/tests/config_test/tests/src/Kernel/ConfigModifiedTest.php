<?php

namespace Drupal\Tests\config_test\Kernel;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\ConfigNameException;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that changes to configuration are found.
 *
 * @group config
 */
class ConfigModifiedTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['config_test', 'system'];

  /**
   * Verifies that changes to configuration are detected.
   */
  public function testIsModified() {
    $this->installConfig(['config_test']);

    /** @var \Drupal\Core\Config\ConfigComparatorInterface $comparator */
    $comparator = $this->container->get('config.comparator');
    $config_name = 'config_test.system';

    /** @var \Drupal\Core\Config\Config $editable_config */
    $editable_config = $this->container->get('config.factory')
      ->getEditable($config_name);

    // Not changed config.
    $this->assertFalse($comparator->isModified($config_name), 'Configuration is not changed after install.');

    // After config is change.
    $editable_config
      ->set('404', 'user/login')
      ->save();
    $this->assertTrue($comparator->isModified($config_name), 'Configuration is modified after last install.');

    // After config is updated.
    $active = $editable_config->getRawData();
    unset($active['uuid']);
    unset($active['_core']);
    $editable_config
      ->set('_core.default_config_hash', Crypt::hashBase64(serialize($active)))
      ->save();
    $this->assertFalse($comparator->isModified($config_name), 'Configuration is not changed after last update.');

    // After config is removed.
    $editable_config->delete();

    // We cannot use $this->setExpectedException() because PHPUnit would skip.
    try {
      $comparator->isModified($config_name);
      $this->fail('Configuration does not exist.');
    }
    catch (ConfigNameException $e) {
      $this->assertEquals(sprintf('Configuration "%s" does not exist.', $config_name), $e->getMessage());
    }
  }

  /**
   * Verifies that non existing config will throw an exception.
   */
  public function testIsModifiedNotExisting() {
    $this->installConfig(['config_test']);

    /** @var \Drupal\Core\Config\ConfigComparatorInterface $comparator */
    $comparator = $this->container->get('config.comparator');

    $not_existing_config = 'config_test.not_existing';

    // We cannot use $this->setExpectedException() because PHPUnit would skip.
    try {
      $comparator->isModified($not_existing_config);
      $this->fail('Configuration does not exist.');
    }
    catch (ConfigNameException $e) {
      $this->assertEquals(sprintf('Configuration "%s" does not exist.', $not_existing_config), $e->getMessage());
    }
  }

}
