<?php

namespace Drupal\Tests\taxonomy\Functional;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Tests\Traits\Core\AssertTokenReplacementTrait;

/**
 * Generates text using placeholders for dummy content to check taxonomy token
 * replacement.
 *
 * @group taxonomy
 */
class TokenReplaceTest extends TaxonomyTestBase {

  use AssertTokenReplacementTrait;

  /**
   * The vocabulary used for creating terms.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected $vocabulary;

  /**
   * Name of the taxonomy term reference field.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $tokenService;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->drupalCreateUser([
      'administer taxonomy',
      'bypass node access',
    ]));
    $this->vocabulary = $this->createVocabulary(['name' => 'V1 <strong>"&lt;name&gt;"</strong>']);
    $this->fieldName = 'taxonomy_' . $this->vocabulary->id();

    $handler_settings = [
      'target_bundles' => [
        $this->vocabulary->id() => $this->vocabulary->id(),
      ],
      'auto_create' => TRUE,
    ];
    $this->createEntityReferenceField('node', 'article', $this->fieldName, NULL, 'taxonomy_term', 'default', $handler_settings, FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');
    $display_repository->getFormDisplay('node', 'article')
      ->setComponent($this->fieldName, [
        'type' => 'options_select',
      ])
      ->save();
    $display_repository->getViewDisplay('node', 'article')
      ->setComponent($this->fieldName, [
        'type' => 'entity_reference_label',
      ])
      ->save();
  }

  /**
   * Creates some terms and a node, then tests the tokens generated from them.
   */
  public function testTaxonomyTokenReplacement() {
    $language_interface = \Drupal::languageManager()->getCurrentLanguage();

    // Create two taxonomy terms with unsafe names.
    $term1 = $this->createTerm($this->vocabulary, ['name' => 'T1 <script>"&lt;name&gt;"</script>']);
    $term2 = $this->createTerm($this->vocabulary, ['name' => 'T2 <strong>"&lt;name&gt;"</strong>']);

    // Edit $term2, setting $term1 as parent.
    $edit = [];
    $edit['name[0][value]'] = '<blink>Blinking Text</blink>';
    $edit['parent[]'] = [$term1->id()];
    $this->drupalGet('taxonomy/term/' . $term2->id() . '/edit');
    $this->submitForm($edit, 'Save');

    // Create node with term2.
    $edit = [];
    $node = $this->drupalCreateNode(['type' => 'article']);
    $edit[$this->fieldName . '[]'] = $term2->id();
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, 'Save');

    // Generate and test sanitized tokens for term1.
    $tests = [];
    $tests['[term:tid]'] = $term1->id();
    $tests['[term:name]'] = $term1->getName();
    $tests['[term:description]'] = $term1->getDescription();
    $tests['[term:url]'] = $term1->toUrl('canonical', ['absolute' => TRUE])->toString();
    $tests['[term:node-count]'] = 0;
    $tests['[term:parent]'] = '[term:parent]';
    $tests['[term:parent:name]'] = '[term:parent:name]';
    $tests['[term:parent:url]'] = '[term:parent:url]';
    $tests['[term:vocabulary:name]'] = $this->vocabulary->label();
    $tests['[term:vocabulary]'] = $this->vocabulary->label();

    // Test to make sure that we generated something for each token.
    $this->assertFalse(in_array(0, array_map('strlen', $tests)), 'No empty tokens generated.');

    $base_bubbleable_metadata = BubbleableMetadata::createFromObject($term1);

    $metadata_tests = [];
    $metadata_tests['[term:tid]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:name]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:description]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:url]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:node-count]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:name]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:url]'] = $base_bubbleable_metadata;
    $bubbleable_metadata = clone $base_bubbleable_metadata;
    $metadata_tests['[term:vocabulary:name]'] = $bubbleable_metadata->addCacheTags($this->vocabulary->getCacheTags());
    $metadata_tests['[term:vocabulary]'] = $bubbleable_metadata->addCacheTags($this->vocabulary->getCacheTags());

    $data = ['term' => $term1];
    $options = ['langcode' => $language_interface->getId()];
    $msg = 'Taxonomy term 1 token %token replaced with %output which is equal to %expected';
    $this->assertTokenReplacementAndCheckMetadata($tests, $data, $options, $msg, $metadata_tests);

