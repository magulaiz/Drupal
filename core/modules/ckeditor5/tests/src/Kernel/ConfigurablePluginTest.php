<?php

namespace Drupal\Tests\ckeditor5\Kernel;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;

/**
 * Tests configurable plugins.
 *
 * @group ckeditor5
 * @internal
 */
class ConfigurablePluginTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ckeditor5',
    // These modules must be installed for ckeditor5_config_schema_info_alter()
    // to work, which in turn is necessary for the plugin definition validation
    // logic.
    // @see \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition::validateDrupalAspects()
    'filter',
    'editor',
  ];

  /**
   * The manager for "CKEditor 5 plugin" plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->manager = $this->container->get('plugin.manager.ckeditor5.plugin');
  }

  /**
   * Tests default settings for configurable CKEditor 5 plugins.
   */
  public function testDefaults() {
    $all_definitions = $this->manager->getDefinitions();
    $configurable_definitions = array_filter($all_definitions, function (CKEditor5PluginDefinition $definition): bool {
      return $definition->isConfigurable();
    });

    $default_plugin_settings = [];
    foreach (array_keys($configurable_definitions) as $plugin_name) {
      $default_plugin_settings[$plugin_name] = $this->manager->getPlugin($plugin_name, NULL)->defaultConfiguration();
    }

    $expected_default_plugin_settings = [
      'ckeditor5_heading' => [
        'enabled_headings' => [
          'heading2',
          'heading3',
          'heading4',
          'heading5',
          'heading6',
        ],
      ],
      'ckeditor5_sourceEditing' => [
        'allowed_tags' => [],
      ],
      'ckeditor5_imageResize' => [
        'allow_resize' => TRUE,
      ],
      'ckeditor5_language' => [
        'language_list' => 'un',
      ],
      'ckeditor5_imageUpload' => [],
    ];
    $this->assertSame($expected_default_plugin_settings, $default_plugin_settings);
  }

  /**
   * Tests that all ::getDynamicPluginConfig() implementations have a unit test.
   */
  public function testDynamicPluginConfigTestCoverage(): void {
    $all_definitions = $this->manager->getDefinitions();
    $problems = [];
    $configurable_definitions = array_filter($all_definitions, function (CKEditor5PluginDefinition $definition): bool {
      return $definition->isConfigurable();
    });
    $test_path = 'Drupal\Tests\ckeditor5\Unit\CLASSPluginTest';
    $missing_tests = [];
    // List of plugins that have dynamic plugin configuration but don't need
    // a unit test because it doesn't have settings of its own and instead uses
    // an out-of-band configuration.
    $plugin_exceptions = ['ImageUpload'];

    foreach ($configurable_definitions as $definition) {
      $class_name_full = $definition->getClass();
      $parts = explode('\\', $class_name_full);
      $class_name = end($parts);
      if ($this->hasDynamicPluginConfigWorthTesting($definition)) {
        $class = str_replace('CLASS', $class_name, $test_path);
        if (!class_exists($class) && !in_array($class_name, $plugin_exceptions)) {
          $missing_tests[$class_name_full] = $class;
        }
      }
    }
    if (!empty($missing_tests)) {
      foreach ($missing_tests as $class_name_full => $test) {
        $problems[] = "Expected $test test coverage to exist for $class_name_full.)";
      }
    }
    $this->assertSame([], $problems);
  }

  /**
   * Checks if the plugin has the getDynamicPluginConfig() method.
   */
  private function hasDynamicPluginConfigWorthTesting(CKEditor5PluginDefinition $definition): bool {
    if ($definition->getClass() === CKEditor5PluginDefault::class) {
      return FALSE;
    }

    $reflected_class = new \ReflectionClass($definition->getClass());
    return $reflected_class->getMethod('getDynamicPluginConfig')->getDeclaringClass()->getName() !== CKEditor5PluginDefault::class;
  }

}
