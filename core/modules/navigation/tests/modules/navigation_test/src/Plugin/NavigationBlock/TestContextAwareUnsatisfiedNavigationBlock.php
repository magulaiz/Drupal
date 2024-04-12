<?php

namespace Drupal\navigation_test\Plugin\NavigationBlock;

use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;

/**
 * Provides a context-aware navigation block.
 */
#[NavigationBlock(
  id: "test_context_aware_unsatisfied",
  admin_label: new TranslatableMarkup("Test context-aware unsatisfied navigation block"),
  context_definitions: [
    'user' => new EntityContextDefinition('entity:foobar'),
  ]
)]
class TestContextAwareUnsatisfiedNavigationBlock extends NavigationBlockPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => 'test',
    ];
  }

}