    // Generate and test sanitized tokens for term2.
    $tests = [];
    $tests['[term:tid]'] = $term2->id();
    $tests['[term:name]'] = $term2->getName();
    $tests['[term:description]'] = $term2->description->processed;
    $tests['[term:description]'] = $term2->getDescription();
    $tests['[term:url]'] = $term2->toUrl('canonical', ['absolute' => TRUE])->toString();
    $tests['[term:node-count]'] = 1;
    $tests['[term:vocabulary]'] = $this->vocabulary->label();
    $tests['[term:parent]'] = $term1->getName();
    $tests['[term:parent:tid]'] = $term1->id();
    $tests['[term:parent:name]'] = $term1->getName();
    $tests['[term:parent:description]'] = $term1->getDescription();
    $tests['[term:parent:url]'] = $term1->toUrl('canonical', ['absolute' => TRUE])->toString();
    $tests['[term:parent:node-count]'] = 0;
    $tests['[term:parent:parent:name]'] = '[term:parent:parent:name]';
    $tests['[term:vocabulary:name]'] = $this->vocabulary->label();
    $tests['[term:parent:vocabulary]'] = $this->vocabulary->label();
    $tests['[term:parent:vocabulary:name]'] = $this->vocabulary->label();

    // Test to make sure that we generated something for each token.
    $this->assertNotContains(0, array_map('strlen', $tests), 'No empty tokens generated.');

    $base_bubbleable_metadata = BubbleableMetadata::createFromObject($term2);
    $metadata_tests = [];
    $metadata_tests['[term:tid]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:name]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:description]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:url]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:node-count]'] = $base_bubbleable_metadata;
    $bubbleable_metadata = clone $base_bubbleable_metadata;
    $bubbleable_metadata = $bubbleable_metadata->addCacheTags($this->vocabulary->getCacheTags());
    $metadata_tests['[term:vocabulary]'] = $bubbleable_metadata;
    $metadata_tests['[term:vocabulary:name]'] = $bubbleable_metadata;
    $base_bubbleable_metadata = BubbleableMetadata::createFromObject($term1)->addCacheTags($term2->getCacheTags());
    $metadata_tests['[term:parent]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:tid]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:name]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:description]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:url]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:node-count]'] = $base_bubbleable_metadata;
    $metadata_tests['[term:parent:parent:name]'] = $base_bubbleable_metadata;
    $bubbleable_metadata = clone $base_bubbleable_metadata;
    $bubbleable_metadata = $bubbleable_metadata->addCacheTags($this->vocabulary->getCacheTags());
    $metadata_tests['[term:parent:vocabulary:name]'] = $bubbleable_metadata;
    $metadata_tests['[term:parent:vocabulary]'] = $bubbleable_metadata;

    $data = ['term' => $term2];
    $msg = 'Taxonomy term 2 token %token replaced with %output which is equal to %expected';
    $this->assertTokenReplacementAndCheckMetadata($tests, $data, $options, $msg, $metadata_tests);

    // Generate and test sanitized tokens.
    $tests = [];
    $tests['[vocabulary:vid]'] = $this->vocabulary->id();
    $tests['[vocabulary:name]'] = $this->vocabulary->label();
    $tests['[vocabulary:description]'] = $this->vocabulary->getDescription();
    $tests['[vocabulary:node-count]'] = 1;
    $tests['[vocabulary:term-count]'] = 2;

    // Test to make sure that we generated something for each token.
    $this->assertNotContains(0, array_map('strlen', $tests), 'No empty tokens generated.');

    $base_bubbleable_metadata = BubbleableMetadata::createFromObject($this->vocabulary);

    $metadata_tests = [];
    $metadata_tests['[vocabulary:vid]'] = $base_bubbleable_metadata;
    $metadata_tests['[vocabulary:name]'] = $base_bubbleable_metadata;
    $metadata_tests['[vocabulary:description]'] = $base_bubbleable_metadata;
    $metadata_tests['[vocabulary:node-count]'] = $base_bubbleable_metadata;
    $metadata_tests['[vocabulary:term-count]'] = $base_bubbleable_metadata;

    $data = ['vocabulary' => $this->vocabulary];
    $msg = 'Taxonomy vocabulary token %token replaced with %output which is equal to %expected';
    $this->assertTokenReplacementAndCheckMetadata($tests, $data, $options, $msg, $metadata_tests);

  }

}
