<?php

namespace Drupal\Tests\image\Functional;

use Drupal\image\Entity\ImageStyle;

/**
 * Tests the image style administration UI.
 *
 * @group image
 */
class ImageAdminUiTest extends ImageFieldTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['help', 'block', 'system'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that if the help text is available on the add effect form.
   */
  public function testAddEffectHelpText(): void {
    $style = $this->createStyle('test_style', 'Test style');

    // Add the help block to the page.
    $this->drupalPlaceBlock('help_block', ['region' => 'help', 'id' => 'block-help']);

    // Open the add effect form and check for the help text.
    $this->drupalGet($style->toUrl()->toString() . '/add/image_resize');

    $this->assertSession()->pageTextContains('Resizing will make images an exact set of dimensions. This may cause images to be stretched or shrunk disproportionately.');
  }

  /**
   * Tests that if the help text is available on the edit effect form.
   */
  public function testEditEffectHelpText(): void {
    // Create a random image style.
    $style = $this->createStyle('test_style', 'Test style');

    // Add the help block to the page.
    $this->drupalPlaceBlock('help_block', ['region' => 'help', 'id' => 'block-help']);

    // Add the crop effect to the image style.
    $edit = [];
    $edit['data[width]'] = 20;
    $edit['data[height]'] = 20;
    $this->drupalGet($style->toUrl()->toString() . '/add/image_resize');
    $this->submitForm($edit, 'Add effect');

    // Open the edit effect form and check image style effect help text.
    $style = ImageStyle::load($style->getName());
    $effects = $style->get('effects');
    $this->assertGreaterThanOrEqual(1, count($effects));
    foreach ($effects as $id => $effect) {
      $this->drupalGet($style->toUrl()->toString() . '/effects/' . $id);

      $this->assertSession()->pageTextContains('Resizing will make images an exact set of dimensions. This may cause images to be stretched or shrunk disproportionately.');
    }
  }

}
