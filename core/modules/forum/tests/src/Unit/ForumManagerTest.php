<?php

namespace Drupal\Tests\forum\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\forum\ForumManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\forum\ForumManager
 * @group forum
 */
class ForumManagerTest extends UnitTestCase {

  /**
   * Tests that deprecations are raised for missing constructor arguments.
   *
   * @group legacy
   */
  public function testConstructorDeprecations(): void {

    $container = new ContainerBuilder();
    $container->set('current_user', $this->createMock(AccountProxy::class));
    \Drupal::setContainer($container);

    $this->expectDeprecation('Calling ' . ForumManager::class . '::__construct() without the $current_user argument is deprecated in drupal:10.1.0 and will be required before drupal:11.0.0. See https://www.drupal.org/node/145353.');

  }

  /**
   * Tests ForumManager::getIndex().
   */
  public function testGetIndex() {
    $entity_field_manager = $this->createMock(EntityFieldManagerInterface::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);

    $storage = $this->getMockBuilder('\Drupal\taxonomy\VocabularyStorage')
      ->disableOriginalConstructor()
      ->getMock();

    $config_factory = $this->createMock('\Drupal\Core\Config\ConfigFactoryInterface');

    $config = $this->getMockBuilder('\Drupal\Core\Config\Config')
      ->disableOriginalConstructor()
      ->getMock();

    $config_factory->expects($this->once())
      ->method('get')
      ->willReturn($config);

    $config->expects($this->once())
      ->method('get')
      ->willReturn('forums');

    $entity_type_manager->expects($this->once())
      ->method('getStorage')
      ->willReturn($storage);

    // This is sufficient for testing purposes.
    $term = new \stdClass();

    $storage->expects($this->once())
      ->method('create')
      ->willReturn($term);

    $connection = $this->getMockBuilder('\Drupal\Core\Database\Connection')
      ->disableOriginalConstructor()
      ->getMock();

    $translation_manager = $this->getMockBuilder('\Drupal\Core\StringTranslation\TranslationManager')
      ->disableOriginalConstructor()
      ->getMock();

    $comment_manager = $this->getMockBuilder('\Drupal\comment\CommentManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $current_user = $this->getMockBuilder('\Drupal\Core\Session\AccountProxy')
      ->disableOriginalConstructor()
      ->getMock();

    $manager = $this->getMockBuilder('\Drupal\forum\ForumManager')
      ->onlyMethods(['getChildren'])
      ->setConstructorArgs([
        $config_factory,
        $entity_type_manager,
        $connection,
        $translation_manager,
        $comment_manager,
        $entity_field_manager,
        $current_user,
      ])
      ->getMock();

    $manager->expects($this->once())
      ->method('getChildren')
      ->willReturn([]);

    // Get the index once.
    $index1 = $manager->getIndex();

    // Get it again. This should not return the previously generated index. If
    // it does not, then the test will fail as the mocked methods will be called
    // more than once.
    $index2 = $manager->getIndex();

    $this->assertEquals($index1, $index2);
  }

}
