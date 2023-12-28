<?php

namespace Drupal\user\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the user email forms.
 */
class EmailChangeForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   *
   * Removing base form id to avoid unapplicable hooks from contrib modules.
   */
  public function getBaseFormId() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\user\UserInterface $account */
    $account = $this->entity;
    $user = $this->currentUser();
    $config = $this->config('user.settings');
    $form['#cache']['tags'] = $config->getCacheTags();

    // Check for new account.
    $register = $account->isNew();

    // Account information.
    $form['account'] = [
      '#type'   => 'container',
      '#weight' => -10,
    ];
    if ($user->id() == $account->id()) {
      $form['account']['current_pass'] = [
        '#type' => 'password',
        '#title' => $this->t('Current password'),
        '#size' => 25,
        '#access' => !$form_state->get('user_pass_reset'),
        '#attributes' => ['autocomplete' => 'off'],
        '#required' => TRUE,
      ];
      $form_state->set('user', $account);
    }

    // The mail field is NOT required if account originally had no mail set
    // and the user performing the edit has 'administer users' permission.
    // This allows users without email address to be edited and deleted.
    // Also see \Drupal\user\Plugin\Validation\Constraint\UserMailRequired.
    $form['account']['mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#description' => $this->t('A valid email address. All emails from the system will be sent to this address. The email address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by email.'),
      '#required' => !(!$account->getEmail() && $user->hasPermission('administer users')),
      '#default_value' => (!$register ? $account->getEmail() : ''),
      '#access' => $account->mail->access('edit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::validateForm($form, $form_state);
    $new_email = $form_state->getValue('mail');
    $current_email = $form_state->getFormObject()->entity->getEmail();
    if ($new_email == $current_email) {
      $form_state->setErrorByName('mail', t("This email address is already associated with your account."));
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\user\UserInterface $account */
    $account = parent::buildEntity($form, $form_state);

    // Set existing password if set in the form state.
    $current_pass = trim($form_state->getValue('current_pass', ''));
    if (strlen($current_pass) > 0) {
      $account->setExistingPassword($current_pass);
    }

    return $account;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditedFieldNames(FormStateInterface $form_state) {
    return ['mail'];
  }

  /**
   * {@inheritdoc}
   */
  protected function flagViolations(EntityConstraintViolationListInterface $violations, array $form, FormStateInterface $form_state) {
    // Manually flag violations of fields not handled by the form display. This
    // is necessary as entity form displays only flag violations for fields
    // contained in the display.
    $field_names = ['mail'];
    foreach ($violations->getByFields($field_names) as $violation) {
      list($field_name) = explode('.', $violation->getPropertyPath(), 2);
      $form_state->setErrorByName($field_name, $violation->getMessage());
    }
    parent::flagViolations($violations, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->messenger()->addStatus('Email changed successfully.');
  }

}
