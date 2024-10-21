<?php

namespace Drupal\big_pipe_regression_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class BigPipeRegressionTestHooks
{
    /**
     * Implements hook_theme().
     *
     * @see \Drupal\Tests\big_pipe\FunctionalJavascript\BigPipeRegressionTest::testBigPipeLargeContent
     */
    #[Hook('theme')]
    public function theme()
    {
        return ['big_pipe_test_large_content' => ['variables' => []]];
    }
}
