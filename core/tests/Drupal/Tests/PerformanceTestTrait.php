<?php

declare(strict_types = 1);

namespace Drupal\Tests;

use Drupal\Core\Url;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SemConv\ResourceAttributes;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides various methods to aid in collecting performance data during tests.
 *
 * @ingroup testing
 */
trait PerformanceTestTrait {

  /**
   * The number of nanoseconds in a second.
   *
   * @var int
   */
  protected int $nanoseconds_per_second = 1000_000_000;

  /**
   * The number of nanoseconds in a millisecond.
   *
   * @var int
   */
  protected int $nanoseconds_per_millisecond = 1000_000;

  /**
   * The number of nanoseconds in a microsecond.
   *
   * @var int
   */
  protected int $nanoseconds_per_microsecond = 1000;

  /**
   * The telemetry service name.
   *
   * A string that uniquely identifies the request being made, for example
   * umamiFrontPageColdCache. Or FALSE to prevent telemetry data from being
   * sent, for example when warming caches.
   */
  protected false|string $telemetryServiceName = FALSE;

  /**
   * Helper for ::setUp().
   *
   * Resets configuration to be closer to production settings.
   */
  protected function doSetUpTasks(): void {
    \Drupal::configFactory()->getEditable('system.performance')
      ->set('css.preprocess', TRUE)
      ->set('js.preprocess', TRUE)
      ->save();
  }

  /**
   * Helper for ::instalModulesFormClassProperty().
   *
   * To use this, override the method and call this helper.
   */
  protected function doInstallModulesFromClassProperty(ContainerInterface $container) {
    // Bypass everything that WebDriverTestBase does here to get closer to
    // a production configuration.
    BrowserTestBase::installModulesFromClassProperty($container);
  }

  /**
   * Helper for ::getMinkDriverArgs().
   *
   * To use this, override the method and call this helper.
   */
  protected function doGetMinkDriverArgs() {

    // Add performance logging preferences to the existing driver arguments to
    // avoid clobbering anything set via environment variables.
    // @see https://chromedriver.chromium.org/logging/performance-log
    $parent_driver_args = parent::getMinkDriverArgs();
    $driver_args = json_decode($parent_driver_args, TRUE);

    $driver_args[1]['goog:loggingPrefs'] = [
      'browser' => 'ALL',
      'performance' => 'ALL',
      'performanceTimeline' => 'ALL',
    ];
    $driver_args[1]['chromeOptions']['perfLoggingPrefs'] = [
      'traceCategories' => 'timeline,devtools.timeline,browser',
    ];

    return json_encode($driver_args);
  }

  /**
   * Logs telemetry data to an Open Telemetry endpoint (when configured).
   *
   * @param callable $callable
   *   A callable, for example wrapping ::drupalGet().
   * @param PerformanceData|null $performance_data
   *   (optional) An instance of the performance data value object.
   * @return mixed
   *   The return value from the callable.
   */
  public function logTelemetry(string $service_name, callable $callable, ?PerformanceData $performance_data) {
    $this->telemetryServiceName = $service_name;
    $return = $this->collectPerformanceData($callable, $performance_data);
    $this->telemetryServiceName = FALSE;
    return $return;
  }

  /**
   * Executes a callable and collects performance data.
   *
   * @param callable $callable
   *   A callable, for example ::drupalGet().
   * @param PerformanceData|null $performance_data
   *   (optional) An instance of the performance data value object.
   *
   * @return mixed
   *   The return value from the callable.
   */
  public function collectPerformanceData(callable $callable, ?PerformanceData $performance_data) {
    $session = $this->getSession();
    $session->getDriver()->getWebDriverSession()->log('performance');
    $return = $callable();
    $this->getChromeDriverPerformanceMetrics($this->traceUrl, $performance_data);
    return $return;
  }

