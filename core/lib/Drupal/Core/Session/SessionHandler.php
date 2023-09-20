<?php

namespace Drupal\Core\Session;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Utility\Error;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

/**
 * Default session handler.
 */
class SessionHandler extends AbstractSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface {

  use DependencySerializationTrait;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  private TimeInterface $time;

  /**
   * Constructs a new SessionHandler instance.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(RequestStack $request_stack, Connection $connection) {
    $this->requestStack = $request_stack;
    $this->connection = $connection;
    $this->time = \Drupal::time();
  }

  /**
   * {@inheritdoc}
   */
  public function open(string $save_path, string $name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function close(): bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  protected function doRead(string $sessionId): string {
    if (empty($sessionId)) {
      return '';
    }
    $query = $this->connection
      ->queryRange('SELECT [session] FROM {sessions} WHERE [sid] = :sid', 0, 1, [
        ':sid' => Crypt::hashBase64($sessionId),
      ]);
    return (string) $query->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  protected function doWrite(string $sessionId, string $data): bool {
    // The exception handler is not active at this point, so we need to do it
    // manually.
    try {
      $request = $this->requestStack->getCurrentRequest();
      $fields = [
        'uid' => $request->getSession()->get('uid', 0),
        'hostname' => $request->getClientIP(),
        'session' => $data,
        'timestamp' => $this->time->getRequestTime(),
      ];
      $this->connection->merge('sessions')
        ->keys(['sid' => Crypt::hashBase64($sessionId)])
        ->fields($fields)
        ->execute();
      return TRUE;
    }
    catch (\Exception $exception) {
      require_once DRUPAL_ROOT . '/core/includes/errors.inc';
      // If we are displaying errors, then do so with no possibility of a
      // further uncaught exception being thrown.
      if (error_displayable()) {
        print '<h1>Uncaught exception thrown in session handler.</h1>';
        print '<p>' . Error::renderExceptionSafe($exception) . '</p><hr />';
      }
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function destroy(string $sessionId): bool {
    return $this->doDestroy($sessionId);
  }

  /**
   * {@inheritdoc}
   */
  protected function doDestroy(string $sessionId): bool {
    // Delete session data.
    $this->connection->delete('sessions')
      ->condition('sid', Crypt::hashBase64($sessionId))
      ->execute();

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function gc($lifetime) {
    // Be sure to adjust 'php_value session.gc_maxlifetime' to a large enough
    // value. For example, if you want user sessions to stay in your database
    // for three weeks before deleting them, you need to set gc_maxlifetime
    // to '1814400'. At that value, only after a user doesn't log in after
    // three weeks (1814400 seconds) will their session be removed.
    $this->connection->delete('sessions')
      ->condition('timestamp', $this->time->getRequestTime() - $lifetime, '<')
      ->execute();
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function updateTimestamp($id, $data): bool {
    // This seems to be correct per the documentation, but I'm not sure.
    $this->write($id, $data);
    return TRUE;
  }

}
