<?php

namespace Drupal\navigation_test\Plugin\NavigationBlock;

use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;
use Drupal\user\UserInterface;

/**
 * Provides a context-aware navigation block.
 */
#[NavigationBlock(
  id: "test_context_aware",
  admin_label: new TranslatableMarkup("Test context-aware navigation block"),
  context_definitions: [
    'user' => new EntityContextDefinition(
      data_type: 'entity:user',
      label: new TranslatableMarkup("User Context"),
      required: FALSE,
      constraints: [
        "NotNull" => [],
      ]
    ),
  ]
)]
class TestContextAwareNavigationBlock extends NavigationBlockPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->getContextValue('user');
    return [
      '#prefix' => '<div id="' . $this->getPluginId() . '--username">',
      '#suffix' => '</div>',
      '#markup' => $user ? $user->getAccountName() : 'No context mapping selected.',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->getContextValue('user') instanceof UserInterface) {
      $this->messenger()->addStatus('User context found.');
    }

    return parent::blockAccess($account);
  }

}
