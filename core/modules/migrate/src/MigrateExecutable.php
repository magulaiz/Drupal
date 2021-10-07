<?php

namespace Drupal\migrate;

use Drupal\Core\Utility\Error;
use Drupal\Component\Utility\Bytes;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Event\MigrateRollbackEvent;
use Drupal\migrate\Event\MigrateRowDeleteEvent;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Plugin\MigrateSourceInterface;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Exception\SourceRewindException;
use Drupal\migrate\Exception\MigrationBusyException;
use Drupal\migrate\Exception\MemoryExhaustionException;
use Drupal\migrate\Exception\MigrationStoppedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Defines a migrate executable class.
 */
class MigrateExecutable implements MigrateExecutableInterface {
  use StringTranslationTrait;

  /**
   * The configuration of the migration to do.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * Status of one row.
   *
   * The value is a MigrateIdMapInterface::STATUS_* constant, for example:
   * STATUS_IMPORTED.
   *
   * @var int
   */
  protected $sourceRowStatus;

  /**
   * The ratio of the memory limit at which an operation will be interrupted.
   *
   * @var float
   */
  protected $memoryThreshold = 0.85;

  /**
   * The PHP memory_limit expressed in bytes.
   *
   * @var int
   */
  protected $memoryLimit;

  /**
   * The configuration values of the source.
   *
   * @var array
   */
  protected $sourceIdValues;

  /**
   * An array of counts. Initially used for cache hit/miss tracking.
   *
   * @var array
   */
  protected $counts = [];

  /**
   * The source.
   *
   * @var \Drupal\migrate\Plugin\MigrateSourceInterface
   */
  protected $source;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Migration message service.
   *
   * @todo https://www.drupal.org/node/2822663 Make this protected.
   *
   * @var \Drupal\migrate\MigrateMessageInterface
   */
  public $message;

  /**
   * Constructs a MigrateExecutable and verifies and sets the memory limit.
   *
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The migration to run.
   * @param \Drupal\migrate\MigrateMessageInterface $message
   *   (optional) The migrate message service.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   (optional) The event dispatcher.
   */
  public function __construct(MigrationInterface $migration, MigrateMessageInterface $message = NULL, EventDispatcherInterface $event_dispatcher = NULL) {
    $this->migration = $migration;
    $this->eventDispatcher = $event_dispatcher;
    $this->message = $message ?? new MigrateMessage();
    $this->getIdMap()->setMessage($this->message);
    $this->setMemoryLimit();
  }

  /**
   * Sets memoryLimit to the given memory limit expressed in Bytes.
   * Defaults to PHP_INT_MAX.
   */
  protected function setMemoryLimit() {
    $limit = $this->getMemoryLimit();
    $this->memoryLimit = $limit == '-1' ? PHP_INT_MAX : Bytes::toNumber($limit);
  }

  /**
   * Gets the memory limit from ini.
   */
  protected function getMemoryLimit() {
    return trim(ini_get('memory_limit'));
  }

  /**
   * Returns the source.
   *
   * Makes sure source is initialized based on migration settings.
   *
   * @return \Drupal\migrate\Plugin\MigrateSourceInterface
   *   The source.
   */
  protected function getSource() {
    if (!isset($this->source)) {
      $this->source = $this->migration->getSourcePlugin();
    }
    return $this->source;
  }

  /**
   * Gets the event dispatcher.
   *
   * @return \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected function getEventDispatcher() {
    if (!$this->eventDispatcher) {
      $this->eventDispatcher = \Drupal::service('event_dispatcher');
    }
    return $this->eventDispatcher;
  }

  /**
   * Only begin the import operation if the migration is currently idle.
   */
  protected function checkIfMigrationIsBusy() {
    if ($this->migration->getStatus() !== MigrationInterface::STATUS_IDLE) {
      throw new MigrationBusyException();
    }
  }

  /**
   * Asks the event dispatcher to dispatch a MigrateImportEvent.
   *
   * @param int $eventValue
   *   MigrateEvents constant value.
   */
  protected function dispatchMigrationEvent(int $eventValue) {
    $event = new MigrateImportEvent($this->migration, $this->message);
    $this->getEventDispatcher()->dispatch($event, $eventValue);
  }

