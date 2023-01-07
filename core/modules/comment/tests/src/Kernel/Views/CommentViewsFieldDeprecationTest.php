<?php

namespace Drupal\Tests\comment\Kernel\Views;

use Drupal\comment\Plugin\views\field\EntityLink;
use Drupal\comment\Plugin\views\field\StatisticsLastCommentName;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group legacy
 */
class CommentViewsFieldDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['comment'];

  /**
   * Tests deprecation of constructing a EntityLink object without the renderer argument.
   *
   * @covers \Drupal\comment\Plugin\views\field\EntityLink::__construct
   */
  public function testEntityLinkConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\comment\Plugin\views\field\EntityLink::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new EntityLink(
      [],
      '',
      [],
    );
  }

  /**
   * Tests deprecation of constructing a StatisticsLastCommentName object without the renderer argument.
   *
   * @covers \Drupal\comment\Plugin\views\field\StatisticsLastCommentName::__construct
   */
  public function testStatisticsLastCommentNameConstructorDeprecation(): void {
    $this->expectDeprecation('Calling Drupal\comment\Plugin\views\field\StatisticsLastCommentName::__construct() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656');
    new StatisticsLastCommentName(
      [],
      '',
      [],
    );
  }

}
