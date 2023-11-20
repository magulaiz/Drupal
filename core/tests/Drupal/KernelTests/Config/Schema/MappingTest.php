<?php

declare(strict_types = 1);

namespace Drupal\KernelTests\Config\Schema;

// cspell:ignore childkey

use Drupal\block\Entity\Block;
use Drupal\Core\Config\Schema\Mapping;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Config\Schema\Mapping
 * @group Config
 */
class MappingTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $configSchemaCheckerExclusions = [
    'config_schema_deprecated_test.settings',
  ];

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

      case 'block.block.positively_powered___alternate_reality_with_fallback_type___':
        $this->enableModules(['config_schema_add_fallback_type_test']);
        $id = 'positively_powered___alternate_reality_with_fallback_type___';
      case 'block.block.positively_powered':
        $this->enableModules(['system', 'block']);
        /** @var \Drupal\Core\Extension\ThemeInstallerInterface $theme_installer */
        $theme_installer = $this->container->get('theme_installer');
        $theme_installer->install(['stark']);
        Block::create([
          'id' => $id ?? 'positively_powered',
          'plugin' => 'system_powered_by_block',
          'theme' => 'stark',
          'status' => TRUE,
          'settings' => [
            'label_display' => FALSE,
            // TRICKY: these 4 are inherited from `type: block_settings`.
            'status' => TRUE,
            'info' => '',
            'view_mode' => 'full',
            'context_mapping' => [],
          ],
          // Avoid showing "Powered by Drupal" on 404 responses.
          'visibility' => [
            'I_CAN_CHOOSE_THIS' => [
              // This is what determines the
              'id' => 'response_status',
              'negate' => FALSE,
              'status_codes' => [
                404,
              ],
            ],
          ],
        ])->save();
        break;

      case 'config_schema_deprecated_test.settings':
        $this->enableModules(['config_schema_deprecated_test']);
        $config = $this->config('config_schema_deprecated_test.settings');
        // @see \Drupal\KernelTests\Core\Config\ConfigSchemaDeprecationTest
        $config
          ->set('complex_structure_deprecated.type', 'fruits')
          ->set('complex_structure_deprecated.products', ['apricot', 'apple'])
          ->save();
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
    yield 'No dynamic type: core.extension' => [
      'core.extension',
      NULL,
      [
        // Keys inherited from `type: config_object`.
        // @see core/config/schema/core.data_types.schema.yml
        '_core',
        'langcode',
        // Keys defined locally, in `type: core.extension`.
        // @see core/config/schema/core.extension.schema.yml
        'module',
        'theme',
        'profile',
      ],
      ['_core'],
      [],
    ];

    // Special case: deprecated  is needed for deprecated config schema:
    // - deprecated keys are treated as optional
    // - if a deprecated property path is itself a mapping, then the keys inside
    //   are not optional
    yield 'No dynamic type: config_schema_deprecated_test.settings' => [
      'config_schema_deprecated_test.settings',
      NULL,
      [
        // Keys inherited from `type: config_object`.
        // @see core/config/schema/core.data_types.schema.yml
        '_core',
        'langcode',
        // Keys defined locally, in `type: config_schema_deprecated_test.settings`.
        // @see core/modules/config/tests/config_schema_deprecated_test/config/schema/config_schema_deprecated_test.schema.yml
        'complex_structure_deprecated',
      ],
      ['_core', 'complex_structure_deprecated'],
      [],
    ];
    yield 'No dynamic type: config_schema_deprecated_test.settings:complex_structure_deprecated' => [
      'config_schema_deprecated_test.settings',
      'complex_structure_deprecated',
      [
        // Keys defined locally, in `type: config_schema_deprecated_test.settings`.
        // @see core/modules/config/tests/config_schema_deprecated_test/config/schema/config_schema_deprecated_test.schema.yml
        'type',
        'products',
      ],
      [],
      [],
    ];

    // A config entity is always a Mapping at the top level, but most nesting is
    // also using Mappings (unless the keys are free to be chosen, then a
    // Sequence would be used).
    yield 'No dynamic type: block.block.branding' => [
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
    yield 'No dynamic type: block.block.branding:dependencies' => [
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

    // Two examples of %parent-based dynamic typing in config schema, and the
    // consequences on what keys are considered valid: it depends on the block
    // plugin being used. See `type: block.block.*`, which uses
    // `type: block.settings.[%parent.plugin]`.
    yield 'Dynamic type with [%parent]: block.block.branding:settings' => [
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
    yield 'Dynamic type with [%parent]: block.block.local_tasks:settings' => [
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

    // An example of childkey-based dynamic mapping typing in config schema, for
    // a mapping inside a sequence: the `id` key-value pair in the mapping
    // determines the type of the mapping. The key in the sequence whose value
    // is the mapping is irrelevant, it can be arbitrarily chosen. See
    // `type: block.block.*` which uses `type: condition.plugin.[id]`.
    yield 'Dynamic type with [childkey]: block.block.positively_powered:visibility.I_CAN_CHOOSE_THIS' => [
      'block.block.positively_powered',
      'visibility.I_CAN_CHOOSE_THIS',
      [
        // Keys inherited from `type: condition.plugin`.
        // @see core/config/schema/core.data_types.schema.yml
        'id',
        'negate',
        'uuid',
        'context_mapping',
        // Keys defined locally, in `type: condition.plugin.response_status`.
        // @see core/modules/system/config/schema/system.schema.yml
        'status_codes',
      ],
      [],
      // Note the presence of `id`, `negate`, `uuid` and `context_mapping` here.
      // That's because there is no `condition.plugin.*` type that specifies
      // defaults. Each individual condition plugin has the freedom to deviate
      // from this approach!
      [
        'condition.plugin.entity_bundle:*' => [
          'id',
          'negate',
          'uuid',
          'context_mapping',
          'bundles',
        ],
        'condition.plugin.request_path' => [
          'id',
          'negate',
          'uuid',
          'context_mapping',
          'pages',
        ],
        'condition.plugin.response_status' => [
          'id',
          'negate',
          'uuid',
          'context_mapping',
          'status_codes',
        ],
        'condition.plugin.current_theme' => [
          'id',
          'negate',
          'uuid',
          'context_mapping',
          'theme',
        ],
      ],
    ];
    // Same, but what if `type: condition.plugin.*` would have existed?
    // @see core/modules/config/tests/config_schema_add_fallback_type_test/config/schema/config_schema_add_fallback_type_test.schema.yml
    yield 'Dynamic type with [childkey]: block.block.positively_powered___alternate_reality_with_fallback_type___:visibility' => [
      'block.block.positively_powered___alternate_reality_with_fallback_type___',
      'visibility.I_CAN_CHOOSE_THIS',
      [
        // Keys inherited from `type: condition.plugin`.
        // @see core/config/schema/core.data_types.schema.yml
        'id',
        'negate',
        'uuid',
        'context_mapping',
        // Keys defined locally, in `type: condition.plugin.response_status`.
        // @see core/modules/system/config/schema/system.schema.yml
        'status_codes',
      ],
      [],
      // Note the ABSENCE of `id`, `negate`, `uuid` and `context_mapping`
      // compared to the previous test case, because now the
      // `condition.plugin.*` type does exist.
      [
        'condition.plugin.entity_bundle:*' => [
          'bundles',
        ],
        'condition.plugin.request_path' => [
          'pages',
        ],
        'condition.plugin.response_status' => [
          'status_codes',
        ],
        'condition.plugin.current_theme' => [
          'theme',
        ],
      ],
    ];
  }

}
