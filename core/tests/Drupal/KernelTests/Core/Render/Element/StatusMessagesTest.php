<?php

namespace Drupal\KernelTests\Core\Render\Element;

use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\Render\Element\StatusMessages;
use Drupal\Core\Render\ElementInfoManager;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Status messages plugin class that returns a test render array.
 */
class TestStatusMessages extends StatusMessages {

  /**
   * {@inheritdoc}
   */
  public static function renderMessages($display = NULL) {
    return ['#plain_text' => 'test successful!'];
  }

}

/**
 * Element info manager that returns a test status messages element.
 */
class TestElementInfoManager extends ElementInfoManager {

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    if ($plugin_id === 'status_messages') {
      return new TestStatusMessages($configuration, $plugin_id, $this->getDefinition($plugin_id));
    }
    return parent::createInstance($plugin_id, $configuration);
  }

}
/**
 * Tests the status_messages element.
 */
class StatusMessagesTest extends KernelTestBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $service_definition = $container->getDefinition('plugin.manager.element_info');
    $service_definition->setClass(TestElementInfoManager::class);
  }

  /**
   * Tests whether the child class' methods are called when extending.
   */
  public function testPluginAlter() {
    $build = ['#type' => 'status_messages'];
    $result = $this->container->get('renderer')->renderPlain($build);

    self::assertEquals('test successful!', $result);
  }

}
