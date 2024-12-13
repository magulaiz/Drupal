<?php

namespace Drupal\Core\Template;

use Drupal\Core\Url;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Collects, sanitizes, and renders attributes for HTMX integration.
 *
 * This object is composed into \Drupal\Core\Ajax\Htmx. The most common usage
 * will be to access it as a property there.
 *
 * There are times when this class is needed directly because a portion of
 * HTML is being managed directly, such as a prefix
 * element.
 *
 * @code
 * $attributes = new Attribute(['id' => 'edit-export-wrapper']);
 * $export_htmx_attributes = new HtmxAttribute();
 * $export_htmx_attributes->swapOob('outerHTML:#edit-export-wrapper');
 * $attributes = $attributes->merge($export_htmx_attributes);
 * $form['export']['#prefix'] = '<div  ' . $attributes . '>';
 * @endcode
 *
 * @see \Drupal\Core\Ajax\Htmx
 * @see https://htmx.org/reference/
 */
class HtmxAttribute implements HtmlAttributeInterface {

  use HtmlAttributeTrait;

  /**
   * Stores the attribute data.
   *
   * @var \Drupal\Core\Template\AttributeValueBase[]
   */
  protected array $storage = [];

  /* ***** Internal utility methods ***** */

  /**
   * Utility method to transform camelCase strings to kebab-case strings.
   *
   * Passes kebab-case strings through without any transformation.
   *
   * @param string $identifier
   *   The string to verify or transform.
   *
   * @return string
   *   The original or transformed string.
   */
  protected function insureKebabCase(string $identifier): string {
    // Check for existing kebab case.
    $kebabParts = explode('-', $identifier);
    // If the number of lower case parts matches the number of parts, then
    // all the parts are lower case.
    $isKebab = count($kebabParts) === count(array_filter($kebabParts, function ($part) {
      return preg_match('#^[[:lower:]]+$#', $part) === 1;
    }));
    if ($isKebab) {
      return $identifier;
    }

    $converter = new CamelCaseToSnakeCaseNameConverter();
    $snakeCase = $converter->normalize($identifier);
    return preg_replace('#[_:]#', '-', $snakeCase);
  }

  /**
   * Utility method to create and store a string value as an attribute.
   *
   * @param string $id
   *   The HTMX attribute id.
   * @param string $value
   *   The attribute value.
   *
   * @return void
   */
  protected function createStringAttribute(string $id, string $value): void {
    $key = 'data-hx-' . $id;
    $attribute = new AttributeString($key, $value);
    $this->storage[$key] = $attribute;
  }

  /**
   * Utility method to create and store a boolean value as an attribute.
   *
   * @param string $id
   *   The HTMX attribute id.
   * @param bool $value
   *   The attribute value.
   *
   * @return void
   */
  protected function createBooleanAttribute(string $id, bool $value): void {
    $key = 'data-hx-' . $id;
    $attribute =  new AttributeBoolean($key, $value);
    $this->storage[$key] = $attribute;

  }

  /**
   * Utility method to create and store an array as an attribute.
   *
   * @param string $id
   *   The HTMX attribute id.
   * @param array{string, string} $value
   *   The attribute values.
   *
   * @return void
   */
  protected function createJsonAttribute(string $id, array $value): void {
    $key = 'data-hx-' . $id;
    $attribute =  new AttributeJson($key, $value);
    $this->storage[$key] = $attribute;
  }

  /* ***** HtmxAttribute methods ***** */

