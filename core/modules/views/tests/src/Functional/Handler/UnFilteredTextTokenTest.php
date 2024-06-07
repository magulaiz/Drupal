<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Functional\Handler;

use Drupal\Tests\views\Functional\ViewTestBase;
use Drupal\views\Views;

/**
 * Tests the plugin unfiltered text handler.
 */
class UnFilteredTextTokenTest extends ViewTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'views_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * Tests the token in unfiltered text.
   */
  public function testUnFilteredTextToken() {
    $admin_user = $this->drupalCreateUser([
      'administer views',
      'administer site configuration',
    ]);
    $this->drupalLogin($admin_user);

    $this->enableView('archive');

    $this->drupalGet('admin/structure/views/view/archive');
    $this->drupalGet('admin/structure/views/nojs/add-handler/archive/page_1/header');

    // Add the 'Unfiltered text'.
    $this->enableCheckbox('Unfiltered text');
    $this->submitForm([], 'Add and configure header');
    // Enable the checkbox 'view has no result', so that.
    // if content not exist still field added in header display.
    $this->enableCheckbox('Display even if view has no result');
    // Enable the checkbox 'replacement token'.
    $this->enableCheckbox('Use replacement tokens from the first row');
    // Add the link with href='[site:url]' token.
    $text = '<a href="[site:url]">Added Site URL: [site:url]</a>';
    $this->submitForm(['options[content]' => $text], 'Apply');

    // Hit the added 'unfiltered text' field page to view entered content.
    $this->drupalGet('admin/structure/views/nojs/handler/archive/page_1/header/area_text_custom');

    $this->drupalGet('admin/structure/views/view/archive/edit/page_1');
    $this->submitForm([], 'Save');

    $this->drupalGet('/archive');

    // Check if the href attribute's value matches the site_url token.
    $href = \Drupal::token()->replace('[site:url]');
    $this->assertSession()->elementExists('xpath', '//a[@href="' . $href . '"]');

  }

  /**
   * Enables the view with specified name.
   *
   * @param string $view_name
   *   The machine name of view.
   */
  protected function enableView(string $view_name): void {
    $view = Views::getView($view_name);
    $view->setDisplay('page_1');
    $this->executeView($view);
    // Enable the Archive view.
    $view->storage->setStatus(TRUE);
    $view->save();
  }

  /**
   * Enables the checkbox with specified field name.
   *
   * @param string $field_name
   *   The field name of checkbox field.
   */
  protected function enableCheckbox(string $field_name): void {
    $page = $this->getSession()->getPage();
    // Check the checkbox.
    $page->checkField($field_name);
    // Confirm the checkbox checked.
    $this->assertSession()->checkboxChecked($field_name);
  }

}
