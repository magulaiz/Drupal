<?php

namespace Drupal\ban\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the IP add and edit forms.
 */
class WhitelistedIPForm extends EntityForm
{

  /**
   * Constructs an Whitelisted IP object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager)
  {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state)
  {
    $form = parent::form($form, $form_state);

    $ip = $this->entity;

    $form['whitelistedIp'] = [
      '#type' => 'textfield',
      '#default_value' => $ip->getWhitelistedIp(),
      '#required' => TRUE,
    ];

    // You will need additional form elements for your custom properties.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $ip = $form_state->getValue('whitelistedIp');
    if ($this->exist($ip)) {
      $form_state->setErrorByName('whitelistedIp', $this->t('The IP is already whitelisted.'));
    }
    elseif (!filter_var($ip, FILTER_VALIDATE_IP)){
      $form_state->setErrorByName('whitelistedIp', $this->t('Not a valid IP.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state)
  {
    $ip = $this->entity;
    $ip->setId($ip->getWhitelistedIp());
    $status = $ip->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The IP %ip was whitelisted.', [
        '%ip' => $ip->getWhitelistedIp(),
      ]));
    } else {
      $this->messenger()->addMessage($this->t('The IP %ip was updated.', [
        '%ip' => $ip->getWhitelistedIp(),
      ]));
    }

    $form_state->setRedirect('entity.ban_whitelisted_ip.collection');
  }

  /**
   * Helper function to check whether an Whitelisted IP configuration entity exists.
   */
  public function exist($ip)
  {
    $entity = $this->entityTypeManager->getStorage('ban_whitelisted_ip')->getQuery()
      ->condition('whitelistedIp', $ip)
      ->execute();
    return (bool)$entity;
  }

}
