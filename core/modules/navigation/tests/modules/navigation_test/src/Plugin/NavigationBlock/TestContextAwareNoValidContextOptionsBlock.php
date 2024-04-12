<?php

namespace Drupal\navigation_test\Plugin\NavigationBlock;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;

/**
 * Provides a context-aware nav block with a not-passed, non-required context.
 */
#[NavigationBlock(
  id: "test_context_aware_no_valid_context_options",
  admin_label: new TranslatableMarkup("Test context-aware block - no valid context options"),
  context_definitions: [
    'user' => new ContextDefinition(data_type: 'email', required: FALSE),
  ]
)]
class TestContextAwareNoValidContextOptionsBlock extends NavigationBlockPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => 'Rendered block with no valid context options',
    ];
  }

}
