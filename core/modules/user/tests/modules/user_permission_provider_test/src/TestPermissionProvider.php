<?php

declare(strict_types=1);

namespace Drupal\user_permission_provider_test;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Permission provider service for testing.
 */
final class TestPermissionProvider {

  use StringTranslationTrait;

  public function __construct(
    TranslationInterface $translation,
  ) {
    $this->setStringTranslation($translation);
  }

  /**
   * Permission callback for testing.
   */
  public function permissionsMultiple(): array {
    return [
      'user_permission_provider_test permission 1' => [
        'title' => $this->t('Test permission multiple 1'),
      ],
      'user_permission_provider_test permission 2' => [
        'title' => $this->t('Test permission multiple 2'),
      ],
    ];
  }

  /**
   * Permission callback for testing.
   */
  public function permissionsSingle(): array {
    return [
      'user_permission_provider_test permission 3' => [
        'title' => $this->t('Test permission single 3'),
      ],
    ];
  }

}
