<?php

namespace Drupal\Tests\layout_builder\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;

/**
 * Provides a base class for testing implementations of a section list.
 *
 * @coversDefaultClass \Drupal\layout_builder\Plugin\SectionStorage\SectionStorageBase
 */
abstract class SectionListTestBase extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'layout_discovery',
    'layout_test',
  ];

  /**
   * The section list implementation.
   *
   * @var \Drupal\layout_builder\SectionListInterface
   */
  protected $sectionList;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $section_data = [
      '11000000-0000-1000-a000-000000000000' => (new Section('layout_test_plugin', [], [
        '10000000-0000-1000-a000-000000000000' => new SectionComponent('10000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('11000000-0000-1000-a000-000000000000'),
      '22000000-0000-1000-a000-000000000000' => (new Section('layout_test_plugin', ['setting_1' => 'bar'], [
        '20000000-0000-1000-a000-000000000000' => new SectionComponent('20000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('22000000-0000-1000-a000-000000000000')
        ->setWeight(1),
    ];
    $this->sectionList = $this->getSectionList($section_data);
  }

  /**
   * Sets up the section list.
   *
   * @param array $section_data
   *   An array of section data.
   *
   * @return \Drupal\layout_builder\SectionListInterface
   *   The section list.
   */
  abstract protected function getSectionList(array $section_data);

  /**
   * Tests ::getSections().
   */
  public function testGetSections() {
    $expected = [
      0 => (new Section('layout_test_plugin', ['setting_1' => 'Default'], [
        '10000000-0000-1000-a000-000000000000' => new SectionComponent('10000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('11000000-0000-1000-a000-000000000000'),
      1 => (new Section('layout_test_plugin', ['setting_1' => 'bar'], [
        '20000000-0000-1000-a000-000000000000' => new SectionComponent('20000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('22000000-0000-1000-a000-000000000000')
        ->setWeight(1),
    ];
    $this->assertSections($expected);
  }

  /**
   * Tests ::getSections().
   */
  public function testGetSectionsByUuid() {
    $expected = [
      '11000000-0000-1000-a000-000000000000' => (new Section('layout_test_plugin', ['setting_1' => 'Default'], [
        '10000000-0000-1000-a000-000000000000' => new SectionComponent('10000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('11000000-0000-1000-a000-000000000000'),
      '22000000-0000-1000-a000-000000000000' => (new Section('layout_test_plugin', ['setting_1' => 'bar'], [
        '20000000-0000-1000-a000-000000000000' => new SectionComponent('20000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('22000000-0000-1000-a000-000000000000')
        ->setWeight(1),
    ];
    $this->assertSections($expected, TRUE);
  }

  /**
   * @covers ::getSection
   */
  public function testGetSection() {
    $this->assertInstanceOf(Section::class, $this->sectionList->getSection('11000000-0000-1000-a000-000000000000'));
  }

  /**
   * @covers ::getSection
   */
  public function testGetSectionInvalidUuid() {
    $this->expectException(\OutOfBoundsException::class);
    $this->expectExceptionMessage('Invalid UUID "uuid"');
    $this->sectionList->getSection('uuid');
  }

  /**
   * @covers ::getSection
   *
   * @group legacy
   */
  public function testGetSectionWithDelta() {
    $this->expectDeprecation('Calling getSection() with delta as an argument is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Instead you should pass a UUID. See https://www.drupal.org/node/3401886');
    $this->assertInstanceOf(Section::class, $this->sectionList->getSection(0));
  }

  /**
   * @covers ::insertSection
   */
  public function testInsertSection() {
    $expected = [
      (new Section('layout_test_plugin', ['setting_1' => 'Default'], [
        '10000000-0000-1000-a000-000000000000' => new SectionComponent('10000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('11000000-0000-1000-a000-000000000000'),
      (new Section('layout_onecol'))->setUuid('33000000-0000-1000-a000-000000000000')
        ->setWeight(1),
      (new Section('layout_test_plugin', ['setting_1' => 'bar'], [
        '20000000-0000-1000-a000-000000000000' => new SectionComponent('20000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('22000000-0000-1000-a000-000000000000')
        ->setWeight(2),
    ];

    $this->sectionList->insertSection(1, (new Section('layout_onecol'))->setUuid('33000000-0000-1000-a000-000000000000'));
    $this->assertSections($expected);
  }

  /**
   * @covers ::appendSection
   */
  public function testAppendSection() {
    $expected = [
      (new Section('layout_test_plugin', ['setting_1' => 'Default'], [
        '10000000-0000-1000-a000-000000000000' => new SectionComponent('10000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('11000000-0000-1000-a000-000000000000'),
      (new Section('layout_test_plugin', ['setting_1' => 'bar'], [
        '20000000-0000-1000-a000-000000000000' => new SectionComponent('20000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('22000000-0000-1000-a000-000000000000')
        ->setWeight(1),
      (new Section('layout_onecol'))->setUuid('33000000-0000-1000-a000-000000000000')
        ->setWeight(2),
    ];

    $this->sectionList->appendSection((new Section('layout_onecol'))->setUuid('33000000-0000-1000-a000-000000000000'));
    $this->assertSections($expected);
  }

  /**
   * @covers ::removeAllSections
   *
   * @dataProvider providerTestRemoveAllSections
   */
  public function testRemoveAllSections($set_blank) {
    $expected = [];
    if ($set_blank === NULL) {
      $this->sectionList->removeAllSections();
    }
    elseif ($set_blank === TRUE) {
      $expected = [$this->sectionList->removeAllSections($set_blank)->getSections()[0]];
    }
    else {
      $this->sectionList->removeAllSections($set_blank);
    }
    $this->assertSections($expected);
  }

  /**
   * Provides test data for ::testRemoveAllSections().
   */
  public static function providerTestRemoveAllSections() {
    $data = [];
    $data[] = [NULL, []];
    $data[] = [FALSE, []];
    $data[] = [TRUE, [new Section('layout_builder_blank')]];
    return $data;
  }

  /**
   * @covers ::removeSection
   */
  public function testRemoveSection() {
    $expected = [
      (new Section('layout_test_plugin', ['setting_1' => 'bar'], [
        '20000000-0000-1000-a000-000000000000' => new SectionComponent('20000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('22000000-0000-1000-a000-000000000000'),
    ];

    $this->sectionList->removeSection('11000000-0000-1000-a000-000000000000');
    $this->assertSections($expected);
  }

  /**
   * @covers ::removeSection
   */
  public function testRemoveSectionInvalidUuid() {
    $this->expectException(\OutOfBoundsException::class);
    $this->expectExceptionMessage('Invalid UUID "uuid"');
    $this->sectionList->removeSection('uuid');
  }

  /**
   * @covers ::removeSection
   *
   * @group legacy
   */
  public function testRemoveSectionWithDelta() {
    $expected = [
      (new Section('layout_test_plugin', ['setting_1' => 'bar'], [
        '20000000-0000-1000-a000-000000000000' => new SectionComponent('20000000-0000-1000-a000-000000000000', 'content', ['id' => 'foo']),
      ]))->setUuid('22000000-0000-1000-a000-000000000000'),
    ];

    $this->expectDeprecation('Calling removeSection() with delta as an argument is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Instead, you should pass a UUID. See https://www.drupal.org/node/3401886');
    $this->sectionList->removeSection(0);
    $this->assertSections($expected);
  }

  /**
   * @covers ::removeSection
   */
  public function testRemoveMultipleSections() {
    $this->sectionList->removeSection('11000000-0000-1000-a000-000000000000');
    $this->sectionList->removeSection('22000000-0000-1000-a000-000000000000');
    $expected = $this->sectionList->getSections()[0];
    $this->assertSections([$expected]);
  }

  /**
   * Tests __clone().
   */
  public function testClone() {
    $this->assertSame(['setting_1' => 'Default'], $this->sectionList->getSections()[0]->getLayoutSettings());

    $new_section_storage = clone $this->sectionList;
    $new_section_storage->getSections()[0]->setLayoutSettings(['asdf' => 'qwer']);
    $this->assertSame(['setting_1' => 'Default'], $this->sectionList->getSections()[0]->getLayoutSettings());
  }

  /**
   * Asserts that the field list has the expected sections.
   *
   * @param \Drupal\layout_builder\Section[] $expected
   *   The expected sections.
   * @param bool $sections_keyed_by_uuid
   *   Whether the sections are keyed by uuid. Defaults to FALSE.
   */
  protected function assertSections(array $expected, bool $sections_keyed_by_uuid = FALSE) {
    $result = $this->sectionList->getSections($sections_keyed_by_uuid);
    $this->assertEquals($expected, $result);
    $this->assertSame(array_keys($expected), array_keys($result));
  }

}
