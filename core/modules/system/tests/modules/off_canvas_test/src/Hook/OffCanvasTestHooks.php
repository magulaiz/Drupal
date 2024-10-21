<?php

namespace Drupal\off_canvas_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class OffCanvasTestHooks
{
    /**
     * Implements hook_page_attachments().
     */
    #[Hook('page_attachments')]
    public function pageAttachments(array &$attachments)
    {
        // This library wraps around the Drupal.offCanvas.resetSize() method and adds
        // a special data-resize-done attribute to help functional JavaScript tests
        // use the off-canvas area when it is fully loaded and ready to be interacted
        // with.
        // @see \Drupal\Tests\system\Traits\OffCanvasTestTrait::waitForOffCanvasArea()
        $attachments['#attached']['library'][] = 'off_canvas_test/resize_helper';
    }
}
