<?php

declare(strict_types = 1);

namespace Drupal\Tests\ckeditor5\Unit;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\ckeditor5\HTMLRestrictions
 * @group ckeditor5
 */
class HTMLRestrictionsTest extends UnitTestCase {

  /**
   * @covers ::count()
   * @dataProvider providerCount
   */
  public function testCount(array $elements, int $expected): void {
    $this->assertCount($expected, new HTMLRestrictions($elements));
  }

  public function providerCount(): \Generator {
    yield 'empty' => [
      [],
      0
    ];

    yield 'one' => [
      ['a' => TRUE],
      1,
    ];

    yield 'two' => [
      ['a' => TRUE, 'b' => FALSE],
      2,
    ];

    yield 'two of which one is a wildcard' => [
      ['a' => TRUE, '$block' => FALSE],
      2,
    ];
  }

  /**
   * @covers ::parse()
   * @dataProvider providerParse
   */
  public function testParse($input, array $expected): void {
    $this->assertSame($expected, HTMLRestrictions::parse($input)->getAllowedElements());
  }

  public function providerParse(): \Generator {
    // All empty cases.
    yield 'empty string' => [
      '',
      []
    ];
    yield 'empty array' => [
      [],
      []
    ];
    yield 'whitespace string' => [
      '             ',
      []
    ];

    // Some nonsense cases.
    yield 'nonsense string' => [
      'Hello there, this looks nothing like a HTML restriction.',
      []
    ];
    yield 'nonsense array #1' => [
      ['foo', 'bar'],
      []
    ];
    yield 'nonsense array #2' => [
      ['foo' => TRUE, 'bar' => FALSE],
      []
    ];

    // Single tag cases.
    yield 'tag without attributes' => [
      '<a>',
      ['a' => FALSE],
    ];
    yield 'tag with wildcard attribute' => [
      '<a *>',
      ['a' => TRUE],
    ];
    yield 'tag with single attribute allowing any value' => [
      '<a target>',
      ['a' => ['target' => TRUE]],
    ];
    yield 'tag with single attribute allowing single specific value' => [
      '<a target="_blank">',
      ['a' => ['target' => ['_blank' => TRUE]]],
    ];
    yield 'tag with single attribute allowing multiple specific values' => [
      '<a target="_self _blank">',
      ['a' => ['target' => ['_self' => TRUE, '_blank' => TRUE]]],
    ];
    yield 'tag with single attribute allowing multiple specific values (reverse order)' => [
      '<a target="_blank _self">',
      ['a' => ['target' => ['_self' => TRUE, '_blank' => TRUE]]],
    ];
    yield 'tag with two attributes' => [
      '<a target class>',
      ['a' => ['target' => TRUE, 'class' => TRUE]],
    ];
    yield 'tag with two attributes, one with a partial wildcard' => [
      '<a target class>',
      ['a' => ['target' => TRUE, 'class' => TRUE]],
    ];

    // Multiple tag cases.
    yield 'two tags' => [
      '<a> <p>',
      ['a' => FALSE, 'p' => FALSE],
    ];
    yield 'two tags (reverse order)' => [
      '<a> <p>',
      ['a' => FALSE, 'p' => FALSE],
    ];

    // Wildcard tag.
    yield '$block' => [
      '<$block class="text-align-left text-align-center text-align-right text-align-justify">',
      [],
    ];

    // @todo test `data-*` attribute, related: #2105841
    // @todo port test coverage similar to #2596083
  }

  /**
   * @covers ::toCKEditor5ElementsArray()
   * @covers ::toFilterHtmlAllowedTagsString()
   * @covers ::toGeneralHtmlSupportConfig()
   * @dataProvider providerRepresentations
   */
  public function testRepresentations(HTMLRestrictions $restrictions, array $expected_elements_array, string $expected_allowed_html_string, array $expected_ghs_config): void {
    $this->assertSame($expected_elements_array, $restrictions->toCKEditor5ElementsArray());
    $this->assertSame($expected_allowed_html_string, $restrictions->toFilterHtmlAllowedTagsString());
    $this->assertSame($expected_ghs_config, $restrictions->toGeneralHtmlSupportConfig());
  }

