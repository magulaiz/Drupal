<?php

declare(strict_types=1);

namespace Drupal\Tests\jsonapi\Functional;

use Drupal\Core\Entity\Entity\EntityLinkSuggester;
use Drupal\Core\Url;

/**
 * JSON:API integration test for the "EntityLinkSuggester" config entity type.
 *
 * @group jsonapi
 */
class EntityLinkSuggesterTest extends ConfigEntityResourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $entityTypeId = 'entity_link_suggester';

  /**
   * {@inheritdoc}
   */
  protected static $resourceTypeName = 'entity_link_suggester--entity_link_suggester';

  /**
   * {@inheritdoc}
   *
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
  protected function getExpectedDocument() {
    $self_url = Url::fromUri('base:/jsonapi/entity_link_suggester/entity_link_suggester/' . $this->entity->uuid())->setAbsolute()->toString(TRUE)->getGeneratedUrl();
    return [
      'jsonapi' => [
        'meta' => [
          'links' => [
            'self' => ['href' => 'http://jsonapi.org/format/1.0/'],
          ],
        ],
        'version' => '1.0',
      ],
      'links' => [
        'self' => ['href' => $self_url],
      ],
      'data' => [
        'id' => $this->entity->uuid(),
        'type' => 'entity_link_suggester--entity_link_suggester',
        'links' => [
          'self' => ['href' => $self_url],
        ],
        'attributes' => [
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
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getPostDocument() {
    // @todo Update in https://www.drupal.org/node/2300677.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessMessage($method) {
    return "The 'administer site configuration' permission is required.";
  }

  /**
   * {@inheritdoc}
   */
  protected function createAnotherEntity($key) {
    $entity_link_suggester = EntityLinkSuggester::create([
      'admin_label' => 'Users only',
      'id' => 'users_only',
      'entity_types' => [
        'node' => [
          'entity_type' => 'user',
          'bundles' => NULL,
        ],
      ],
    ]);
    $entity_link_suggester->save();
    return $entity_link_suggester;
  }

}
