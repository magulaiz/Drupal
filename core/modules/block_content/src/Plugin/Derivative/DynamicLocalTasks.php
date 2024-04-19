<?php

namespace Drupal\block_content\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates block_content-related local tasks.
 */
class DynamicLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * Creates a DynamicLocalTasks object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The config factory.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager, protected ImmutableConfig $config) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id): ContainerDeriverInterface|DynamicLocalTasks|static {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory')->get('block_content.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition): array {
    // Provide an edit_form task if standalone block_content URLs are enabled.
    $this->derivatives["entity.block_content.canonical"] = [
      'route_name' => "entity.block_content.canonical",
      'title' => $this->t('Edit'),
      'base_route' => 'entity.block_content.canonical',
      'weight' => 2,
    ] + $base_plugin_definition;

    if ($this->config->get('standalone_url')) {
      $this->derivatives["entity.block_content.canonical"]['title'] = $this->t('View');

      $this->derivatives["entity.block_content.edit_form"] = [
        'route_name' => "entity.block_content.edit_form",
        'title' => $this->t('Edit'),
        'base_route' => 'entity.block_content.canonical',
        'weight' => 2,
      ] + $base_plugin_definition;
    }

    $this->derivatives["entity.block_content.delete_form"] = [
      'route_name' => "entity.block_content.delete_form",
      'title' => $this->t('Delete'),
      'base_route' => "entity.block_content.canonical",
      'weight' => 10,
    ] + $base_plugin_definition;

    return $this->derivatives;
  }

}
