<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Dex;

use Drupal\autowire_test\TestInjection;
use Drupal\dex_test\Command\DexExampleCommand;
use Drupal\dex_test\Command\DexExampleConfigureCommand;
use Drupal\dex_test\Command\DexExamplePrivateCommand;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests integration with Symfony Console.
 *
 * @group Dex
 */
final class DexConsoleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'autowire_test',
    'dex_test',
  ];

  /**
   * Tests the application wrapped by symfony/runtime.
   */
  public function testApplication(): void {
    $tester = $this->applicationTester();
    $this->assertEquals(Command::SUCCESS, $tester->run(['command' => 'example:command']));
    $this->assertStringContainsString('Dependency injection test: ' . TestInjection::class, $tester->getDisplay());
    $this->assertStringContainsString('Option test: No', $tester->getDisplay());
    $this->assertStringContainsString('Argument test: No', $tester->getDisplay());
    $this->assertStringContainsString('[OK] Done', $tester->getDisplay());

    // When host is provided without port, 80 is used, which is omitted from
    // generated URLs.
    $tester = $this->applicationTester(['HOST' => 'example.com']);
    $this->assertEquals(Command::SUCCESS, $tester->run(['command' => 'example:command', 'scenario' => 'absolute_url']));
    $this->assertStringContainsString('Base Url test: http://example.com/abc', $tester->getDisplay());

    // A different port is present in generated URLs.
    $tester = $this->applicationTester(['HOST' => 'example.com', 'PORT' => 3333]);
    $this->assertEquals(Command::SUCCESS, $tester->run(['command' => 'example:command', 'scenario' => 'absolute_url']));
    $this->assertStringContainsString('Base Url test: http://example.com:3333/abc', $tester->getDisplay());

    // Test legacy command.
    $tester = $this->applicationTester();
    $this->assertEquals(Command::SUCCESS, $tester->run(['command' => 'example:command-configured']));
    $this->assertStringContainsString('Done with configured command.', $tester->getDisplay());

    // Test private command.
    $tester = $this->applicationTester();
    $this->assertEquals(Command::SUCCESS, $tester->run(['command' => 'example:command-private']));
    $this->assertStringContainsString('Done with private command.', $tester->getDisplay());
  }

  /**
   * Test command loader has the discovered commands.
   *
   * @covers \Drupal\Core\DependencyInjection\Compiler\DexCompilerPass
   */
  public function testCommandLoader(): void {
    /** @var \Symfony\Component\Console\CommandLoader\CommandLoaderInterface $commandLoader */
    $commandLoader = \Drupal::service('console.command_loader');
    $command = $commandLoader->get('example:command');
    $this->assertInstanceOf(DexExampleCommand::class, $command);
    $this->assertTrue(\Drupal::hasService(DexExampleCommand::class));
  }

  /**
   * Test command loader has the discovered legacy commands IDs.
   *
   * @covers \Drupal\Core\DependencyInjection\Compiler\DexCompilerPass
   */
  public function testCommandIds(): void {
    $commandIds = \Drupal::getContainer()->getParameter('console.command.ids');
    $this->assertEquals([
      DexExampleConfigureCommand::class,
      // A public alias is created for this private command.
      'console.command.public_alias.' . DexExamplePrivateCommand::class,
    ], $commandIds);
  }

  /**
   * Integration test for a command.
   *
   * Tests command is registered to the container, and has expected input/output
   * from options, arguments, autowiring, and return code.
   *
   * @covers \Drupal\dex_test\Command\DexExampleCommand
   */
  public function testConsoleCommand(): void {
    /** @var \Drupal\dex_test\Command\DexExampleCommand $command */
    $command = \Drupal::service(DexExampleCommand::class);
    $tester = new CommandTester($command);
    $code = $tester->execute(['argument-test' => 'Foo', '--option-test' => TRUE]);
    $this->assertStringContainsString('Dependency injection test: ' . TestInjection::class, $tester->getDisplay());
    $this->assertStringContainsString('Option test: Yes', $tester->getDisplay());
    $this->assertStringContainsString('Argument test: Yes', $tester->getDisplay());
    $this->assertStringContainsString('[OK] Done', $tester->getDisplay());
    $this->assertEquals(Command::SUCCESS, $code);
  }

  private function applicationTester(array $context = []): ApplicationTester {
    $application = include __DIR__ . '/../../../../../../vendor/bin/dex';
    $application = $application($context);
    $application->setAutoExit(FALSE);
    return new ApplicationTester($application);
  }

}
