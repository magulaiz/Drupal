<?php

declare(strict_types=1);

namespace Drupal\migrate\Plugin\Discovery;

use Drupal\Component\Annotation\Doctrine\StaticReflectionParser as BaseStaticReflectionParser;
use Drupal\Component\Annotation\Reflection\MockFileFinder;
use Drupal\Component\ClassFinder\ClassFinder;
use Drupal\Core\Plugin\Discovery\AttributeClassDiscovery;
use Drupal\Core\Plugin\Discovery\AttributeDiscoveryWithAnnotations;

/**
 * Enables both attribute and annotation discovery for plugin definitions.
 *
 * @internal
 *   This is a temporary solution to the fact that migration source plugins have
 *   more than one provider. This functionality will be moved to core in
 *   https://www.drupal.org/node/2786355.
 */
class AttributeDiscoveryWithAnnotationsAutomatedProviders extends AttributeDiscoveryWithAnnotations {

  use AnnotatedDiscoveryAutomatedProvidersTrait;

  public function __construct(
    string $subdir,
    \Traversable $rootNamespaces,
    string $pluginDefinitionAttributeName = 'Drupal\Component\Plugin\Attribute\Plugin',
    string $pluginDefinitionAnnotationName = 'Drupal\Component\Annotation\Plugin',
    array $additionalNamespaces = [],
  ) {
    parent::__construct($subdir, $rootNamespaces, $pluginDefinitionAttributeName, $pluginDefinitionAnnotationName, $additionalNamespaces);
    $this->finder = new ClassFinder();
  }

  /**
   * {@inheritdoc}
   *
   * The only significant change here from the parent method is passing FALSE
   * for classAnnotationOptimize when creating new static reflection parser.
   */
  protected function parseClass(string $class, \SplFileInfo $fileinfo): array {
    $finder = MockFileFinder::create($fileinfo->getPathName());
    // Note that the parser is instantiated here with FALSE as the last
    // parameter. This is needed so that the parser includes the 'extends'
    // declaration and extracts providers from ancestor classes.
    $parser = new BaseStaticReflectionParser($class, $finder, FALSE);

    $reflection_class = $parser->getReflectionClass();
    /** @var \Drupal\Component\Annotation\AnnotationInterface $annotation */
    if ($annotation = $this->getAnnotationReader()->getClassAnnotation($reflection_class, $this->pluginDefinitionAnnotationName)) {
      $this->prepareAnnotationDefinition($annotation, $class, $parser);
      return ['id' => $annotation->getId(), 'content' => $annotation->get()];
    }

    if ($reflection_class->hasClassAttribute($this->pluginDefinitionAttributeName)) {
      // Do not call the parent class method, since it will try to parse for
      // annotations again, but call the core ancestor class ::parseClass().
      return AttributeClassDiscovery::parseClass($class, $fileinfo);
    }
    return ['id' => NULL, 'content' => NULL];
  }

}
