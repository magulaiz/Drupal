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
   * @covers \Drupal\Core\Recipe\ConsoleInputCollector::prompt
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
    default: "I don't know that!"
YAML
    );
    ConsoleInputCollector::create($this->container, io: $io)
      ->collectAll($recipe);
    $this->assertSame(['capital' => '<scream>'], $recipe->getInputValues());
  }

  /**
   * @covers \Drupal\Core\Recipe\ConsoleInputCollector::prompt
   */
  public function testMissingArgumentsThrowsException(): void {
    $recipe = $this->createRecipe(<<<YAML
name: 'Collecting prompt input'
input:
  capital:
    description: The capital of a long-defunct country.
    prompt:
      method: ask
    default: "I don't know that!"
YAML
    );
    $this->expectException(\ArgumentCountError::class);
    $this->expectExceptionMessage('Argument #1 ($question) not passed');
    ConsoleInputCollector::create($this->container)->collectAll($recipe);
  }

}
