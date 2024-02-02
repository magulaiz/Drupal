<?php

namespace Drupal\Tests\views\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the views bulk form with batch action.
 *
 * @group action
 * @see \Drupal\action\Plugin\views\field\BulkForm
 */
class UserBatchActionTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
<<<<<<< HEAD
    // @todo Remove this in https://www.drupal.org/node/3219959
    'block',
=======
>>>>>>> upstream/11.x
    'user',
    'user_batch_action_test',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests user admin batch.
   */
  public function testUserAction() {
<<<<<<< HEAD
    $themes = ['bartik', 'classy', 'olivero', 'seven', 'test_subseven'];
=======
    $themes = ['stark', 'olivero', 'claro'];
>>>>>>> upstream/11.x
    $this->container->get('theme_installer')->install($themes);

    $this->drupalLogin($this->rootUser);

    foreach ($themes as $theme) {
      $this->config('system.theme')->set('default', $theme)->save();
      $this->drupalGet('admin/people');
      $edit = [
        'user_bulk_form[0]' => TRUE,
        'action' => 'user_batch_action_test_action',
      ];
      $this->submitForm($edit, 'Apply');
      $this->assertSession()->pageTextContains('One item has been processed.');
      $this->assertSession()->pageTextContains($theme . ' theme used');
    }
  }

}
