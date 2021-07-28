<?php

namespace Drupal\media\OEmbed;

use Drupal\Component\Serialization\Json;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * Defines a class to parse oEmbed resource data in XML format.
 *
 * @internal
 *   This class is an internal part of the oEmbed system and should only be
 *   instantiated by Drupal\media\OEmbed\ResourceFetcher.
 */
class XmlDecoder implements DecoderInterface {

  /**
   * {@inheritdoc}
   */
  public function decode($data, $format, array $context = []) {
    // Enable userspace error handling.
    $was_using_internal_errors = libxml_use_internal_errors(TRUE);
    libxml_clear_errors();

    $content = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
    // Restore the previous error handling behavior.
    libxml_use_internal_errors($was_using_internal_errors);

    $error = libxml_get_last_error();
    if ($error) {
      libxml_clear_errors();
      throw new ResourceException($error->message, $context['url']);
    }
    elseif ($content === FALSE) {
      throw new ResourceException('The fetched resource could not be parsed.', $context['url']);
    }

    // Convert XML to JSON so that the parsed resource has a consistent array
    // structure, regardless of any XML attributes or quirks of the XML parser.
    $data = Json::encode($content);
    return Json::decode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {
    return $format === 'xml';
  }

}
