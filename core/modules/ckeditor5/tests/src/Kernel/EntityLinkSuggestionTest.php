<?php

declare(strict_types = 1);

namespace Drupal\Tests\ckeditor5\Kernel;

// cspell:ignore Sofie

use Drupal\ckeditor5\Controller\EntityLinkSuggestionsController;
use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\editor\Entity\Editor;
use Drupal\node\Entity\NodeType;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @coversDefaultClass \Drupal\ckeditor5\Controller\EntityLinkSuggestionsController
 * @group ckeditor5
 * @internal
 */
class EntityLinkSuggestionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'ckeditor5',
    'filter',
    'editor',
    'system',
    'user',
    'datetime',
    'datetime_range',
    'language',
    'content_translation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create text format, associate CKEditor 5, validate.
    FilterFormat::create([
      'format' => 'test_format',
      'name' => 'Test format',
      'filters' => [
        'filter_html' => [
          'status' => TRUE,
          'settings' => [
            'allowed_html' => '<p> <br> <a href data-entity-type data-entity-uuid download>',
          ],
        ],
        'entity_links' => [
          'status' => TRUE,
        ],
      ],
    ])->save();
    Editor::create([
      'format' => 'test_format',
      'editor' => 'ckeditor5',
      'settings' => [
        'toolbar' => [
          'items' => [
            'link',
          ],
        ],
        'plugins' => [
          // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\EntityLinkSuggestions::defaultConfiguration()
          'ckeditor5_link_entity_suggestions' => [
            'allow_download_links' => TRUE,
            'suggestions' => NULL,
          ],
        ],
      ],
    ])->save();
    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair(
        Editor::load('test_format'),
        FilterFormat::load('test_format')
      ))
    ));

    // Create a node type for testing.
    $node_type = NodeType::create([
      'type' => 'page',
      'name' => 'Basic page',
    ]);
    $node_type->save();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('date_format');
    $this->installSchema('node', ['node_access']);
    $this->container->get('content_translation.manager')->setEnabled('node', $node_type->id(), TRUE);

    // Create an account with "f" in the username.
    $user = User::create([
      'name' => 'sofie',
    ]);
    $user->addRole('create page content');
    $user->addRole('use text format test_format');
    $user->save();
    $this->container->get('current_user')->setAccount($user);

    DateFormat::create([
      'id' => 'fallback',
      'label' => 'Fallback',
      'pattern' => 'Y-m-d',
    ])->save();

    // Create the translation language.
    $this->installConfig(['language']);
    ConfigurableLanguage::createFromLangcode('de')->save();

    // Load the test node entity.
    $node = Node::create([
      'type' => 'page',
      'title' => 'foo',
    ]);
    $node->setCreatedTime(time());
    $node->save();
    $translation = $node->addTranslation('de', [
      'title' => 'foo_translated_de',
    ])->setCreatedTime(time());
    $translation->save();
  }

  /**
   * Data provider.
   *
   * @return \Generator
   *   Test scenarios.
   */
  public function providerEntityLinkSuggestions(): \Generator {
    yield 'no suggestion configuration' => [
      [
        'allow_download_links' => TRUE,
        'suggestions' => NULL,
      ],
    ];
  }

  /**
   * Test the generated entity link suggestions based on editor configuration.
   *
   * @dataProvider providerEntityLinkSuggestions
   */
  public function testEntityLinkSuggestions(array $plugin_configuration): void {
    $editor = Editor::load('test_format');
    $settings = $editor->getSettings();
    $settings['plugins']['ckeditor5_link_entity_suggestions'] = $plugin_configuration;
    $editor->setSettings($plugin_configuration);

    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair(
        Editor::load('test_format'),
        FilterFormat::load('test_format')
      ))
    ));

    // Create a sample editor.
    $editor = Editor::load('test_format');
    $controller = EntityLinkSuggestionsController::create($this->container);

    $request = Request::create("/", 'GET', ['q' => 'foo']);

    $request->query->set('q', 'f');
    $response = $controller->suggestions($request, $editor, 'node', 'en');
    $this->assertInstanceOf(JsonResponse::class, $response);

    $data = json_decode($response->getContent(), TRUE);

    // Perform assertions on the response data.
    $this->assertArrayHasKey('suggestions', $data);
    $this->assertIsArray($data['suggestions']);
    // Assert that there are 2 suggestions.
    $this->assertEquals(2, count($data['suggestions']));

    // The first suggestion's label should be foo.
    $this->assertEquals('foo', $data['suggestions'][0]['label']);
    // Assert the remaining fields.
    $this->assertEquals('node', $data['suggestions'][0]['entity_type_id']);
    $this->assertEquals('entity:node/1', $data['suggestions'][0]['path']);

    // The second suggestion's label should be sofie.
    $this->assertEquals('sofie', $data['suggestions'][1]['label']);
    // Assert the remaining fields.
    $this->assertEquals('user', $data['suggestions'][1]['entity_type_id']);
    $this->assertEquals('entity:user/1', $data['suggestions'][1]['path']);

    $request->query->set('q', 'fo');
    $response = $controller->suggestions($request, $editor, 'node', 'en');
    $this->assertInstanceOf(JsonResponse::class, $response);

    $data = json_decode($response->getContent(), TRUE);
    // Perform assertions on the response data.
    $this->assertArrayHasKey('suggestions', $data);
    $this->assertIsArray($data['suggestions']);
    // Assert that there is only 1 suggestion.
    $this->assertEquals(1, count($data['suggestions']));

    // The suggestion's label should be foo.
    $this->assertEquals('foo', $data['suggestions'][0]['label']);
    // Assert the remaining fields for foo.
    $this->assertEquals('node', $data['suggestions'][0]['entity_type_id']);
    $this->assertEquals('entity:node/1', $data['suggestions'][0]['path']);

    // Change language to de as default so that translation shows up.
    $request->query->set('q', 'fo');
    $response = $controller->suggestions($request, $editor, 'node', 'de');
    $this->assertInstanceOf(JsonResponse::class, $response);

    $data = json_decode($response->getContent(), TRUE);
    // Perform assertions on the response data.
    $this->assertArrayHasKey('suggestions', $data);
    $this->assertIsArray($data['suggestions']);
    // Assert that there is only 1 suggestion.
    $this->assertEquals(1, count($data['suggestions']));

    // The suggestion's label should be foo.
    $this->assertEquals('foo_translated_de', $data['suggestions'][0]['label']);
    // Assert the remaining fields for foo.
    $this->assertEquals('node', $data['suggestions'][0]['entity_type_id']);
    $this->assertEquals('entity:node/1', $data['suggestions'][0]['path']);
  }

}
