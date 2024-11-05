<?php

declare(strict_types=1);

namespace Drupal\system_test\Hook;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for system_test.
 */
class SystemTestHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      case 'help.page.system_test':
        $output = '';
        $output .= '<h2>' . t('Test Help Page') . '</h2>';
        $output .= '<p>' . t('This is a test help page for the system_test module for the purpose of testing if the "Help" link displays properly.') . '</p>';
        return $output;
    }
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$page) {
    // Used by FrontPageTestCase to get the results of
    // \Drupal::service('path.matcher')->isFrontPage().
    $frontpage = \Drupal::state()->get('system_test.front_page_output', 0);
    if ($frontpage && \Drupal::service('path.matcher')->isFrontPage()) {
      \Drupal::messenger()->addStatus(t('On front page.'));
    }
  }

  /**
   * Implements hook_filetransfer_info().
   */
  #[Hook('filetransfer_info')]
  public function filetransferInfo() {
    return [
      'system_test' => [
        'title' => t('System Test FileTransfer'),
        'class' => 'Drupal\system_test\MockFileTransfer',
        'weight' => -10,
      ],
    ];
  }

}
