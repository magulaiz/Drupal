<?php

namespace Drupal\router_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\Routing\Annotation\Route;

class TestAttributes extends ControllerBase {

  #[Route('/test_method_attribute', requirements: ['_access' => 'TRUE'])]
  public function attributeMethod() {
    return ['#markup' => 'Testing method with a Route attribute'];
  }

}