  public function providerRepresentations(): \Generator {
    yield 'empty set' => [
      HTMLRestrictions::emptySet(),
      [],
      '',
      [],
    ];

    yield 'only tags' => [
      new HTMLRestrictions(['a' => FALSE, 'p' => FALSE, 'br' => FALSE]),
      ['<a>', '<p>', '<br>'],
      '<a> <p> <br>',
      [
        ['name' => 'a'],
        ['name' => 'p'],
        ['name' => 'br'],
      ]
    ];

    yield 'realistic' => [
      new HTMLRestrictions(['a' => ['href' => TRUE, 'hreflang' => ['en', 'fr']], 'p' => ['data-*' => TRUE], 'br' => FALSE]),
      ['<a href hreflang="en fr">', '<p data-*>', '<br>'],
      '<a href hreflang="en fr"> <p data-*> <br>',
      [
        [
          'name' => 'a',
          'attributes' => [
            'href' => TRUE,
            'hreflang' => ['en', 'fr'],
          ],
        ],
        [
          'name' => 'p',
          'attributes' => [
            'data-*' => TRUE,
          ]
        ],
        ['name' => 'br'],
      ]
    ];
  }


  /**
   * @covers ::diff()
   * @dataProvider  providerDiff
   */
  public function testDiff(HTMLRestrictions $a, HTMLRestrictions $b, HTMLRestrictions $expected): void {
    $this->assertEquals($expected, $a->diff($b));
  }

  public function providerDiff(): \Generator {
    // Empty set operand cases.
    yield 'any set diffing with empty set' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
    ];
    yield 'empty set diffing with anything' => [
      HTMLRestrictions::emptySet(),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
    ];

    // Basic cases.
    yield 'set diffing with a set that has an empty intersection' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['b' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
    ];
    yield 'set diffing with an identical set' => [
      new HTMLRestrictions(['b' => ['href' => TRUE]]),
      new HTMLRestrictions(['b' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
    ];
    yield 'set diffing with a superset' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['b' => ['href' => TRUE], 'a' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
    ];

    // Attribute diffing.
    yield 'attribute restrictions are more permissive: <a href> vs <a *>' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => TRUE]),
      HTMLRestrictions::emptySet(),
    ];
    yield 'attribute restrictions are more permissive: <a> vs <a href>' => [
      new HTMLRestrictions(['a' => FALSE]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
    ];
    yield 'attribute restrictions are more restrictive: <a href> vs <a>' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => FALSE]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
    ];
    yield 'attribute restrictions are more restrictive: <a *> vs <a href>' => [
      new HTMLRestrictions(['a' => TRUE]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => TRUE]),
    ];
    yield 'attribute restrictions are different: <a href> vs <a hreflang>' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => ['hreflang' => TRUE]]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
    ];
  }

  /**
   * @covers ::intersect()
   * @dataProvider  providerIntersect
   */
  public function testIntersect(HTMLRestrictions $a, HTMLRestrictions $b, HTMLRestrictions $expected): void {
    $this->assertEquals($expected, $a->intersect($b));
  }

  public function providerIntersect(): \Generator {
    // Empty set operand cases.
    yield 'any set intersecting with empty set' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
      HTMLRestrictions::emptySet(),
    ];
    yield 'empty set intersecting with anything' => [
      HTMLRestrictions::emptySet(),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
    ];

    // Basic cases.
    yield 'set intersecting with a set that has an empty intersection' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['b' => ['href' => TRUE]]),
      HTMLRestrictions::emptySet(),
    ];
    yield 'set intersecting with an identical set' => [
      new HTMLRestrictions(['b' => ['href' => TRUE]]),
      new HTMLRestrictions(['b' => ['href' => TRUE]]),
      new HTMLRestrictions(['b' => ['href' => TRUE]]),
    ];
    yield 'set intersecting with a superset' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['b' => ['href' => TRUE], 'a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
    ];

    // Attribute intersecting.
    yield 'attribute restrictions are more permissive: <a href> vs <a *>' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => TRUE]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
    ];
    yield 'attribute restrictions are more permissive: <a> vs <a href>' => [
      new HTMLRestrictions(['a' => FALSE]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => FALSE]),
    ];
    yield 'attribute restrictions are more restrictive: <a href> vs <a>' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => FALSE]),
      new HTMLRestrictions(['a' => FALSE]),
    ];
    yield 'attribute restrictions are more restrictive: <a *> vs <a href>' => [
      new HTMLRestrictions(['a' => TRUE]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
    ];
    yield 'attribute restrictions are different: <a href> vs <a hreflang>' => [
      new HTMLRestrictions(['a' => ['href' => TRUE]]),
      new HTMLRestrictions(['a' => ['hreflang' => TRUE]]),
      new HTMLRestrictions(['a' => []]),
    ];
  }

}
