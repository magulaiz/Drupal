<?php

namespace Drupal\Tests\Core\Theme;

use Drupal\Core\Theme\ThemeColorsParser;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Tests ThemeColorsExtractor class.
 *
 * Files for this test are stored in core/modules/system/tests/fixtures.
 *
 * @coversDefaultClass \Drupal\Core\Theme\ThemeColorsParser
 *
 * @group Theme
 */
class ThemeColorsParserTest extends UnitTestCase {

  /**
   * The ThemeColorsParser object.
   *
   * @var \Drupal\Core\Theme\ThemeColorsParser
   */
  protected $themeColorsParser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->themeColorsParser = new ThemeColorsParser();
  }

  /**
   * Tests the themeColorsParser object for a non-existing file.
   *
   * @covers ::parse
   */
  public function testColorParserNotExisting() {
    $parsed_yaml = $this->themeColorsParser->parse('/does_not_exist.colors.yml');
    ['colors' => $colors, 'schemes' => $schemes] = $parsed_yaml;
    $this->assertEmpty($colors, 'Non existing .colors.yml returns empty array for colors.');
    $this->assertEmpty($schemes, 'Non existing .colors.yml returns empty array for schemes.');
  }

  /**
   * Tests the themeColorsParser object.
   *
   * @covers ::parse
   */
  public function testColorParser() {
    $colors_file = <<<COLOR_FILE
# colors.yml for testing YAML parsing.
colors:
  base_primary_color: 'Primary base color'

schemes:
  default:
    label: 'Blue Lagoon'
    colors:
      base_primary_color: '#1b9ae4'
COLOR_FILE;

    vfsStream::setup('root');
    vfsStream::create([
      'fixtures' => [
        'good.colors.yml' => $colors_file,
      ],
    ]);
    $filename = vfsStream::url('root/fixtures/good.colors.yml');
    ['colors' => $colors, 'schemes' => $schemes] = $this->themeColorsParser->parse($filename);
    $this->assertEquals(['base_primary_color' => 'Primary base color'], $colors);
    $this->assertEquals([
      'default' => [
        'label' => 'Blue Lagoon',
        'colors' => [
          'base_primary_color' => '#1b9ae4',
        ],
      ],
    ], $schemes);
  }

  /**
   * Tests if correct exception is thrown for a broken colors file.
   *
   * @covers ::parse
   */
  public function testColorParserBroken() {
    $broken = <<<BROKEN
# colors.yml for testing broken YAML parsing exception handling.
colors:
  base_primary_color: 'Primary base color'

schemes:
  default:
    label: 'Blue Lagoon'
    colors::;;
      base_primary_color: '#1b9ae4'
BROKEN;

    vfsStream::setup('root');
    vfsStream::create([
      'fixtures' => [
        'broken.colors.yml' => $broken,
      ],
    ]);
    $filename = vfsStream::url('root/fixtures/broken.colors.yml');
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('broken.colors.yml');
    $this->themeColorsParser->parse($filename);
  }

  /**
   * Tests the themeColorsParser object for a missing required property.
   *
   * @covers ::parse
   */
  public function testColorParserMissingRequired() {
    $missing_required = <<<MISSING_REQUIRED
# colors.yml for testing missing required parameter exception handling.
colors:

schemes:
  default:
    label: 'Blue Lagoon'
    colors:
      base_primary_color: '#1b9ae4'
MISSING_REQUIRED;

    vfsStream::setup('root');
    vfsStream::create([
      'fixtures' => [
        'required.colors.yml' => $missing_required,
      ],
    ]);
    $filename = vfsStream::url('root/fixtures/required.colors.yml');
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('At least one configurable color is required.');
    $this->themeColorsParser->parse($filename);
  }

  /**
   * Tests if correct exception is thrown for a variable name mismatch.
   *
   * @covers ::parse
   */
  public function testColorParserSchemeMismatch() {
    $scheme_mismatch = <<<SCHEME_MISMATCH
# colors.yml for testing broken YAML parsing exception handling.
colors:
  base_primary_color: 'Primary base color'

schemes:
  default:
    label: 'Blue Lagoon'
    colors:
      base_secondary_color: '#1b9ae4'
SCHEME_MISMATCH;

    vfsStream::setup('root');
    vfsStream::create([
      'fixtures' => [
        'mismatch.colors.yml' => $scheme_mismatch,
      ],
    ]);
    $filename = vfsStream::url('root/fixtures/mismatch.colors.yml');
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('default');
    $this->themeColorsParser->parse($filename);
  }

}
