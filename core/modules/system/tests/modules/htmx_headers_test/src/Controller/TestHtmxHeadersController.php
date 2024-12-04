<?php

declare(strict_types=1);

namespace Drupal\htmx_headers_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Http\HtmxLocationResponseData;
use Drupal\Core\Http\HtmxResponseHeaders;

/**
 * Returns responses for HTMX Header test routes.
 */
final class TestHtmxHeadersController extends ControllerBase {

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function locationUrl(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->location($this->getUrl());
    $build['content'] = [
      '#markup' => $this->t('Location'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Helper function to build a sample url.
   *
   * @return \Drupal\Core\Url
   *   The url object.
   */
  protected function getUrl(): Url {
    return Url::fromRoute('<front>');
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function locationJson(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $data = new HtmxLocationResponseData(
      path: $this->getUrl(),
      source: 'source-value',
      event: 'event-value',
      headers: ['Header-one' => 'one'],
      handler: 'handler-value',
      target: 'target-value',
      swap: 'swap-value',
      select: 'select-value',
      values: ['one' => '1', 'two' => '2'],
    );
    $htmxHeaders->location($data);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Location'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function pushUrlFalse(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->pushUrl(FALSE);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('pushUrl'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function pushUrl(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->pushUrl($this->getUrl());
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('pushUrl'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function replaceUrlFalse(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->replaceUrl(FALSE);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('pushUrl'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function replaceUrl(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->replaceUrl($this->getUrl());
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('replaceUrl'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function redirectHeader(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->redirect($this->getUrl());
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('redirect'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function refresh(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->refresh(TRUE);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('refresh'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function reswap(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->reswap('swap-value');
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('reswap'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function retarget(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->retarget('retarget-value');
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('retarget'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function reselect(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->reselect('reselect-value');
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('reselect'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function triggers(): array {
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->trigger('trigger-value')
      ->triggerAfterSettle('trigger-value')
      ->triggerAfterSwap('trigger-value');
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('reselect'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

  /**
   * Builds the response with header.
   *
   * @return array
   *   The render array
   */
  public function triggersJson(): array {
    $data = [
      'showMessage' =>
        [
          'level' => 'info',
          'message' => 'Here Is A Message',
        ],
    ];
    $htmxHeaders = new HtmxResponseHeaders();
    $htmxHeaders->trigger($data)
      ->triggerAfterSettle($data)
      ->triggerAfterSwap($data);
    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('reselect'),
      '#attached' => [
        'http_header' => $htmxHeaders->toArray(),
      ],
    ];

    return $build;
  }

}
