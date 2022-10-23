<?php

namespace Drupal\Tests\language\Unit\Plugin\LanguageNegotiation;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a class for creating plugins via factory.
 */
trait LanguageNegotiationFactoryTrait {

  /**
   * Creates a @LanguageNegotiaton plugin using the factory ::create method.
   *
   * @return \Drupal\language\LanguageNegotiationMethodInterface
   */
  private function createLanguageNegotiationPlugin(array $configuration = [], $plugin_definition = NULL) {
    $this->assertTrue(in_array(ContainerFactoryPluginInterface::class, class_implements(self::PLUGIN_CLASS)));
    return self::PLUGIN_CLASS::create(\Drupal::getContainer(), $configuration, self::PLUGIN_CLASS::METHOD_ID, $plugin_definition);
  }

}
