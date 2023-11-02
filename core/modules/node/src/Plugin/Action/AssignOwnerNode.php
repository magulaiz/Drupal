<?php

namespace Drupal\node\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\Entity\User;

/**
 * Assigns ownership of a node to a user.
 */
#[Action(
  id: 'node_assign_owner_action',
  label: new TranslatableMarkup('Change the author of content'),
  type: 'node'
)]
class AssignOwnerNode extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\node\NodeInterface $entity */
    $entity->setOwnerId($this->configuration['owner_uid'])->save();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'owner_uid' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['owner_uid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Username'),
      '#target_type' => 'user',
      '#selection_settings' => ['include_anonymous' => FALSE],
      '#default_value' => $this->configuration['owner_uid'] ? User::load($this->configuration['owner_uid']) : NULL,
      '#size' => '6',
      '#maxlength' => '60',
      '#description' => $this->t('The username of the user to which you would like to assign ownership.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['owner_uid'] = $form_state->getValue('owner_uid');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->getOwner()->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
