<?php

namespace Drupal\system_test\Hook;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;
class SystemTestHooks
{
    /**
     * Implements hook_help().
     */
    #[Hook('help')]
    public function help($route_name, \Drupal\Core\Routing\RouteMatchInterface $route_match)
    {
        switch ($route_name) {
            case 'help.page.system_test':
                $output = '';
                $output .= '<h2>' . \t('Test Help Page') . '</h2>';
                $output .= '<p>' . \t('This is a test help page for the system_test module for the purpose of testing if the "Help" link displays properly.') . '</p>';
                return $output;
        }
    }
    /**
     * Implements hook_system_info_alter().
     */
    #[Hook('system_info_alter')]
    public function systemInfoAlter(&$info, \Drupal\Core\Extension\Extension $file, $type)
    {
        // We need a static otherwise the last test will fail to alter common_test.
        static $test;
        if (($dependencies = \Drupal::state()->get('system_test.dependencies')) || $test) {
            if ($file->getName() == 'module_test') {
                $info['hidden'] = \FALSE;
                $info['dependencies'][] = \array_shift($dependencies);
                \Drupal::state()->set('system_test.dependencies', $dependencies);
                $test = \TRUE;
            }
            if ($file->getName() == 'common_test') {
                $info['hidden'] = \FALSE;
                $info['version'] = '8.x-2.4-beta3';
            }
        }
        // Make the system_dependencies_test visible by default.
        if ($file->getName() == 'system_dependencies_test') {
            $info['hidden'] = \FALSE;
        }
        if (\in_array($file->getName(), ['system_incompatible_module_version_dependencies_test', 'system_incompatible_core_version_dependencies_test', 'system_incompatible_module_version_test'])) {
            $info['hidden'] = \FALSE;
        }
        if ($file->getName() == 'requirements1_test' || $file->getName() == 'requirements2_test') {
            $info['hidden'] = \FALSE;
        }
        if ($file->getName() == 'system_test') {
            $info['hidden'] = \Drupal::state()->get('system_test.module_hidden', \TRUE);
        }
    }
    /**
     * Implements hook_page_attachments().
     */
    #[Hook('page_attachments')]
    public function pageAttachments(array &$page)
    {
        // Used by FrontPageTestCase to get the results of
        // \Drupal::service('path.matcher')->isFrontPage().
        $frontpage = \Drupal::state()->get('system_test.front_page_output', 0);
        if ($frontpage && \Drupal::service('path.matcher')->isFrontPage()) {
            \Drupal::messenger()->addStatus(\t('On front page.'));
        }
    }
    /**
     * Implements hook_filetransfer_info().
     */
    #[Hook('filetransfer_info')]
    public function filetransferInfo()
    {
        return ['system_test' => ['title' => \t('System Test FileTransfer'), 'class' => 'Drupal\system_test\MockFileTransfer', 'weight' => -10]];
    }
}
