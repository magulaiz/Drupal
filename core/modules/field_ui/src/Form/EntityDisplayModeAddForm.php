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
    if ($this->getRequest()->query->get('parent')) {
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
    /** @var \Drupal\Core\Routing\RouteBuilderInterface $router_builder */
    $router_builder = \Drupal::service('router.builder');

    // Rebuild the router before redirecting back to the display route to ensure
    // that the new display mode is displayed in the local tasks. This is
    // required because usually the router rebuild happens during kernel
    // termination which is after the response has been sent. This is too late
    // because the browser may have already redirected the user before this.
    // @see \Drupal\Core\Routing\RouteBuilder::destruct()
    $router_builder->rebuildIfNeeded();

    $command = new RedirectCommand(FieldUI::getDisplayRouteInfo(
      $this->getEntity()->toArray()['targetEntityType'],
      $this->getRequest()->query->get('parent'),
      $this->displayContext === 'view',
    )->toString());
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
