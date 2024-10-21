<?php

namespace Drupal\module_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class ModuleTestFileHooks
{
    /**
     * Implements hook_test_hook().
     */
    #[Hook('test_hook')]
    public function testHook()
    {
        return ['module_test' => 'success!'];
    }
}
