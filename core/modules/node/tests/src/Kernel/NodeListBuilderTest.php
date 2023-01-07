<?php

namespace Drupal\Tests\node\Kernel;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\NodeListBuilder;

/**
 * Tests the admin listing fallback when views is not enabled.
 *
 * @coversDefaultClass \Drupal\node\NodeListBuilder
 *
 * @group node
 */
class NodeListBuilderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
  }

  /**
   * Tests that the correct cache contexts are set.
   */
  public function testCacheContexts() {
    /** @var \Drupal\Core\Entity\EntityListBuilderInterface $list_builder */
    $list_builder = $this->container->get('entity_type.manager')->getListBuilder('node');

    $build = $list_builder->render();
    $this->container->get('renderer')->renderRoot($build);

    $this->assertEqualsCanonicalizing(['languages:' . LanguageInterface::TYPE_INTERFACE, 'theme', 'url.query_args.pagers:0', 'user.node_grants:view', 'user.permissions'], $build['#cache']['contexts']);
  }

  /**
   * Tests deprecation of constructing a NodeListBuilderTest object without the renderer argument.
   *
   * @covers ::__construct
   * @group legacy
   */
  public function testNodeListBuilderDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\node\NodeListBuilder::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new NodeListBuilder(
      $this->prophesize(EntityTypeInterface::class)->reveal(),
      $this->container->get('entity_type.manager')->getStorage('node'),
      $this->container->get('date.formatter'),
      $this->container->get('redirect.destination')
    );
  }

}
