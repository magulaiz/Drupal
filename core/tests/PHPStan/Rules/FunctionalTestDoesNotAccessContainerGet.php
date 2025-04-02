<?php

declare(strict_types=1);

namespace Drupal\PHPStan\Rules;

use Drupal\FunctionalTests\Installer\InstallerTestBase;
use Drupal\Tests\BrowserTestBase;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Ensures functional tests do not access $this->container->get().
 *
 * @implements Rule<Node\Expr\MethodCall>
 */
final class FunctionalTestDoesNotAccessContainerGet implements Rule {

  /**
   * {@inheritdoc}
   */
  public function getNodeType(): string {
    return MethodCall::class;
  }

  /**
   * {@inheritdoc}
   */
  public function processNode(Node $node, Scope $scope): array {
    $class = $scope->getClassReflection();

    if ($class === null || !$class->isSubclassOf(BrowserTestBase::class) || $class->is(InstallerTestBase::class) || $class->isSubclassOf(InstallerTestBase::class)) {
      return [];
    }

    if ($node instanceof MethodCall && $node->name instanceof Node\Identifier && $node->name->name === 'get') {
      $var = $node->var;
      if ($var instanceof PropertyFetch && $var->name instanceof Node\Identifier && $var->name->name === 'container') {
        return [
          RuleErrorBuilder::message('Functional tests should not access $this->container->get(). Use \Drupal::service() instead.')
            ->identifier('functionalTest.noContainerGet')
            ->line($node->getStartLine())
            ->build(),
          ];
      }
    }

    return [];
  }

}
