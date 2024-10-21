<?php

namespace Drupal\media_test_oembed\Hook;

use Drupal\media\OEmbed\Provider;
use Drupal\Core\Hook\Attribute\Hook;
class MediaTestOembedHooks
{
    /**
     * Implements hook_oembed_resource_url_alter().
     */
    #[Hook('oembed_resource_url_alter')]
    public function oembedResourceUrlAlter(array &$parsed_url, \Drupal\media\OEmbed\Provider $provider)
    {
        if ($provider->getName() === 'Vimeo') {
            $parsed_url['query']['altered'] = 1;
        }
    }
}
