<?php

namespace Drupal\action_bulk_test\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * @Action(
 *   id = "test_action",
 *   label = @Translation("Test action"),
 *   type = "node",
 *   confirm_form_route_name = "action_bulk_test.action.confirm",
 * )
 */
class TestAction extends ActionBase {

  /**
   * @inheritdoc
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $return_as_object ? AccessResult::allowed() : TRUE;
  }

  /**
   * @inheritdoc
   */
  public function execute() {}

}
