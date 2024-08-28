<?php

declare(strict_types=1);

namespace Drupal\Tests\image\Functional;

use Drupal\image\Entity\ImageStyle;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests Test call of hook_image_derivative_created.
 *
 * @group image
 */
class ImageDerivativeCreatedHookTest extends ImageFieldTestBase {

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }


  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test call of hook_image_derivative_created.
   */
  public function testHookDerivativeCreatedCalled(): void {
    static $file;

    if (!isset($file)) {
      $files = $this->drupalGetTestFiles('image');
      $file = reset($files);
    }

    // Make sure we have an image in our wrapper testing file directory.
    $source_uri = \Drupal::service('file_system')->copy($file->uri, 'public://');

    // Setup a style to be created and effects to add to it.
    $style_name = $this->randomMachineName(10);
    $style_label = $this->randomString();
    $style_path = 'admin/config/media/image-styles/manage/' . $style_name;
    $effect_edits = [
      'image_resize' => [
        'data[width]' => 100,
        'data[height]' => 101,
      ],
      'image_scale' => [
        'data[width]' => 110,
        'data[height]' => 111,
        'data[upscale]' => 1,
      ],
    ];

    // Add style form.
    $edit = [
      'name' => $style_name,
      'label' => $style_label,
    ];
    $this->drupalGet('admin/config/media/image-styles/add');
    $this->submitForm($edit, 'Create new style');

    // Add each sample effect to the style.
    foreach ($effect_edits as $effect => $edit) {
      // Add the effect.
      $this->drupalGet($style_path);
      $this->submitForm(['new' => $effect], 'Add');
      $this->submitForm($edit, 'Add effect');
    }

    // The hook should not have been called yet.
    $this->assertNull(\Drupal::state()->get('image_module_test_image_derivative_created.original_uri'));
    $this->assertNull(\Drupal::state()->get('image_module_test_image_derivative_created.style'));
    $this->assertNull(\Drupal::state()->get('image_module_test_image_derivative_created.derivative_uri'));

    // Initiate relevant state variables to FALSE.
    $state = \Drupal::state();
    $state->set('image_module_test_image_derivative_created.original_uri', FALSE);
    $state->set('image_module_test_image_derivative_created.style', FALSE);
    $state->set('image_module_test_image_derivative_created.derivative_uri', FALSE);

    // Load the saved image style.
    $style = ImageStyle::load($style_name);

    // Build the derivative image.
    $derivative_uri = $style->buildUri($source_uri);
    $style->createDerivative($source_uri, $derivative_uri);

    // Assert that the hook was called, by checking the state variables.
    $this->assertEquals($state->get('image_module_test_image_derivative_created.original_uri'), $source_uri);
    $this->assertEquals($state->get('image_module_test_image_derivative_created.style'), $style_name);
    $this->assertEquals($state->get('image_module_test_image_derivative_created.derivative_uri'), $derivative_uri);
  }

}
