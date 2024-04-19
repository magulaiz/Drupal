<?php

namespace Drupal\navigation\Controller;

use Drupal\block\Controller\BlockLibraryController;

/**
 * Provides a list of navigation block plugins to be added to the layout.
 */
class NavigationBlockLibraryController extends BlockLibraryController {

  /**
   * {@inheritdoc}
   */
  protected function getFilteredBlockDefinitions(string $theme, float|bool|int|string|null $region): array {
    $definitions = $this->blockManager->getFilteredDefinitions('block_ui', $this->contextRepository->getAvailableContexts(), [
      'supports_navigation' => TRUE,
      'region' => $region,
    ]);
    // Order by category, and then by admin label.
    $definitions = $this->blockManager->getSortedDefinitions($definitions);
    // Filter out definitions that are not intended to be placed by the UI.
    $definitions = array_filter($definitions, function (array $definition) {
      return !empty($definition['supports_navigation']) || empty($definition['_block_ui_hidden']);
    });
    return $definitions;
  }

}
