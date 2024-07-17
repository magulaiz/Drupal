<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Defines a compiler pass to allow automatic override per backend.
 *
 * A module developer has to tag a backend service with "backend_overridable":
 * @code
 * custom_service:
 *   class: ...
 *   tags:
 *     - { name: backend_overridable }
 * @endcode
 *
 * As a site admin you set the 'default_backend' in your services.yml file:
 * @code
 * parameters:
 *   default_backend: sqlite
 * @endcode
 *
 * As a developer for alternative storage engines you register a service with
 * $your_backend.$original_service:
 *
 * @code
 * sqlite.custom_service:
 *   class: ...
 * @endcode
 */
class BackendCompilerPass implements CompilerPassInterface {

  public const string BACKEND_OVERRIDE_SERVICE_TAG = '_backend_override_service';

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    $driver_backend = NULL;
    if ($container->hasParameter('default_backend')) {
      $default_backend = $container->getParameter('default_backend');
      // Opt out from the default backend.
      if (!$default_backend) {
        return;
      }
    }
    else {
      try {
        $driver_backend = $container->get('database')->driver();
        $default_backend = $container->get('database')->databaseType();
        $container->set('database', NULL);
      }
      catch (\Exception) {
        // If Drupal is not installed or a test doesn't define database there
        // is nothing to override.
        return;
      }
    }

    foreach ($container->findTaggedServiceIds('backend_overridable') as $id => $attributes) {
      // Previously, overrides were registered as aliases. If a container has
      // a registered alias for the overridden service, assume that's
      // purposeful and do not kludge the alias. We also avoid re-processing
      // an override by matching a tag indicating the overriding service.
      if ($container->getDefinition($id)->hasTag(self::BACKEND_OVERRIDE_SERVICE_TAG) || $container->hasAlias($id)) {
        continue;
      }
      foreach (["$driver_backend.$id", "$default_backend.$id"] as $candidateOverride) {
        if ($container->hasDefinition($candidateOverride) || $container->hasAlias($candidateOverride)) {
          $this->overrideService($container, $id, $candidateOverride);
          break;
        }
      }
    }
  }

  /**
   * Override a service by replacement.
   *
   * Backend-overridden services cannot be aliases, as they cannot be tagged.
   * Services injected lazily (e.g. with a service locator) might depend on tag
   * matching, and some factories request services by their original name.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   Container builder.
   * @param string $id
   *   ID of service to replace.
   * @param string $overrideId
   *   ID of service definition or alias to use for replacement.
   */
  protected function overrideService(ContainerBuilder $container, string $id, string $overrideId): void {
    $override = $container->hasDefinition($overrideId)
      ? clone $container->getDefinition($overrideId)
      : $container->getAlias($overrideId);
    if ($override instanceof Alias) {
      $override = clone $container->getDefinition((string) $override);
    }
    assert($override instanceof Definition);
    $override->addTag(self::BACKEND_OVERRIDE_SERVICE_TAG, ['service' => $overrideId]);
    foreach ($container->getDefinition($id)->getTags() as $tag => $attributes) {
      if (!in_array($tag, ['backend_overridable', '_provider'])) {
        $override->addTag($tag, $attributes);
      }
    }
    $override->setPublic(TRUE);
    $container->setDefinition($id, $override);
  }

}
