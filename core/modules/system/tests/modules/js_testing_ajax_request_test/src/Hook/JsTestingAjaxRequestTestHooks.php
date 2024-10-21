<?php

namespace Drupal\js_testing_ajax_request_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class JsTestingAjaxRequestTestHooks
{
    /**
     * Implements hook_page_attachments().
     */
    #[Hook('page_attachments')]
    public function pageAttachments(array &$attachments)
    {
        // Unconditionally attach an asset to the page.
        $attachments['#attached']['library'][] = 'js_testing_ajax_request_test/track_ajax_requests';
    }
}
