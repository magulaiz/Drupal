<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel;

use Drupal\Core\Utility\UserEmailNotification;
use Drupal\Core\Test\AssertMailTrait;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests _user_mail_notify() use of user.settings.notify.*.
 *
 * @group user
 */
class UserMailNotifyTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'locale',
    'language',
  ];

  use AssertMailTrait {
    getMails as drupalGetMails;
  }

  /**
   * Data provider for user mail testing.
   *
   * @return array
   */
  public static function userMailsProvider() {
    return [
      'cancel confirm notification' => [
        UserEmailNotification::CancelConfirm->value,
        [UserEmailNotification::CancelConfirm->value],
      ],
      'password reset notification' => [
        UserEmailNotification::PasswordReset->value,
        [UserEmailNotification::PasswordReset->value],
      ],
      'status activated notification' => [
        UserEmailNotification::StatusActivated->value,
        [UserEmailNotification::StatusActivated->value],
      ],
      'status blocked notification' => [
        UserEmailNotification::StatusBlocked->value,
        [UserEmailNotification::StatusBlocked->value],
      ],
      'status canceled notification' => [
        UserEmailNotification::StatusCanceled->value,
        [UserEmailNotification::StatusCanceled->value],
      ],
      'register admin created notification' => [
        UserEmailNotification::RegisterAdminCreated->value,
        [UserEmailNotification::RegisterAdminCreated->value],
      ],
      'register no approval required notification' => [
        UserEmailNotification::RegisterNoApprovalRequired->value,
        [UserEmailNotification::RegisterNoApprovalRequired->value],
      ],
      'register pending approval notification' => [
        UserEmailNotification::RegisterPendingApproval->value,
        [UserEmailNotification::RegisterPendingApproval->value, UserEmailNotification::RegisterPendingApprovalAdmin->value],
      ],
    ];
  }

  /**
   * Tests mails are sent when notify.$op is TRUE.
   *
   * @param string $op
   *   The operation being performed on the account.
   * @param array $mail_keys
   *   The mail keys to test for.
   *
   * @dataProvider userMailsProvider
   */
  public function testUserMailsSent($op, array $mail_keys): void {
    $this->installConfig('user');
    $this->config('system.site')->set('mail', 'test@example.com')->save();
    $this->config('user.settings')->set('notify.' . $op, TRUE)->save();
    $return = _user_mail_notify($op, $this->createUser());
    $this->assertTrue($return);
    foreach ($mail_keys as $key) {
      $filter = ['key' => $key];
      $this->assertNotEmpty($this->getMails($filter));
    }
    $this->assertSameSize($mail_keys, $this->getMails());
  }

  /**
   * Tests mails are not sent when notify.$op is FALSE.
   *
   * @param string $op
   *   The operation being performed on the account.
   *
   * @dataProvider userMailsProvider
   */
  public function testUserMailsNotSent($op): void {
    $this->installConfig('user');
    $this->config('user.settings')->set('notify.' . $op, FALSE)->save();
    $return = _user_mail_notify($op, $this->createUser());
    $this->assertNull($return);
    $this->assertEmpty($this->getMails());
  }

  /**
   * Tests recovery email content and token langcode is aligned.
   */
  public function testUserRecoveryMailLanguage(): void {

    // Install locale schema.
    $this->installSchema('locale', [
      'locales_source',
      'locales_target',
      'locales_location',
    ]);

    // Add new language for translation purpose.
    ConfigurableLanguage::createFromLangcode('zh-hant')->save();
    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Install configs.
    $this->installConfig(['language', 'locale', 'user']);

    locale_system_set_config_langcodes();
    $langcodes = array_keys(\Drupal::languageManager()->getLanguages());
    $locale_config_manager = \Drupal::service('locale.config_manager');
    $names = $locale_config_manager->getComponentNames();
    $locale_config_manager->updateConfigTranslations($names, $langcodes);

    $this->config('user.settings')->set('notify.' . UserEmailNotification::StatusCanceled->value, TRUE)->save();

    // Set language prefix.
    $config = $this->config('language.negotiation');
    $config->set('url.prefixes', ['en' => 'en', 'zh-hant' => 'zh', 'fr' => 'fr'])->save();

    // Reset services to apply change.
    \Drupal::service('kernel')->rebuildContainer();

    // Update zh-hant password_reset config with custom translation.
    $configLanguageOverride = $this->container->get('language_manager')->getLanguageConfigOverride('zh-hant', 'user.mail');
    $configLanguageOverride->set(UserEmailNotification::PasswordReset->value . '.subject', 'hant subject [user:display-name]')->save();
    $configLanguageOverride->set(UserEmailNotification::PasswordReset->value . '.body', 'hant body [user:display-name] and token link [user:one-time-login-url]')->save();

    // Update fr password_reset config with custom translation.
    $configLanguageOverride = $this->container->get('language_manager')->getLanguageConfigOverride('fr', 'user.mail');
    $configLanguageOverride->set(UserEmailNotification::PasswordReset->value . '.subject', 'fr subject [user:display-name]')->save();
    $configLanguageOverride->set(UserEmailNotification::PasswordReset->value . '.body', 'fr body [user:display-name] and token link [user:one-time-login-url]')->save();

    // Current language is 'en'.
    $currentLanguage = $this->container->get('language_manager')->getCurrentLanguage()->getId();
    $this->assertSame('en', $currentLanguage);

    // Set preferred_langcode to 'zh-hant'.
    $user = $this->createUser();
    $user->set('preferred_langcode', 'zh-hant')->save();
    $preferredLangcode = $user->getPreferredLangcode();
    $this->assertSame('zh-hant', $preferredLangcode);

    // Recovery email should respect user preferred langcode by default if
    // langcode not set.
    $this->config('system.site')->set('mail', 'test@example.com')->save();
    $params['account'] = $user;
    $default_email = \Drupal::service('plugin.manager.mail')->mail('user', UserEmailNotification::PasswordReset->value, $user->getEmail(), $preferredLangcode, $params);
    $this->assertTrue($default_email['result']);

    // Assert for zh.
    $this->assertMailString('subject', 'hant subject', 1);
    $this->assertMailString('body', 'hant body', 1);
    $this->assertMailString('body', 'zh/user/reset', 1);

    // Recovery email should be fr when langcode specified.
    $french_email = \Drupal::service('plugin.manager.mail')->mail('user', UserEmailNotification::PasswordReset->value, $user->getEmail(), 'fr', $params);
    $this->assertTrue($french_email['result']);

    // Assert for fr.
    $this->assertMailString('subject', 'fr subject', 1);
    $this->assertMailString('body', 'fr body', 1);
    $this->assertMailString('body', 'fr/user/reset', 1);

  }

}
