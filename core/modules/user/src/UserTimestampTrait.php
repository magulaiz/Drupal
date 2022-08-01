<?php

namespace Drupal\user;

use Drupal\Core\TypedData\ComputedItemListTrait;

trait UserTimestampTrait {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue(): void {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->getEntity();
    if (!$account->isAnonymous()) {
      $key_value = \Drupal::keyValue("user.timestamp.{$this->getName()}");
      $this->list[0] = $this->createItem(0, $key_value->get($account->id(), 0));
    }
  }

}
