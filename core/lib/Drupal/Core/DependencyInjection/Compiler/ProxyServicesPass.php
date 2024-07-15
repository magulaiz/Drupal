<?php

namespace Drupal\Core\DependencyInjection\Compiler;

use Drupal\Component\ProxyBuilder\ProxyBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replaces all services with a lazy flag.
 *
 * @see lazy_services
 */
class ProxyServicesPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    foreach ($container->getDefinitions() as $service_id => $definition) {
      if ($definition->isLazy()) {
        $proxy_class = ProxyBuilder::buildProxyClassName($definition->getClass());
        if (class_exists($proxy_class)) {
          // Copy the existing definition to a new entry.
          $definition->setLazy(FALSE);
          // Ensure that the service is accessible.
          $originally_public = $definition->isPublic();
          $definition->setPublic(TRUE);
          $new_service_id = 'drupal.proxy_original_service.' . $service_id;
          $container->setDefinition($new_service_id, $definition);

          $proxy_definition = (new Definition($proxy_class))
            ->setPublic($originally_public)
            ->setArguments([new Reference('service_container'), $new_service_id]);
          // Backend-overridable services are special-cased, as their tags
          // are processed based on the original service ID in
          // BackendCompilerPass.
          if ($definition->hasTag('backend_overridable')) {
            $proxy_definition->addTag('backend_overridable');
            $definition->clearTag('backend_overridable');
          }
          $container->setDefinition($service_id, $proxy_definition);
        }
        else {
          $class_name = $definition->getClass();

          // Find the root namespace.
          $match = [];
          preg_match('/([a-zA-Z0-9_]+\\\\[a-zA-Z0-9_]+)\\\\(.+)/', $class_name, $match);
          $root_namespace = $match[1];

          // Find the root namespace path.
          $root_namespace_dir = '[namespace_root_path]';

          $namespaces = $container->getParameter('container.namespaces');

          // Hardcode Drupal Core, because it is not registered.
          $namespaces['Drupal\Core'] = 'core/lib/Drupal/Core';

          if (isset($namespaces[$root_namespace])) {
            $root_namespace_dir = $namespaces[$root_namespace];
          }

          $message = <<<EOF

Missing proxy class '$proxy_class' for lazy service '$service_id'.
Use the following command to generate the proxy class:
  php core/scripts/generate-proxy-class.php '$class_name' "$root_namespace_dir"


EOF;
          trigger_error($message, E_USER_WARNING);
        }
      }
    }
  }

}
