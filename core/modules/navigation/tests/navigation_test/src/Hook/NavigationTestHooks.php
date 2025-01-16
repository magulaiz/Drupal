<?php

declare(strict_types=1);

namespace Drupal\navigation_test\Hook;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\State\StateInterface;

/**
 * Hooks implementations for navigation_test module.
 */
class NavigationTestHooks {

  /**
   * NavigationTestHooks constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(
    protected StateInterface $state,
  ) {
  }

  /**
   * Implements hook_block_alter().
   */
  #[Hook('block_alter')]
  public function blockAlter(&$definitions): void {
    if ($this->state->get('navigation_safe_alter')) {
      $definitions['navigation_link']['allow_in_navigation'] = TRUE;
      $definitions['navigation_shortcuts']['allow_in_navigation'] = FALSE;
    }
  }

  /**
   * Implements hook_navigation_content_top().
   */
  #[Hook('navigation_content_top')]
  public function navigationContentTop(): array {
    return $this->generateContentTopItems('content_top');
  }

  /**
   * Implements hook_navigation_content_top_alter().
   */
  #[Hook('navigation_content_top_alter')]
  public function navigationContentTopAlter(&$content_top): void {
    if (\Drupal::keyValue('navigation_test')->get('content_top_alter')) {
      $this->generateContentTopItemsAlter($content_top);
    }
  }

  /**
   * Implements hook_navigation_content_footer_top().
   */
  #[Hook('navigation_content_footer_top')]
  public function navigationContentFooterTop(): array {
    return $this->generateContentTopItems('content_footer_top');
  }

  /**
   * Implements hook_navigation_content_footer_top_alter().
   */
  #[Hook('navigation_content_footer_top_alter')]
  public function navigationContentFooterTopAlter(array &$content_footer_top): void {
    if (\Drupal::keyValue('navigation_test')->get('content_footer_top_alter')) {
      $this->generateContentTopItemsAlter($content_footer_top);
    }
  }

  /**
   * Generate content for the content_top section.
   *
   * @param string $hookKey
   *   The key to check if the content should be generated.
   *
   * @return array
   *   An associative array of renderable elements.
   */
  public function generateContentTopItems(string $hookKey): array  {
    if (\Drupal::keyValue('navigation_test')->get($hookKey)) {
      $items = [
        'navigation_foo' => [
          '#markup' => 'foo',
        ],
        'navigation_bar' => [
          '#markup' => 'bar',
        ],
        'navigation_baz' => [
          '#markup' => 'baz',
        ],
      ];
    }
    else {
      $items = [
        'navigation_foo' => [],
        'navigation_bar' => [],
        'navigation_baz' => [],
      ];
    }
    // Add cache tags to our items to express a made up dependency to test
    // cacheability. Note that as we're always returning the same items,
    // sometimes only with cacheability metadata. By doing this we're testing
    // conditional rendering of content_top items.
    foreach ($items as &$element) {
      CacheableMetadata::createFromRenderArray($element)
        ->addCacheTags(['navigation_test'])
        ->applyTo($element);
    }
    return $items;
  }

  /**
   * Alter content_top items.
   *
   * @param array $content_footer_top
   *   An associative array of content to modify.
   */
  public function generateContentTopItemsAlter(array &$content_footer_top): void {
    // Remove a specific element.
    unset($content_footer_top['navigation_foo']);
    // Modify an element.
    $content_footer_top['navigation_bar']['#markup'] = 'new bar';
    // Change weight.
    $content_footer_top['navigation_baz']['#weight'] = '-100';
  }

}
