<?php

namespace Drupal\Core\Test\HttpClientMiddleware;

use Drupal\Core\Utility\Error;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Blocks unknown external hosts and replaces the user agent for test requests.
 */
class TestHttpClientMiddleware {

  /**
   * List of external host names that tests can make HTTP requests to.
   *
   * @var string[]
   */
  protected static array $allowedHosts = ['ftp.drupal.org', 'oembed.com'];

  /**
   * Adds a host name to the allow list for the remainder of this test run.
   *
   * @param string $host
   *   The hostname to allow.
   */
  public static function allowHost(string $host): void {
    static::$allowedHosts[] = $host;
  }

  /**
   * Block unknown external hosts and replace the user agent.
   */
  public function __invoke() {
    // If the database prefix is being used to run the tests in a copied
    // database, then set the User-Agent header to the database prefix so that
    // any calls to other Drupal pages will run the test-prefixed database. The
    // user agent is used to ensure that multiple testing sessions running at
    // the same time won't interfere with each other as they would if the
    // database prefix were stored statically in a file or database variable.
    return function ($handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        if ($user_agent = drupal_generate_test_ua(drupal_valid_test_ua())) {
          $request = $request->withHeader('User-Agent', $user_agent);
        }

        // Allow specific hosts with no alterations.
        if (in_array($request->getUri()->getHost(), static::$allowedHosts, TRUE)) {
          return $handler($request, $options);
        }

        // Disallow other external hosts.
        $host = parse_url(getenv('SIMPLETEST_BASE_URL'), PHP_URL_HOST);
        if ($host !== $request->getUri()->getHost()) {
          throw new \RuntimeException(sprintf('Tests should only make requests to the SIMPLETEST_BASE_URL host of %s, but a request to %s was made.', $host, $request->getUri()->getHost()));
        }

        return $handler($request, $options)
          ->then(function (ResponseInterface $response) {
            if (!drupal_valid_test_ua()) {
              return $response;
            }
            if (!empty($response->getHeader('X-Drupal-Wait-Terminate')[0])) {
              $lock = \Drupal::lock();
              if (!$lock->acquire('test_wait_terminate')) {
                $lock->wait('test_wait_terminate');
              }
              $lock->release('test_wait_terminate');
            }
            $headers = $response->getHeaders();
            foreach ($headers as $header_name => $header_values) {
              if (preg_match('/^X-Drupal-Assertion-[0-9]+$/', $header_name, $matches)) {
                foreach ($header_values as $header_value) {
                  $parameters = unserialize(urldecode($header_value));
                  if (count($parameters) === 3) {
                    if ($parameters[1] === 'User deprecated function') {
                      // Fire the same deprecation message to allow it to be
                      // collected by
                      // \Drupal\TestTools\Extension\DeprecationBridge\DeprecationHandler::collectActualDeprecation().
                      // phpcs:ignore Drupal.Semantics.FunctionTriggerError
                      @trigger_error((string) $parameters[0], E_USER_DEPRECATED);
                    }
                    else {
                      throw new \Exception($parameters[1] . ': ' . $parameters[0] . "\n" . Error::formatBacktrace([$parameters[2]]));
                    }
                  }
                  else {
                    throw new \Exception('Error thrown with the wrong amount of parameters.');
                  }
                }
              }
            }
            return $response;
          });
      };
    };
  }

}
