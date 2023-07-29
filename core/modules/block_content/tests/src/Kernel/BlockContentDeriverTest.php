<?php

namespace Drupal\Tests\block_content\Kernel;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\block_content\Plugin\Derivative\BlockContent as DerivativeBlockContent;
use Drupal\Component\Plugin\PluginBase;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests block content plugin deriver.
 *
 * @group block_content
 */
class BlockContentDeriverTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'block_content', 'system', 'user'];

  /**
   * The definition array of the base plugin.
   *
   * @var array
   */
  protected $baseDefinition = [
    'id' => 'block_content',
    'provider' => 'block_content',
    'class' => '\Drupal\block_content\Plugin\Block\BlockContentBlock',
    'deriver' => '\Drupal\block_content\Plugin\Derivative\BlockContent',
  ];

  /**
   * The block content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $blockContentStorage;

  /**
   * The tested block content derivative class.
   *
   * @var \Drupal\block_content\Plugin\Derivative\BlockContent
   */
  protected $blockContentDerivative;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('block_content');

    $this->blockContentStorage = \Drupal::entityTypeManager()->getStorage('block_content');
    $this->blockContentDerivative = new DerivativeBlockContent($this->blockContentStorage);
  }

  /**
   * Tests that only reusable blocks are derived.
   */
  public function testReusableBlocksOnlyAreDerived() {
    // Create a block content type.
    $block_content_type = BlockContentType::create([
      'id' => 'spiffy',
      'label' => 'Mucho spiffy',
      'description' => "Provides a block type that increases your site's spiffiness by up to 11%",
    ]);
    $block_content_type->save();
    // And a block content entity.
    $block_content = BlockContent::create([
      'info' => 'Spiffy prototype',
      'type' => 'spiffy',
    ]);
    $block_content->save();

    // Ensure the reusable block content is provided as a derivative block
    // plugin.
    /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
    $block_manager = $this->container->get('plugin.manager.block');
    $plugin_id = 'block_content' . PluginBase::DERIVATIVE_SEPARATOR . $block_content->uuid();
    $this->assertTrue($block_manager->hasDefinition($plugin_id));

    // Set the block not to be reusable.
    $block_content->setNonReusable();
    $block_content->save();

    // Ensure the non-reusable block content is not provided a derivative block
    // plugin.
    $this->assertFalse($block_manager->hasDefinition($plugin_id));
  }

  /**
   * Tests derivate definitions admin labels.
   */
  public function testGetDerivativeDefinitionsAdminLabels() {
    $block_content_entities = $this->getTestBlockContentEntities();

    foreach ($block_content_entities as $entity) {
      $plugin = \Drupal::service('plugin.manager.block')->createInstance('block_content:' . $entity->uuid());
      $plugin_definition = $plugin->getPluginDefinition();

      // Check the plugin definition admin label.
      $expected_label = $entity->label() ?? sprintf('%s %s', $entity->type->entity->label(), $entity->id());
      $this->assertEquals($expected_label, $plugin_definition['admin_label']);
      $this->assertNotNull($plugin_definition['admin_label']);

      // Check the deprecation notice is no longer triggered.
      $previous_error_handler = set_error_handler(function ($severity, $message, $file, $line) use (&$previous_error_handler) {
        // Convert deprecation error into a catchable exception.
        if ($severity === E_DEPRECATED) {
          throw new \ErrorException($message, 0, $severity, $file, $line);
        }
        if ($previous_error_handler) {
          return $previous_error_handler($severity, $message, $file, $line);
        }
      });

      try {
        $expected_suggestion = str_replace(' ', '', strtolower($expected_label));
        $this->assertEquals($expected_suggestion, $plugin->getMachineNameSuggestion());
      }
      catch (\ErrorException $e) {
        $this->fail(sprintf('Deprecation notice thrown when calling the a block content getMachineNameSuggestion(). Message: %s', $e->getMessage()));
      }
    }
  }

  /**
   * Creates the block content test entities.
   */
  protected function getTestBlockContentEntities() {
    // Create a block content type.
    $block_content_type = BlockContentType::create([
      'id' => 'spiffy',
      'label' => 'Mucho spiffy',
      'description' => "Provides a block type that increases your site's spiffiness by up to 11%",
    ]);
    $block_content_type->save();
    // Create a block content entity with a label.
    $block_content_label = BlockContent::create([
      'info' => 'Spiffy prototype',
      'type' => 'spiffy',
    ]);
    $block_content_label->save();
    // Create a block content entity without a label.
    $block_content_no_label = BlockContent::create([
      'type' => 'spiffy',
    ]);
    $block_content_no_label->save();

    // Created entities keyed by their id.
    return [
      $block_content_label->id() => $block_content_label,
      $block_content_no_label->id() => $block_content_no_label,
    ];
  }

}
