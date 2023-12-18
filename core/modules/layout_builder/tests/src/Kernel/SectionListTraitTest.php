<?php

namespace Drupal\Tests\layout_builder\Kernel;

/**
 * @coversDefaultClass \Drupal\layout_builder\SectionListTrait
 *
 * @group layout_builder
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
