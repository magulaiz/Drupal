<?php

namespace Drupal\Tests\editor\Kernel;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of editor entities.
 *
 * @group editor
 */
class EditorValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['editor', 'editor_test', 'filter'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $format = FilterFormat::create([
      'format' => 'test',
      'name' => 'Test',
    ]);
    $format->save();

    $this->entity = Editor::create([
      'format' => $format->id(),
      'editor' => 'unicorn',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that validation fails if config dependencies are invalid.
   */
  public function testInvalidDependencies(): void {
    // Remove the config dependencies from the editor entity.
    $dependencies = $this->entity->getDependencies();
    $dependencies['config'] = [];
    $this->entity->set('dependencies', $dependencies);

    $this->assertValidationErrors(['' => 'This text editor requires a text format.']);

    // Things look sort-of like `filter.format.*` should fail validation
    // because they don't exist.
    $dependencies['config'] = [
      'filter.format',
      'filter.format.',
    ];
    $this->entity->set('dependencies', $dependencies);
    $this->assertValidationErrors([
      '' => 'This text editor requires a text format.',
      'dependencies.config.0' => "The 'filter.format' config does not exist.",
      'dependencies.config.1' => "The 'filter.format.' config does not exist.",
    ]);
  }

  /**
   * Tests validating an editor with an unknown plugin ID.
   */
  public function testInvalidPluginId(): void {
    $this->entity->setEditor('non_existent');
    $this->assertValidationErrors(['editor' => "The 'non_existent' plugin does not exist."]);
  }

  /**
   * {@inheritdoc}
   */
  public function testLabelValidation(): void {
    // @todo Remove this override in https://www.drupal.org/i/3231354. The label of Editor entities is dynamically computed: it's retrieved from the associated FilterFormat entity. That issue will change this.
    // @see \Drupal\editor\Entity\Editor::label()
    $this->markTestSkipped();
  }

  /**
   * Tests validating an editor with an unknown plugin ID.
   */
  public function testImageUploadSettingsAreConditionallyRequired(): void {
    // When image uploads are disabled, no other key-value pairs are needed.
    $this->entity->setImageUploadSettings(['status' => FALSE]);
    $this->assertValidationErrors([]);

    // But when they are enabled, many others are needed.
    $this->entity->setImageUploadSettings(['status' => TRUE]);
    $this->assertValidationErrors([
      'image_upload' => [
        "'scheme' is a required key.",
        "'directory' is a required key.",
        "'max_size' is a required key.",
        "'max_dimensions' is a required key.",
      ],
    ]);

    // Specify all required keys, but forget one.
    $this->entity->setImageUploadSettings([
      'status' => TRUE,
      'scheme' => 'public',
      'directory' => 'uploaded-images',
      'max_size' => '5 MB',
    ]);
    $this->assertValidationErrors(['image_upload' => "'max_dimensions' is a required key."]);

    // Specify all required keys.
    $this->entity->setImageUploadSettings([
      'status' => TRUE,
      'scheme' => 'public',
      'directory' => 'uploaded-images',
      'max_size' => '5 MB',
      'max_dimensions' => [
        'width' => 10000,
        'height' => 10000,
      ],
    ]);
    $this->assertValidationErrors([]);
  }

}
