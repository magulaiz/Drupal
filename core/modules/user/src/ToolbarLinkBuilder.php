<?php

namespace Drupal\user;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * ToolbarLinkBuilder fills out the placeholders generated in user_toolbar().
 */
class ToolbarLinkBuilder implements TrustedCallbackInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ToolbarHandler constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(AccountProxyInterface $account, ModuleHandlerInterface $module_handler) {
    $this->account = $account;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Lazy builder callback for rendering toolbar links.
   *
   * @return array
   *   A renderable array as expected by the renderer service.
   */
  public function renderToolbarLinks() {
    $links = [
      'account' => [
        'title' => $this->t('View profile'),
        'url' => Url::fromRoute('user.page'),
        'attributes' => [
          'title' => $this->t('User account'),
        ],
      ],
      'account_edit' => [
        'title' => $this->t('Edit profile'),
        'url' => Url::fromRoute('entity.user.edit_form', ['user' => $this->account->id()]),
        'attributes' => [
          'title' => $this->t('Edit user account'),
        ],
      ],
      'logout' => [
        'title' => $this->t('Log out'),
        'url' => Url::fromRoute('user.logout'),
      ],
    ];
    $build = [
      '#theme' => 'links__toolbar_user',
      '#links' => $links,
      '#attributes' => [
        'class' => ['toolbar-menu'],
      ],
      '#cache' => [
        'contexts' => ['user'],
      ],
    ];

    if ($this->moduleHandler->moduleExists('language')) {
      $build['#pre_render'] = [
        [
          '\Drupal\language\ConfigurableLanguageManager',
          'switchToUserAdminLanguage',
        ],
      ];
      $build['#post_render'] = [
        ['\Drupal\language\ConfigurableLanguageManager', 'restoreLanguage'],
      ];
    }

    return $build;
  }

  /**
   * Lazy builder callback for rendering the username.
   *
   * @return array
   *   A renderable array as expected by the renderer service.
   */
  public function renderDisplayName() {
    return [
      '#markup' => $this->account->getDisplayName(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['renderToolbarLinks', 'renderDisplayName'];
  }

}
