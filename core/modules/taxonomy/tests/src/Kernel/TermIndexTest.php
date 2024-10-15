<?php

declare(strict_types=1);

namespace Drupal\Tests\taxonomy\Kernel;

use Drupal\Core\Database\Database;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the taxonomy index maintenance.
 *
 * @group taxonomy
 */
class TermIndexKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'taxonomy',
    'node',
    'field',
    'user',
    'system',
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

    // Create a vocabulary.
    $this->vocabulary = $this->createVocabulary();

    // Create taxonomy term reference fields for the 'article' content type.
    $this->fieldName1 = $this->randomMachineName();
    $this->fieldName2 = $this->randomMachineName();

    $handler_settings = [
      'target_bundles' => [
        $this->vocabulary->id() => $this->vocabulary->id(),
      ],
      'auto_create' => TRUE,
    ];

    $this->createEntityReferenceField('node', 'article', $this->fieldName1, 'Taxonomy field 1', 'taxonomy_term', 'default', $handler_settings, FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    $this->createEntityReferenceField('node', 'article', $this->fieldName2, 'Taxonomy field 2', 'taxonomy_term', 'default', $handler_settings, FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
  }

  /**
   * Tests that the taxonomy index is maintained properly.
   */
  public function testTaxonomyIndex(): void {
    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');

    // Create terms in the vocabulary.
    $term_1 = $this->createTerm($this->vocabulary);
    $term_2 = $this->createTerm($this->vocabulary);

    // Create a node referencing the first term.
    $node = $this->createNode([
      'type' => 'article',
      'title' => $this->randomString(),
      $this->fieldName1 => [['target_id' => $term_1->id()]],
      $this->fieldName2 => [['target_id' => $term_1->id()]],
    ]);

    // Check that the term is indexed, and only once.
    $connection = Database::getConnection();
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

  /**
   * Creates a taxonomy term for a given vocabulary.
   *
   * @param \Drupal\taxonomy\VocabularyInterface $vocabulary
   *   The vocabulary to which the term will belong.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The created term.
   */
  protected function createTerm($vocabulary) {
    $term = $this->container->get('entity_type.manager')->getStorage('taxonomy_term')->create([
      'vid' => $vocabulary->id(),
      'name' => $this->randomString(),
    ]);
    $term->save();
    return $term;
  }

  /**
   * Creates a node with the given values.
   *
   * @param array $values
   *   The values to create the node with.
   *
   * @return \Drupal\node\NodeInterface
   *   The created node.
   */
  protected function createNode(array $values) {
    $node = $this->container->get('entity_type.manager')->getStorage('node')->create($values);
    $node->save();
    return $node;
  }

}
