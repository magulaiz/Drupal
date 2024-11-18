<?php

namespace Drupal\Component\Plugin\Discovery;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Component\FileCache\FileCacheInterface;
use Drupal\Component\Plugin\Attribute\PluginPropertyInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;

/**
 * Defines a discovery mechanism to find plugins with attributes.
 */
class AttributeClassDiscovery implements DiscoveryInterface {

  use DiscoveryTrait;

  /**
   * The file cache object.
   */
  protected FileCacheInterface $fileCache;

  /**
   * Constructs a new instance.
   *
   * @param string[] $pluginNamespaces
   *   (optional) An array of namespace that may contain plugin implementations.
   *   Defaults to an empty array.
   * @param string $pluginDefinitionAttributeName
   *   (optional) The name of the attribute that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Plugin\Attribute\Plugin'.
   */
  public function __construct(
    protected readonly array $pluginNamespaces = [],
    protected readonly string $pluginDefinitionAttributeName = Plugin::class,
  ) {
    $file_cache_suffix = str_replace('\\', '_', $this->pluginDefinitionAttributeName);
    $this->fileCache = FileCacheFactory::get('attribute_discovery:' . $this->getFileCacheSuffix($file_cache_suffix));
  }

  /**
   * Gets the file cache suffix.
   *
   * This method allows classes that extend this class to add additional
   * information to the file cache collection name.
   *
   * @param string $default_suffix
   *   The default file cache suffix.
   *
   * @return string
   *   The file cache suffix.
   */
  protected function getFileCacheSuffix(string $default_suffix): string {
    return $default_suffix;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = [];

    // Search for classes within all PSR-4 namespace locations.
    foreach ($this->getPluginNamespaces() as $namespace => $dirs) {
      foreach ($dirs as $dir) {
        if (file_exists($dir)) {
          $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
          );
          foreach ($iterator as $fileinfo) {
            assert($fileinfo instanceof \SplFileInfo);
            if ($fileinfo->getExtension() === 'php') {
              if ($cached = $this->fileCache->get($fileinfo->getPathName())) {
                if (isset($cached['id'])) {
                  // Explicitly unserialize this to create a new object instance.
                  $content = unserialize($cached['content']);
                  $third_party_attributes = isset($cached['third_party_attributes']) ? unserialize($cached['third_party_attributes']) : [];
                  $definitions[$cached['id']] = $this->addThirdPartyPropertiesToDefinition($cached['id'], $content, $third_party_attributes ?? []);
                }
                continue;
              }

              $sub_path = $iterator->getSubIterator()->getSubPath();
              $sub_path = $sub_path ? str_replace(DIRECTORY_SEPARATOR, '\\', $sub_path) . '\\' : '';
              $class = $namespace . '\\' . $sub_path . $fileinfo->getBasename('.php');
              try {
                ['id' => $id, 'content' => $content, 'third_party_attributes' => $third_party_attributes] = $this->parseClass($class, $fileinfo);
                if ($id) {
                  // Explicitly serialize this to create a new object instance.
                  // Cache the definition content before it is modified in
                  // ::addThirdPartyPropertiesToDefinition() by third-party
                  // property attributes, which should be applied only if the
                  // module provider exists.
                  $this->fileCache->set($fileinfo->getPathName(), ['id' => $id, 'content' => serialize($content), 'third_party_attributes' => serialize($third_party_attributes)]);
                  $definitions[$id] = $this->addThirdPartyPropertiesToDefinition($id, $content, $third_party_attributes ?? []);
                }
                else {
                  // Store a NULL object, so that the file is not parsed again.
                  $this->fileCache->set($fileinfo->getPathName(), [NULL]);
                }
              }
              // Plugins may rely on Attribute classes defined by modules that
              // are not installed. In such a case, a 'class not found' error
              // may be thrown from reflection. However, this is an unavoidable
              // situation with optional dependencies and plugins. Therefore,
              // silently skip over this class and avoid writing to the cache,
              // so that it is scanned each time. This ensures that the plugin
              // definition will be found if the module it requires is
              // enabled.
              catch (\Error $e) {
                if (!preg_match('/(Class|Interface) .* not found$/', $e->getMessage())) {
                  throw $e;
                }
              }
            }
          }
        }
      }
    }

