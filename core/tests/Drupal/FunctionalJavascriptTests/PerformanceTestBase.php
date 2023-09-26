<?php

declare(strict_types = 1);

namespace Drupal\FunctionalJavascriptTests;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
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
 * Collects performance metrics.
 *
 * @ingroup testing
 */
class PerformanceTestBase extends WebDriverTestBase {

  /**
   * The number of nanoseconds in a second.
   *
   * @var int
   */
  const NANOSECONDS_PER_SECOND = 1000_000_000;

  /**
   * The number of nanoseconds in a millisecond.
   *
   * @var int
   */
  const NANOSECONDS_PER_MILLISECOND = 1000_000;

  /**
   * The number of nanoseconds in a microsecond.
   *
   * @var int
   */
  const NANOSECONDS_PER_MICROSECOND = 1000;

  /**
   * The number of stylesheets requested.
   */
  protected int $stylesheetCount = 0;

  /**
   * The number of scripts requested.
   */
  protected int $scriptCount = 0;

  /**
   * The classname of the parent class for use in identifying traces.
   */
  protected string $parentClass;

  /**
   * The telemetry service name.
   *
   * A string that uniquely identifies the request being made, for example
   * umamiFrontPageColdCache. Or FALSE to prevent telemetry data from being
   * sent, for example when warming caches.
   */
  protected false|string $telemetryServiceName = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    \Drupal::configFactory()->getEditable('system.performance')
      ->set('css.preprocess', TRUE)
      ->set('js.preprocess', TRUE)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function installModulesFromClassProperty(ContainerInterface $container) {
    // Bypass everything that WebDriverTestBase does here to get closer to
    // a production configuration.
    BrowserTestBase::installModulesFromClassProperty($container);
  }

