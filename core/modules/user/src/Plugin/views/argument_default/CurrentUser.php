<?php

namespace Drupal\user\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsArgumentDefault;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Default argument plugin to extract the current user.
 *
 * This plugin actually has no options so it does not need to do a great deal.
 */
#[ViewsArgumentDefault(
  id: 'current_user',
  title: new TranslatableMarkup('User ID from logged in user'),
)]
class CurrentUser extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * Gets the current active user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * CurrentUser constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if ($current_user === NULL) {
      @trigger_error('Calling' . __CLASS__ . '::__construct() without the $current_user argument is deprecated in drupal:10.1.0 and is removed in drupal:11.0.0. See https://www.drupal.org/node/3347878', E_USER_DEPRECATED);
      $current_user = \Drupal::currentUser();
    }
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return $this->currentUser->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

}
