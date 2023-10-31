<?php

namespace Drupal\Tests\views_ui\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\views_ui\Traits\FilterEntityReferenceTrait;

/**
 * Tests views creation wizard.
 *
 * @group views_ui
 * @see \Drupal\views\Plugin\views\filter\EntityReference
 */
class FilterEntityReferenceTest extends WebDriverTestBase {

  use FilterEntityReferenceTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'views',
    'views_ui',
    'views_test_entity_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_entity_reference'];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $admin_user = $this->drupalCreateUser([
      'administer views',
    ]);
    $this->drupalLogin($admin_user);

    $this->setUpEntityTypes();
  }

  /**
   * Tests end to end creation of a Content Entity Reference filter.
   */
  public function testAddEntityReferenceFieldWithDefaultSelectionHandler() {
    $this->drupalGet('admin/structure/views/view/content');

    // Open the dialog.
    $this->getSession()->getPage()->clickLink('views-add-filter');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Wait for the popup to open and the search field to be available.
    $this->assertSession()->waitForElementVisible('named', [
      'field',
      'override[controls][options_search]',
    ]);

    // Test that the both entity_reference and numeric options are visible.
    $page = $this->getSession()->getPage();
    $this->assertTrue($page->findField('name[node__field_test.field_test_target_id]')
      ->isVisible());
    $this->assertTrue($page->findField('name[node__field_test.field_test_target_id_reference]')
      ->isVisible());
    $page->findField('name[node__field_test.field_test_target_id_reference]')
      ->click();
    $this->assertTrue($page->find('css', 'button.button.button--primary.form-submit.ui-button')
      ->isVisible());
    $this->htmlOutput($page->getHtml());
    $page->find('css', 'button.button.button--primary.form-submit.ui-button')
      ->click();

    // Wait for the selection handler to show up.
    $this->assertSession()->waitForElementVisible('named', [
      'select',
      'options[sub_handler]',
    ]);
    $this->getSession()
      ->getPage()
      ->selectFieldOption('options[sub_handler]', 'default:node');
    $this->htmlOutput($page->getHtml());

    // Check that that default handler target bundles are available.
    $this->assertTrue($page->findField('options[reference_default:node][target_bundles][article]')
      ->isVisible());
    $this->assertTrue($page->findField('options[reference_default:node][target_bundles][page]')
      ->isVisible());
    $this->assertTrue($page->findField('options[widget]')->isVisible());

    // Ensure that disabled form elements from selection handler do not show up
    // @see \Drupal\views\Plugin\views\filter\EntityReference method
    // buildExtraOptionsForm.
    $this->assertFalse($page->hasField('options[reference_default:node][target_bundles_update]'));
    $this->assertFalse($page->hasField('options[reference_default:node][auto_create]'));
    $this->assertFalse($page->hasField('options[reference_default:node][auto_create_bundle]'));

    // Choose the default handler using the select widget with article type
    // checked.
    $page->checkField('options[reference_default:node][target_bundles][article]');
    $page->selectFieldOption('options[widget]', 'select');
    $this->assertSame($this->getSession()
      ->getPage()
      ->findField('options[widget]')
      ->getValue(), 'select');
    $this->getSession()
      ->getPage()
      ->find('xpath', "//*[contains(text(), 'Apply and continue')]")
      ->press();

    // Test the exposed filter options show up correctly.
    $this->assertSession()->waitForElementVisible('named', [
      'checkbox',
      'options[expose_button][checkbox][checkbox]',
    ]);
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($page->find('css', 'input[name="options[expose_button][checkbox][checkbox]"]')
      ->isVisible());
    $page->checkField('options[expose_button][checkbox][checkbox]');
    $this->assertTrue($this->getSession()->getPage()->hasCheckedField('options[expose_button][checkbox][checkbox]'));

    // Check the exposed filters multiple option.
    $this->assertSession()->waitForElementVisible('named', [
      'checkbox',
      'options[expose][multiple]',
    ]);
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($page->find('css', 'input[name="options[expose][multiple]"]')
      ->isVisible());
    $page->checkField('options[expose][multiple]');
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($this->getSession()->getPage()->hasCheckedField('options[expose][multiple]'));
    $page->find('css', 'button.button.button--primary.form-submit.ui-button')
      ->click();
    $this->assertSession()->waitForElementRemoved('css', '.ui-dialog');

    // Wait for the Views Preview to show up with the new reference field.
    $this->assertSession()->waitForElementVisible('named', [
      'select',
      'field_test_target_id_reference[]',
    ]);
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($page->find('css', 'select[name="field_test_target_id_reference[]"]')
      ->isVisible());
    $this->assertTrue($page->find('css', 'select[name="field_test_target_id_reference[]"]')
      ->hasAttribute('multiple'));

    // Opening the settings form and change the handler to use an Entity
    // Reference view.
    // @see views.view.test_entity_reference.yml
    $base_url = Url::fromRoute('entity.view.collection')->toString();
    $url = $base_url . '/nojs/handler-extra/content/page_1/filter/field_test_target_id_reference';
    $extra_settings_selector = 'a[href="' . $url . '"]';
    $element = $this->assertSession()->waitForElementVisible('css', $extra_settings_selector);
    $this->assertNotNull($element);
    $element->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('options[sub_handler]', 'views');
    $this->htmlOutput($page->getHtml());
    $page->selectFieldOption('options[reference_views][view][view_and_display]', 'test_entity_reference:entity_reference');
    $page->find('xpath', "//*[contains(text(), 'Apply')]")
      ->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The Views Reference filter has a title Filter to a single result, so
    // ensure only that result is available as an option.
    $this->assertSession()->waitForElementRemoved('css', '.ui-dialog');

    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());

    $this->assertCount(1, $page->findAll('css', 'select[name="field_test_target_id_reference[]"] option'));

    // Change to an autocomplete filter.
    // Opening the settings form and change the handler to use an Entity
    // Reference view.
    // @see views.view.test_entity_reference.yml
    $page->find('css', $extra_settings_selector)
      ->click();
    $this->assertSession()->waitForElementVisible('named', [
      'radio',
      'options[widget]',
    ]);
    $this->getSession()
      ->getPage()
      ->selectFieldOption('options[widget]', 'autocomplete');
    $this->assertSame($this->getSession()
      ->getPage()
      ->findField('options[widget]')
      ->getValue(), 'autocomplete');
    $this->getSession()
      ->getPage()
      ->find('xpath', "//*[contains(text(), 'Apply')]")
      ->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Check that it is now an autocomplete.
    $this->assertSession()->waitForElementVisible('named', [
      'field',
      'field_test_target_id_reference',
    ]);
    $page = $this->getSession()->getPage();
    $this->assertTrue($page->find('css', 'input[name="field_test_target_id_reference"]')
      ->isVisible());
    $this->assertTrue($page->find('css', 'input[name="field_test_target_id_reference"]')
      ->hasAttribute('data-autocomplete-path'));
  }

  /**
   * Tests end to end creation of a Config Entity Reference filter.
   */
  public function testAddConfigEntityReferenceFieldWithDefaultSelectionHandler() {
    $this->drupalGet('admin/structure/views/view/content');

    // Open the dialog.
    $this->getSession()->getPage()->clickLink('views-add-filter');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Wait for the popup to open and the search field to be available.
    $this->assertSession()->waitForElementVisible('named', [
      'field',
      'override[controls][options_search]',
    ]);

    // Test that the both entity_reference and numeric options are visible.
    $page = $this->getSession()->getPage();
    $this->assertTrue($page->findField('name[node__field_test_config.field_test_config_target_id]')
      ->isVisible());
    $this->assertTrue($page->findField('name[node__field_test_config.field_test_config_target_id_reference]')
      ->isVisible());
    $page->findField('name[node__field_test_config.field_test_config_target_id_reference]')
      ->click();
    $this->assertTrue($page->find('css', 'button.button.button--primary.form-submit.ui-button')
      ->isVisible());
    $this->htmlOutput($page->getHtml());
    $page->find('css', 'button.button.button--primary.form-submit.ui-button')
      ->click();

    // Wait for the selection handler to show up.
    $this->assertSession()->waitForElementVisible('named', [
      'select',
      'options[sub_handler]',
    ]);
    $this->getSession()
      ->getPage()
      ->selectFieldOption('options[sub_handler]', 'default:node_type');
    $this->htmlOutput($page->getHtml());


    // Choose the default handler using the select widget with article type
    // checked.
    $page->selectFieldOption('options[widget]', 'select');
    $this->assertSame($this->getSession()
      ->getPage()
      ->findField('options[widget]')
      ->getValue(), 'select');
    $this->getSession()
      ->getPage()
      ->find('xpath', "//*[contains(text(), 'Apply and continue')]")
      ->press();

    // Test the exposed filter options show up correctly.
    $this->assertSession()->waitForElementVisible('named', [
      'checkbox',
      'options[expose_button][checkbox][checkbox]',
    ]);
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($page->find('css', 'input[name="options[expose_button][checkbox][checkbox]"]')
      ->isVisible());
    $page->checkField('options[expose_button][checkbox][checkbox]');
    $this->assertTrue($this->getSession()->getPage()->hasCheckedField('options[expose_button][checkbox][checkbox]'));

    // Check the exposed filters multiple option.
    $this->assertSession()->waitForElementVisible('named', [
      'checkbox',
      'options[expose][multiple]',
    ]);
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($page->find('css', 'input[name="options[expose][multiple]"]')
      ->isVisible());
    $page->checkField('options[expose][multiple]');
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($this->getSession()->getPage()->hasCheckedField('options[expose][multiple]'));
    $page->find('css', 'button.button.button--primary.form-submit.ui-button')
      ->click();
    $this->assertSession()->waitForElementRemoved('css', '.ui-dialog');

    // Wait for the Views Preview to show up with the new reference field.
    $this->assertSession()->waitForElementVisible('named', [
      'select',
      'field_test_config_target_id_reference[]',
    ]);
    $page = $this->getSession()->getPage();
    $this->htmlOutput($page->getHtml());
    $this->assertTrue($page->find('css', 'select[name="field_test_config_target_id_reference[]"]')
      ->isVisible());
    $this->assertTrue($page->find('css', 'select[name="field_test_config_target_id_reference[]"]')
      ->hasAttribute('multiple'));

    // Check references config options.
    $options = $page->findAll('css', 'select[name="field_test_config_target_id_reference[]"] option');
    $this->assertCount(2, $options);
    $this->assertSame('article', $options[0]->getValue());
    $this->assertSame('page', $options[1]->getValue());

    $base_url = Url::fromRoute('entity.view.collection')->toString();
    $url = $base_url . '/nojs/handler-extra/content/page_1/filter/field_test_config_target_id_reference';
    $extra_settings_selector = 'a[href="' . $url . '"]';

    // Change to an autocomplete filter.
    $page->find('css', $extra_settings_selector)
      ->click();
    $this->assertSession()->waitForElementVisible('named', [
      'radio',
      'options[widget]',
    ]);
    $this->getSession()
      ->getPage()
      ->selectFieldOption('options[widget]', 'autocomplete');
    $this->assertSame($this->getSession()
      ->getPage()
      ->findField('options[widget]')
      ->getValue(), 'autocomplete');
    $this->getSession()
      ->getPage()
      ->find('xpath', "//*[contains(text(), 'Apply')]")
      ->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Check that it is now an autocomplete.
    $this->assertSession()->waitForElementVisible('named', [
      'field',
      'field_test_config_target_id_reference',
    ]);
    $page = $this->getSession()->getPage();
    $this->assertTrue($page->find('css', 'input[name="field_test_config_target_id_reference"]')
      ->isVisible());
    $this->assertTrue($page->find('css', 'input[name="field_test_config_target_id_reference"]')
      ->hasAttribute('data-autocomplete-path'));
  }

}
