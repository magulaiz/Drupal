<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\ModuleDiscovery;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\module_discovery_attribute_method_service_test\AttributeToService\TestMethodService;
use Drupal\module_discovery_attribute_other_service_test\AttributeToService\OtherTestClassService;
use Drupal\module_discovery_attribute_service_test\AttributeToService\TestClassService;
use Drupal\module_discovery_collision_test\ExistingServiceByClass;
use Drupal\module_discovery_collision_test\NewDiscoveryService;
use Drupal\module_discovery_definition_template_test\ServiceDefinitionTemplate\TestServiceDefinitionTemplate;
use Drupal\module_discovery_glob_test\FirstService;
use Drupal\module_discovery_glob_test\Second\SecondService;
use Drupal\module_discovery_glob_test\Second\Third\ThirdService;
use Drupal\module_discovery_glob_test\Second\UnmatchedSecondService;
use Drupal\module_discovery_test\TestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Tests services automatically created from 'module' or 'modules' paths.
 *
 * @group ModuleDiscovery
 */
final class ModuleDiscoveryTest extends KernelTestBase {

  /**
   * Tests 'src' path for 'module' converts classes to services.
   */
  public function testSrc(): void {
    $this->moduleInstaller()->install(['module_discovery_test']);
    $this->assertInstanceOf(TestService::class, \Drupal::getContainer()->get(TestService::class));
  }

  /**
   * Tests 'src' path for 'modules' throws an exception.
   */
  public function testSrcInModules(): void {
    $this->expectExceptionMessage('Paths for all modules must be a subdirectory of src/.');
    $this->moduleInstaller()->install(['module_discovery_all_src_test']);
  }

  /**
   * Tests 'resource' alongside 'module' throws an exception.
   */
  public function testResourceWithModule(): void {
    $this->expectExceptionMessage('Service definitions may not have a `module` or `modules` definition simultaneously with a `resource` definition.');
    $this->moduleInstaller()->install(['module_discovery_with_resource_test']);
  }

  /**
   * Referencing directories outside its own directory throw an exception.
   */
  public function testEscapeDirectory(): void {
    $this->expectExceptionMessage('Paths may not escape extension src/ directories with relative paths.');
    $this->moduleInstaller()->install(['module_discovery_escape_test']);
  }

  /**
   * Tests existing services are not re-created.
   */
  public function testExistingServices(): void {
    $this->moduleInstaller()->install(['module_discovery_collision_test']);
    $container = \Drupal::getContainer();
    // These services are defined in YAML and are not overridden.
    $this->assertEquals('definition_from_yaml', $container->get('service_by_id')->definitionSource);
    $this->assertEquals('definition_from_yaml', $container->get(ExistingServiceByClass::class)->definitionSource);
    // This service is automatically created.
    $this->assertEquals('definition_from_discovery', $container->get(NewDiscoveryService::class)->definitionSource);
  }

  /**
   * Tests globs patterns do not work.
   */
  public function testGlobbingPatterns(): void {
    $this->expectExceptionMessage('Globbing is not supported in patterns.');
    $this->moduleInstaller()->install(['module_discovery_glob_test']);
  }

  /**
   * Test a class that cannot be reflected.
   */
  public function testMalformedClass(): void {
    $this->expectExceptionMessage('Class "Drupal\module_discovery_malformed_class_test\BadInterface" not found while loading "Drupal\module_discovery_malformed_class_test\BadClass".');
    $this->moduleInstaller()->install(['module_discovery_malformed_class_test']);
  }

  /**
   * Tests services with autoconfigured attributes are created.
   */
  public function testAutoconfigureAttribute(): void {
    $this->moduleInstaller()->install([
      'module_discovery_attribute_service_test',
      'module_discovery_attribute_other_service_test',
      'module_discovery_attribute_method_service_test',
    ]);

    $container = \Drupal::getContainer();
    $this->assertInstanceOf(TestClassService::class, $container->get(TestClassService::class));
    $this->assertInstanceOf(OtherTestClassService::class, $container->get(OtherTestClassService::class));
    $this->assertInstanceOf(TestMethodService::class, $container->get(TestMethodService::class));

    // Runtime code cant tell if a service received a tag or not, so we rely on
    // a service locator to bring them together.
    /** @var \Symfony\Component\DependencyInjection\ServiceLocator $locator */
    $locator = $container->get('autoconfigure_attribute_to_service_locator');
    $this->assertInstanceOf(TestClassService::class, $locator->get(TestClassService::class));
    $this->assertInstanceOf(OtherTestClassService::class, $locator->get(OtherTestClassService::class));
    $this->assertInstanceOf(TestMethodService::class, $locator->get(TestMethodService::class));
    // Ensure no other services were added to the locator, and that method
    // service with multiple attributes did not create multiple services.
    $this->assertCount(3, $locator);
  }

  /**
   * Tests defaults from the service definition are copied to created services.
   */
  public function testDerivativeServiceDefinitions(): void {
    $this->moduleInstaller()->install([
      'module_discovery_definition_template_test',
    ]);

    $container = \Drupal::getContainer();
    $this->assertInstanceOf(TestServiceDefinitionTemplate::class, $container->get(TestServiceDefinitionTemplate::class));

    // Runtime code cant tell if a service received a tag or not, so we rely on
    // a service locator to bring them together.
    /** @var \Symfony\Component\DependencyInjection\ServiceLocator $locator */
    $locator = $container->get('definition_template_locator');
    $this->assertCount(1, $locator);
    $service = $locator->get(TestServiceDefinitionTemplate::class);
    $this->assertInstanceOf(TestServiceDefinitionTemplate::class, $service);
    // Call the method to ensure autowiring.
    $this->assertStringContainsString('The time is', $service->getTime());
  }

  /**
   * The module installer.
   */
  private function moduleInstaller(): ModuleInstallerInterface {
    return \Drupal::service('module_installer');
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    // Failed module install with invalid class causes requests to blow up.
    $request = Request::create('');
    $request->setSession(new Session(new MockArraySessionStorage()));
    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $this->container->get('request_stack');
    $request_stack->push($request);

    parent::tearDown();
  }

}
