<?php

declare(strict_types=1);

namespace Drupal\user_permission_provider_test;

/**
 * Traditional callbacks defined by permissions.yml.
 */
final class PermissionCallbacks {

  /**
   * Permission callback for testing.
   */
  public function permissionsMultiple(): array {
    return [
      'user_permission_provider_test permission_callbacks permission 1' => [
        'title' => \t('permission_callbacks permission multiple 1'),
      ],
      'user_permission_provider_test permission_callbacks permission 2' => [
        'title' => \t('permission_callbacks permission multiple 2'),
      ],
    ];
  }

  /**
   * Permission callback for testing.
   */
  public function permissionsSingle(): array {
    return [
      'user_permission_provider_test permission_callbacks permission 3' => [
        'title' => \t('permission_callbacks permission single 3'),
      ],
    ];
  }

}
