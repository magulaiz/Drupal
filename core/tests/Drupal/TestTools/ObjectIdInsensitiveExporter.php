<?php

declare(strict_types = 1);

namespace Drupal\TestTools;

use SebastianBergmann\Exporter\Exporter;

/**
 * Special exporter that does not care about object ids.
 */
class ObjectIdInsensitiveExporter extends Exporter {

  /**
   * Map of object ids.
   *
   * This should only ever be filled in a cloned version of the exporter.
   *
   * Normally all of this would happen in the $processed context object, but
   * we cannot easily add new properties to that.
   *
   * @var array<string, int>
   */
  private array $ids = [];

  /**
   * {@inheritdoc}
   */
  public function export($value, $indentation = 0) {
    // Call parent method, but on a clone, to prevent side effects on the
    // original object.
    return (parent::export(...))->bindTo(clone $this)($value, $indentation);
  }

  /**
   * {@inheritdoc}
   */
  protected function recursiveExport(&$value, $indentation, $processed = NULL) {
    $result = parent::recursiveExport($value, $indentation, $processed);
    if (!is_object($value)) {
      return $result;
    }
    $class = get_class($value);
    $hash = spl_object_hash($value);
    $start = sprintf('%s Object &%s', $class, $hash);
    if (!str_starts_with($result, $start)) {
      return $result;
    }
    $id = (string) ($this->ids[$hash] ??= count($this->ids));
    // Replace first occurrence.
    return implode($id, explode($hash, $result, 2));
  }

}
