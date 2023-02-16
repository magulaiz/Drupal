<?php

namespace Drupal\Tests\image\Functional;

use Drupal\image\Entity\ImageStyle;
use Drupal\image\ImageStyleInterface;

/**
 * {@inheritdoc}
 */
class ImageAdminUiTest extends ImageFieldTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['help', 'block', 'system'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The image style used for testing.
   *
   * @var \Drupal\image\ImageStyleInterface
   */
  protected ImageStyleInterface $style;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->style = $this->createStyle('test_style', 'Test style');

    // Add the help block to the page.
    $this->drupalPlaceBlock('help_block', ['region' => 'help', 'id' => 'block-help']);
  }

  /**
   * Tests if the help text is available on the add/edit effect form.
   */
  public function testEffectHelpText(): void {
    // Tests if the help text is available on the add effect form
    // Open the add effect form and check for the help text.
    $this->drupalGet($this->style->toUrl()->toString() . '/add/image_resize');

    $this->assertSession()->pageTextContains('Resizing will make images an exact set of dimensions. This may cause images to be stretched or shrunk disproportionately.');

    // Tests if the help text is available on the edit effect form.
    // Add the crop effect to the image style.
    $edit = [];
    $edit['data[width]'] = 20;
    $edit['data[height]'] = 20;
    $this->submitForm($edit, 'Add effect');

    // Open the edit effect form and check image style effect help text.
    $style = ImageStyle::load($this->style->getName());
    $effects = $style->get('effects');
    $this->assertGreaterThanOrEqual(1, count($effects));
    foreach ($effects as $id => $effect) {
      $this->drupalGet($style->toUrl()->toString() . '/effects/' . $id);

      $this->assertSession()->pageTextContains('Resizing will make images an exact set of dimensions. This may cause images to be stretched or shrunk disproportionately.');
    }
  }

}
