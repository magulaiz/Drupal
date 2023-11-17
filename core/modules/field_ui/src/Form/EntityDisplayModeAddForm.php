<?php

namespace Drupal\field_ui\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_ui\FieldUI;
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
    // Add an ajax callback when the form is loaded from respective field UI's.
    if (\Drupal::request()->query->get('parent')) {
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::ajaxSubmit',
      ];
    }
    // Change replace_pattern to avoid undesired dots.
    $form['id']['#machine_name']['replace_pattern'] = '[^a-z0-9_]+';
    $definition = $this->entityTypeManager->getDefinition($this->targetEntityTypeId);
    $form['#title'] = $this->t('Add new @entity-type %label', ['@entity-type' => $definition->getLabel(), '%label' => $this->entityType->getSingularLabel()]);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $isViewMode = FALSE;
    if (str_contains($form_state->getBuildInfo()['form_id'], 'view')) {
      $isViewMode = TRUE;
    }
    $command = new RedirectCommand(FieldUI::getDisplayRouteInfo($this->getEntity()->toArray()['targetEntityType'], \Drupal::request()->query->get('parent'), $isViewMode)->toString());
    $response = new AjaxResponse();
    return $response->addCommand($command);
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
