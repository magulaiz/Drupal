<?php

declare(strict_types=1);

namespace Drupal\PHPStan\Rules;

use Drupal\FunctionalTests\Installer\InstallerTestBase;
use Drupal\Tests\BrowserTestBase;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Ensures that classes extending \Drupal\Tests\BrowserTestBase do not access $this->container.
 *
 * @implements Rule<Node\Expr\PropertyFetch>
 */
final class FunctionalTestDoesNotAccessContainerProperty implements Rule {

  /**
   * {@inheritdoc}
   */
  public function getNodeType(): string {
    return PropertyFetch::class;
  }

  /**
   * {@inheritdoc}
   */
  public function processNode(Node $node, Scope $scope): array {
    $class = $scope->getClassReflection();

    if ($class === null || !$class->isSubclassOf(BrowserTestBase::class) || $class->is(InstallerTestBase::class) || $class->isSubclassOf(InstallerTestBase::class)) {
      return [];
    }

    if ($node instanceof PropertyFetch && $node->name instanceof Node\Identifier && $node->name->name === 'container') {
      return [
        RuleErrorBuilder::message('Functional tests should not access $this->container. Use \Drupal::service() instead.')
          ->identifier('functionalTest.noContainerAccess')
          ->line($node->getStartLine())
          ->build(),
      ];
    }

    return [];
  }

}
