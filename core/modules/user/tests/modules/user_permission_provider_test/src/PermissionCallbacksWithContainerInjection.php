<?php

declare(strict_types=1);

namespace Drupal\user_permission_provider_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Traditional callbacks defined by permissions.yml.
 */
final class PermissionCallbacksWithContainerInjection implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Private constructor to force entrypoint via create().
   */
  private function __construct(
    TranslationInterface $translation,
  ) {
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('string_translation'),
    );
  }

  /**
   * Permission callback for testing.
   */
  public function permissions(): array {
    return [
      'container injection permission_callbacks permission' => [
        'title' => $this->t('Container injection permission'),
      ],
    ];
  }

}
