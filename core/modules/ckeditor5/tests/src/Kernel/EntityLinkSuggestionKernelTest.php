<?php

declare(strict_types = 1);

namespace Drupal\Tests\ckeditor5\Kernel;

use Drupal\ckeditor5\Controller\EntityLinkSuggestionsController;
use Drupal\ckeditor5\Plugin\Editor\CKEditor5;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\editor\Entity\Editor;
use Drupal\node\Entity\NodeType;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * For testing the drupalEntityLinkSuggestions plugin.
 *
 * @group ckeditor5
 * @internal
 */
class EntityLinkSuggestionKernelTest extends KernelTestBase {

  use CKEditor5TestTrait;

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

    // Create an account with "f" in the username.
    $user = User::create([
      'name' => 'so',
    ]);
    $user->addRole('create page content');
    $user->addRole('use text format test_format');
    $user->save();
    $this->container->get('current_user')->setAccount($user);
  }

  /**
   * Test the entity link suggestions.
   */
  public function testEntityLinkSuggestions(): void
  {

    // Load the test node entity.
    $node = Node::create([
      'type' => 'page',
      'title' => 'foo',
    ]);
    $node->setCreatedTime(time());
    $node->save();

    // Create a sample editor.
    $editor = Editor::load('test_format');
    $controller = EntityLinkSuggestionsController::create($this->container);

    $request = Request::create("/", 'GET', ['q' => 'foo']);

    $request->query->set('q', 'f');
    // Testing the suggestions function.
    $response = $controller->suggestions($request, $editor, 'node', 'en');
    // Testing the getSuggestions function.
    $a = $controller->getSuggestions('node', ['node'], 'f');
    $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);

    $data = json_decode($response->getContent(), TRUE);

    // Perform assertions on the response data.
    $this->assertArrayHasKey('suggestions', $data);
    print_r($response);
    $this->assertIsArray($data['suggestions']);

  }
}
