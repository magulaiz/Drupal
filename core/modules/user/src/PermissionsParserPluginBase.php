<?php

declare(strict_types = 1);

namespace Drupal\user;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\CallableResolver;
use Drupal\user\Permissions\PermissionsRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Base class for permissions parser plugins.
 */
abstract class PermissionsParserPluginBase extends PluginBase implements ContainerFactoryPluginInterface, PermissionsParserInterface {

  use StringTranslationTrait;

  protected const PROCESSED_KEY = '_processed';

  protected $callableResolver;

  /**
   * Constructs a PermissionsParserPluginBase object.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    TranslationInterface $stringTranslation,
    CallableResolver $callableResolver) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->stringTranslation = $stringTranslation;
    $this->callableResolver = $callableResolver;
  }

  /**
   * {@inheritdoc}
   */
  public abstract function parse(array &$ary, string $provider, PermissionsRepositoryInterface $permissionObj): int;

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function description(): string {
    return (string) $this->pluginDefinition['description'];
  }

  protected function tWrapper($s, $options = [], $context = []) {
    if (is_object($s) || is_array($s)) {
      return $s;
    }

    if (!is_string($s)) {
      $s = '' . $s;
    }

    return $this->t($s, $options, $context);
  }

}