    // Plugin discovery is a memory expensive process due to reflection and the
    // number of files involved. Collect cycles at the end of discovery to be as
    // efficient as possible.
    gc_collect_cycles();
    return $definitions;
  }

  /**
   * Parses attributes from a class.
   *
   * @param class-string $class
   *   The class to parse.
   * @param \SplFileInfo $fileinfo
   *   The SPL file information for the class.
   *
   * @return array
   *   An array with the keys 'id' and 'content'. The 'id' is the plugin ID and
   *   'content' is the plugin definition.
   *
   * @throws \ReflectionException
   * @throws \Error
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function parseClass(string $class, \SplFileInfo $fileinfo): array {
    // @todo Consider performance improvements over using reflection.
    // @see https://www.drupal.org/project/drupal/issues/3395260.
    $reflection_class = new \ReflectionClass($class);

    $id = $content = NULL;
    $third_party_attributes = [];
    if ($attributes = $reflection_class->getAttributes($this->pluginDefinitionAttributeName, \ReflectionAttribute::IS_INSTANCEOF)) {
      /** @var \Drupal\Component\Plugin\Attribute\AttributeInterface $attribute */
      $attribute = $attributes[0]->newInstance();
      $this->prepareAttributeDefinition($attribute, $class);

      $id = $attribute->getId();
      $content = $attribute->get();

      if ($property_reflectors = $reflection_class->getAttributes(PluginPropertyInterface::class, \ReflectionAttribute::IS_INSTANCEOF)) {
        foreach ($property_reflectors as $property_reflector) {
          $property_class = $property_reflector->getName();
          if (!str_starts_with($property_class, 'Drupal\\Component\\') &&
              !str_starts_with($property_class, 'Drupal\\Core\\')) {
            throw new InvalidPluginDefinitionException($id, sprintf('Invalid plugin property class: %s. Plugin property attributes can be provided only from core.', $property_class));
          }

          /** @var \Drupal\Component\Plugin\Attribute\PluginPropertyInterface $property_attribute */
          $property_attribute = $property_reflector->newInstance();
          $property_attribute->setClass($property_class);
          // Check that the property attribute is allowed to work with the plugin
          // attribute.
          if (!$property_attribute->isValidPluginClass($attribute::class)) {
            throw new InvalidPluginDefinitionException($id, sprintf('May not use plugin property class %s with main plugin attribute class "%s".', $property_class, $attribute::class));
          }
          if (empty($property_attribute->getModuleDependencies())) {
            // Add properties from attributes if they do not have module
            // dependencies, since they are not dependent on modules being
            // installed.
            $content = $property_attribute->addToDefinition($content);
            continue;
          }

          // Attributes with a provider other than core are saved separately.
          // They will be added to the definition after being retrieved from
          // file cache, if the module that is the provider is installed.
          $third_party_attributes[] = $property_attribute;
        }
      }
    }
    return ['id' => $id, 'content' => $content, 'third_party_attributes' => $third_party_attributes];
  }

  /**
   * Prepares the attribute definition.
   *
   * @param \Drupal\Component\Plugin\Attribute\AttributeInterface $attribute
   *   The attribute derived from the plugin.
   * @param string $class
   *   The class used for the plugin.
   */
  protected function prepareAttributeDefinition(AttributeInterface $attribute, string $class): void {
    $attribute->setClass($class);
  }

  /**
   * Gets an array of PSR-4 namespaces to search for plugin classes.
   *
   * @return string[][]
   */
  protected function getPluginNamespaces(): array {
    return $this->pluginNamespaces;
  }

  /**
   * Add properties from third party attributes.
   *
   * @param string $id
   *   The plugin ID.
   * @param array|object $definition
   *   The definition parsed from the plugin class attribute.
   * @param \Drupal\Component\Plugin\Attribute\PluginProperty[] $third_party_attributes
   *   Third-party attributes from modules that provide additional properties to'
   *   the definition.
   *
   * @return array|object
   *   The plugin definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function addThirdPartyPropertiesToDefinition(string $id, array|object $definition, array $third_party_attributes = []): array|object {
    // Need to filter third party attributes by installed modules, which will
    // happen in subclass method.
    // @see \Drupal\Core\Plugin\Discovery\AttributeClassDiscovery::addThirdPartyPropertiesToDefinition()
    return $definition;
  }

}
