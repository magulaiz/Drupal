<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\layout_builder\Section;

/**
 * Tests validation of entity_view_display entities.
 *
 * @group Entity
 * @group Validation
 */
class EntityViewDisplayValidationTest extends EntityViewModeValidationTest {

  /**
   * {@inheritdoc}
   */
  protected bool $hasLabel = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = EntityViewDisplay::create([
      'targetEntityType' => 'user',
      'bundle' => 'user',
      // The mode was created by the parent class.
      'mode' => 'test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that the plugin ID of a Layout Builder section is validated.
   */
  public function testLayoutSectionPluginIdIsValidated(): void {
    $this->enableModules(['layout_builder', 'layout_discovery']);

    $this->entity = $this->container->get('entity_display.repository')
      ->getViewDisplay('user', 'user');
    $this->assertInstanceOf(LayoutEntityDisplayInterface::class, $this->entity);
    $this->entity->enableLayoutBuilder()->save();
    $sections = array_map(fn (Section $section) => $section->toArray(), $this->entity->getSections());
    $this->assertCount(1, $sections);
    $sections[0]['layout_id'] = 'non_existent';

    $this->entity->setThirdPartySetting('layout_builder', 'sections', $sections);
    $this->assertValidationErrors([
      'third_party_settings.layout_builder.sections.0.layout_id' => "The 'non_existent' plugin does not exist.",
    ]);
  }

}
