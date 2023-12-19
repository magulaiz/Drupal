<?php

namespace Drupal\user\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityConstraintViolationListInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the user password forms.
 *
 * Users followed the link in the email, now they can enter a new password.
 *
 * @internal
 */
class UserPasswordChangeForm extends ContentEntityForm {

  /**
   * The Password Hasher.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordHasher;

  /**
   * Constructs a UserPasswordForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Password $password_hasher
   *   The password hasher.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, PasswordInterface $password_hasher, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->passwordHasher = $password_hasher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('password'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

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
    $config = \Drupal::config('user.settings');
    $form['#cache']['tags'] = $config->getCacheTags();

    // Check for new account.
    $register = $account->isNew();

    // For a new account, there are 2 sub-cases:
    // $self_register: A user creates their own, new, account
    // (path '/user/register')
    // $admin_create: An administrator creates a new account for another user
    // (path '/admin/people/create')
    // If the current user is logged in and has permission to create users
    // then it must be the second case.
    $admin_create = $register && $account->access('create');

    // Display password field only for existing users or when user is allowed to
    // assign a password during registration.
    if (!$register) {
      $form['account']['pass'] = [
        '#type' => 'password_confirm',
        '#size' => 25,
        '#description' => $this->t('To change the current user password, enter the new password in both fields.'),
      ];

      // To skip the current password field, the user must have logged in via a
      // one-time link and have the token in the URL. Store this in $form_state
      // so it persists even on subsequent Ajax requests.
      $request = $this->getRequest();
      if (!$form_state->get('user_pass_reset') && ($token = $request->query->get('pass-reset-token'))) {
        $session_key = 'pass_reset_' . $account->id();
        $session_value = $request->getSession()->get($session_key);
        $user_pass_reset = isset($session_value) && hash_equals($session_value, $token);
        $form_state->set('user_pass_reset', $user_pass_reset);
      }

      // The user must enter their current password to change to a new one.
      if ($user->id() == $account->id()) {
        $form['account']['current_pass'] = [
          '#type' => 'password',
          '#title' => $this->t('Current password'),
          '#size' => 25,
          '#access' => !$form_state->get('user_pass_reset'),
          '#weight' => -5,
          // Do not let web browsers remember this password, since we are
          // trying to confirm that the person submitting the form actually
          // knows the current one.
          '#attributes' => ['autocomplete' => 'off'],
        ];
        $form_state->set('user', $account);

        // The user may only change their own password without their current
        // password if they logged in via a one-time login link.
        if (!$form_state->get('user_pass_reset')) {
          $form['account']['current_pass']['#description'] = $this->t('Required if you want to change the %pass below. <a href=":request_new_url" title="Send password reset instructions via email.">Reset your password</a>.', [
            '%pass' => $this->t('Password'),
            ':url' => Url::fromRoute('user.pass')->toString(),
          ]);
        }
      }
    }
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Change Password')];
    return $form;
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

    // Skip the protected user field constraint if the user came from the
    // password recovery page.
    $account->_skipProtectedUserFieldConstraint = $form_state->get('user_pass_reset');

    return $account;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check current pass only if not password reset.
    if (!$form_state->get('user_pass_reset')) {
      $current_pass = $form_state->getValue('current_pass');
      $user = $form_state->get('user');
      if (!$this->passwordHasher->check($current_pass, $user->getPassword())) {
        $form_state->setErrorByName('current_pass', t('Your current password is incorrect.'));
      }
    }
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function flagViolations(EntityConstraintViolationListInterface $violations, array $form, FormStateInterface $form_state) {
    // Manually flag violations of fields not handled by the form display. This
    // is necessary as entity form displays only flag violations for fields
    // contained in the display.
    $field_names = ['pass'];
    foreach ($violations->getByFields($field_names) as $violation) {
      list($field_name) = explode('.', $violation->getPropertyPath(), 2);
      $form_state->setErrorByName($field_name, $violation->getMessage());
    }
    parent::flagViolations($violations, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditedFieldNames(FormStateInterface $form_state) {
    return ['pass'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var Drupal\Core\Session\AccountInterface $user */
    $user = $form_state->getFormObject()->getEntity();
    // Set new password.
    $new_pass = $form_state->getValue('pass');
    // If there's a session set to the users id, remove the password reset tag
    // since a new password was saved.
    $this->getRequest()->getSession()->remove('pass_reset_' . $user->id());
    $user->setPassword($new_pass);
    $user->save();
    $this->messenger()->addStatus('Password changed successfully.');
  }

}
