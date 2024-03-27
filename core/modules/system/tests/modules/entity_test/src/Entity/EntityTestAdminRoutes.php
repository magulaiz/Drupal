<?php

declare(strict_types=1);

namespace Drupal\entity_test\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a test entity type with administrative routes.
 */
#[ContentEntityType(
  id: 'entity_test_admin_routes',
  label: new TranslatableMarkup('Test entity - admin routes'),
  entity_keys: [
    'id' => 'id',
    'uuid' => 'uuid',
    'bundle' => 'type',
    'label' => 'name',
    'langcode' => 'langcode',
  ],
  handlers: [
    'view_builder' => 'Drupal\entity_test\EntityTestViewBuilder',
    'access' => 'Drupal\entity_test\EntityTestAccessControlHandler',
    'form' => [
      'default' => 'Drupal\entity_test\EntityTestForm',
      'delete' => 'Drupal\entity_test\EntityTestDeleteForm',
    ],
    'views_data' => 'Drupal\views\EntityViewsData',
    'route_provider' => ['html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider'],
  ],
  links: [
    'canonical' => '/entity_test_admin_routes/manage/{entity_test_admin_routes}',
    'edit-form' => '/entity_test_admin_routes/manage/{entity_test_admin_routes}/edit',
    'delete-form' => '/entity_test/delete/entity_test_admin_routes/{entity_test_admin_routes}',
  ],
  admin_permission: 'administer entity_test content',
  base_table: 'entity_test_admin_routes',
  data_table: 'entity_test_admin_routes_property_data',
  translatable: TRUE
)]
class EntityTestAdminRoutes extends EntityTest {

}
