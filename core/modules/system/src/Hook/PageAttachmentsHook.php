<?php

namespace Drupal\system\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BareHtmlPageRenderer;

/**
 * Implements hook_page_attachments() for the system module.
 */
class PageAttachmentHooks {

  /**
   * Constructs a new PageAttachmentsHook.
   *
   * @param \Drupal\Core\Render\BareHtmlPageRenderer $bareHtmlPageRenderer
   *   The renderer service.
   */
  public function __construct(
    private readonly BareHtmlPageRenderer $bareHtmlPageRenderer,
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