  /**
   * Gets the chromedriver performance log and extracts metrics from it.
   *
   * The performance log is cumulative, and is emptied each time it is
   * collected. If the log grows to the point it will overflow, it may also be
   * emptied resulting in lost messages. There is no specific
   * LargestContentfulPaint event, instead there are
   * largestContentfulPaint::Candidate events which may be superseded by later
   * events. From manual testing none of the core pages result in more than
   * two largestContentfulPaint::Candidate events, so we keep looking until
   * either two have been sent, or until 30 seconds has passed.
   * @todo https://www.drupal.org/project/drupal/issues/3379757
   */
  protected function getChromeDriverPerformanceMetrics(?PerformanceData $performance_data): void {
    $attempts = 0;
    $lcp_count = 0;
    $messages = [];
    $session = $this->getSession();
    while ($attempts <= 30) {
      $attempts++;
      $performance_log = $session->getDriver()->getWebDriverSession()->log('performance');

      foreach ($performance_log as $entry) {
        $decoded = json_decode($entry['message'], TRUE);
        $message = $decoded['message'];
        if ($message['method'] === 'Tracing.dataCollected' && $message['params']['name'] === 'largestContentfulPaint::Candidate') {
          $lcp_count++;
        }
        $messages[] = $message;
      }
      // Only check once if $this->telemetryServiceName is false, since
      // largestContentfulPaint is not currently asserted on.
      if ($lcp_count === 2 || !$this->telemetryServiceName) {
        break;
      }
      sleep(1);
    }
    if (is_object($performance_data)) {
      $this->collectNetworkData($messages, $performance_data);
    }

    if ($this->telemetryServiceName) {
      $this->openTelemetryTracing($messages);
    }
  }

  /**
   * Prepares data for assertions.
   *
   * @param array $messages
   *   The chromedriver performance log messages.
   * @param PerformanceData $performance_data
   *   An instance of the performance data value object.
   */
  protected function collectNetworkData(array $messages, $performance_data): void {
    $stylesheet_count = 0;
    $script_count = 0;
    foreach ($messages as $message) {
      if ($message['method'] === 'Network.responseReceived') {
        if ($message['params']['type'] === 'Stylesheet') {
          $stylesheet_count++;
        }
        if ($message['params']['type'] === 'Script') {
          $this->scriptCount++;
        }
      }
    }
    $performance_data->setStylesheetCount($stylesheet_count);
    $performance_data->setScriptCount($script_count);
  }

