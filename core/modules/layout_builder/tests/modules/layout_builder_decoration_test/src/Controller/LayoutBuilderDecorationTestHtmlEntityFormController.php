<?php

namespace Drupal\layout_builder_decoration_test\Controller;

use Drupal\Core\Controller\FormController;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Overrides the entity form controller service for layout builder decoration test.
 */
class LayoutBuilderDecorationTestHtmlEntityFormController extends FormController {

  /**
   * Constructs a LayoutBuilderDecorationTestHtmlEntityFormController object.
   *
   * @param \Drupal\Core\Controller\FormController $entityFormController
   *   The entity form controller being decorated.
   */
  public function __construct(protected FormController $entityFormController)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function getContentResult(Request $request, RouteMatchInterface $route_match) {
    return $this->entityFormController->getContentResult($request, $route_match);
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormArgument(RouteMatchInterface $route_match) {
    return $this->entityFormController->getFormArgument($route_match);
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormObject(RouteMatchInterface $route_match, $form_arg) {
    return $this->entityFormController->getFormObject($route_match, $form_arg);
  }

}
