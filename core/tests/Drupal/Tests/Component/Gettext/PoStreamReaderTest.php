<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Gettext;

use Drupal\Component\Gettext\PoStreamReader;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\Warning;

/**
 * Unit tests for the Gettext PO file header handling features.
 *
 * @see Drupal\Component\Gettext\PoHeader.
 *
 * @group Gettext
 */
class PoStreamreaderTest extends TestCase {

  /**
   * This test validates that calling readItem with a NULL fd
   * returns NULL. See issue #3301239
   *
   */
  public function testOpeningFileError() {
    $reader = new PoStreamReader();
    $reader->setURI('fake');
    $this->assertNull($reader->readItem());
  }

}