  /**
   * Tries to rewind the source.
   *
   * @throws Drupal\migrate\Exception\SourceRewindException
   */
  protected function prepareSource(MigrateSourceInterface $source) {
    try {
      $source->rewind();
    }
    catch (\Exception $exception) {
      throw new SourceRewindException();
    }
  }

  /**
   * Sets some used variables before launching the migration.
   */
  private function prepareNeededVariables() {
    $this->importSource = $this->getSource();
    $this->importIdMap = $this->getIdMap();
    $this->importDestination = $this->migration->getDestinationPlugin();
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    try {
      $this->checkIfMigrationIsBusy();
      $this->dispatchMigrationEvent(MigrateEvents::PRE_IMPORT);
      $this->migration->checkRequirements();
      $this->migration->setStatus(MigrationInterface::STATUS_IMPORTING);
      $this->prepareNeededVariables();
      $this->prepareSource($this->importSource);
      $result = $this->doMigrationImport();
      $this->dispatchMigrationEvent(MigrateEvents::POST_IMPORT);
      $this->migration->setStatus(MigrationInterface::STATUS_IDLE);
      return $result;
    }
    catch (MigrationBusyException $busyException) {
      $migrationId = $this->migration->id();
      $migrationStatus = $this->migration->getStatusLabel();
      $translatedArguments = ["@id" => $migrationId, "@status" => t($migrationStatus)];
      $translatedMessage = $this->t('Migration @id is busy with another operation: @status', $translatedArguments);
      $this->message->display($translatedMessage, 'error');
    }
    catch (RequirementsException $requirementsException) {
      $migrationId = $this->migration->id();
      $message = $requirementsException->getMessage();
      $requirements = $requirementsException->getRequirementsString();
      $arguments = ['@id' => $migrationId, '@message' => $message, '@requirements' => $requirements];
      $translatedMessage = $this->t('Migration @id did not meet the requirements. @message @requirements', $arguments);
      $this->message->display($translatedMessage, 'error');
    }
    catch (SourceRewindException $rewindException) {
      $message = $rewindException->getMessage();
      $file = $rewindException->getFile();
      $line = $rewindException->getLine();
      $arguments = ["@e" => $message, "@file" => $file, "@line" => $line];
      $translatedMessage = $this->t('Migration failed with source plugin exception: @e in @file line @line', $arguments);
      $this->message->display($translatedMessage, 'error');
      $this->migration->setStatus(MigrationInterface::STATUS_IDLE);
    }
    catch (\Exception $exception) {
      $message = $exception->getMessage();
      $file = $exception->getFile();
      $line = $exception->getLine();
      $arguments = ['@e' => $message, '@file' => $file, '@line' => $line];
      $translatedMessage = $this->t('Migration failed with source plugin exception: @e in @file line @line', $arguments);
      $this->message->display($translatedMessage, 'error');
      $this->migration->setStatus(MigrationInterface::STATUS_IDLE);
    }
    return MigrationInterface::RESULT_FAILED;
  }

  /**
   * ETL Process. Gets the row, processes the row then save the row.
   */
  protected function processImportRow() {
    try {
      $this->currentProcessedRow = $this->importSource->current();
      $this->sourceIdValues = $row->getSourceIdValues();
      $this->processRow($row);
      $this->saveCurrentProcessedRow = TRUE;
    }
    catch (MigrateSkipRowException $migrateSkipRowException) {
      if ($migrateSkipRowException->getSaveToMap()) {
        $this->importMigrateMap->saveIdMapping($this->currentProcessedRow, [], MigrateIdMapInterface::STATUS_IGNORED);
      }
      if ($message = trim($migrateSkipRowException->getMessage())) {
        $this->saveMessage($message, MigrationInterface::MESSAGE_INFORMATIONAL);
      }
      $this->saveCurrentProcessedRow = FALSE;
    }
    catch (MigrateException $migrateException) {
      $this->getIdMap()->saveIdMapping($row, [], $migrateException->getStatus());
      $this->saveMessage($migrateException->getMessage(), $migrateException->getLevel());
      $this->saveCurrentProcessedRow = FALSE;
    }
  }

  /**
   * Asks the event dispatcher to dispatch a MigratePreRowSaveEvent.
   *
   * @param int $eventValue
   *   MigrateEvents constant.
   */
  protected function dispatchMigrationPreRowSaveEvent(int $eventValue) {
    $event = new MigratePreRowSaveEvent($this->migration, $this->message, $this->currentProcessedRow);
    $this->getEventDispatcher()->dispatch($event, $eventValue);
  }

