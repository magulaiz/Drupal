<?php

declare(strict_types=1);

namespace Drupal\Core\Cache;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\State\StateInterface;

/**
 * A query-string cache handler service to support browser-caching.
 *
 * The string changes on every update or full cache flush, forcing browsers to
 * load a new copy of the files, as the URL changed.
 */
class QueryString implements QueryStringInterface {

  /**
   * Creates a new QueryString instance.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   State service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   System time service.
   */
  public function __construct(
    protected StateInterface $state,
    protected TimeInterface $time
  ) {}

  /**
   * {@inheritdoc}
   */
  public function reset(): void {
    // The timestamp is converted to base 36 in order to make it more compact.
    $this->state->set('system.css_js_query_string', base_convert(strval($this->time->getRequestTime()), 10, 36));
  }

  /**
   * {@inheritdoc}
   */
  public function get(): string {
    return $this->state->get('system.css_js_query_string', '0');
  }

}
