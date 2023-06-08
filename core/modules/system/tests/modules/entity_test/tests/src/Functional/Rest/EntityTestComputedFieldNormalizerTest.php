<?php

namespace Drupal\Tests\entity_test\Functional\Rest;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\rest\Functional\AnonResourceTestTrait;
use Drupal\entity_test\Entity\EntityTestComputedFieldBundle;

/**
 * Test normalization of computed field.
 *
 * @group rest
 */
class EntityTestComputedFieldNormalizerTest extends EntityTestResourceTestBase {

  use AnonResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $entityTypeId = 'entity_test_computed_field';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    $this->grantPermissionsToTestedRole(['administer entity_test content']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessMessage($method) {
    if ($this->config('rest.settings')->get('bc_entity_resource_permissions')) {
      return parent::getExpectedUnauthorizedAccessMessage($method);
    }

    return "The 'administer entity_test content' permission is required.";
  }

  /**
   * {@inheritdoc}
   */
  protected function createEntity() {
    $bundle = EntityTestComputedFieldBundle::create([
      'name' => 'Entity Test Computed Field Bundle',
      'type' => 'entity_test_computed_field',
      'id' => 'entity_test_computed_field',
    ]);
    $bundle->save();

    $entity_test = parent::createEntity();
    return $entity_test;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedNormalizedEntity() {
    $expected = parent::getExpectedNormalizedEntity();
    $bundle = EntityTestComputedFieldBundle::load('entity_test_computed_field');
    $expected['computed_reference_field'] = [];
    $expected['computed_string_field'] = [];
    unset($expected['field_test_text'], $expected['langcode'], $expected['type'], $expected['uuid']);
    // @see \Drupal\entity_test\Plugin\Field\ComputedTestCacheableStringItemList::computeValue().
    $expected['computed_test_cacheable_string_field'] = [
      [
        'value' => 'computed test cacheable string field',
      ],
    ];

    $expected['uuid'] = [
      0 => [
        'value' => $this->entity->uuid(),
      ],
    ];

    $expected['type'] = [
      0 => [
        'target_id' => 'entity_test_computed_field',
        'target_type' => 'entity_test_comp_field_bundle',
        'target_uuid' => $bundle->uuid(),
      ],
    ];

    return $expected;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedCacheContexts() {
    return Cache::mergeContexts(parent::getExpectedCacheContexts(), ['url.query_args:computed_test_cacheable_string_field']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedCacheTags() {
    return Cache::mergeTags(parent::getExpectedCacheTags(), ['field:computed_test_cacheable_string_field']);
  }

  /**
   * {@inheritdoc}
   */
  public function testPost() {
    // Post test not required.
    $this->markTestSkipped();
  }

  /**
   * {@inheritdoc}
   */
  public function testPatch() {
    // Patch test not required.
    $this->markTestSkipped();
  }

  /**
   * {@inheritdoc}
   */
  public function testDelete() {
    // Delete test not required.
    $this->markTestSkipped();
  }

}