  /**
   * Asks the event dispatcher to dispatch a MigratePostRowSaveEvent.
   *
   * @param int $eventValue
   *   MigrateEvents constant.
   * @param array $destination_id_values
   *   The destination ids returned by the import when successful.
   */
  protected function dispatchMigrationPostRowSaveEvent(int $eventValue, $destination_id_values) {
    $event = new MigratePostRowSaveEvent($this->migration, $this->message, $this->currentProcessedRow, $destination_id_values);
    $this->getEventDispatcher()->dispatch($event, $eventValue);
  }

  /**
   * Saves the currently processed row : pre-row save event, destination import, post-row save event.
   */
  protected function saveImportRow() {
    try {
      if (!$this->saveCurrentProcessedRow) {
        return;
      }
      $this->dispatchMigrationPreRowSaveEvent(MigrateEvents::PRE_ROW_SAVE);
      $destination_ids = $this->importIdMap->lookupDestinationIds($this->sourceIdValues);
      $destination_id_values = $destination_ids ? reset($destination_ids) : [];
      $destination_id_values = $destination->import($this->currentProcessedRow, $destination_id_values);
      $this->dispatchMigrationPostRowSaveEvent(MigrateEvents::POST_ROW_SAVE, $destination_id_values);
      if ($destination_id_values) {
        // We do not save an idMap entry for config.
        if ($destination_id_values !== TRUE) {
          $this->importIdMap->saveIdMapping($this->currentProcessedRow, $destination_id_values, $this->sourceRowStatus, $this->importDestination->rollbackAction());
        }
      }
      else {
        $this->importIdMap->saveIdMapping($this->currentProcessedRow, [], MigrateIdMapInterface::STATUS_FAILED);
        if (!$this->importIdMap->messageCount()) {
          $message = $this->t('New object was not saved, no error provided');
          $this->saveMessage($message);
          $this->message->display($message);
        }
      }
    }
    catch (MigrateException $e) {
      $this->getIdMap()->saveIdMapping($this->currentProcessedRow, [], $e->getStatus());
      $this->saveMessage($e->getMessage(), $e->getLevel());
    }
    catch (\Exception $e) {
      $this->getIdMap()->saveIdMapping($this->currentProcessedRow, [], MigrateIdMapInterface::STATUS_FAILED);
      $this->handleException($e);
    }
  }

  /**
   * Finalizes the migration row import by setting row status to imported,
   * checking for memory exhaustion and checking if stop migration was requested.
   * It then passes onto the next row.
   */
  protected function resolveImportRow() {
    $this->sourceRowStatus = MigrateIdMapInterface::STATUS_IMPORTED;

    // Check for memory exhaustion.
    if (($return = $this->checkStatus()) != MigrationInterface::RESULT_COMPLETED) {
      throw new MemoryExhaustionException();
    }

    // If anyone has requested we stop, return the requested result.
    if ($this->migration->getStatus() == MigrationInterface::STATUS_STOPPING) {
      throw new MigrationStoppedException();
    }

    $this->importSource->next();
  }

  /**
   * Runs the import : process, save and resolve.
   */
  protected function importRow() {
    $this->processImportRow();
    $this->saveImportRow();
    $this->resolveImportRow();
  }

