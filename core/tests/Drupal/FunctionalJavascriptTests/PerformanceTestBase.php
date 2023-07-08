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
  const NANOSECONDS_PER_SECOND = 1000000000;

  /**
   * The number of nanoseconds in a second.
   *
   * @var int
   */
  const NANOSECONDS_PER_MILLISECOND = 1000000;

  /**
   * The number of nanoseconds in a second.
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
    $session = $this->getSession();
    $performance_log = $session->getDriver()->getWebDriverSession()->log('performance');

    $messages = [];
    foreach ($performance_log as $entry) {
      $decoded = json_decode($entry['message'], TRUE);
      $messages[] = $decoded['message'];
    }
    $this->collectNetworkData($path, $messages);
    $this->openTelemetryTracing($path, $messages);
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
   * Send metrics to OpenTelemetry.
   *
   * @param string|\Drupal\Core\Url $path
   *   The path as passed to static::drupalGet().
   * @param array $messages
   *   The ChromeDriver performance log messages.
   */
  protected function openTelemetryTracing($path, array $messages): void {
    // Open telemetry timestamps are always in nanoseconds.
    $timestamp = (int) (\Drupal::service('datetime.time')->getCurrentMicroTime() * static::NANOSECONDS_PER_SECOND);
    $collector = $_ENV['OTEL_COLLECTOR'] ?? NULL;
    if ($collector === NULL) {
      return;
    }

    $entry = $this->getSession()->evaluateScript("window.performance.getEntriesByType('navigation')")[0];
    $first_request_timestamp = $entry['requestStart'] * static::NANOSECONDS_PER_MILLISECOND;
    $first_response_timestamp = $entry['responseStart'] * static::NANOSECONDS_PER_MILLISECOND;

    $time_to_first_byte = $entry['responseStart'] - $entry['requestStart'];

    $router = \Drupal::service('router.no_access_checks');
    $route_provider = \Drupal::service('router.route_provider');
    $route_path = $path;
    if ($path instanceof Url && $path->isRouted()) {
      $route_name = $path->getRouteName();
      $route = $route_provider->getRouteByName($route_name);
      $route_path = $route->getPath();
    }
    else {
      if ($path instanceof Url) {
        $path = $path->getInternalPath();
      }
      try {
        $match = $router->match($path);
        $route = $route_provider->getRouteByName($match[0]);
        $route_path = $route->getPath();
      }
      catch (\Exception $e) {
      }
    }

    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    // Most of the time the function will be the test method calling
    // static::drupalGet() but sometimes it will be helper methods such as
    // static::drupalLogin().
    $service_name = str_replace('\\', '_', $backtrace[3]['class']) . '_' . $backtrace[3]['function'];

    // @todo: consider setting up the resource using environment variables
    // OTEL_SERVICE_NAME, OTEL_RESOURCE_ATTRIBUTES
    // See https://opentelemetry.io/docs/instrumentation/php/resources/
    $resource = ResourceInfoFactory::merge(ResourceInfo::create(Attributes::create([
      ResourceAttributes::SERVICE_NAMESPACE => 'Drupal',
      ResourceAttributes::SERVICE_NAME => $service_name,
      ResourceAttributes::SERVICE_INSTANCE_ID => 1,
      ResourceAttributes::SERVICE_VERSION => \Drupal::VERSION,
      ResourceAttributes::DEPLOYMENT_ENVIRONMENT => 'local',
    ])), ResourceInfoFactory::defaultResource());

    $transport = (new OtlpHttpTransportFactory())->create($collector, 'application/x-protobuf');
    $exporter = new SpanExporter($transport);
    $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter), NULL, $resource);
    $tracer = $tracerProvider->getTracer('Drupal');

    $span = $tracer->spanBuilder('GET ' . $route_path)
      ->setStartTimestamp($timestamp)
      ->setAttribute('http.method', 'GET')
      ->setAttribute('http.url', $path)
      ->setSpanKind(SpanKind::KIND_SERVER)
      ->startSpan();
    $last_timestamp = $first_byte_timestamp = (int) ($timestamp + ($first_response_timestamp - $first_request_timestamp));

    try {
      $first_request_timestamp = NULL;
      $scope = $span->activate();
      $first_byte_span = $tracer->spanBuilder('First Byte')
        ->setStartTimestamp($timestamp)
        ->setAttribute('http.url', $path)
        ->startSpan();
      $first_byte_span->end($first_byte_timestamp);
      if (isset($entry['domContentLoadedEventStart'])) {
        $dom_span = $tracer->spanBuilder('domContentLoadedEventStart')
          ->setStartTimestamp($timestamp)
          ->setAttribute('http.url', $path)
          ->startSpan();
        $dom_timestamp = $entry['domContentLoadedEventStart'] * static::NANOSECONDS_PER_MILLISECOND;
        $last_timestamp = $dom_content_loaded_timestamp = (int) ($timestamp + ($dom_timestamp - $first_request_timestamp));
        $dom_span->end($dom_content_loaded_timestamp);
      }
      // Largest contentful paint is not available from
      // window.performance::getEntriesByType() so use the performance log
      // messages to get it instead.
      $lcp_timestamp = NULL;
      $message_first_request_timestamp = NULL;
      foreach ($messages as $message) {
        // Since chrome timestamps are since OS start, we take the first network
        // request as '0' and calculate offsets against that.
        if ($message_first_request_timestamp === NULL && $message['method'] === 'Network.requestWillBeSent') {
          $message_first_request_timestamp = (int) ($message['params']['timestamp'] * static::NANOSECONDS_PER_SECOND);
        }
        // There can be multiple largestContentfulPaint candidates so just keep
        // overriding if there is more than one.
        if ($message['method'] === 'Tracing.dataCollected' && $message['params']['name'] === 'largestContentfulPaint::Candidate') {
          $lcp_timestamp = $message['params']['ts'] * static::NANOSECONDS_PER_MICROSECOND;
        }
      }
      if (isset($lcp_timestamp)) {
        $lcp_span = $tracer->spanBuilder('largestContentfulPaint')
          ->setStartTimestamp($timestamp)
          ->setAttribute('http.url', $path)
          ->startSpan();
        $last_timestamp = $largest_contentful_paint_timestamp = (int) ($timestamp + ($lcp_timestamp - $message_first_request_timestamp));
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
