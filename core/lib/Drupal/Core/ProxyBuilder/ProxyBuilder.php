<?php

namespace Drupal\Core\ProxyBuilder;

use Drupal\Component\ProxyBuilder\ProxyBuilder as BaseProxyBuilder;

/**
 * Extend the component proxy builder by using the DependencySerializationTrait.
 *
 * @deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. The native
 *   lazy services from Symfony are now being used. Therefore, there is no need
 *   for Drupal proxy classes anymore. There is no replacement.
 *
 * @see https://www.drupal.org/node/3397076
 */
class ProxyBuilder extends BaseProxyBuilder {

  /**
   * {@inheritdoc}
   */
  protected function buildUseStatements() {
    $output = parent::buildUseStatements();

    $output .= 'use \Drupal\Core\DependencyInjection\DependencySerializationTrait;' . "\n\n";

    return $output;
  }

}
