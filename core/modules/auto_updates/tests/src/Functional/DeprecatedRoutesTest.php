<?php

declare(strict_types = 1);

namespace Drupal\Tests\auto_updates\Functional;

use Drupal\Core\Url;

/**
 * @covers \Drupal\auto_updates\Controller\UpdateController::redirectDeprecatedRoute
 * @covers \Drupal\auto_updates\Routing\RouteSubscriber
 * @group auto_updates
 * @group legacy
 * @internal
 */
class DeprecatedRoutesTest extends AutoUpdatesFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['auto_updates'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that deprecated routes are redirected with an informative message.
   */
  public function testDeprecatedRoutesAreRedirected(): void {
    $account = $this->createUser([
      'administer software updates',
      'administer site configuration',
    ]);
    $this->drupalLogin($account);

    $routes = [
      'auto_updates.module_update' => ['update.module_update', NULL],
      'auto_updates.report_update' => ['update.report_update', NULL],
      'auto_updates.theme_update' => ['update.theme_update', NULL],
      'auto_updates.update_readiness' => ['auto_updates.status_check', 'system.status'],
    ];
    $assert_session = $this->assertSession();

    foreach ($routes as $deprecated_route => [$redirect_route, $final_route]) {
      $deprecated_url = Url::fromRoute($deprecated_route)
        ->setAbsolute()
        ->toString();
      $redirect_url = Url::fromRoute($redirect_route)
        ->setAbsolute()
        ->toString();
      if ($final_route) {
        $final_url = Url::fromRoute($final_route)
          ->setAbsolute()
          ->toString();
      }

      $this->drupalGet($deprecated_url);
      $assert_session->statusCodeEquals(200);
      $assert_session->addressEquals($final_url ?? $redirect_url);
      $assert_session->responseContains("This page was accessed from $deprecated_url, which is deprecated and will not work in the next major version of Automatic Updates. Please use <a href=\"$redirect_url\">$redirect_url</a> instead.");
    }
  }

}
