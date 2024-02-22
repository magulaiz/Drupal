<?php

namespace Drupal\Tests\layout_builder\Kernel;

use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionListInterface;
use Drupal\layout_builder\SectionListTrait;

/**
 * @coversDefaultClass \Drupal\layout_builder\SectionListTrait
 *
 * @group layout_builder
 * @group #slow
 */
class SectionListTraitTest extends SectionListTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getSectionList(array $section_data) {
    return new TestSectionList($section_data);
  }

  /**
   * @covers ::addBlankSection
   */
  public function testAddBlankSection() {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('A blank section must only be added to an empty list');
    $this->sectionList->addBlankSection();
  }

}

class TestSectionList implements SectionListInterface {

  use SectionListTrait {
    addBlankSection as public;
  }

  /**
   * An array of sections.
   *
   * @var \Drupal\layout_builder\Section[]
   */
  protected $sections;

  /**
   * TestSectionList constructor.
   */
  public function __construct(array $sections) {
    // Loop through each section and reconstruct it to ensure that all default
    // values are present.
    foreach (array_values($sections) as $weight => $section) {
      $this->sections[$section->getUuid()] = (Section::fromArray($section->toArray()))->setWeight($weight);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setSections(array $sections) {
    $this->sections = [];
    foreach (array_values($sections) as $weight => $section) {
      $this->sections[$section->getUuid()] = $section->setWeight($weight);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSections(bool $key_by_uuid = FALSE) {
    $sections = $this->sections;
    if ($key_by_uuid) {
      return $sections;
    }
    else {
      $sections_numerically_keyed = [];
      foreach ($sections as $section) {
        $sections_numerically_keyed[$section->getWeight()] = $section;
      }
      return $sections_numerically_keyed;
    }
  }

}
