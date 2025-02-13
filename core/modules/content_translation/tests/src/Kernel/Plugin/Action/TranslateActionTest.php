<?php

declare(strict_types=1);

namespace Drupal\Tests\content_translation\Kernel\Plugin\Action;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\system\Entity\Action;

/**
 * Tests Content Entity Translate action.
 *
 * @covers \Drupal\block_content\Plugin\migrate\source\d7\BlockCustomTranslation
 *
 * @group action
 * @group content_translation
 */
class TranslateActionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_translation',
    'language',
    'node',
    'system',
    'user',
  ];

  /**
   * The content translation manager.
   *
   * @var \Drupal\content_translation\ContentTranslationManagerInterface
   */
  protected $contentTranslationManager;

  /**
   * The language manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The added languages.
   *
   * @var array
   */
  protected $langcodes = [];

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->contentTranslationManager = $this->container->get('content_translation.manager');
    $this->languageManager = $this->container->get('language_manager');
    $this->renderer = $this->container->get('renderer');

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('node', ['node_access']);

    // Install system's configuration as default date formats are needed.
    $this->setupLanguages();

    // Create a node type for testing.
    $type = NodeType::create(['type' => 'page', 'name' => 'page']);
    $type->save();

    $this->enableTranslation();
  }

  /**
   * Enables translations where it needed.
   */
  protected function enableTranslation(): void {
    // Enable translation for the page content type.
    $this->contentTranslationManager->setEnabled('node', 'page', TRUE);
  }

  /**
   * Adds additional languages.
   */
  protected function setupLanguages(): void {
    $this->langcodes = ['es', 'fr'];
    foreach ($this->langcodes as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }
    array_unshift($this->langcodes, $this->languageManager->getDefaultLanguage()->getId());
  }

  /**
   * Tests Content Entity Translate action.
   */
  public function testTranslateAction(): void {
    // Create a translate action config.
    $action = Action::create([
      'id' => 'translate_action',
      'label' => 'Translate',
      'plugin' => 'entity:translate_action:node',
      'configuration' => [
        'source_langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
        'target_langcodes' => ['fr'],
      ],
    ]);
    $action->save();

    // Create a translatable test node.
    $node = Node::create([
      'type' => 'page',
      'title' => 'Test node',
      'uid' => 1,
      'langcode' => 'en',
    ]);
    $node->save();

    // Make sure only fr translation is created.
    $this->executeActionOnEntities($action, [$node]);

    $this->assertTrue($node->hasTranslation('fr'));
    $this->assertFalse($node->hasTranslation('es'));
    $fr_translation = $node->getTranslation('fr');
    $this->assertEquals($fr_translation->label(), 'Test node');
    $fr_translation->setTitle('Node de test');
    $fr_translation->save();

    // Make sure also es translation is created using fr translation as source.
    // Update the action configuration accordingly.
    $action->set('configuration', ['source_langcode' => 'fr', 'target_langcodes' => ['es']]);
    $action->save();

    $this->executeActionOnEntities($action, [$node]);

    $this->assertTrue($node->hasTranslation('es'));
    $es_translation = $node->getTranslation('es');
    $this->assertEquals($es_translation->label(), 'Node de test');
    $es_translation_metadata = $this->contentTranslationManager->getTranslationMetadata($es_translation);
    $this->assertEquals($es_translation_metadata->getSource(), 'fr');
  }

  /**
   * Execute an action for given entities.
   *
   * @param \Drupal\system\Entity\Action $action
   *   The name of the theme hook to invoke; e.g. 'links' for links.html.twig.
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   An array of entity objects to be deleted.
   */
  protected function executeActionOnEntities($action, array $entities = []): void {
    $this->renderer->executeInRenderContext(new RenderContext(), function () use ($entities, $action) {
      $action->execute($entities);
    });
  }

}
