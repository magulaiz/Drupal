<?php

namespace Drupal\fillpdf\Form;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Form controller for the FillPdfForm disable form.
 *
 * @internal
 */
class FillPdfFormDisableForm extends FillPdfFormConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $label = trim($this->getEntity()->label()) ?: $this->t('unnamed');
    return $this->t('Disable %label?', ['%label' => $label]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This will break PDF generation links, but will maintain already populated PDF files.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Disable');
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

    $status = $fillpdf_form->setUnpublished()->save();

    if ($status === SAVED_UPDATED) {
      $this->getLogger('fillpdf')->notice('Disabled FillPDF form %id.', [
        '%id' => $fillpdf_form->id(),
      ]);
      $this->messenger()->addStatus($this->t('FillPDF form has been disabled.'));

      return new RedirectResponse($fillpdf_form->toUrl()->toString());
    }
  }

}
