<?php

declare(strict_types=1);

namespace Drupal\Tests\image\Unit;

use Drupal\Core\Image\ImageFactory;
use Drupal\image\ImageValidatorSettingsTrait;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the image validator settings trait.
 *
 * @group image
 * @coversDefaultClass \Drupal\image\ImageValidatorSettingsTrait
 */
class ImageValidatorSettingsTest extends UnitTestCase {

  use ImageValidatorSettingsTrait;

  /**
   * The image factory.
   */
  protected ImageFactory $imageFactory;

  /**
   * @covers ::getImageUploadValidators
   */
  public function testGetImageUploadValidators(): void {
    $this->imageFactory = $this->createMock(ImageFactory::class);
    // Ensure we don't support webp which is in the settings.
    $this->imageFactory->expects($this->once())
      ->method('getSupportedExtensions')
      ->willReturn(['jpg', 'png', 'gif']);

    $settings = [
      'max_resolution' => '100x100',
      'min_resolution' => '10x10',
      'file_extensions' => 'jpg png webp',
    ];
    $validators = $this->getImageUploadValidators($settings);

    $this->assertNotEmpty($validators);

    $this->assertEquals([
      'FileIsImage' => [],
      'FileImageDimensions' => [
        'maxDimensions' => '100x100',
        'minDimensions' => '10x10',
      ],
      'FileExtension' => [
        'extensions' => 'jpg png',
      ],
    ], $validators);
  }

}
