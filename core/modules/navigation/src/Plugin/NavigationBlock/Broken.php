<?php

declare(strict_types=1);

namespace Drupal\navigation\Plugin\NavigationBlock;

use Drupal\Core\Block\BlockPluginTrait;
use Drupal\Core\Cache\CacheableDependencyTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a fallback plugin for missing navigation block plugins.
 */
#[NavigationBlock(
  id: "broken",
  admin_label: new TranslatableMarkup("Broken/Missing"),
  category: new TranslatableMarkup("Navigation")
)]
class Broken extends PluginBase implements NavigationBlockPluginInterface, ContainerFactoryPluginInterface {

  use BlockPluginTrait;
  use CacheableDependencyTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * Creates a Broken Block instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];
    if ($this->currentUser->hasPermission('administer navigation_block')) {
      $build += $this->brokenMessage();
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state):array {
    return $this->brokenMessage();
  }

  /**
   * Generate message with debug info as to why the navigation block is broken.
   *
   * @return array
   *   Render array containing debug information.
   */
  protected function brokenMessage(): array {
    $build['message'] = [
      '#markup' => $this->t('This navigation block is broken or missing. You may be missing content or you might need to install the original module.'),
    ];

    return $build;
  }

}
