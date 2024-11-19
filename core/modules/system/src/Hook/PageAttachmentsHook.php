<?php

namespace Drupal\system\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BareHtmlPageRendererInterface;

/**
 * Implements hook_page_attachments() for the system module.
 */
class PageAttachmentsHook {

  /**
   * Constructs a new PageAttachmentsHook.
   *
   * @param \Drupal\Core\Render\BareHtmlPageRendererInterface $bareHtmlPageRenderer
   *   The renderer service.
   */
  public function __construct(
    private readonly BareHtmlPageRendererInterface $bareHtmlPageRenderer,
  ) {}

  /**
   * Implements hook_page_attachments().
   *
   * @see template_preprocess_maintenance_page()
   * @see \Drupal\Core\EventSubscriber\ActiveLinkResponseFilter
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$page): void {
    $this->bareHtmlPageRenderer->systemPageAttachments($page);
  }

}
