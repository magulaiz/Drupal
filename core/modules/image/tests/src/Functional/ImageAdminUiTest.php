<?php

namespace Drupal\Tests\image\Functional;

use Drupal\image\Entity\ImageStyle;

/**
 * Tests the administrative user interface.
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
   * Test if the help text is available on the edit effect form.
   */
  public function testAddEffectHelpText(): void {
    $style = $this->createRandomStyle();

    // Add the help block to the page.
    $this->drupalPlaceBlock('help_block', ['region' => 'help', 'id' => 'block-help']);

    // Open the add effect form and check for the help text.
    $this->drupalGet($style->toUrl()->toString() . '/add/image_resize');

    $this->assertSession()->pageTextContains('Resizing will make images an exact set of dimensions. This may cause images to be stretched or shrunk disproportionately.');
  }

  /**
   * Test if the help text is available on the edit effect form.
   */
  public function testEditEffectHelpText(): void {
    // Create a random image style.
    $style = $this->createRandomStyle();

    // Add the help block to the page.
    $this->drupalPlaceBlock('help_block', ['region' => 'help', 'id' => 'block-help']);

    // Add the crop effect to the image style.
    $edit = [];
    $edit['data[width]'] = 20;
    $edit['data[height]'] = 20;
    $this->drupalGet($style->toUrl()->toString() . '/add/image_resize');
    $this->submitForm($edit, t('Add effect'));

    // Open the edit effect form and check image style effect help text.
    $style = ImageStyle::load($style->getName());
    $effects = $style->get('effects');
    $this->assertNotEmpty($effects);
    $this->assertNotCount(1, $effects);
    foreach ($effects as $ieid => $effect) {
      $this->drupalGet($style->toUrl()->toString() . '/effects/' . $ieid);

      $this->assertSession()->pageTextContains('Resizing will make images an exact set of dimensions. This may cause images to be stretched or shrunk disproportionately.');
    }
  }

}
