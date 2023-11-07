<?php

declare(strict_types = 1);

namespace Drupal\KernelTests\Config\Schema;

use Drupal\block\Entity\Block;
use Drupal\Core\Config\Schema\Mapping;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Config\Schema\Mapping
 * @group Config
 */
class MappingTest extends KernelTestBase {

  /**
   * @dataProvider providerMappingInterpretation
   */
  public function testMappingInterpretation(
    string $config_name,
    ?string $property_path,
    array $expected_valid_keys,
    array $expected_optional_keys,
    array $expected_dynamically_valid_keys,
  ): void {
    // Some config needs some dependencies installed.
    switch ($config_name) {
      case 'block.block.branding':
        $this->enableModules(['system', 'block']);
        /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
        $theme_installer = $this->container->get('theme_installer');
        $theme_installer->install(['stark']);
        Block::create([
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
        ])->save();
        break;

      case 'block.block.local_tasks':
        $this->enableModules(['system', 'block']);
        /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
        $theme_installer = $this->container->get('theme_installer');
        $theme_installer->install(['stark']);
        Block::create([
          'id' => 'local_tasks',
          'plugin' => 'local_tasks_block',
          'theme' => 'stark',
          'status' => TRUE,
          'settings' => [
            'primary' => TRUE,
            'secondary' => FALSE,
            // TRICKY: these 4 are inherited from `type: block_settings`.
            'status' => TRUE,
            'info' => '',
            'view_mode' => 'full',
            'context_mapping' => [],
          ],
        ])->save();
        break;
    }

    /** @var \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager */
    $typed_config_manager = \Drupal::service('config.typed');
    $mapping = $typed_config_manager->get($config_name);
    if ($property_path) {
      $mapping = $mapping->get($property_path);
    }

    assert($mapping instanceof Mapping);
    $expected_required_keys = array_values(array_diff($expected_valid_keys, $expected_optional_keys));
    $this->assertSame($expected_valid_keys, $mapping->getValidKeys());
    $this->assertSame($expected_required_keys, $mapping->getRequiredKeys());
    $this->assertSame($expected_dynamically_valid_keys, $mapping->getDynamicallyValidKeys());
    $this->assertSame($expected_optional_keys, $mapping->getOptionalKeys());
  }

  public function providerMappingInterpretation(): \Generator {
    $available_block_settings_types = [
      'block.settings.field_block:*:*:*' => [
        'formatter',
      ],
      'block.settings.extra_field_block:*:*:*' => [
        'formatter',
      ],
      'block.settings.system_branding_block' => [
        'use_site_logo',
        'use_site_name',
        'use_site_slogan',
      ],
      'block.settings.system_menu_block:*' => [
        'level',
        'depth',
        'expand_all_items',
      ],
      'block.settings.local_tasks_block' => [
        'primary',
        'secondary',
      ],
    ];

    // A simple config often is just a single Mapping object.
    yield 'core.extension' => [
      'core.extension',
      NULL,
      ['_core', 'langcode', 'module', 'theme', 'profile'],
      ['_core'],
      [],
    ];

    // A config entity is always a Mapping at the top level, but most nesting is
    // also using Mappings (unless the keys are free to be chosen, then a
    // Sequence would be used).
    yield 'block.block.branding' => [
      'block.block.branding',
      NULL,
      [
        // Keys inherited from `type: config_entity`.
        // @see core/config/schema/core.data_types.schema.yml
        'uuid',
        'langcode',
        'status',
        'dependencies',
        'third_party_settings',
        '_core',
        // Keys defined locally, in `type: block.block.*`.
        // @see core/modules/block/config/schema/block.schema.yml
        'id',
        'theme',
        'region',
        'weight',
        'provider',
        'plugin',
        'settings',
        'visibility',
      ],
      ['third_party_settings', '_core'],
      [],
    ];

    // An example of nested Mapping objects in config entities.
    yield 'block.block.branding:dependencies' => [
      'block.block.branding',
      'dependencies',
      [
        // Keys inherited from `type: config_dependencies_base`.
        // @see core/config/schema/core.data_types.schema.yml
        'config',
        'content',
        'module',
        'theme',
        // Keys defined locally, in `type: config_dependencies`.
        // @see core/config/schema/core.data_types.schema.yml
        'enforced',
      ],
      // All these keys are optional!
      ['config', 'content', 'module', 'theme', 'enforced'],
      [],
    ];

    // And finally, two examples of dynamic typing in config schema, and the
    // consequences on what keys are considered valid: it depends on the block
    // plugin being used.
    yield 'block.block.branding:settings' => [
      'block.block.branding',
      'settings',
      [
        // Keys inherited from `type: block.settings.*`, which in turn is
        // inherited from `type: block_settings`.
        // @see core/config/schema/core.data_types.schema.yml
        'id',
        'label',
        'label_display',
        'provider',
        'status',
        'info',
        'view_mode',
        'context_mapping',
        // Keys defined locally, in `type: block.settings.system_branding_block`.
        // @see core/modules/block/config/schema/block.schema.yml
        ...$available_block_settings_types['block.settings.system_branding_block'],
      ],
      [],
      $available_block_settings_types,
    ];
    yield 'block.block.local_tasks:settings' => [
      'block.block.local_tasks',
      'settings',
      [
        // Keys inherited from `type: block.settings.*`, which in turn is
        // inherited from `type: block_settings`.
        // @see core/config/schema/core.data_types.schema.yml
        'id',
        'label',
        'label_display',
        'provider',
        'status',
        'info',
        'view_mode',
        'context_mapping',
        // Keys defined locally, in `type: block.settings.local_tasks_block`.
        // @see core/modules/system/config/schema/system.schema.yml
        ...$available_block_settings_types['block.settings.local_tasks_block'],
      ],
      [],
      $available_block_settings_types,
    ];
  }

}
