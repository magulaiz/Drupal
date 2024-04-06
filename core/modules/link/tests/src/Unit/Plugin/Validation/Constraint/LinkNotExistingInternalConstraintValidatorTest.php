<?php

declare(strict_types=1);

namespace Drupal\Tests\link\Unit\Plugin\Validation\Constraint;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Drupal\link\Plugin\Validation\Constraint\LinkNotExistingInternalConstraint;
use Drupal\link\Plugin\Validation\Constraint\LinkNotExistingInternalConstraintValidator;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @coversDefaultClass \Drupal\link\Plugin\Validation\Constraint\LinkNotExistingInternalConstraintValidator
 * @group Link
 */
class LinkNotExistingInternalConstraintValidatorTest extends UnitTestCase {

  /**
   * @covers ::validate
   * @dataProvider providerValidate
   */
  public function testValidate(string $mode, string $value, mixed $urlGeneratorReturn, bool $valid): void {
    switch ($mode) {
      case 'uri':
        $url = Url::fromUri($value);
        break;

      case 'route':
      default:
        $url = Url::fromRoute($value);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->any())
          ->method('generateFromRoute')
          ->with($value, [], [])
          ->willReturn($urlGeneratorReturn);
        $url->setUrlGenerator($urlGenerator);
        break;

    }

    $link = $this->createMock(LinkItemInterface::class);
    $link->expects($this->any())
      ->method('getUrl')
      ->willReturn($url);

    $context = $this->createMock(ExecutionContextInterface::class);

    if ($valid) {
      $context->expects($this->never())
        ->method('addViolation');
    }
    else {
      $context->expects($this->once())
        ->method('addViolation');
    }

    $constraint = new LinkNotExistingInternalConstraint();

    $validator = new LinkNotExistingInternalConstraintValidator();
    $validator->initialize($context);
    $validator->validate($link, $constraint);
  }

  /**
   * Data provider for ::testValidate.
   */
  public static function providerValidate(): \Generator {
    yield ['uri', 'https://www.drupal.org', NULL, TRUE];
    yield ['route', 'example.existing_route', '/example/existing', TRUE];
    yield ['route', 'example.not_existing_route', new RouteNotFoundException(), FALSE];
  }

  /**
   * @covers ::validate
   *
   * @see \Drupal\Core\Url::fromUri
   */
  public function testValidateWithMalformedUri(): void {
    $link = $this->createMock(LinkItemInterface::class);
    $link->expects($this->any())
      ->method('getUrl')
      ->willThrowException(new \InvalidArgumentException());

    $context = $this->createMock(ExecutionContextInterface::class);
    $context->expects($this->never())
      ->method('addViolation');

    $constraint = new LinkNotExistingInternalConstraint();

    $validator = new LinkNotExistingInternalConstraintValidator();
    $validator->initialize($context);
    $validator->validate($link, $constraint);
  }

}
