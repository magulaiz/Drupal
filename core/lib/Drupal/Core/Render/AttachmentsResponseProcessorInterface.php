<?php

namespace Drupal\Core\Render;

/**
 * Defines an interface for processing attachments of responses that have them.
 *
 * @see \Drupal\Core\Ajax\AjaxResponse
 * @see \Drupal\Core\Ajax\AjaxResponseAttachmentsProcessor
 * @see \Drupal\Core\Render\HtmlResponse
 * @see \Drupal\Core\Render\HtmlResponseAttachmentsProcessor
 */
interface AttachmentsResponseProcessorInterface {

  /**
   * Processes the attachments of a response that has attachments.
   *
   * Libraries, JavaScript settings, feeds, HTML <head> tags, HTML <head> links,
   * HTTP headers, and the HTTP status code are attached to render arrays using
   * the #attached property. The #attached property is an associative array,
   * where the keys are the attachment types and the values are the attached
   * data.
   *
   * The available keys are:
   * - 'library' (asset libraries): The value should be a string.
   *   @code
   *   $build['#attached']['library'][] = 'core/jquery';
   *   @endcode
   * - 'drupalSettings' (JavaScript settings): The value should be given in
   *   key-value format.
   *   @code
   *   $build['#attached']['drupalSettings']['foo'] = 'bar';
   *   @endcode
   * - 'feed' (RSS feeds): The value should be an array in the format
   *   ['url', 'title'].
   *   @code
   *   $build['#attached']['feed'][] = [$url, $this->t('Feed title')];
   *   @endcode
   * - 'html_head' (tags in HTML <head>): The value should be an array in the
   *   format ['tag_data', 'key']. The key is a unique string used to identify
   *   the element in implementations of 'hook_page_attachments_alter'.
   *   @code
   *   $build['#attached']['html_head'][] = [
   *     [
   *       '#tag' => 'meta',
   *       '#attributes' => [
   *         'property' => 'og:image',
   *         'content' => 'path/to/image',
   *       ],
   *     ],
   *     'unique_key',
   *   ];
   *   @endcode
   * - 'html_head_link' (HTML <head> <link> tags): The value should be an
   *   array in the format [$link_data, $flag]. $flag is a boolean value. When
   *   set to true, the html_head_link will be included in the HTTP header as
   *   well. The default value is false.
   *   @code
   *   $build['#attached']['html_head_link'][] = [
   *     'rel' => 'canonical',
   *     'href' => $url->toString(),
   *   ];
   *   @endcode
   * - 'http_header' (HTTP headers and status code): The value should be an
   *   array in the format [$header_name, $header_value, $replace]. $replace is
   *   a boolean value that determines whether to replace the current value with
   *   the new one or add it to the existing ones. If the value is not replaced,
   *   it will be appended, resulting in a header like this:
   *   'Header: value1, value2'. The default value is false.
   *   @code
   *   $build['#attached']['http_header'] = [
   *     ['Content-Type', 'application/rss+xml; charset=utf-8'],
   *   ];
   *   @endcode
   *
   * Placeholders need to be rendered first in order to have all attachments
   * available for processing. For an example, see
   * \Drupal\Core\Render\HtmlResponseAttachmentsProcessor::renderPlaceholders()
   *
   * @param \Drupal\Core\Render\AttachmentsInterface $response
   *   The response to process.
   *
   * @return \Drupal\Core\Render\AttachmentsInterface
   *   The processed response, with the attachments updated to reflect their
   *   final values.
   *
   * @throws \InvalidArgumentException
   *   Thrown when the $response parameter is not the type of response object
   *   the processor expects.
   */
  public function processAttachments(AttachmentsInterface $response);

}
