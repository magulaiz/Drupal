<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Gettext;

use Drupal\Component\Gettext\PoStreamReader;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Gettext PO file header handling features.
 *
 * @see Drupal\Component\Gettext\PoHeader.
 *
 * @group Gettext
 */
class PoStreamReaderTest extends TestCase {

  /**
   * Calling open should throws an exception if URI is invalid .
   *
   * See issue #3301239.
   */
  public function testOpenMethodThrowsExceptionOnInvalidURI(): void {
    $reader = new PoStreamReader();
    $reader->setURI('fake');
    $this->expectException(\Exception::class);
    $reader->open();
  }

  /**
   * Validates that calling readItem with a NULL fd returns NULL.
   *
   * See issue #3301239.
   */
  public function testOpeningFileError(): void {
    $reader = new PoStreamReader();
    $reader->setURI('fake');
    $this->assertNull($reader->readItem());
  }

}
