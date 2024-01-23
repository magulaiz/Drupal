<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Rest;

use Drupal\Core\Entity\Entity\EntityLinkSuggester;
use Drupal\Tests\rest\Functional\EntityResource\ConfigEntityResourceTestBase;

abstract class EntityLinkSuggesterResourceTestBase extends ConfigEntityResourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node'];

  /**
   * {@inheritdoc}
   */
  protected static $entityTypeId = 'entity_link_suggester';

  /**
   * @var \Drupal\Core\Entity\Entity\EntityLinkSuggesterInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method) {
    $this->grantPermissionsToTestedRole(['administer site configuration']);
  }

  /**
   * {@inheritdoc}
   */
  protected function createEntity() {
    $entity_link_suggester = EntityLinkSuggester::create([
      'admin_label' => 'Nodes only',
      'id' => 'nodes_only',
      'entity_types' => [
        'node' => [
          'entity_type' => 'node',
          'bundles' => NULL,
        ],
      ],
    ]);
    $entity_link_suggester->save();
    return $entity_link_suggester;
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedNormalizedEntity() {
    return [
      'langcode' => 'en',
      'status' => TRUE,
      'dependencies' => [
        'modules' => [
          'node',
        ],
      ],
      'drupal_internal_id' => 'nodes_only',
      'admin_label' => 'Nodes only',
      'entity_types' => [
        'node' => [
          'entity_type' => 'node',
          'bundles' => NULL,
        ],
      ],
      'uuid' => $this->entity->uuid(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getNormalizedPostEntity() {
    // @todo Update in https://www.drupal.org/node/2300677.
    return [];
  }

}
