<?php

namespace Drupal\router_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Test controller.
 */
#[Route('/test_class_attribute', requirements: ['_access' => 'TRUE'])]
class TestClassAttribute extends ControllerBase {

  /**
   * Provides test content.
   */
  public function __invoke() {
    return ['#markup' => 'Testing __invoke() with a Route attribute on the class'];
  }

}
