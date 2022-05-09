<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Controller\UserController;
use Drupal\user\Form\UserCancelForm;
use Drupal\user\Form\UserMultipleCancelConfirm;
use Drupal\user\UserInterface;

/**
 * Tests deprecation of procedural custom user account cancelling method.
 *
 * @group user
 * @group legacy
 */
class UserDeprecatedCancelCustomMethodTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'user_cancel_deprecated_test',
    // In these modules, the hook has been previously implemented.
    'comment',
    'history',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
  }

  /**
   * Tests hook_user_cancel() hook deprecation.
   */
  public function testHookDeprecation(): void {
    $account = $this->createUser();
    $this->expectDeprecation('The deprecated hook hook_user_cancel() is implemented in these functions: user_cancel_deprecated_test_user_cancel(). The hook is deprecated in drupal:9.5.0 and is removed from drupal:10.0.0. In order to act on user account cancellation provide an event subscriber that listens to the \Drupal\user\Event\AccountCancelEvent event. The event subscriber can be defined with a priority higher than the core subscribers in order to cancel them by using AccountCancelEvent::stopPropagation(). See https://www.drupal.org/node/3279455');
    $this->container->get('user.account_cancellation')->cancel($account->id(), 'user_cancel_test_deprecated');
  }

  /**
   * Tests deprecation of procedural code.
   *
   * @covers \user_cancel
   * @covers \_user_cancel
   * @covers \_user_cancel_session_regenerate
   */
  public function testProceduralCodeDeprecations(): void {
    $this->expectDeprecation("user_cancel is deprecated in drupal:9.5.0 and is removed from drupal:10.0.0. Use the method ::cancel() from the 'user.account_cancellation' service instead. See https://www.drupal.org/node/3279455");
    user_cancel([], 123, 'abc');
    $this->expectDeprecation("_user_cancel is deprecated in drupal:9.5.0 and is removed from drupal:10.0.0. Use the method ::doCancelAccount() from the 'user.account_cancellation' service instead. See https://www.drupal.org/node/3279455");
    _user_cancel([], $this->prophesize(UserInterface::class)->reveal(), 'abc');
    $this->expectDeprecation("_user_cancel_session_regenerate is deprecated in drupal:9.5.0 and is removed from drupal:10.0.0. Use the method ::regenerateSession() from the 'user.account_cancellation' service instead. See https://www.drupal.org/node/3279455");
    _user_cancel_session_regenerate();
  }

  /**
   * Tests constructor parameter additions deprecation messages.
   *
   * @covers \Drupal\user\Controller\UserController::__construct
   * @covers \Drupal\user\Form\UserCancelForm::__construct
   * @covers \Drupal\user\Form\UserMultipleCancelConfirm::__construct
   */
  public function testConstructorParamAdditionsDeprecationMessages(): void {
    $this->expectDeprecation('Calling Drupal\user\Controller\UserController::__construct() without the $account_cancellation argument is deprecated in drupal:9.5.0 and it will be required in drupal:10.0.0. See https://www.drupal.org/node/3279455');
    new UserController(
      $this->container->get('date.formatter'),
      $this->container->get('entity_type.manager')->getStorage('user'),
      $this->container->get('user.data'),
      $this->container->get('logger.factory')->get('user'),
      $this->container->get('flood')
    );

    $this->expectDeprecation('Calling Drupal\user\Form\UserCancelForm::__construct() without the $account_cancellation argument is deprecated in drupal:9.5.0 and it will be required in drupal:10.0.0. See https://www.drupal.org/node/3279455');
    new UserCancelForm(
      $this->container->get('entity.repository'),
      $this->container->get('entity_type.bundle.info'),
      $this->container->get('datetime.time')
    );

    $this->expectDeprecation('Calling Drupal\user\Form\UserMultipleCancelConfirm::__construct() without the $account_cancellation argument is deprecated in drupal:9.5.0 and it will be required in drupal:10.0.0. See https://www.drupal.org/node/3279455');
    new UserMultipleCancelConfirm(
      $this->container->get('tempstore.private'),
      $this->container->get('entity_type.manager')->getStorage('user'),
      $this->container->get('entity_type.manager')
    );
  }

}
