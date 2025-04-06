<?php

declare(strict_types=1);

namespace Drupal\Tests\help\Kernel;

/**
 * Verify the order of the help page with an alter hook.
 *
 * @group help
 */
class HelpPageReverseOrderTest extends HelpPageOrderTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['more_help_page_test'];

  /**
   * Strings to search for on admin/help, in order.
   *
   * These are reversed, due to the alter hook.
   *
   * @var string[]
   */
  protected array $stringOrder = [
    'This description should appear',
    'Module overviews are provided',
  ];

}