  /**
   * {@inheritdoc}
   */
  public function hasAttribute($name): bool {
    return array_key_exists($name, $this->storage);
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator(): \ArrayIterator {
    return new \ArrayIterator($this->storage);
  }

  /**
   * {@inheritdoc}
   */
  public function count(): int {
    return count($this->storage);
  }

  /* ***** Request attributes ***** */

  /**
   * Issues a GET request to the specified URL.
   *
   * @param \Drupal\Core\Url $url
   *   The URL for the GET request.
   *
   * @return HtmxAttribute
   *   returns self so that attribute methods may be chained.
   *
   * @see https://htmx.org/attributes/hx-get/
   */
  public function get(Url $url): HtmxAttribute {
    $this->createStringAttribute('get', $url->toString());
    return $this;
  }

  /**
   * Issues a POST request to the specified URL.
   *
   * @param \Drupal\Core\Url $url
   *   The URL for the POST request.
   *
   * @return HtmxAttribute
   *   returns self so that attribute methods may be chained.
   *
   * @see https://htmx.org/attributes/hx-post/
   */
  public function post(Url $url): HtmxAttribute {
    $this->createStringAttribute('post', $url->toString());
    return $this;
  }

  /**
   * Issues a PUT request to the specified URL.
   *
   * @param \Drupal\Core\Url $url
   *   The URL for the PUT request.
   *
   * @return HtmxAttribute
   *   returns self so that attribute methods may be chained.
   *
   * @see https://htmx.org/attributes/hx-put/
   */
  public function put(Url $url): HtmxAttribute {
    $this->createStringAttribute('put', $url->toString());
    return $this;
  }

  /**
   * Issues a PATCH request to the specified URL.
   *
   * @param \Drupal\Core\Url $url
   *   The URL for the PATCH request.
   *
   * @return HtmxAttribute
   *   returns self so that attribute methods may be chained.
   *
   * @see https://htmx.org/attributes/hx-patch/
   */
  public function patch(Url $url): HtmxAttribute {
    $this->createStringAttribute('patch', $url->toString());
    return $this;
  }

  /**
   * Issues a DELETE request to the specified URL.
   *
   * @param \Drupal\Core\Url $url
   *   The URL for the DELETE request.
   *
   * @return HtmxAttribute
   *   returns self so that attribute methods may be chained.
   *
   * @see https://htmx.org/attributes/hx-delete/
   */
  public function delete(Url $url): HtmxAttribute {
    $this->createStringAttribute('delete', $url->toString());
    return $this;
  }

  /* ***** Remaining HTMX 'core' attributes ***** */

  /**
   * Handle events with inline scripts on elements.
   *
   * @param string $event
   *   An event in either camelCase or kebab-case.
   * @param string $action
   *   The action to take when the event occurs.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-on/
   */
  public function on(string $event, string $action): HtmxAttribute {
    // Special case: the `::EventName` shorthand for `htmx:EventName`.
    // Remove one leading `:` so that our final attribute is
    // `data-hx--event-name` rather than `data-hx---event-name`.
    $event = preg_replace('#^::#', ':', $event);
    $formattedEvent = 'on-' . $this->insureKebabCase($event);
    $this->createStringAttribute($formattedEvent, $action);
    return $this;
  }

  /**
   * Control URLs in browser history.
   *
   * Use a boolean when this attribute is added along with ::get
   * - true: pushes the fetched URL into history.
   * - false: disables pushing the fetched URL if it would otherwise be pushed
   *   due to inheritance or hx-boost.
   *
   * Use a URL to cause a push into the location bar. This may be relative or
   * absolute, as per history.pushState()
   *
   * @param bool|\Drupal\Core\Url $value
   *   Use a Url object or a boolean, depending on use case.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-push-url/
   */
  public function pushUrl(bool|Url $value): HtmxAttribute {
    if ($value instanceof Url) {
      $this->createStringAttribute('push-url', $value->toString());
    }
    else {
      $this->createStringAttribute('push-url', $value ? 'true' : 'false');
    }
    return $this;
  }

  /**
   * Select content to swap in from a response.
   *
   * Uses the selector to select elements from the response.
   *
   * @param string $selector
   *   A CSS query selector.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-select/
   */
  public function select(string $selector): HtmxAttribute {
    $this->createStringAttribute('select', $selector);
    return $this;
  }

  /**
   * Select content for an out-of-band swap from a response.
   *
   * Each value in the comma separated list of values can specify any valid
   * hx-swap strategy by separating the selector and the swap strategy with a
   * colon, such as #alert:afterbegin.
   *
   * @param string $selectors
   *   A comma separated list of elements to be swapped out of band.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-select-oob/
   */
  public function selectOob(string $selectors): HtmxAttribute {
    $this->createStringAttribute('select-oob', $selectors);
    return $this;
  }

  /**
   * Controls how content will swap in.
   *
   * The hx-swap attribute allows you to specify how the response will be
   * swapped in relative to the target of an AJAX request.
   *
   * @param string $strategy
   *   A comma separated list of elements to be swapped out of band.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-swap/
   */
  public function swap(string $strategy, bool $ignoreTitle = TRUE): HtmxAttribute {
    // HTMX defaults this behavior to FALSE, that is it replaces page title.
    // We believe our most common use case is to not change the title.
    if ($ignoreTitle) {
      $strategy .= '  ignoreTitle:true';
    }
    $this->createStringAttribute('swap', $strategy);
    return $this;
  }

  /**
   * Designate content in a response for an out-of-band swap.
   *
   * The hx-swap-oob attribute allows you to specify that some content in a
   * response should be swapped into the DOM somewhere other than the target,
   * that is “Out of Band”. This allows you to piggyback updates to other
   * element updates on a response.
   *
   * @param string $value
   *   A comma separated list of elements to be swapped out of band.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-swap-oob/
   */
  public function swapOob(true|string $value): HtmxAttribute {
    if ($value === TRUE) {
      $this->createStringAttribute('swap-oob', 'true');
    }
    else {
      $this->createStringAttribute('swap-oob', $value);
    }
    return $this;
  }

  /**
   * Specifies the target element to receive  the incoming markup.
   *
   * The hx-target attribute allows you to target a different element for
   * swapping than the one issuing the AJAX request. There are a variety
   * of target string syntaxes.  See the URL below for details.
   *
   * @param string $target
   *   The target descriptor.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-target/
   */
  public function target(string $target): HtmxAttribute {
    $this->createStringAttribute('target', $target);
    return $this;
  }

  /**
   * Specifies what triggers a request.
   *
   * Used with an HTMX request attribute. Allows:
   * - An event name (e.g. “click” or “my-custom-event”) followed by an event
   *   filter and a set of event modifiers
   * - A polling definition of the form every <timing declaration>
   * - A comma-separated list of such events.
   *
   * @param string $triggerDefinition
   *   The trigger definition.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-trigger/
   */
  public function trigger(string $triggerDefinition): HtmxAttribute {
    $this->createStringAttribute('trigger', $triggerDefinition);
    return $this;
  }

  /**
   * Add to the parameters that will be submitted with an HTMX request.
   *
   * The value of this attribute is a list of name-expression values
   * which will be converted to JSON (JavaScript Object Notation) format.
   *
   * @param array<string, string> $values
   *   The values in an array of 'name' => 'value' pairs.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-trigger/
   */
  public function vals(array $values): HtmxAttribute {
    $this->createJsonAttribute('vals', $values);
    return $this;
  }

  /* ***** Remaining HTMX 'core' attributes ***** */

  /**
   * Add progressive enhancement for links and forms.
   *
   * The hx-boost attribute allows you to “boost” normal anchors and form tags
   * to use AJAX instead. This has the nice fallback that, if the user does not
   * have javascript enabled, the site will continue to work.
   *
   * @param bool $value
   *   Should the element and its descendants be "boosted"?
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-boost/
   */
  public function boost(bool $value): HtmxAttribute {
    if ($value === TRUE) {
      $this->createStringAttribute('boost', 'true');
    }
    else {
      $this->createStringAttribute('boost', 'false');
    }
    return $this;
  }

  /**
   * Shows a confirm() dialog before issuing a request.
   *
   * @param string $message
   *   The user facing message.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-confirm/
   */
  public function confirm(string $message): HtmxAttribute {
    $this->createStringAttribute('confirm', $message);
    return $this;
  }

  /**
   * Disables HTMX processing for the given node and any descendants.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-disable/
   */
  public function disable(): HtmxAttribute {
    $this->createBooleanAttribute('disable', TRUE);
    return $this;
  }

  /**
   * Adds the disabled attribute to the specified elements during a request.
   *
   * The descriptor syntax is the same as hx-target. See the documentation
   * link below for more details.
   *
   * @param string $descriptor
   *   The attribute value.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-disabled-elt/
   */
  public function disabledElements(string $descriptor): HtmxAttribute {
    $this->createStringAttribute('disabled-elt', $descriptor);
    return $this;
  }

  /**
   * Control and disable automatic HTMX attribute inheritance for child nodes.
   *
   * @param string $names
   *   The attribute names to disinherit or * for all.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-disinherit/
   */
  public function disinherit(string $names): HtmxAttribute {
    $this->createStringAttribute('disinherit', $names);
    return $this;
  }

  /**
   * Changes the request encoding type.
   *
   * @param string $method
   *   The encoding method.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-encoding/
   */
  public function encoding(string $method = 'multipart/form-data'): HtmxAttribute {
    $this->createStringAttribute('encoding', $method);
    return $this;
  }

  /**
   * Enables HTMX extensions for an element and descendants.
   *
   * @param string $names
   *   An extension name, or a comma separated list of names.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-ext/
   */
  public function ext(string $names): HtmxAttribute {
    $this->createStringAttribute('ext', $names);
    return $this;
  }

  /**
   * Add to the headers that will be submitted with an HTMX request.
   *
   * @param array<string, string> $headerValues
   *   The header values as name => value.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-headers/
   */
  public function headers(array $headerValues): HtmxAttribute {
    $this->createJsonAttribute('headers', $headerValues);
    return $this;
  }

  /**
   * Prevent sensitive data being saved to the history cache.
   *
   * Set the hx-history attribute to false on any element in the current
   * document, or any html fragment loaded into the current document by htmx,
   * to prevent sensitive data being saved to the localStorage cache when htmx
   * takes a snapshot of the page state.
   *
   * @param bool $value
   *   Sets the string value to 'true' or 'false'. Defaults to FALSE.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-history/
   */
  public function history(bool $value = FALSE): HtmxAttribute {
    if ($value) {
      $this->createStringAttribute('history', 'true');
    }
    else {
      $this->createStringAttribute('history', 'false');
    }
    return $this;
  }

  /**
   * The element to snapshot and restore during history navigation.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-history-elt/
   */
  public function historyElement(): HtmxAttribute {
    $this->createBooleanAttribute('history-elt', TRUE);
    return $this;
  }

  /**
   * Include additional element values in HTMX requests.
   *
   * The descriptor syntax is the same as hx-target. See the documentation
   * link below for more details.
   *
   * @param string $descriptors
   *   The element descriptors.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-include/
   */
  public function include(string $descriptors): HtmxAttribute {
    $this->createStringAttribute('include', $descriptors);
    return $this;
  }

  /**
   * The element to put the htmx-request class on during the request.
   *
   * @param string $selector
   *   The element CSS selector value. Selector may be prefixed with `closest`.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-indicator/
   */
  public function indicator(string $selector): HtmxAttribute {
    $this->createStringAttribute('indicator', $selector);
    return $this;
  }

  /**
   * Control automatic attribute inheritance for child nodes.
   *
   * HTMX evaluates attribute inheritance with hx-inherit in two ways when
   * hx-inherit is set on a parent node:
   *  - data-hx-inherit="*"
   *    All attribute inheritance for this element will be enabled.
   * - data-hx-hx-inherit="hx-select hx-get hx-target"
   *   Enable inheritance for only one or multiple specified attributes.
   *
   * @param string $attributes
   *   The attributes to inherit.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-inherit/
   */
  public function inherit(string $attributes): HtmxAttribute {
    $this->createStringAttribute('inherit', $attributes);
    return $this;
  }

  /**
   * Filters the parameters that will be submitted with a request.
   *
   * @param string $filter
   *   The filter string. Multiple syntax options, see the link below.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-params/
   */
  public function params(string $filter): HtmxAttribute {
    $this->createStringAttribute('params', $filter);
    return $this;
  }

  /**
   * Specifies elements to keep unchanged between requests.
   *
   * @param string $id
   *   The id attribute of the element to preserve.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-preserve/
   */
  public function preserve(string $id): HtmxAttribute {
    $this->createStringAttribute('preserve', $id);
    return $this;
  }

  /**
   * Shows a prompt() before submitting a request.
   *
   * @param string $message
   *   The message to display in the prompt.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-prompt/
   */
  public function prompt(string $message): HtmxAttribute {
    $this->createStringAttribute('prompt', $message);
    return $this;
  }

  /**
   * Control URLs in the browser location bar.
   *
   * Use a boolean when this attribute is added along with a request:
   * - true: replaces the fetched URL in the browser navigation bar.
   * - false: disables replacing the fetched URL if it would otherwise be
   *   replaced due to inheritance.
   *
   * Use a URL to replace the value in the location bar. This may be relative or
   * absolute, as per history.replaceState().
   *
   * @param bool|\Drupal\Core\Url $value
   *   Use a Url object or a boolean, depending on use case.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-replace-url/
   */
  public function replaceUrl(bool|Url $value): HtmxAttribute {
    if ($value instanceof Url) {
      $this->createStringAttribute('replace-url', $value->toString());
    }
    else {
      $this->createStringAttribute('replace-url', $value ? 'true' : 'false');
    }
    return $this;
  }

  /**
   * Configures various aspects of the request.
   *
   * The hx-request attribute supports the following configuration values:
   * - timeout: (integer) the timeout for the request, in milliseconds.
   * - credentials: (boolean) if the request will send credentials.
   * - noHeaders: (boolean) strips all headers from the request.
   *
   * Dynamic javascript values are not supported for security and for
   * simplicity.  If you need calculated values you should do determine them
   * here on the server-side
   *
   * @param array<string, int|bool> $configValues
   *   The configuration values as name => value.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-headers/
   */
  public function request(array $configValues): HtmxAttribute {
    $this->createJsonAttribute('request', $configValues);
    return $this;
  }

  /**
   * Synchronize AJAX requests between multiple elements.
   *
   * @param string $selector
   *   A CSS selector followed by a strategy.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-sync/
   */
  public function sync(string $selector): HtmxAttribute {
    $this->createStringAttribute('sync', $selector);
    return $this;
  }

  /**
   * Cause an element to validate itself before it submits a request.
   *
   * @param bool $value
   *   Should the element validate before the request.
   *
   * @return $this
   *
   * @see https://htmx.org/attributes/hx-validate/
   */
  public function validate(bool $value = TRUE): HtmxAttribute {
    if ($value) {
      $this->createStringAttribute('validate', 'true');
    }
    else {
      $this->createStringAttribute('validate', 'false');
    }
    return $this;
  }

}
