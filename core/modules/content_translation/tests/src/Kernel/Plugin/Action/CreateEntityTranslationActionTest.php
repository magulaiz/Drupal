<?php

declare(strict_types=1);

namespace Drupal\Tests\content_translation\Kernel\Plugin\Action;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\system\Entity\Action;

/**
 * Tests Content Entity Translate action.
 *
 * @covers \Drupal\content_translation\Plugin\Action\CreateEntityTranslationAction
 *
 * @group action
 * @group content_translation
 */
class CreateEntityTranslationActionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'content_translation',
    'field',
    'language',
    'node',
    'system',
    'user',
  ];

  /**
   * The content translation manager.
   */
  protected ContentTranslationManagerInterface $contentTranslationManager;

  /**
   * The language manager.
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The added languages.
   */
  protected array $langcodes = [];

  /**
   * The renderer.
   */
  protected RendererInterface $renderer;

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
    $entity_type = 'node';
    $article_type = NodeType::create(['type' => 'article', 'name' => 'article']);
    $article_type->save();

    $page_type = NodeType::create(['type' => 'page', 'name' => 'page']);
    $page_type->save();

    $this->enableTranslation();

    $field_name = 'field_ref_article';
    // Look for or add the specified field to the requested entity bundle.
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'type' => 'entity_reference',
      'entity_type' => $entity_type,
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'translatable' => '0',
      'settings' => [
        'target_type' => $entity_type,
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => 'page',
      'label' => 'Article reference',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'article' => 'article',
          ],
        ],
      ],
    ])->save();

  }

  /**
   * Enables translations where it needed.
   */
  protected function enableTranslation(): void {
    // Enable translation for the page content type.
    $this->contentTranslationManager->setEnabled('node', 'page', TRUE);
    $this->contentTranslationManager->setEnabled('node', 'article', TRUE);
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
  public function testCreateEntityTranslationAction(): void {
    // Create a translate action config.
    $action = Action::create([
      'id' => 'translate_action',
      'label' => 'Translate',
      'plugin' => 'entity:translate_action:node',
      'configuration' => [
        'source_langcode' => 'en',
        'target_langcodes' => ['fr'],
      ],
    ]);
    $action->save();

    // Create a translatable test nodes.
    $article_node = Node::create([
      'type' => 'article',
      'title' => 'Article Test node',
      'uid' => 1,
      'langcode' => 'en',
    ]);
    $article_node->save();

    $page_node = Node::create([
      'type' => 'page',
      'title' => 'Page Test node',
      'uid' => 1,
      'langcode' => 'en',
      'field_ref_article' => [
        ['target_id' => $article_node->id()],
      ],
    ]);
    $page_node->save();

    $this->assertFalse($page_node->hasTranslation('fr'));
    $this->assertFalse($article_node->hasTranslation('fr'));

    // Make sure only fr translation is created.
    $this->executeActionOnEntities($action, [$page_node]);

    $this->assertTrue($page_node->hasTranslation('fr'));
    $this->assertFalse($page_node->hasTranslation('es'));
    $fr_page_translation = $page_node->getTranslation('fr');
    $this->assertEquals($fr_page_translation->label(), 'Page Test node');
    // Also check the referenced article node.
    $article_node = Node::load($article_node->id());
    $this->assertTrue($article_node->hasTranslation('fr'));

    // Make sure also es translation is created using fr translation as source.
    $fr_article_translation = $article_node->getTranslation('fr');
    $fr_page_translation->setTitle('Page Node de test');
    $fr_page_translation->save();
    $fr_article_translation->setTitle('Article Node de test');
    $fr_article_translation->save();

    // Update the action configuration accordingly.
    $action->set('configuration', ['source_langcode' => 'fr', 'target_langcodes' => ['es']]);
    $action->save();

    $this->executeActionOnEntities($action, [$page_node]);

    // Reload the page node to get the updated translations.
    $page_node = Node::load($page_node->id());
    $this->assertTrue($page_node->hasTranslation('es'));
    $es_page_translation = $page_node->getTranslation('es');
    $this->assertEquals($es_page_translation->label(), 'Page Node de test');
    $this->assertEquals($this->contentTranslationManager->getTranslationMetadata($es_page_translation)->getSource(), 'fr');

    // Reload the article node to get the updated translations.
    $article_node = Node::load($article_node->id());
    $this->assertTrue($article_node->hasTranslation('es'));
    $es_article_translation = $article_node->getTranslation('es');
    $this->assertEquals($es_article_translation->label(), 'Article Node de test');
    $this->assertEquals($this->contentTranslationManager->getTranslationMetadata($es_article_translation)->getSource(), 'fr');
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
