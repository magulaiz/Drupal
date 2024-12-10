<?php

namespace Drupal\Component\Plugin\Discovery;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Component\FileCache\FileCacheFactory;
use Drupal\Component\FileCache\FileCacheInterface;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

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
                  $dependencies = !empty($cached['dependencies']) ? unserialize($cached['dependencies']) : [];
                  if (!is_array($dependencies) || !$this->hasMissingClassDependencies($dependencies)) {
                    // Explicitly unserialize this to create a new object instance.
                    $definitions[$cached['id']] = unserialize($cached['content']);
                  }
                }
                continue;
              }

              $sub_path = $iterator->getSubIterator()->getSubPath();
              $sub_path = $sub_path ? str_replace(DIRECTORY_SEPARATOR, '\\', $sub_path) . '\\' : '';
              $class = $namespace . '\\' . $sub_path . $fileinfo->getBasename('.php');
              try {
                ['id' => $id, 'content' => $content, 'dependencies' => $dependencies] = $this->parseClass($class, $fileinfo);
                if ($id) {
                  $definitions[$id] = $content;
                  // Explicitly serialize this to create a new object instance.
                  $this->fileCache->set($fileinfo->getPathName(), ['id' => $id, 'content' => serialize($content), 'dependencies' => serialize($dependencies)]);
                }
                elseif (empty($dependencies)) {
                  // Store a NULL object, so that the file is not parsed again.
                  // If there are dependencies, do not store, so that the class
                  // can be parsed again later to check whether dependencies are
                  // met.
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
   */
  protected function parseClass(string $class, \SplFileInfo $fileinfo): array {
    // Use PHPParser to check class does not have any missing dependencies. This
    // is to check that the plugin class does not have any missing dependencies
    // (extended class, implemented interfaces, or used traits) which would
    // make reflection throw exceptions or cause a fatal error.
    if (!($static_parsed_class = $this->getStaticParsedClass($fileinfo))) {
      return ['id' => NULL, 'content' => NULL, 'dependencies' => NULL];
    }
    if (($dependencies = $this->getClassDependencies($static_parsed_class)) &&
         $this->hasMissingClassDependencies($dependencies)) {
      return ['id' => NULL, 'content' => NULL, 'dependencies' => $dependencies];
    }

    // @todo Consider performance improvements over using reflection.
    // @see https://www.drupal.org/project/drupal/issues/3395260.
    $reflection_class = new \ReflectionClass($class);

    $id = $content = NULL;
    if ($attributes = $reflection_class->getAttributes($this->pluginDefinitionAttributeName, \ReflectionAttribute::IS_INSTANCEOF)) {
      /** @var \Drupal\Component\Plugin\Attribute\AttributeInterface $attribute */
      $attribute = $attributes[0]->newInstance();
      $this->prepareAttributeDefinition($attribute, $class);

      $id = $attribute->getId();
      $content = $attribute->get();
    }
    return ['id' => $id, 'content' => $content, 'dependencies' => $dependencies];
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
   *   An array of namespaces to search.
   */
  protected function getPluginNamespaces(): array {
    return $this->pluginNamespaces;
  }

  /**
   * Get the dependencies for the class.
   *
   * @param \PhpParser\Node\Stmt\Class_ $static_parsed_class
   *   The plugin class as a statically parsed object.
   *
   * @return string[][]
   *   The dependencies for the class, if any, as a two-dimensional array of
   *   dependency names indexed by the type, such as 'class' or 'interface' or
   *   'trait'.
   */
  protected function getClassDependencies(Class_ $static_parsed_class): array {
    // Get lists of interface, class, and trait dependencies. Note that this
    // list will be filtered by providers in the Core subclass method
    // AttributeClassDiscovery::getClassDependencies().
    $interfaces = [];
    foreach ($static_parsed_class->implements as $interface) {
      $interfaces[] = (string) $interface;
    }
    $extends = [];
    if ($static_parsed_class->extends) {
      $extends[] = (string) $static_parsed_class->extends;
    }
    $traits = [];
    foreach ($static_parsed_class->getTraitUses() as $trait_use) {
      foreach ($trait_use->traits as $trait) {
        $traits[] = (string) $trait;
      }
      foreach ($trait_use->adaptations as $adaptation) {
        if ($adaptation->trait) {
          $traits[] = (string) $adaptation->trait;
        }
      }
    }

    return [
      'class' => $extends,
      'interface' => $interfaces,
      'trait' => $traits,
    ];
  }

  /**
   * Get the class parsed by PhpParser, with namespaces resolved.
   *
   * @param \SplFileInfo $fileinfo
   *   File info for the class file.
   *
   * @return \PhpParser\Node\Stmt\Class_|null
   *   The parsed class object.
   */
  protected function getStaticParsedClass(\SplFileInfo $fileinfo): ?Class_ {
    $parser = (new ParserFactory())->createForHostVersion();
    $stmts = $parser->parse(file_get_contents($fileinfo->getPathname()));
    $nameResolver = new NameResolver();
    $nodeTraverser = new NodeTraverser();
    $nodeTraverser->addVisitor($nameResolver);
    $stmts = $nodeTraverser->traverse($stmts);
    $nodeFinder = new NodeFinder();
    return $nodeFinder->findFirstInstanceOf($stmts, Class_::class);
  }

  /**
   * Whether any of the dependencies in the list are missing.
   *
   * @param string[][] $dependencies
   *   A two-dimensional array of dependency names indexed by the type, such as
   *   'class' or 'interface' or 'trait'.
   *
   * @return bool
   *   TRUE if any of the dependencies are not found.
   */
  protected function hasMissingClassDependencies(array $dependencies): bool {
    foreach ($dependencies as $type => $names) {
      if (!in_array($type, ['class', 'interface', 'trait'])) {
        continue;
      }
      foreach ($names as $name) {
        // Doing *_exists() checks here on the identified dependencies does run
        // a risk that additional dependencies in the hierarchies of the
        // dependencies are missing, resulting in an exception or fatal error.
        // The rationale behind running these checks is that that risk is lower
        // than the risk of the identified dependencies themselves being missing
        // and causing a thrown exception or fatal error on reflection of the
        // plugin class. In the case that an exception or fatal error does
        // result here, then the Drupal\Component\Plugin\Attribute\Dependencies
        // attribute should be added to the plugin class to explicitly define
        // the missing providers.
        if (!call_user_func($type . '_exists', $name)) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
