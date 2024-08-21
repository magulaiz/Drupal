<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Recipe;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Recipe\ConsoleInputCollector;
use Drupal\Core\Recipe\DefaultValueResolver;
use Drupal\Core\Recipe\InputCollectorBase;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @group Recipe
 */
class InputTest extends KernelTestBase {

  use RecipeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user'];

  private readonly Recipe $recipe;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('user');
    $this->config('core.menu.static_menu_link_overrides')
      ->set('definitions', [])
      ->save();
    $this->config('system.site')
      ->set('langcode', 'en')
      ->set('name', 'Testing!')
      ->set('mail', 'ben@deep.space')
      ->set('uuid', $this->container->get(UuidInterface::class)->generate())
      ->save();

    $this->recipe = Recipe::createFromDirectory($this->getDrupalRoot() . '/core/recipes/feedback_contact_form');
  }

  /**
   * @covers \Drupal\Core\Recipe\DefaultValueResolver
   */
  public function testDefaultValueFromConfig(): void {
    // Collect the input values before processing the recipe.
    DefaultValueResolver::create($this->container)->collectAll($this->recipe);
    RecipeRunner::processRecipe($this->recipe);

    $this->assertSame(['ben@deep.space'], ContactForm::load('feedback')?->getRecipients());
  }

  /**
   * @covers \Drupal\Core\Recipe\InputCollectorBase::validate
   */
  public function testInputIsValidated(): void {
    // @phpstan-ignore-next-line
    $collector = new class (
      $this->container->get(TypedDataManagerInterface::class),
    ) extends InputCollectorBase {

      /**
       * {@inheritdoc}
       */
      protected function collectValue(string $name, array $definition): mixed {
        assert($name === 'feedback_contact_form.recipient');
        return 'not-an-email-address';
      }

    };
    try {
      $collector->collectAll($this->recipe);
      $this->fail('Expected an exception due to validation failure, but none was thrown.');
    }
    catch (ValidationFailedException $e) {
      $this->assertSame('not-an-email-address', $e->getValue());
      $this->assertSame('This value is not a valid email address.', (string) $e->getViolations()->get(0)->getMessage());
    }
  }

  /**
   * @covers \Drupal\Core\Recipe\ConsoleInputCollector::collectValue
   */
  public function testPromptArgumentsAreForwarded(): void {
    $validator = new class () {

      public function __invoke(): void {}

    };
    $this->container->set('test_validator', $validator);

    $io = $this->createMock(StyleInterface::class);
    $io->expects($this->once())
      ->method('ask')
      ->with('What is the capital of Assyria?', "I don't know that!", $validator)
      ->willReturn('<scream>');

    $recipe = $this->createRecipe(<<<YAML
name: 'Collecting prompt input'
input:
  capital:
    description: The capital of a long-defunct country.
    prompt:
      method: ask
      arguments:
        question: What is the capital of Assyria?
        validator: 'test_validator'
    default:
      source: value
      value: "I don't know that!"
YAML
    );
    ConsoleInputCollector::create($this->container, io: $io)
      ->collectAll($recipe);
    $this->assertSame(['capital' => '<scream>'], $recipe->getInputValues());
  }

  /**
   * @covers \Drupal\Core\Recipe\ConsoleInputCollector::collectValue
   */
  public function testMissingArgumentsThrowsException(): void {
    $recipe = $this->createRecipe(<<<YAML
name: 'Collecting prompt input'
input:
  capital:
    description: The capital of a long-defunct country.
    prompt:
      method: ask
    default:
      source: value
      value: "I don't know that!"
YAML
    );
    $this->expectException(\ArgumentCountError::class);
    $this->expectExceptionMessage('Argument #1 ($question) not passed');
    ConsoleInputCollector::create($this->container, io: $this->createMock(StyleInterface::class))->collectAll($recipe);
  }

  /**
   * @covers \Drupal\Core\Recipe\DefaultValueResolver::collectValue
   */
  public function testDefaultValueFromNonExistentConfig(): void {
    $recipe = $this->createRecipe(<<<YAML
name: 'Default value from non-existent config'
input:
  capital:
    description: This will be erroneous.
    default:
      source: config
      config: ['foo.baz', 'bar']
YAML
    );
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage("The 'foo.baz' config object does not exist.");
    DefaultValueResolver::create($this->container)->collectAll($recipe);
  }

  public function testLiterals(): void {
    $recipe = $this->createRecipe(<<<YAML
name: Literals as input
install:
  - config_test
input:
  capital:
    description: Your favorite state capital.
    default:
      source: value
      value: Boston
  some_int:
    description: This is an integer and should be stored as an integer.
    default:
      source: value
      value: 1234
  some_bool:
    description: This is a boolean and should be stored as a boolean.
    default:
      source: value
      value: false
  some_float:
    description: Pi is a float, should be stored as a float.
    default:
      source: value
      value: 3.141
config:
  actions:
    config_test.types:
      simpleConfigUpdate:
        int: \${some_int}
        boolean: \${some_bool}
        float: \${some_float}
    system.site:
      simpleConfigUpdate:
        name: '\${capital} rocks!'
        slogan: int is \${some_int}, bool is \${some_bool} and float is \${some_float}
YAML
    );
    DefaultValueResolver::create($this->container)->collectAll($recipe);
    RecipeRunner::processRecipe($recipe);

    $config = $this->config('config_test.types');
    $this->assertSame(1234, $config->get('int'));
    $this->assertFalse($config->get('boolean'));
    $this->assertSame(3.141, $config->get('float'));

    $config = $this->config('system.site');
    $this->assertSame("Boston rocks!", $config->get('name'));
    $this->assertSame('int is 1234, bool is  and float is 3.141', $config->get('slogan'));
  }

}
