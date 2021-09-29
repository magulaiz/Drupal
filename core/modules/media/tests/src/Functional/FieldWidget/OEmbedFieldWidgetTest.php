<?php

namespace Drupal\Tests\media\Functional\FieldWidget;

use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\media\Functional\MediaFunctionalTestBase;

/**
 * @covers \Drupal\media\Plugin\Field\FieldWidget\OEmbedWidget
 *
 * @group media
 */
class OEmbedFieldWidgetTest extends MediaFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->config('media.settings')->set('standalone_url', TRUE)->save();
  }

  /**
   * Test to ensure that help text exists when it is set on field configuration.
   */
  public function testFieldWidgetHelpText() {
    $this->drupalLogin($this->rootUser);

    $media_type = $this->createMediaType('oembed:video');
    $source_field = $media_type->getSource()
      ->getSourceFieldDefinition($media_type)
      ->getName();

    /** @var \Drupal\field\Entity\FieldConfig $field */
    $field = FieldConfig::loadByName('media', $media_type->id(), $source_field);
    $field->setDescription('This is help text for oEmbed field.')
      ->save();

    $this->drupalGet('media/add/' . $media_type->id());
    $this->assertSession()->pageTextContains($field->getDescription());
  }

}
