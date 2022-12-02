<?php

namespace Drupal\fillpdf\Form;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form controller for the FillPdfForm enable form.
 *
 * @internal
 */
class FillPdfFormEnableForm extends FillPdfFormConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $label = trim($this->getEntity()->label()) ?: $this->t('unnamed');
    return $this->t('Enable %label?', ['%label' => $label]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Enable');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->getEntity()->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fillpdf_form = $this->getEntity();

    $status = $fillpdf_form->setPublished()->save();

    if ($status === SAVED_UPDATED) {
      $this->getLogger('fillpdf')->notice('Enabled FillPDF form %id.', [
        '%id' => $fillpdf_form->id(),
      ]);
      $this->messenger()->addStatus($this->t('FillPDF form has been enabled.'));

      return new RedirectResponse($fillpdf_form->toUrl()->toString());
    }
  }

}