  /**
   * Executes the migration import : while source is valid, import source rows.
   */
  protected function doMigrationImport() : int {
    try {
      while ($source->valid()) {
        $this->importRow();
      }
      return MigrationInterface::RESULT_COMPLETED;
    }
    catch (MemoryExhaustionException $memExhaustException) {
      return MigrationInterface::RESULT_COMPLETED;
    }
    catch (MigrationStoppedException $migrationStoppedException) {
      $return = $this->migration->getInterruptionResult();
      $this->migration->clearInterruptionResult();
      return $return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rollback() {
    // Only begin the rollback operation if the migration is currently idle.
    if ($this->migration->getStatus() !== MigrationInterface::STATUS_IDLE) {
      $this->message->display($this->t('Migration @id is busy with another operation: @status', ['@id' => $this->migration->id(), '@status' => $this->t($this->migration->getStatusLabel())]), 'error');
      return MigrationInterface::RESULT_FAILED;
    }

    // Announce that rollback is about to happen.
    $this->getEventDispatcher()->dispatch(new MigrateRollbackEvent($this->migration), MigrateEvents::PRE_ROLLBACK);

    // Optimistically assume things are going to work out; if not, $return will be
    // updated to some other status.
    $return = MigrationInterface::RESULT_COMPLETED;

    $this->migration->setStatus(MigrationInterface::STATUS_ROLLING_BACK);
    $id_map = $this->getIdMap();
    $destination = $this->migration->getDestinationPlugin();

    // Loop through each row in the map, and try to roll it back.
    $id_map->rewind();
    while ($id_map->valid()) {
      $destination_key = $id_map->currentDestination();
      if ($destination_key) {
        $map_row = $id_map->getRowByDestination($destination_key);
        if ($map_row['rollback_action'] == MigrateIdMapInterface::ROLLBACK_DELETE) {
          $this->getEventDispatcher()
            ->dispatch(new MigrateRowDeleteEvent($this->migration, $destination_key), MigrateEvents::PRE_ROW_DELETE);
          $destination->rollback($destination_key);
          $this->getEventDispatcher()
            ->dispatch(new MigrateRowDeleteEvent($this->migration, $destination_key), MigrateEvents::POST_ROW_DELETE);
        }
        // We're now done with this row, so remove it from the map.
        $id_map->deleteDestination($destination_key);
      }
      else {
        // If there is no destination key the import probably failed and we can
        // remove the row without further action.
        $source_key = $id_map->currentSource();
        $id_map->delete($source_key);
      }
      $id_map->next();

      // Check for memory exhaustion.
      if (($return = $this->checkStatus()) != MigrationInterface::RESULT_COMPLETED) {
        break;
      }

      // If anyone has requested we stop, return the requested result.
      if ($this->migration->getStatus() == MigrationInterface::STATUS_STOPPING) {
        $return = $this->migration->getInterruptionResult();
        $this->migration->clearInterruptionResult();
        break;
      }
    }

    // Notify modules that rollback attempt was complete.
    $this->getEventDispatcher()->dispatch(new MigrateRollbackEvent($this->migration), MigrateEvents::POST_ROLLBACK);
    $this->migration->setStatus(MigrationInterface::STATUS_IDLE);

    return $return;
  }

  /**
   * Get the ID map from the current migration.
   *
   * @return \Drupal\migrate\Plugin\MigrateIdMapInterface
   *   The ID map.
   */
  protected function getIdMap() {
    return $this->migration->getIdMap();
  }

  /**
   * {@inheritdoc}
   */
  public function processRow(Row $row, array $process = NULL, $value = NULL) {
    foreach ($this->migration->getProcessPlugins($process) as $destination => $plugins) {
      $multiple = FALSE;
      /**
       * @var \Drupal\migrate\Plugin\MigrateProcessInterface $plugin
      */
      foreach ($plugins as $plugin) {
        $definition = $plugin->getPluginDefinition();
        // Many plugins expect a scalar value but the current value of the
        // pipeline might be multiple scalars (this is set by the previous
        // plugin) and in this case the current value needs to be iterated
        // and each scalar separately transformed.
        if ($multiple && !$definition['handle_multiples']) {
          $new_value = [];
          if (!is_array($value)) {
            throw new MigrateException(sprintf('Pipeline failed at %s plugin for destination %s: %s received instead of an array,', $plugin->getPluginId(), $destination, $value));
          }
          $break = FALSE;
          foreach ($value as $scalar_value) {
            try {
              $new_value[] = $plugin->transform($scalar_value, $this, $row, $destination);
            }
            catch (MigrateSkipProcessException $e) {
              $new_value[] = NULL;
              $break = TRUE;
            }
          }
          $value = $new_value;
          if ($break) {
            break;
          }
        }
        else {
          try {
            $value = $plugin->transform($value, $this, $row, $destination);
          }
          catch (MigrateSkipProcessException $e) {
            $value = NULL;
            break;
          }
          $multiple = $plugin->multiple();
        }
      }
      // Ensure all values, including nulls, are migrated.
      if ($plugins) {
        if (isset($value)) {
          $row->setDestinationProperty($destination, $value);
        }
        else {
          $row->setEmptyDestinationProperty($destination);
        }
      }
      // Reset the value.
      $value = NULL;
    }
  }

  /**
   * Fetches the key array for the current source record.
   *
   * @return array
   *   The current source IDs.
   */
  protected function currentSourceIds() {
    return $this->getSource()->getCurrentIds();
  }

  /**
   * {@inheritdoc}
   */
  public function saveMessage($message, $level = MigrationInterface::MESSAGE_ERROR) {
    $this->getIdMap()->saveMessage($this->sourceIdValues, $message, $level);
  }

  /**
   * Takes an Exception object and both saves and displays it.
   *
   * Pulls in additional information on the location triggering the exception.
   *
   * @param \Exception $exception
   *   Object representing the exception.
   * @param bool $save
   *   (optional) Whether to save the message in the migration's mapping table.
   *   Set to FALSE in contexts where this doesn't make sense.
   */
  protected function handleException(\Exception $exception, $save = TRUE) {
    $result = Error::decodeException($exception);
    $message = $result['@message'] . ' (' . $result['%file'] . ':' . $result['%line'] . ')';
    if ($save) {
      $this->saveMessage($message);
    }
    $this->message->display($message, 'error');
  }

  /**
   * Checks for exceptional conditions, and display feedback.
   */
  protected function checkStatus() {
    if ($this->memoryExceeded()) {
      return MigrationInterface::RESULT_INCOMPLETE;
    }
    return MigrationInterface::RESULT_COMPLETED;
  }

  /**
   * Tests whether we've exceeded the desired memory threshold.
   *
   * If so, output a message.
   *
   * @return bool
   *   TRUE if the threshold is exceeded, otherwise FALSE.
   */
  protected function memoryExceeded() {
    $usage = $this->getMemoryUsage();
    $pct_memory = $usage / $this->memoryLimit;
    if (!$threshold = $this->memoryThreshold) {
      return FALSE;
    }
    if ($pct_memory > $threshold) {
      $this->message->display(
            $this->t(
                'Memory usage is @usage (@pct% of limit @limit), reclaiming memory.',
                [
                  '@pct' => round($pct_memory * 100),
                  '@usage' => $this->formatSize($usage),
                  '@limit' => $this->formatSize($this->memoryLimit),
                ]
            ),
            'warning'
        );
      $usage = $this->attemptMemoryReclaim();
      $pct_memory = $usage / $this->memoryLimit;
      /* Use a lower threshold - we don't want to be in a situation where we keep
      coming back here and trimming a tiny amount */
      if ($pct_memory > (0.90 * $threshold)) {
        $this->message->display(
          $this->t(
              'Memory usage is now @usage (@pct% of limit @limit), not enough reclaimed, starting new batch',
              [
                '@pct' => round($pct_memory * 100),
                '@usage' => $this->formatSize($usage),
                '@limit' => $this->formatSize($this->memoryLimit),
              ]
          ),
          'warning'
          );
        return TRUE;
      }
      else {
        $this->message->display(
          $this->t(
              'Memory usage is now @usage (@pct% of limit @limit), reclaimed enough, continuing',
              [
                '@pct' => round($pct_memory * 100),
                '@usage' => $this->formatSize($usage),
                '@limit' => $this->formatSize($this->memoryLimit),
              ]
          ),
          'warning'
          );
        return FALSE;
      }
    }
    else {
      return FALSE;
    }
  }

  /**
   * Returns the memory usage so far.
   *
   * @return int
   *   The memory usage.
   */
  protected function getMemoryUsage() {
    return memory_get_usage();
  }

  /**
   * Tries to reclaim memory.
   *
   * @return int
   *   The memory usage after reclaim.
   */
  protected function attemptMemoryReclaim() {
    // First, try resetting Drupal's static storage - this frequently releases
    // plenty of memory to continue.
    drupal_static_reset();

    // Entity storage can blow up with caches, so clear it out.
    \Drupal::service('entity.memory_cache')->deleteAll();

    // @todo explore resetting the container.    // Run garbage collector to further reduce memory.
    gc_collect_cycles();

    return memory_get_usage();
  }

  /**
   * Generates a string representation for the given byte count.
   *
   * @param int $size
   *   A size in bytes.
   *
   * @return string
   *   A translated string representation of the size.
   */
  protected function formatSize($size) {
    return format_size($size);
  }

}
