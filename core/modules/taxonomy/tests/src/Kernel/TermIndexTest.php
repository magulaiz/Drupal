<?php

declare(strict_types=1);

namespace Drupal\Tests\taxonomy\Kernel;

use Drupal\Core\Database\Database;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\node\Entity\Node;

/**
 * Tests the taxonomy index maintenance.
 *
 * @group taxonomy
 */
class TermIndexTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'taxonomy',
    'node',
    'field',
    'user',
    'system',
    'text',
  ];

  /**
   * The vocabulary for testing.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected $vocabulary;

  /**
   * Name of the first taxonomy term reference field.
   *
   * @var string
   */
  protected $fieldName1;

  /**
   * Name of the second taxonomy term reference field.
   *
   * @var string
   */
  protected $fieldName2;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Install required schema for the test.
    $this->installEntitySchema('taxonomy_term');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('taxonomy', ['taxonomy_index']);
    $this->installSchema('node', ['node_access']);

    // Create a vocabulary.
    $this->vocabulary = Vocabulary::create([
      'name' => 'Test Vocabulary',
      'vid' => 'test_vocabulary',
    ]);
    $this->vocabulary->save();

    // Create two taxonomy term reference fields on the article content type.
    $this->fieldName1 = 'field_' . $this->randomMachineName();
    $this->fieldName2 = 'field_' . $this->randomMachineName();
    $this->createTaxonomyField($this->fieldName1);
    $this->createTaxonomyField($this->fieldName2);
  }

  /**
   * Helper function to create a taxonomy term reference field.
   */
  protected function createTaxonomyField(string $field_name): void {
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'taxonomy_term',
      ],
      'cardinality' => -1,
    ]);
    $field_storage->save();

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'label' => $field_name,
    ])->save();
  }

  /**
   * Tests that the taxonomy index is maintained properly.
   */
  public function testTaxonomyIndex(): void {
    $connection = Database::getConnection();

    // Create terms in the vocabulary.
    $term_1 = Term::create([
      'name' => 'Term 1',
      'vid' => $this->vocabulary->id(),
    ]);
    $term_1->save();

    $term_2 = Term::create([
      'name' => 'Term 2',
      'vid' => $this->vocabulary->id(),
    ]);
    $term_2->save();

    // Create a node and assign terms.
    $node = Node::create([
      'type' => 'article',
      'title' => $this->randomMachineName(),
      $this->fieldName1 => [['target_id' => $term_1->id()]],
      $this->fieldName2 => [['target_id' => $term_1->id()]],
    ]);
    $node->save();

    // Check that the term is indexed once.
    $index_count = $connection->select('taxonomy_index')
      ->condition('nid', $node->id())
      ->condition('tid', $term_1->id())
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(1, $index_count, 'Term 1 is indexed once.');

    // Update the node to change one term.
    $node->{$this->fieldName1} = [['target_id' => $term_2->id()]];
    $node->save();

    // Check that both terms are indexed.
    $index_count = $connection->select('taxonomy_index')
      ->condition('nid', $node->id())
      ->condition('tid', $term_1->id())
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(1, $index_count, 'Term 1 is indexed.');
    $index_count = $connection->select('taxonomy_index')
      ->condition('nid', $node->id())
      ->condition('tid', $term_2->id())
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(1, $index_count, 'Term 2 is indexed.');

    // Update the node to change another term.
    $node->{$this->fieldName2} = [['target_id' => $term_2->id()]];
    $node->save();

    // Check that only one term is indexed.
    $index_count = $connection->select('taxonomy_index')
      ->condition('nid', $node->id())
      ->condition('tid', $term_1->id())
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(0, $index_count, 'Term 1 is not indexed.');
    $index_count = $connection->select('taxonomy_index')
      ->condition('nid', $node->id())
      ->condition('tid', $term_2->id())
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(1, $index_count, 'Term 2 is indexed once.');
  }

}
