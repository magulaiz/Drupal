<?php

declare(strict_types=1);

namespace Drupal\help_page_test\Hook;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for help_page_test.
 */
class HelpPageTestHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match): string {
    $output = '';
    switch ($route_name) {
      case 'help.page.help_page_test':
        // Make the help text conform to core standards. See
        // \Drupal\system\Tests\Functional\GenericModuleTestBase::assertHookHelp().
        $output = '<p>' . $this->t('Read the <a href=":url">online documentation for the Help Page Test module</a>.', [':url' => 'http://www.example.com']) . '</p>';
        break;

      case 'help_page_test.has_help':
        $output = '<p>' . $this->t('I have help!') . '</p>';
        break;

      case 'help_page_test.test_array':
        $output = 'Help text from help_page_test_help module.';
        break;
    }
    return $output;
  }

}
