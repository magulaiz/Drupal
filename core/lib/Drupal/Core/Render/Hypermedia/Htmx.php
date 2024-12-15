<?php

namespace Drupal\Core\Render\Hypermedia;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Http\HtmxHeaderInterface;
use Drupal\Core\Http\HtmxResponseHeaders;
use Drupal\Core\Render\Hypermedia\Operations\HtmxOperationInterface;
use Drupal\Core\Render\Hypermedia\Operations\HtmxRequestOperationInterface;
use Drupal\Core\Template\AttributeHelper;
use Drupal\Core\Template\HtmlAttributeInterface;
use Drupal\Core\Template\HtmxAttribute;

/**
 * Collects HTMX attributes and headers and manages complex behaviors.
 *
 * An instance of this object is required to add HTMX behaviors to a render
 * element.
 *
 * Methods to configure common behaviors are provided:
 *
 * @code
 * $htmx = new Htmx();
 * $node_url = Url::fromRoute(
 *    route_name: entity.node.canonical',
 *    route_parameters: ['node' => 123],
 *  );
 * $htmx->insert($node_url, 'div.example', 'article.page')
 *
 * $build = [
 *    '#htmx' => $htmx,
 *  ];
 * @endcode
 *
 * Attributes may also be specified explicitly:
 *
 * @code
 * $htmx = new Htmx();
 * $form_url = Url::fromRoute(
 *   route_name: 'config.export_single',
 *   route_parameters: ['config_type' => $config_type, 'config_name' => $config_name],
 * );
 *
 * $htmx->attributes()
 *   ->post($form_url)
 *   ->select('select[data-drupal-selector="edit-config-name"]')
 *   ->target('select[data-drupal-selector="edit-config-name"]')
 *   ->swap('outerHTML');
 *
 * $build = [
 *   '#htmx' => $htmx,
 * ];
 * @endcode
 *
 * HTMX headers are added in a similar way.
 *
 * @code
 * // Also update the browser URL.
 * $push = Url::fromRoute(
 *   route_name: 'config.export_single',
 *   route_parameters: ['config_type' => $default_type, 'config_name' => $default_name],
 * );
 *
 * $htmx = new Htmx();
 * $htmx->headers()->pushUrl($push);
 * $build = [
 *   '#htmx' => $htmx,
 * ];
 * @endcode
 *
 * @see \Drupal\Core\Template\HtmxAttribute
 * @see \Drupal\Core\Http\HtmxResponseHeaders
 */
class Htmx implements HtmxInterface {

  /**
   * Optional request operation.
   *
   * @var \Drupal\Core\Render\Hypermedia\Operations\HtmxRequestOperationInterface|null
   */
  protected HtmxRequestOperationInterface|null $requestOperation = NULL;

  /**
   * Additional operations that do not depend on a request.
   *
   * @var \Drupal\Core\Render\Hypermedia\Operations\HtmxOperationInterface[]
   */
  protected array $additionalOperations = [];

  public function __construct(
    public readonly HtmxAttribute $attributes = new HtmxAttribute(),
    public readonly HtmxHeaderInterface $headers = new HtmxResponseHeaders(),
  ) {}

  /**
   * {@inheritdoc}
   */
  public function attributes(): HtmxAttribute {
    return $this->attributes;
  }

  /**
   * {@inheritdoc}
   */
  public function headers(): HtmxHeaderInterface {
    return $this->headers;
  }

  /**
   * {@inheritdoc}
   */
  public function getCombinedAttributes(array|HtmlAttributeInterface $attributes): HtmlAttributeInterface|array {
    return AttributeHelper::mergeCollections($attributes, $this->attributes);
  }

  /**
   * {@inheritdoc}
   */
  public function getCombinedHeaders(array $headers): array {
    return NestedArray::mergeDeep($headers, $this->headers->toArray());
  }

  /**
   * {@inheritdoc}
   */
  public function merge(HtmxInterface $htmx): HtmxInterface {
    $this->attributes->merge($htmx->attributes());
    $this->headers->merge($htmx->headers());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequestOperation(HtmxRequestOperationInterface $operation): HtmxInterface {
    $this->requestOperation = $operation;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setAdditionalOperation(HtmxOperationInterface $operation): HtmxInterface {
    if ($operation instanceof HtmxRequestOperationInterface) {
      throw new \ValueError('Htmx::setRequestOperation() must be used to add HtmxRequestOperationInterface operations');
    }
    $this->additionalOperations[] = $operation;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasOperations(): bool {
    return $this->requestOperation instanceof HtmxRequestOperationInterface
    || count($this->additionalOperations) !== 0;
  }

  /**
   * {@inheritdoc}
   */
  public function processOperations(): void {
    if ($this->hasOperations() === FALSE) {
      return;
    }
    $operations = [];
    if (count($this->additionalOperations) !== 0) {
      $operations = $this->additionalOperations;
    }
    if ($this->requestOperation instanceof HtmxRequestOperationInterface) {
      array_unshift($operations, $this->requestOperation);
    }
    foreach ($operations as $operation) {
      $operation->setProperties($this);
    }
  }

}
