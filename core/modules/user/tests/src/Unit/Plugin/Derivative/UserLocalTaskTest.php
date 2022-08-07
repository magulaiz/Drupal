<?php

namespace Drupal\Tests\user\Unit\Plugin\Derivative;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Plugin\Derivative\UserLocalTask;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the local tasks deriver class.
 *
 * @coversDefaultClass \Drupal\user\Plugin\Derivative\UserLocalTask
 * @group user
 */
class UserLocalTaskTest extends UnitTestCase {

  /**
   * The local tasks deriver.
   *
   * @var \Drupal\user\Plugin\Derivative\UserLocalTask
   */
  protected $deriver;

  protected function setUp(): void {
    parent::setUp();

    $prophecy = $this->prophesize(EntityTypeInterface::class);
    $prophecy->hasLinkTemplate('entity-permissions-form')->willReturn(FALSE);
    $entity_no_link_template = $prophecy->reveal();

    $prophecy = $this->prophesize(EntityTypeInterface::class);
    $prophecy->hasLinkTemplate('entity-permissions-form')->willReturn(TRUE);
    $prophecy->getBundleOf()->willReturn(NULL);
    $entity_no_bundle_of = $prophecy->reveal();

    $prophecy = $this->prophesize(EntityTypeInterface::class);
    $prophecy->hasLinkTemplate('entity-permissions-form')->willReturn(TRUE);
    $prophecy->getBundleOf()->willReturn('content_entity_type_id');
    $entity_bundle_of = $prophecy->reveal();

    $prophecy = $this->prophesize(EntityTypeInterface::class);
    $prophecy->hasLinkTemplate('entity-permissions-form')->willReturn(FALSE);
    $prophecy->get('field_ui_base_route')->willReturn('field_ui.base_route');
    $content_entity_type = $prophecy->reveal();

    $prophecy = $this->prophesize(EntityTypeManagerInterface::class);
    $prophecy->getDefinitions()->willReturn([
      'entity_no_link_template_id' => $entity_no_link_template,
      'entity_no_bundle_of_id' => $entity_no_bundle_of,
      'entity_bundle_of_id' => $entity_bundle_of,
      'content_entity_type_id' => $content_entity_type,
    ]);
    $entity_type_manager = $prophecy->reveal();

    // The route provider is only used to check that the route exists, so make
    // sure that getRoutesByNames() returns a non-empty array.
    $prophecy = $this->prophesize(RouteProviderInterface::class);
    $prophecy->getRoutesByNames(['entity.entity_bundle_of_id.entity_permissions_form'])
      ->willReturn(['not empty']);
    $routeProvider = $prophecy->reveal();

    $this->deriver = new UserLocalTask($entity_type_manager, $this->getStringTranslationStub(), $routeProvider);
  }

  /**
   * Tests the derivatives generated for local tasks.
   *
   * @covers \Drupal\user\Plugin\Derivative\UserLocalTask::getDerivativeDefinitions()
   */
  public function testGetDerivativeDefinitions() {
    $expected = [
      'permissions_entity_bundle_of_id' => [
        'route_name' => 'entity.entity_bundle_of_id.entity_permissions_form',
        'weight' => 10,
        'title' => $this->getStringTranslationStub()->translate('Manage permissions'),
        'base_route' => 'field_ui.base_route',
      ],
    ];
    $this->assertEquals($expected, $this->deriver->getDerivativeDefinitions([]));
  }

  /**
   * Tests UserLocalTask deprecation.
   *
   * @group legacy
   */
  public function testUserLocalTaskDeprecation() {
    $prophecy = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager = $prophecy->reveal();
    $prophecy = $this->prophesize(RouteProviderInterface::class);
    $route_provider = $prophecy->reveal();
    $prophecy = $this->prophesize(ContainerInterface::class);
    $prophecy->get('router.route_provider')->willReturn($route_provider);
    \Drupal::setContainer($prophecy->reveal());

    $this->expectDeprecation('Calling Drupal\user\Plugin\Derivative\UserLocalTask::__construct without the $route_provider argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3302306');
    $this->deriver = new UserLocalTask($entity_type_manager, $this->getStringTranslationStub());
  }

}
