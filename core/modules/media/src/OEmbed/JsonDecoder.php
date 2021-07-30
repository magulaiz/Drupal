<?php

namespace Drupal\media\OEmbed;

use Drupal\Component\Serialization\Json;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * Defines a class to parse oEmbed resource data in JSON format.
 *
 * @internal
 *   This class is an internal part of the oEmbed system and should only be
 *   instantiated by Drupal\media\OEmbed\ResourceFetcher.
 */
class JsonDecoder implements DecoderInterface {

  /**
   * {@inheritdoc}
   */
  public function decode($data, $format, array $context = []) {
    $data = Json::decode($data);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new ResourceException('Error decoding oEmbed resource: ' . json_last_error_msg(), $context['url']);
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {
    return strstr($format, 'application/json') || strstr($format, 'text/javascript');
  }

}
