<?php

namespace Drupal\field_ui\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the add form for entity display modes.
 *
 * @internal
 */
class EntityDisplayModeAddForm extends EntityDisplayModeFormBase {

  use AjaxFormHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL) {
    $form = parent::buildForm($form, $form_state, $entity_type_id);

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::ajaxSubmit',
    ];
    // Change replace_pattern to avoid undesired dots.
    $form['id']['#machine_name']['replace_pattern'] = '[^a-z0-9_]+';
    $definition = $this->entityTypeManager->getDefinition($this->targetEntityTypeId);
    $form['#title'] = $this->t('Add new @entity-type %label', ['@entity-type' => $definition->getLabel(), '%label' => $this->entityType->getSingularLabel()]);
    $form['#prefix'] = '<div id="display-mode-add">';
    $form['#suffix'] = '</div>';
    return $form;
  }

  /**
   * Submit form #ajax callback.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages or represents a
   *   successful submission.
   *
   * @see \Drupal\Core\Ajax\AjaxFormHelperTrait
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state): AjaxResponse {
    if ($form_state->hasAnyErrors()) {
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -1000,
      ];
      $form['#sorted'] = FALSE;
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('#display-mode-add', $form));
    }
    else {
      $response = $this->successfulAjaxSubmit($form, $form_state);
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {

    if ($redirect_url = $this->getRedirectUrl()) {
      $command = new RedirectCommand($redirect_url->setAbsolute()->toString());
    }
    else {
      // Display mode add always provides a destination.
      throw new \Exception("No destination provided by Display mode add form");
    }
    $response = new AjaxResponse();
    return $response->addCommand($command);
  }

  /**
   * Gets the form's redirect URL from 'destination' provide in the request.
   *
   * @return \Drupal\Core\Url|null
   *   The redirect URL or NULL if dialog should just be closed.
   */
  protected function getRedirectUrl() {
    // \Drupal\Core\Routing\RedirectDestination::get() cannot be used directly
    // because it will use <current> if 'destination' is not in the query
    // string.
    if ($this->getRequest()->query->has('destination') && $destination = $this->getRedirectDestination()->get()) {
      return Url::fromUri('base:' . $destination);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $form_state->setValueForElement($form['id'], $this->targetEntityTypeId . '.' . $form_state->getValue('id'));
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    $definition = $this->entityTypeManager->getDefinition($this->targetEntityTypeId);
    if (!$definition->get('field_ui_base_route') || !$definition->hasViewBuilderClass()) {
      throw new NotFoundHttpException();
    }

    $this->entity->setTargetType($this->targetEntityTypeId);
  }

}