  /**
   * Sends metrics to OpenTelemetry.
   *
   * @param array $messages
   *   The ChromeDriver performance log messages.
   *
   * @see https://opentelemetry.io/docs/instrumentation/php/manual/
   */
  protected function openTelemetryTracing(array $messages): void {
    // Open telemetry timestamps are always in nanoseconds.
    $collector = $_ENV['OTEL_COLLECTOR'] ?? NULL;
    if ($collector === NULL) {
      return;
    }
    $timestamp = NULL;
    $url = NULL;
    $dom_loaded_timestamp_page = NULL;
    $dom_loaded_timestamp_timeline = NULL;
    $timestamp_since_os_boot = NULL;
    foreach ($messages as $message) {
      // Since chrome timestamps are since OS start, we take the first network
      // request as '0' and calculate offsets against that.
      if ($timestamp === NULL && $message['method'] === 'Network.requestWillBeSent') {
        $url = $message['url'];
        $timestamp = (int) ($message['params']['wallTime'] * $this->nanoseconds_per_second);
        // Network timestamps are formatted as a second float with three point
        // precision. Record this so it can be compared against other
        // timestamps.
        $timestamp_since_os_boot = (int) ($message['params']['timestamp'] * $this->nanoseconds_per_second);
      }
      // The DOM content loaded event is in both the 'page' and 'timeline'
      // sections of the performance log in different formats. This lets us
      // compare 'ts' and 'timestamp' which are not only in two different
      // formats, but appear to start from slightly different points in time.
      // By subtracting one from the other, we can generate an offset to apply
      // to all other 'ts' timestamps. Note that if the two events actually
      // happen at different times, then the offset will be wrong by that
      // difference.
      // See https://bugs.chromium.org/p/chromium/issues/detail?id=1463436
      if ($dom_loaded_timestamp_page === NULL && $message['method'] === 'Page.domContentEventFired') {
        $dom_loaded_timestamp_page = $message['params']['timestamp'] * $this->nanoseconds_per_second;
      }
      if ($dom_loaded_timestamp_timeline === NULL && $message['method'] === 'Tracing.dataCollected' && isset($message['params']['args']['data']['type']) && $message['params']['args']['data']['type'] === 'DOMContentLoaded') {
        $dom_loaded_timestamp_timeline = $message['params']['ts'] * $this->nanoseconds_per_microsecond;
      }
    }

    $offset = $dom_loaded_timestamp_page - $dom_loaded_timestamp_timeline;
    $entry = $this->getSession()->evaluateScript("window.performance.getEntriesByType('navigation')")[0];
    $first_request_timestamp = $entry['requestStart'] * $this->nanoseconds_per_millisecond;
    $first_response_timestamp = $entry['responseStart'] * $this->nanoseconds_per_millisecond;

    // @todo: get commit hash from an environment variable and add this as an
    // additional attribute.
    // @see https://www.drupal.org/project/drupal/issues/3379761
    $resource = ResourceInfoFactory::merge(ResourceInfo::create(Attributes::create([
      ResourceAttributes::SERVICE_NAMESPACE => 'Drupal',
      ResourceAttributes::SERVICE_NAME => $this->telemetryServiceName,
      ResourceAttributes::SERVICE_INSTANCE_ID => 1,
      ResourceAttributes::SERVICE_VERSION => \Drupal::VERSION,
      ResourceAttributes::DEPLOYMENT_ENVIRONMENT => 'local',
    ])), ResourceInfoFactory::defaultResource());

    $transport = (new OtlpHttpTransportFactory())->create($collector, 'application/x-protobuf');
    $exporter = new SpanExporter($transport);
    $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter), NULL, $resource);
    $tracer = $tracerProvider->getTracer('Drupal');

    $span = $tracer->spanBuilder('main')
      ->setStartTimestamp($timestamp)
      ->setAttribute('http.method', 'GET')
      ->setAttribute('http.url', $url)
      ->setSpanKind(SpanKind::KIND_SERVER)
      ->startSpan();
    $last_timestamp = $first_byte_timestamp = (int) ($timestamp + ($first_response_timestamp - $first_request_timestamp));

    try {
      $scope = $span->activate();
      $first_byte_span = $tracer->spanBuilder('firstByte')
        ->setStartTimestamp($timestamp)
        ->setAttribute('http.url', $url)
        ->startSpan();
      $first_byte_span->end($first_byte_timestamp);
      // Largest contentful paint is not available from
      // window.performance::getEntriesByType() so use the performance log
      // messages to get it instead.
      $lcp_timestamp = NULL;
      $fcp_timestamp = NULL;
      $lcp_size = 0;
      foreach ($messages as $message) {
        if ($message['method'] === 'Tracing.dataCollected' && $message['params']['name'] === 'firstContentfulPaint') {
          if (!isset($fcp_timestamp)) {
            // Tracing timestamps are microseconds since OS boot. However they
            // appear to start from a slightly different point from page
            // timestamps. Apply an offset calculated from DOM content loaded.
            // See https://bugs.chromium.org/p/chromium/issues/detail?id=1463436
            $fcp_timestamp = ($message['params']['ts'] * $this->nanoseconds_per_microsecond) + $offset;
            $fcp_span = $tracer->spanBuilder('firstContentfulPaint')
              ->setStartTimestamp($timestamp)
              ->setAttribute('http.url', $url)
              ->startSpan();
            $last_timestamp = $first_contentful_paint_timestamp = (int) ($timestamp + ($fcp_timestamp - $timestamp_since_os_boot));
            $fcp_span->end($first_contentful_paint_timestamp);
          }
        }

        // There can be multiple largestContentfulPaint candidates, remember
        // the largest one.
        if ($message['method'] === 'Tracing.dataCollected' && $message['params']['name'] === 'largestContentfulPaint::Candidate' && $message['params']['args']['data']['size'] > $lcp_size) {
          $lcp_timestamp = ($message['params']['ts'] * $this->nanoseconds_per_microsecond) + $offset;
          $lcp_size = $message['params']['args']['data']['size'];
        }
      }
      if (isset($lcp_timestamp)) {
        $lcp_span = $tracer->spanBuilder('largestContentfulPaint')
          ->setStartTimestamp($timestamp)
          ->setAttribute('http.url', $url)
          ->startSpan();
        $last_timestamp = $largest_contentful_paint_timestamp = (int) ($timestamp + ($lcp_timestamp - $timestamp_since_os_boot));
        $lcp_span->setAttribute('lcp.size', $lcp_size);
        $lcp_span->end($largest_contentful_paint_timestamp);
      }
    }
    finally {
      // The scope must be detached before the span is ended, because it's
      // created from the span.
      if (isset($scope)) {
        $scope->detach();
      }
      $span->end($last_timestamp);
      $tracerProvider->shutdown();
    }
  }

}
