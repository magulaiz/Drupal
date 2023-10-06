<?php

namespace Drupal\ban\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to delete an IP.
 */

class WhitelistedIPDeleteForm extends EntityConfirmFormBase {

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        return $this->t('Are you sure you want to delete %ip?', ['%ip' => $this->entity->getWhitelistedIp()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        return new Url('entity.ban_whitelisted_ip.collection');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText() {
        return $this->t('Delete');
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->entity->delete();
        $this->messenger()->addMessage($this->t('Entity %ip has been deleted.', ['%ip' => $this->entity->getWhitelistedIp()]));

        $form_state->setRedirectUrl($this->getCancelUrl());
    }

}
