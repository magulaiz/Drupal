<?php

namespace Drupal\Tests\language\Unit\Plugin\LanguageNegotiation;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a class for creating plugins via factory.
 */
trait LanguageNegotiationFactoryTrait {

  /**
   * Returns the plugin class to use for creating the language negotiation plugin.
   *
   * @return string
   *   The plugin class name.
   */
  abstract protected function getPluginClass(): string;
  
  /**
   * Creates a @LanguageNegotiation plugin using the factory ::create method.
   *
   * @return \Drupal\language\LanguageNegotiationMethodInterface
   */
  private function createLanguageNegotiationPlugin(array $configuration = [], $plugin_definition = NULL) {
    $this->assertTrue(in_array(ContainerFactoryPluginInterface::class, class_implements($this->getPluginClass())));
    return $this->getPluginClass()::create(\Drupal::getContainer(), $configuration, $this->getPluginClass()::METHOD_ID, $plugin_definition);
  }

}
