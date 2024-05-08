<?php

declare(strict_types=1);

namespace Drupal\Tests\migrate\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\Tests\UnitTestCase;

/**
 * @group migrate
 */
#[CoversClass(\Drupal\migrate\Exception\RequirementsException::class)]
class RequirementsExceptionTest extends UnitTestCase {

  protected const MISSING_REQUIREMENTS = ['random_jackson_pivot', 'exoplanet'];

  public function testGetRequirements() {
    $exception = new RequirementsException('Missing requirements ', ['requirements' => static::MISSING_REQUIREMENTS]);
    $this->assertEquals(['requirements' => static::MISSING_REQUIREMENTS], $exception->getRequirements());
  }

  /**
   * @dataProvider getRequirementsProvider
   */
  public function testGetExceptionString($expected, $message, $requirements) {
    $exception = new RequirementsException($message, $requirements);
    $this->assertEquals($expected, $exception->getRequirementsString());
  }

  /**
   * Provides a list of requirements to test.
   */
  public static function getRequirementsProvider() {
    return [
      [
        'requirements: random_jackson_pivot.',
        'Single Requirement',
        ['requirements' => static::MISSING_REQUIREMENTS[0]],
      ],
      [
        'requirements: random_jackson_pivot. requirements: exoplanet.',
        'Multiple Requirements',
        ['requirements' => static::MISSING_REQUIREMENTS],
      ],
    ];
  }

}
