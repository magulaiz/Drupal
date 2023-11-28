<?php

namespace Drupal\Tests\Core\Test;

use Drupal\KernelTests\AssertContentTrait;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\KernelTests\AssertContentTrait
 * @group Test
 */
class AssertContentTraitTest extends UnitTestCase {

  /**
   * @covers ::getTextContent
   */
  public function testGetTextContent() {
    $test = new class() {
      use AssertContentTrait {
        getTextContent as public;
        setRawContent as public;
      }
    };
    $raw_content = <<<EOT

<Head>
<style>
@import url("foo.css");
</style>
</head>
<body>
bar
</body>
EOT;
    $test->setRawContent($raw_content);
    $this->assertStringNotContainsString('foo', $test->getTextContent());
    $this->assertStringNotContainsString('<body>', $test->getTextContent());
    $this->assertStringContainsString('bar', $test->getTextContent());
  }

}
