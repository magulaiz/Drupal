<?php

declare(strict_types=1);

namespace Drupal\user_permission_provider_test;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Traditional callbacks defined by permissions.yml.
 */
final class PermissionCallbacksFromExistingService {

  use StringTranslationTrait;

  /**
   * A service which also provides permissions.
   */
  public function __construct(
    TranslationInterface $translation,
  ) {
    $this->setStringTranslation($translation);
  }

  /**
   * Permission callback for testing.
   */
  public function permissions(): array {
    return [
      'existing service permission_callbacks permission' => [
        'title' => $this->t('Existing service permission'),
      ],
    ];
  }

}
