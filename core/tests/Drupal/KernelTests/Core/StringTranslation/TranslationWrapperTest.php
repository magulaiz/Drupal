<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\StringTranslation;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationWrapper;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the TranslationWrapper backward compatibility layer.
 *
 * @coversDefaultClass \Drupal\Core\StringTranslation\TranslationWrapper
 * @group StringTranslation
 */
class TranslationWrapperTest extends KernelTestBase {

  /**
   * @covers ::__construct
   */
  public function testTranslationWrapper(): void {
    $object = new TranslationWrapper('Backward compatibility');
    $this->assertInstanceOf(TranslatableMarkup::class, $object);
  }

}
