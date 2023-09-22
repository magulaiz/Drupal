<?php

namespace Drupal\Tests\block\FunctionalJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\block\Traits\BlockCreationTrait;

/**
 * Tests the JavaScript functionality of the block add filter.
 *
 * @group block
 */
class BlockFilterTest extends WebDriverTestBase {

  use BlockCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user', 'block'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Blocks to be installed on filter block layout  test.
   *
   * @var array[]
   */
  protected $blocks = [
    'left_sidebar' => [
      'page_title',
      'system_branding_block',
    ],
    'right_sidebar' => [
      'search_form_block',
    ],
    'content' => [
      'system_messages_block',
      'system_main_block',
    ],
    'footer' => [
      'help_block',
      'system_powered_by_block',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
    ]);

    $this->drupalLogin($admin_user);
  }

  /**
   * Tests block filter.
   */
  public function testBlockFilter() {
    $this->drupalGet('admin/structure/block');
    $assertSession = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    // Find the block filter field on the add-block dialog.
    $page->find('css', '#edit-blocks-region-header-title')->click();
    $filter = $assertSession->waitForElement('css', '.block-filter-text');

    // Get all block rows, for assertions later.
    $block_rows = $page->findAll('css', '.block-add-table tbody tr');

    // Test block filter reduces the number of visible rows.
    $filter->setValue('ad');
    $session->wait(10000, 'jQuery("#drupal-live-announce").html().indexOf("blocks are available") > -1');
    $visible_rows = $this->filterVisibleElements($block_rows);
    if (count($block_rows) > 0) {
      $this->assertNotSameSize($block_rows, $visible_rows);
    }

    // Test Drupal.announce() message when multiple matches are expected.
    $expected_message = count($visible_rows) . ' blocks are available in the modified list.';
    $this->assertAnnounceContains($expected_message);

    // Test Drupal.announce() message when only one match is expected.
    $filter->setValue('Powered by');
    $session->wait(10000, 'jQuery("#drupal-live-announce").html().indexOf("block is available") > -1');
    $visible_rows = $this->filterVisibleElements($block_rows);
    $this->assertCount(1, $visible_rows);
    $expected_message = '1 block is available in the modified list.';
    $this->assertAnnounceContains($expected_message);

    // Test Drupal.announce() message when no matches are expected.
    $filter->setValue('Pan-Galactic Gargle Blaster');
    $session->wait(10000, 'jQuery("#drupal-live-announce").html().indexOf("0 blocks are available") > -1');
    $visible_rows = $this->filterVisibleElements($block_rows);
    $this->assertCount(0, $visible_rows);
    $expected_message = '0 blocks are available in the modified list.';
    $this->assertAnnounceContains($expected_message);
  }

  /**
   * Test block filter on block layout page.
   */
  public function testRegionsBlockFilter() {
    $defaultTheme = $this->config('system.theme')->get('default');
    $this->container->get('theme_installer')->install(['stark']);
    $this->config('system.theme')->set('default', 'stark')->save();
    // Empty message displayed when the type doesn't match with blocks.
    $emptyMessage = 'There are no blocks matching the filter conditions.';

    $blockConfig = [];

    foreach ($this->blocks as $region => $blocks) {
      foreach ($blocks as $blockId) {
        $blockEntity = $this->placeBlock($blockId, ['region' => $region]);
        $humanRegion = ucwords(str_replace('_', ' ', $region));
        $blockConfig[$blockId] = [
          'region' => $humanRegion,
          'label' => $blockEntity->label(),
        ];
      }
    }

    // Add more three blocks with friendly labels containing same word to check if all will be displayed.
    $fakeBlock1 = $this->placeBlock(
      'system_messages_block',
      ['region' => 'content', 'label' => 'a common block label to be displayed']
    );
    $fakeBlock2 = $this->placeBlock(
      'system_messages_block',
      ['region' => 'content', 'label' => 'label to be displayed']
    );
    $fakeBlock3 = $this->placeBlock(
      'system_messages_block',
      ['region' => 'content', 'label' => 'label display']
    );
    $this->drupalGet('admin/structure/block');

    // Start the tests
    $assertSession = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    $inputFilter = $page->find('css', '[data-drupal-selector="edit-search-blocks"]');
    $allBlocks = $page->findAll('css', '#blocks tbody tr.draggable');
    $inputFilter->setValue('this text cant be found');
    $this->assertSession()->waitForElement('css', '#block-filter-region-empty-message');

    // Text if any block was displayed
    $visibleBlocks = $this->filterVisibleElements($allBlocks);
    $assertSession->assert(count($visibleBlocks) === 0, "Some blocks has been displayed but should not");
    $assertSession->pageTextContains($emptyMessage);

    // Change filter value to found one block specific.
    $inputFilter->setValue($blockConfig['page_title']['label']);
    $this->assertSession()->waitForElementRemoved('css', '#block-filter-region-empty-message');

    // Test if the message disappear.
    $assertSession->pageTextNotContains($emptyMessage);
    $assertSession->assert(
      count($this->filterVisibleElements($allBlocks)) === 1,
      "Only the block {$blockConfig['page_title']['label']} should appear, but more them one appeared"
    );
    $assertSession->pageTextContains($blockConfig['page_title']['label']);
    $assertSession->pageTextContains($blockConfig['page_title']['region']);

    // Search by another word that doesn't exist.
    // And check if the empty appear once.
    $inputFilter->setValue('string test');
    $this->assertSession()->waitForElement('css', '#block-filter-region-empty-message');
    $assertSession->pageTextContainsOnce($emptyMessage);

    // Test each block validating if the regions will be displayed.
    foreach ($blockConfig as $blockTest) {
      $inputFilter->setValue($blockTest['label']);
      $this->assertSession()
        ->waitForElementVisible('xpath', "//td[contains(text(), '" . $blockTest['label'] . "')]");
      $assertSession->pageTextContains($blockTest['label']);
      $assertSession->pageTextContains($blockTest['region']);
    }

    // Test drag and drop after any filter applied.
    $inputFilter->setValue('');
    $this->assertSession()
      ->waitForElementVisible('css', '#blocks tbody tr[data-drupal-selector="edit-blocks-' . $fakeBlock1->id() . '"] a.tabledrag-handle');
    $sideBarSecondRegion = $this->getSession()
      ->getPage()
      ->find('css', '#blocks tbody tr[data-drupal-selector="edit-blocks-region-sidebar-second-message"]');

    $blockToMove = $this->getSession()
      ->getPage()
      ->find('css', 'tr[data-drupal-selector="edit-blocks-' . $fakeBlock1->id() . '"] a.tabledrag-handle');

    $blockToMove->dragTo($sideBarSecondRegion);
    $this->assertEquals(
      'sidebar_second',
      $this->getSession()->getPage()->findField('edit-blocks-' . $fakeBlock1->id() . '-region')->getValue(),
      "Drupal {$fakeBlock1->id()} should be positioned on right sidebar"
    );
    // Test filter when user changes the region by select element.
    $this->getSession()
      ->getPage()
      ->findField('edit-blocks-' . $fakeBlock1->id() . '-region')
      ->setValue('sidebar_first');
    $this->assertSession()
      ->waitForElementVisible('css', '#blocks tbody tr[data-drupal-selector="edit-blocks-' . $fakeBlock1->id() . '"] a.tabledrag-handle');
    $this->assertEquals(
      'sidebar_first',
      $this->getSession()->getPage()->findField('edit-blocks-' . $fakeBlock1->id() . '-region')->getValue(),
      "Drupal {$fakeBlock1->id()} should be positioned on left sidebar"
    );

    $this->moveBlock($fakeBlock1->id(), 'tr[data-drupal-selector="edit-blocks-region-highlighted-message"]');
    $this->moveBlock($fakeBlock2->id(), 'tr[data-drupal-selector="edit-blocks-region-highlighted"]');
    $this->moveBlock($fakeBlock3->id(), 'tr[data-drupal-selector="edit-blocks-region-help-message"]');
    $inputFilter->setValue('label');

    $this->assertBlockOnRegion($fakeBlock1->id(), 'highlighted');
    $this->assertBlockOnRegion($fakeBlock2->id(), 'highlighted');
    $this->assertBlockOnRegion($fakeBlock3->id(), 'help');

    $this->moveBlock($fakeBlock3->id(), 'tr[data-drupal-selector="edit-blocks-region-highlighted-message"]');
    $this->assertSession()->waitForElementVisible('css', 'tr[data-drupal-selector="edit-blocks-region-help-message"]');
    $this->assertFalse($this->getSession()->getPage()->find('css', 'tr[data-drupal-selector="edit-blocks-region-highlighted-message"]')->isVisible());

    // Save blocks and test if was saved in the correction region.
    $this->submitForm([], 'Save blocks');
    $page = $this->getSession()
      ->getPage();
    $this->assertSession()->pageTextContains('The block settings have been updated.');
    $this->assertBlockOnRegion($fakeBlock1->id(), 'highlighted');
    $this->assertBlockOnRegion($fakeBlock2->id(), 'highlighted');
    $this->assertBlockOnRegion($fakeBlock3->id(), 'help');

    // Test move up with filter.
    $page->find('css', '[data-drupal-selector="edit-search-blocks"]')
      ->setValue('label');
    $this->moveBlock($fakeBlock3->id(), 'tr[data-drupal-selector="edit-blocks-region-header-message"]');
    $this->moveBlock($fakeBlock2->id(), 'tr.region-title-highlighted');
    $page->find('css', '[data-drupal-selector="edit-search-blocks"]')
      ->setValue('');
    // Wait debounce time to make sure that filter was cleaned.
    $this->getSession()
      ->wait(210);
    $this->assertBlockOnRegion($fakeBlock3->id(), 'header');
    $this->assertBlockOnRegion($fakeBlock2->id(), 'header');
    // Back to the previous theme default to avoid failing other tests.
    $this->config('system.theme')->set('default', $defaultTheme)->save();
  }

  /**
   * Removes any non-visible elements from the passed array.
   *
   * @param \Behat\Mink\Element\NodeElement[] $elements
   *   An array of node elements.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   */
  protected function filterVisibleElements(array $elements) {
    $elements = array_filter($elements, function (NodeElement $element) {
      return $element->isVisible();
    });
    return $elements;
  }

  /**
   * Checks for inclusion of text in #drupal-live-announce.
   *
   * @param string $expected_message
   *   The text expected to be present in #drupal-live-announce.
   *
   * @internal
   */
  protected function assertAnnounceContains(string $expected_message): void {
    $assert_session = $this->assertSession();
    $this->assertNotEmpty($assert_session->waitForElement('css', "#drupal-live-announce:contains('$expected_message')"));
  }

  /**
   * @param $blockId
   * @param $regionExpected
   *
   * @return void
   */
  protected function assertBlockOnRegion($blockId, $regionExpected) {
    $selectElement = $this->getSession()
      ->getPage()
      ->findField('edit-blocks-' . $blockId . '-region');

    $trFromSelect = $selectElement->getParent()
      ->getParent()
      ->getParent();

    // Test if the select element was updated.
    $this->assertEquals(
      $regionExpected,
      $selectElement->getValue(),
      'Select value should be ' . $regionExpected . ' but ' . $selectElement->getValue() . ' found.'
    );

    // Test data parent element was updated.
    $this->assertEquals(
      $regionExpected,
      $trFromSelect->getAttribute('data-parent-region'),
      'Data parent-region should be ' . $regionExpected . ' but ' . $trFromSelect->getAttribute('data-parent-region') . ' found.'
    );

    // To make sure that element was positioned in the correct place.
    $previousRegionElement = $trFromSelect->find('xpath', 'preceding-sibling::tr[contains(@class, "region-title")][1]');
    $this->assertEquals(
      $regionExpected,
      $previousRegionElement->getAttribute('data-region'),
      'The previous tr region of the element should be ' . $regionExpected . '. ' . $previousRegionElement->getAttribute('region') . ' found.'
    );
  }

  /**
   * Move blocks dragging or selecting from the region to the other.
   *
   * @param string $blockId
   *   The block to be moved.
   * @param string $dest
   *   The destination region.
   */
  protected function moveBlock(string $blockId, string $dest): void {
    $this->assertSession()->waitForElementVisible('css', $dest);
    $destRegion = $this->getSession()
      ->getPage()
      ->find('css', $dest);
    $this->assertNotEmpty($destRegion, 'Destination region ' . $dest . ' does not exists.');

    $dragRow = '#blocks tbody tr[data-drupal-selector="edit-blocks-' . $blockId . '"] a.tabledrag-handle';
    $blockToMove = $this->getSession()
      ->getPage()
      ->find('css', $dragRow);
    $this->assertNotEmpty($blockToMove, 'Block id ' . $dragRow . ' does not exits');
    $blockToMove->dragTo($destRegion);
    $this->assertSession()
      ->waitForElementVisible('css', 'tr[data-drupal-selector="edit-blocks-' . $blockId . '"].drag-previous');
  }

}
