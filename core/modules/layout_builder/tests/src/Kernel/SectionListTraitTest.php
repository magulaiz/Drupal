<?php

declare(strict_types=1);

namespace Drupal\Tests\layout_builder\Kernel;

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
  public function testAddBlankSection(): void {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('A blank section must only be added to an empty list');
    $this->sectionList->addBlankSection();
  }

}
