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
      'uuid' => '966e5967-f19c-44b0-87b1-697441385b08',
      'created' => '694702320',
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
      'uuid' => '36c25329-6c3b-452e-82fa-e20c502f69ed',
    ]);
    $node->setCreatedTime(1695058272);
    $node->save();
    $translation = $node->addTranslation('de', [
      'title' => 'Deutsch foo',
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
    $suggestion_node_1_en = [
      'description' => 'by sofie on 2023-09-19',
      'entity_type_id' => 'node',
      'entity_uuid' => '36c25329-6c3b-452e-82fa-e20c502f69ed',
      'group' => 'Content - Basic page',
      'label' => 'foo',
      'path' => 'entity:node/1',
      'exposed_attributes' => [
        'download' => FALSE,
      ],
    ];
    $suggestion_node_1_de = [
      'description' => 'by sofie on 2023-09-19',
      'entity_type_id' => 'node',
      'entity_uuid' => '36c25329-6c3b-452e-82fa-e20c502f69ed',
      'group' => 'Content - Basic page',
      'label' => 'Deutsch foo',
      'path' => 'entity:node/1',
      'exposed_attributes' => [
        'download' => FALSE,
      ],
    ];

    $suggestion_user_1 = [
      'description' => 'on 1992-01-06',
      'entity_type_id' => 'user',
      'entity_uuid' => '966e5967-f19c-44b0-87b1-697441385b08',
      'group' => 'User',
      'label' => 'sofie',
      'path' => 'entity:user/1',
      'exposed_attributes' => [
        'download' => FALSE,
      ],
    ];

    // "f", multiple results, from node vs user.
    yield 'suggestions=default (everything), host entity type=node, host entity langcode=en, search term="f"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => NULL,
      ],
      'search term' => 'f',
      'host entity type' => 'node',
      'host entity langcode' => 'en',
      'expected suggestions' => [
        $suggestion_node_1_en,
        $suggestion_user_1,
      ],
    ];
    yield 'suggestions=default (everything), host entity type=user, host entity langcode=en, search term="f"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => NULL,
      ],
      'search term' => 'f',
      'host entity type' => 'user',
      'host entity langcode' => 'en',
      'expected suggestions' => [
        $suggestion_user_1,
        $suggestion_node_1_en,
      ],
    ];

    // "f", single result due to (different) suggestion restrictions.
    yield 'suggestions=nodes only, host entity type=node, host entity langcode=en, search term="f"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => [
          ['entity_type_id' => 'node', 'bundles' => NULL],
        ],
      ],
      'search term' => 'f',
      'host entity type' => 'node',
      'host entity langcode' => 'en',
      'expected suggestions' => [
        $suggestion_node_1_en,
      ],
    ];
    yield 'suggestions=users only, host entity type=node, host entity langcode=en, search term="f"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => [
          ['entity_type_id' => 'user', 'bundles' => NULL],
        ],
      ],
      'search term' => 'f',
      'host entity type' => 'node',
      'host entity langcode' => 'en',
      'expected suggestions' => [
        $suggestion_user_1,
      ],
    ];

    // "f", no result due to even tighter suggestion restrictions.
    yield 'suggestions=article nodes only, host entity type=node, host entity langcode=en, search term="f"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => [
          ['entity_type_id' => 'node', 'bundles' => ['article']],
        ],
      ],
      'search term' => 'f',
      'host entity type' => 'node',
      'host entity langcode' => 'en',
      'expected suggestions' => [
        [
          'description' => 'No content suggestions found. This URL will be used as is.',
          'group' => 'No results',
          'label' => 'f',
          'path' => 'f',
        ],
      ],
    ];

    // "fo", single result, but different labels due to host entity langcode.
    yield 'suggestions=default (everything), host entity type=node, host entity langcode=en, search term="fo"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => NULL,
      ],
      'search term' => 'fo',
      'host entity type' => 'node',
      'host entity langcode' => 'en',
      'expected suggestions' => [
        $suggestion_node_1_en,
      ],
    ];
    yield 'suggestions=default (everything), host entity type=node, host entity langcode=de, search term="fo"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => NULL,
      ],
      'search term' => 'fo',
      'host entity type' => 'node',
      'host entity langcode' => 'de',
      'expected suggestions' => [
        $suggestion_node_1_de,
      ],
    ];

    // "Deutsch" (which appears only on a translation of an entity!), single
    // result, but different labels due to host entity langcode.
    yield 'suggestions=default (everything), host entity type=node, host entity langcode=en, search term="Deutsch"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => NULL,
      ],
      'search term' => 'Deutsch',
      'host entity type' => 'node',
      'host entity langcode' => 'en',
      'expected suggestions' => [
        $suggestion_node_1_en,
      ],
    ];
    yield 'suggestions=default (everything), host entity type=node, host entity langcode=de, search term="Deutsch"' => [
      'configuration' => [
        'allow_download_links' => TRUE,
        'suggestions' => NULL,
      ],
      'search term' => 'Deutsch',
      'host entity type' => 'node',
      'host entity langcode' => 'de',
      'expected suggestions' => [
        $suggestion_node_1_de,
      ],
    ];
  }

  /**
   * Test the generated entity link suggestions based on editor configuration.
   *
   * @dataProvider providerEntityLinkSuggestions
   */
  public function testEntityLinkSuggestions(array $plugin_configuration, string $search, string $host_entity_type_id, string $host_entity_langcode, array $expected): void {
    // Set the given configuration for the entity link suggestions plugin.
    $editor = Editor::load('test_format');
    $settings = $editor->getSettings();
    $settings['plugins']['ckeditor5_link_entity_suggestions'] = $plugin_configuration;
    $editor->setSettings($settings);

    // Whatever configuration it is, it must be valid.
    $this->assertSame([], array_map(
      function (ConstraintViolation $v) {
        return (string) $v->getMessage();
      },
      iterator_to_array(CKEditor5::validatePair(
        Editor::load('test_format'),
        FilterFormat::load('test_format')
      ))
    ));

    $controller = EntityLinkSuggestionsController::create($this->container);

    $request = Request::create("/irrelevant-in-kernel-test");
    $request->query->set('q', $search);
    $response = $controller->suggestions($request, $editor, $host_entity_type_id, $host_entity_langcode);
    $this->assertInstanceOf(JsonResponse::class, $response);

    $data = json_decode($response->getContent(), TRUE);

    // Perform assertions on the response data.
    $this->assertArrayHasKey('suggestions', $data);
    $this->assertIsArray($data['suggestions']);
    $this->assertSame($expected, $data['suggestions']);
  }

}
