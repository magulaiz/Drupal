<?php

namespace Drupal\Tests\system\FunctionalJavascript\Batch;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * @group system2
 */
class ProcessingTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['batch_test', 'test_page_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * Tests batches defined in a form submit handler.
   */
  public function testBatchForm() {
    $edit = ['batch' => 'batch_8'];
    $this->drupalGet('batch-test');
    $this->submitForm($edit, 'Submit');
    $this->assertNotNull($this->assertSession()->waitForLink('the error page'));
    $this->assertSession()->assertNoEscaped('<');
    $this->assertSession()->responseContains('Exception in batch');
    $this->clickLink('the error page');
    $this->assertSession()->pageTextContains('Redirection successful.');
  }

}
