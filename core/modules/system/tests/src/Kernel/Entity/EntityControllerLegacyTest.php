<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Entity\Controller\EntityController;

/**
 * Tests that entity controller optional parameter deprecation.
 *
 * @group Entity
 * @group legacy
 */
class EntityControllerLegacyTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test'];

  /**
   * @covers ::__construct
   */
  public function testOptionalParameterDeprecation(): void {

    $this->expectDeprecation('Calling Drupal\Core\Entity\Controller\EntityController::__construct() without the $route_match argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/2897251');

    new EntityController(
      $this->container->get('entity_type.manager'),
      $this->container->get('entity_type.bundle.info'),
      $this->container->get('entity.repository'),
      $this->container->get('renderer'),
      $this->container->get('string_translation'),
      $this->container->get('url_generator'),
      NULL
    );
  }

}
