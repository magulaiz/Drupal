<?php

namespace Drupal\field_ui\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\field_ui\FieldUI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the add form for entity display modes.
 *
 * @internal
 */
class EntityDisplayModeAddForm extends EntityDisplayModeFormBase {

  use AjaxFormHelperTrait;

  /**
   * Constructs a new EntityDisplayModeAddForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle service.
   * @param \Drupal\Core\Entity\EntityDisplayRepository $entityDisplayRepository
   *   The entity display repository.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder
   *   The route builder.
   */
  public function __construct(protected EntityTypeBundleInfoInterface $entityTypeBundleInfo, protected EntityDisplayRepositoryInterface $entityDisplayRepository, protected RouteBuilderInterface $routeBuilder) {
    parent::__construct($entityTypeBundleInfo, $entityDisplayRepository);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_display.repository'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type_id = NULL) {
    $form = parent::buildForm($form, $form_state, $entity_type_id);
    // Add an ajax callback when the form is loaded from respective field UI's.
    if ($this->getRequest()->query->get('bundle')) {
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::ajaxSubmit',
      ];
    }
    // Change replace_pattern to avoid undesired dots.
    $form['id']['#machine_name']['replace_pattern'] = '[^a-z0-9_]+';
    $definition = $this->entityTypeManager->getDefinition($this->targetEntityTypeId);
    $form['#title'] = $this->t('Add new @entity-type %label', [
      '@entity-type' => $definition->getLabel(),
      '%label' => $this->entityType->getSingularLabel(),
    ]);
    $bundle = $this->getRequest()->query->get('bundle') ?: NULL;
    // Validate the bundle to avoid CSRF.
    if (in_array($bundle, array_keys($this->entityTypeBundleInfo->getBundleInfo($entity_type_id)))) {
      $form['bundles_by_entity']['#default_value'][] = $bundle ?? [];
    }
    $form['#prefix'] = '<div id="mode-add-form-wrapper">';
    $form['#suffix'] = '</div>';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state): AjaxResponse {
    // Rebuild the router before redirecting back to the display route to ensure
    // that the new display mode is displayed in the local tasks. This is
    // required because usually the router rebuild happens during kernel
    // termination which is after the response has been sent. This is too late
    // because the browser may have already redirected the user before this.
    // @see \Drupal\Core\Routing\RouteBuilder::destruct()
    $this->routeBuilder->rebuildIfNeeded();

    $command = new RedirectCommand(FieldUI::getDisplayRouteInfo(
      $this->getEntity()->toArray()['targetEntityType'],
      $this->getRequest()->query->get('bundle'),
      $this->displayContext === 'view',
    )->toString());
    $response = new AjaxResponse();
    return $response->addCommand($command);
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasAnyErrors()) {
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -1000,
      ];
      $form['#sorted'] = FALSE;
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('#mode-add-form-wrapper', $form));
    }
    else {
      $response = $this->successfulAjaxSubmit($form, $form_state);
    }
    return $response;
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
