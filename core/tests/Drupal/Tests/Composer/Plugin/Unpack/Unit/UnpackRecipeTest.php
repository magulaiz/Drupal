<?php

declare(strict_types=1);

namespace core\tests\Drupal\Tests\Composer\Plugin\Unpack\Unit;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Semver\Constraint\ConstraintInterface;
use Drupal\Composer\Plugin\Unpack\RootComposer;
use Drupal\Composer\Plugin\Unpack\UnpackCollection;
use Drupal\Composer\Plugin\Unpack\Unpackers\RecipeUnpacker;
use Drupal\Composer\Plugin\Unpack\Unpackers\UnpackerFactory;
use Drupal\Composer\Plugin\Unpack\Unpackers\UnpackerInterface;
use Drupal\Composer\Plugin\Unpack\UnpackManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UnpackRecipeTest extends TestCase {

  use ProphecyTrait;

  /**
   * The unpack manager.
   *
   * @var \Drupal\Composer\Plugin\Unpack\UnpackManager
   */
  protected UnpackManager $unpackManager;

  /**
   * The unpack collection.
   *
   * @var \Drupal\Composer\Plugin\Unpack\UnpackCollection
   */
  protected UnpackCollection $unpackCollection;

  /**
   * The unpacker factory.
   *
   * @var \Drupal\Composer\Plugin\Unpack\Unpackers\UnpackerFactory
   */
  protected UnpackerFactory $unpackerFactory;

  /**
   * The root composer.
   *
   * @var \Drupal\Composer\Plugin\Unpack\RootComposer
   */
  protected RootComposer $rootComposer;

  /**
   * The composer.
   *
   * @var \Composer\Composer
   */
  protected Composer $composer;

  /**
   * The io.
   *
   * @var \Composer\IO\IOInterface
   */
  protected IOInterface $io;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $root_package = $this->prophesize(RootPackageInterface::class);
    $root_package->getExtra()->willReturn([
      'drupal-unpack' => [
        'ignore' => ['drupal/core'],
      ],
    ]);
    $composer = $this->getMockBuilder(Composer::class)
      ->onlyMethods(['getPackage'])
      ->getMock();
    $composer->expects($this->any())
      ->method('getPackage')
      ->willReturn($root_package->reveal());
    $this->composer = $composer;
    $this->io = $this->createMock(IOInterface::class);
    $this->unpackManager = new UnpackManager($this->composer, $this->io);
    $this->unpackCollection = $this->getPropertyReflection($this->unpackManager, 'unpackCollection');
    $this->rootComposer = $this->getPropertyReflection($this->unpackManager, 'rootComposer');
    $this->unpackerFactory = $this->getPropertyReflection($this->unpackManager, 'unpackerFactory');
  }

  /**
   * Test that only supported packages are registered.
   */
  public function testRegisterPackage(): void {
    $recipe_package = $this->createPackage('drupal-recipe', 'drupal/recipe-a');
    $recipe_event = $this->createInstallEvent($recipe_package);
    $this->unpackManager->registerPackage($recipe_event);
    $this->assertEquals($this->unpackCollection->popPackageQueue(), $recipe_package);

    $non_recipe_package = $this->createPackage('drupal-module', 'drupal/module-a');
    $non_recipe_event = $this->createInstallEvent($non_recipe_package);
    $this->unpackManager->registerPackage($non_recipe_event);
    $this->assertNull($this->unpackCollection->popPackageQueue());
  }

  /**
   * Test that only unpackable packages are handled.
   */
  public function testPackageIsUnpackable(): void {
    $recipe_package = $this->createPackage('drupal-recipe', 'drupal/recipe-a');
    $this->assertTrue($this->unpackerFactory->isUnpackable($recipe_package));
    $unpacker = $this->unpackerFactory->create($recipe_package);
    $this->assertInstanceOf(UnpackerInterface::class, $unpacker);

    $non_recipe_package = $this->createPackage('drupal-module', 'drupal/module-a');
    $this->assertFalse($this->unpackerFactory->isUnpackable($non_recipe_package));
    $this->assertNull($this->unpackerFactory->create($non_recipe_package));
  }

  /**
   * Test that required packages from a recipe are unpacked.
   */
  public function testUnpacksRequiredPackages(): void {
    // Simulate an already unpacked recipe.
    $already_unpacked_recipe = $this->createPackage('drupal-recipe', 'drupal/recipe-a');

    // Assert that once a package is unpacked, it is added to the collection.
    $this->unpackCollection->addUnpackedPackage($already_unpacked_recipe);
    $this->assertTrue($this->unpackCollection->isUnpacked($already_unpacked_recipe));

    $recipe_to_unpack = $this->createPackage('drupal-recipe', 'drupal/recipe-b');
    $mock_constraint = $this->createMock(ConstraintInterface::class);
    $recipe_dependencies = [
      'module' => new Link('drupal/recipe-b', 'drupal/module-a', $mock_constraint, Link::TYPE_REQUIRE, '1.0.0'),
      'theme' => new Link('drupal/recipe-b', 'drupal/theme-a', $mock_constraint, Link::TYPE_REQUIRE, '1.0.0'),
      'recipe' => new Link('drupal/recipe-b', 'drupal/recipe-c', $mock_constraint, Link::TYPE_REQUIRE, '1.0.0'),
      'unpacked_recipe' => new Link('drupal/recipe-b', 'drupal/recipe-a', $mock_constraint, Link::TYPE_REQUIRE, '1.0.0'),
      'same_recipe' => new Link('drupal/recipe-b', 'drupal/recipe-b', $mock_constraint, Link::TYPE_REQUIRE, '1.0.0'),
      'ignore_module' => new Link('drupal/recipe-b', 'drupal/core', $mock_constraint, Link::TYPE_REQUIRE, '1.0.0'),
    ];
    $recipe_to_unpack->expects($this->any())
      ->method('getRequires')
      ->willReturn($recipe_dependencies);

    // Mock the unpacker.
    $unpacker = $this->getMockBuilder(RecipeUnpacker::class)
      ->setConstructorArgs([$recipe_to_unpack, $this->composer, $this->io, $this->rootComposer, $this->unpackCollection])
      ->onlyMethods(['getPackageFromLinkTarget', 'updateRootDependencies'])
      ->getMock();
    $unpacker->method('getPackageFromLinkTarget')
      ->willReturnMap([
        [$recipe_dependencies['module'], $this->createPackage('drupal-module', 'drupal/module-a')],
        [$recipe_dependencies['theme'], $this->createPackage('drupal-theme', 'drupal/theme-a')],
        [$recipe_dependencies['recipe'], $this->createPackage('drupal-recipe', 'drupal/recipe-c')],
        [$recipe_dependencies['unpacked_recipe'], $already_unpacked_recipe],
        [$recipe_dependencies['same_recipe'], $recipe_to_unpack],
      ]);
    // Do not run the update root dependencies when unpacking the recipe.
    $unpacker->expects($this->once())
      ->method('updateRootDependencies')
      ->willReturnCallback(function () {});

    // Unpack the recipe package.
    $unpacker->unpackDependencies();
    // Assert recipe was unpacked.
    $this->assertTrue($this->unpackCollection->isUnpacked($recipe_to_unpack));
    // Assert dependencies were added to the collection.
    $this->assertTrue($this->unpackCollection->dependencyExists('drupal/module-a'));
    $this->assertTrue($this->unpackCollection->dependencyExists('drupal/theme-a'));
    // Assert the unpacked recipes were not added to the collection.
    $this->assertNotTrue($this->unpackCollection->dependencyExists('drupal/recipe-a'));
    $this->assertNotTrue($this->unpackCollection->dependencyExists('drupal/recipe-b'));
    // Assert the recipe dependency was added into the queue of packages to
    // unpack.
    $this->assertNotTrue($this->unpackCollection->dependencyExists('drupal/recipe-c'));
    $this->assertSame($recipe_dependencies['recipe']->getTarget(), $this->unpackCollection->popPackageQueue()->getPrettyName());
    // Assert the ignored package was not added to the collection.
    $this->assertNotTrue($this->unpackCollection->dependencyExists('drupal/core'));
  }

  /**
   * Create a package mock.
   *
   * @param string $type
   *   The package type.
   * @param string $name
   *   The package name.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   The package mock.
   */
  protected function createPackage(string $type, string $name): MockObject {
    $package = $this->createMock(PackageInterface::class);
    $package->expects($this->any())
      ->method('getType')
      ->willReturn($type);
    $package->expects($this->any())
      ->method('getPrettyName')
      ->willReturn($name);

    return $package;
  }

  /**
   * Create an install event mock.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package mock.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   The event mock.
   */
  protected function createInstallEvent(PackageInterface $package): MockObject {
    $operation = new InstallOperation($package);
    $event = $this->createMock(PackageEvent::class);
    $event->expects($this->once())
      ->method('getOperation')
      ->willReturn($operation);

    return $event;
  }

  /**
   * Get a protected property from a class instance.
   *
   * @param object $object
   *   The class instance to return the property from.
   * @param string $property
   *   The name of the property to return.
   *
   * @return mixed
   *   The instance property value.
   */
  protected function getPropertyReflection(object $object, string $property): mixed {
    $ref = new \ReflectionProperty($object, $property);
    $ref->setAccessible(TRUE);

    return $ref->getValue($object);
  }

}
