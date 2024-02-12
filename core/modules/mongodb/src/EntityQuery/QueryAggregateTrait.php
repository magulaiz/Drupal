<?php

namespace Drupal\mongodb\EntityQuery;

/**
 * Provides a trait for the SQL storage entity query aggregate class.
 */
trait QueryAggregateTrait {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return $this
      ->prepare()
      ->addAggregate()
      ->compile()
      ->compileAggregate()
      ->addGroupBy()
      ->addSort()
      ->addSortAggregate()
      ->finish()
      ->result();
  }

  /**
   * {@inheritdoc}
   */
  public function conditionAggregateGroupFactory($conjunction = 'AND') {
    $class = static::getClass($this->namespaces, 'ConditionAggregate');
    return new $class($conjunction, $this, $this->namespaces);
  }

  /**
   * {@inheritdoc}
   */
  public function existsAggregate($field, $function, $langcode = NULL) {
    return $this->conditionAggregate->exists($field, $function, $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function notExistsAggregate($field, $function, $langcode = NULL) {
    return $this->conditionAggregate->notExists($field, $function, $langcode);
  }

}
