<?php

namespace Drupal\ban\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\ban\BanIpManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Provides a form to unban IP addresses.
 *
 * @internal
 */
class BanDeleteMultiple extends ConfirmFormBase {

  /**
   * The banned IP addresses.
   *
   * @var array
   */
  protected $banIps;

  /**
   * The IP manager.
   *
   * @var \Drupal\ban\BanIpManagerInterface
   */
  protected $ipManager;

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructs a new BanDelete object.
   *
   * @param \Drupal\ban\BanIpManagerInterface $ip_manager
   *   The IP manager.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   */
  public function __construct(BanIpManagerInterface $ip_manager, PrivateTempStoreFactory $temp_store_factory) {
    $this->ipManager = $ip_manager;
    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ban.ip_manager'),
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ban_ip_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to unblock %ips_amount IP addresses?', ['%ips_amount' => count($this->banIps)]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Unblock');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('ban.admin_page');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $ban_id
   *   The IP address record ID to unban.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $ban_id = '') {
    $this->banIps = $this->tempStoreFactory->get('ban_ip_delete_multiple')->get('selected_ips');
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($this->banIps as $ip) {
      $this->ipManager->unbanIp($ip);
    }
    $this->tempStoreFactory->get('ban_ip_delete_multiple')->delete('selected_ips');
    $this->logger('user')->notice('Deleted %ips_amount IP addresses.', ['%ips_amount' => count($this->banIps)]);
    $this->messenger()->addStatus($this->t('The selected IP addresses were unblocked.'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
