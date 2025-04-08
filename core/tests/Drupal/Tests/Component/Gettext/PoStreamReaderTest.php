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
   * Creates and returns a PoStreamReader instance with a fake URI.
   *
   * @return \Drupal\Component\Gettext\PoStreamReader
   *   The PoStreamReader instance.
   */
  private function createPoStreamReader(): PoStreamReader {
    $reader = new PoStreamReader();
    $reader->setURI('fake');
    return $reader;
  }

  /**
   * Calling open should throw an exception if URI is invalid.
   *
   * See issue #3301239.
   */
  public function testOpenMethodThrowsExceptionOnInvalidURI(): void {
    $reader = $this->createPoStreamReader();
    $this->expectException(\Exception::class);
    $reader->open();
  }

  /**
   * Validates that calling readItem with a NULL file descriptor returns NULL.
   *
   * See issue #3301239.
   */
  public function testOpeningFileError(): void {
    $reader = $this->createPoStreamReader();
    $this->assertNull($reader->readItem());
  }

}
