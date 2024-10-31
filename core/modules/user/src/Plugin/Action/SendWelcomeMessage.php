<?php

declare(strict_types=1);

namespace Drupal\user\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use \Drupal\Core\Access\AccessResultInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Send welcome message to a user.
 *
 * @Action(
 *   id = "user_welcome_message_action",
 *   label = @Translation("Send welcome message to selected users"),
 *   type = "user"
 * )
 */
class SendWelcomeMessage extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new SendWelcomeMessage object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($account = NULL): void {
    if (empty($account) || empty($account->getEmail())) {
      return;
    }

    $op = 'register_pending_approval';
    if ($account->isActive()) {
      // Determine the user approval method.
      switch ($this->configFactory->get('user.settings')->get('register')) {
        case UserInterface::REGISTER_ADMINISTRATORS_ONLY:
          $op = 'register_admin_created';
          break;

        case UserInterface::REGISTER_VISITORS:
        default:
          $op = 'register_no_approval_required';
      }
    }

    _user_mail_notify($op, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    /** @var \Drupal\user\UserInterface $object */
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