  /**
   * {@inheritdoc}
   */
  protected function getMinkDriverArgs() {

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
   * {@inheritdoc}
   */
  public function drupalGet($path, array $options = [], array $headers = []): string {
    // Reset the performance log from any previous HTTP requests. The log is
    // cumulative until it is collected explicitly.
    $session = $this->getSession();
    $session->getDriver()->getWebDriverSession()->log('performance');
    $return = parent::drupalGet($path, $options, $headers);
    $this->getChromeDriverPerformanceMetrics($path);
    return $return;
  }

  /**
   * Gets the chromedriver performance log and extracts metrics from it.
   */
  protected function getChromeDriverPerformanceMetrics(string|Url $path): void {
    // The performance log is cumulative, and is emptied each time it is
    // collected. If the log grows to the point it will overflow, it may also be
    // emptied resulting in lost messages. There is no specific
    // LargestContentfulPaint event, instead there are
    // largestContentfulPaint::Candidate events which may be superseded by later
    // events. From manual testing none of the core pages result in more than
    // two largestContentfulPaint::Candidate events, so we keep looking until
    // either two have been sent, or until 30 seconds has passed.
    // @todo https://www.drupal.org/project/drupal/issues/3379757
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
    $this->collectNetworkData($path, $messages);
    if ($this->telemetryServiceName) {
      $this->openTelemetryTracing($path, $messages);
    }
  }

  /**
   * Prepares data for assertions.
   *
   * @param string|\Drupal\Core\Url $path
   *   The path as passed to static::drupalGet().
   * @param array $messages
   *   The chromedriver performance log messages.
   */
  protected function collectNetworkData(string|Url $path, array $messages): void {
    $this->stylesheetCount = 0;
    $this->scriptCount = 0;
    foreach ($messages as $message) {
      if ($message['method'] === 'Network.responseReceived') {
        if ($message['params']['type'] === 'Stylesheet') {
          $this->stylesheetCount++;
        }
        if ($message['params']['type'] === 'Script') {
          $this->scriptCount++;
        }
      }
    }
  }

  /**
   * Sends metrics to OpenTelemetry.
   *
   * @param string|\Drupal\Core\Url $path
   *   The path as passed to static::drupalGet().
   * @param array $messages
   *   The ChromeDriver performance log messages.
   */
  protected function openTelemetryTracing($path, array $messages): void {
    // Open telemetry timestamps are always in nanoseconds.
    $collector = $_ENV['OTEL_COLLECTOR'] ?? NULL;
    if ($collector === NULL) {
      return;
    }
    $timestamp = NULL;
    $dom_loaded_timestamp_page = NULL;
    $dom_loaded_timestamp_timeline = NULL;
    $timestamp_since_os_boot = NULL;
    foreach ($messages as $message) {
      // Since chrome timestamps are since OS start, we take the first network
      // request as '0' and calculate offsets against that.
      if ($timestamp === NULL && $message['method'] === 'Network.requestWillBeSent') {
        $timestamp = (int) ($message['params']['wallTime'] * static::NANOSECONDS_PER_SECOND);
        // Network timestamps are formatted as a second float with three point
        // precision. Record this so it can be compared against other
        // timestamps.
        $timestamp_since_os_boot = (int) ($message['params']['timestamp'] * static::NANOSECONDS_PER_SECOND);
      }
      // The DOM content loaded event is in both the 'page' and 'timeline'
      // sections of the performance log in different formats. This lets us
      // compare 'ts' and 'timestamp' which are not only in two different
      // formats, but appear to start from slightly different points in time.
      // By subtracting one from the other, we can generate an offset to apply
      // to all other 'ts' timestamps. Note that if the two events actually
      // happen at different times, then the offset will be wrong by that
      // difference.
      // @see https://bugs.chromium.org/p/chromium/issues/detail?id=1463436
      if ($dom_loaded_timestamp_page === NULL && $message['method'] === 'Page.domContentEventFired') {
        $dom_loaded_timestamp_page = $message['params']['timestamp'] * static::NANOSECONDS_PER_SECOND;
      }
      if ($dom_loaded_timestamp_timeline === NULL && $message['method'] === 'Tracing.dataCollected' && isset($message['params']['args']['data']['type']) && $message['params']['args']['data']['type'] === 'DOMContentLoaded') {
        $dom_loaded_timestamp_timeline = $message['params']['ts'] * static::NANOSECONDS_PER_MICROSECOND;
      }
    }

    $offset = $dom_loaded_timestamp_page - $dom_loaded_timestamp_timeline;
    $entry = $this->getSession()->evaluateScript("window.performance.getEntriesByType('navigation')")[0];
    $first_request_timestamp = $entry['requestStart'] * static::NANOSECONDS_PER_MILLISECOND;
    $first_response_timestamp = $entry['responseStart'] * static::NANOSECONDS_PER_MILLISECOND;

    $time_to_first_byte = $entry['responseStart'] - $entry['requestStart'];

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
      ->setAttribute('http.url', $path)
      ->setSpanKind(SpanKind::KIND_SERVER)
      ->startSpan();
    $last_timestamp = $first_byte_timestamp = (int) ($timestamp + ($first_response_timestamp - $first_request_timestamp));

    try {
      $first_request_timestamp = NULL;
      $scope = $span->activate();
      $first_byte_span = $tracer->spanBuilder('firstByte')
        ->setStartTimestamp($timestamp)
        ->setAttribute('http.url', $path)
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
            // @see https://bugs.chromium.org/p/chromium/issues/detail?id=1463436
            $fcp_timestamp = ($message['params']['ts'] * static::NANOSECONDS_PER_MICROSECOND) + $offset;
            $fcp_span = $tracer->spanBuilder('firstContentfulPaint')
              ->setStartTimestamp($timestamp)
              ->setAttribute('http.url', $path)
              ->startSpan();
            $last_timestamp = $first_contentful_paint_timestamp = (int) ($timestamp + ($fcp_timestamp - $timestamp_since_os_boot));
            $fcp_span->end($first_contentful_paint_timestamp);
          }
        }

        // There can be multiple largestContentfulPaint candidates, remember
        // the largest one.
        if ($message['method'] === 'Tracing.dataCollected' && $message['params']['name'] === 'largestContentfulPaint::Candidate' && $message['params']['args']['data']['size'] > $lcp_size) {
          $lcp_timestamp = ($message['params']['ts'] * static::NANOSECONDS_PER_MICROSECOND) + $offset;
          $lcp_size = $message['params']['args']['data']['size'];
        }
      }
      if (isset($lcp_timestamp)) {
        $lcp_span = $tracer->spanBuilder('largestContentfulPaint')
          ->setStartTimestamp($timestamp)
          ->setAttribute('http.url', $path)
          ->startSpan();
        $last_timestamp = $largest_contentful_paint_timestamp = (int) ($timestamp + ($lcp_timestamp - $timestamp_since_os_boot));
        $lcp_span->setAttribute('lcp.size', $lcp_size);
        $lcp_span->end($largest_contentful_paint_timestamp);
      }
    }
    finally {
      if (isset($scope)) {
        $scope->detach();
      }
      $span->end($last_timestamp);
      $tracerProvider->shutdown();
    }
  }

}
