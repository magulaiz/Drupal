<?php

namespace Drupal\mongodb\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\StringFilter as CoreStringFilter;

/**
 * Overrides the views filter plugin "string".
 */
class StringFilter extends CoreStringFilter {

  use FilterPluginTrait;

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $field = "$this->realField";

    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function opEqual($field) {
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $this->value, $this->operator());
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opContains($field) {
    $pattern = '%' . $this->connection->escapeLike($this->value) . '%';
    $operator = $this->getConditionOperator('LIKE');
    if (strpos($field, '.') !== FALSE) {
      $placeholder = $this->placeholder();
      $this->query->addConditionField($placeholder, $field);
      $field = $placeholder;
    }
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $pattern, $operator);
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opContainsWord($field) {
    $condition = $this->operator == 'word' ? $this->query->getConnection()->condition('OR') : $this->query->getConnection()->condition('AND');

    // Don't filter on empty strings.
    if (empty($this->value)) {
      return;
    }

    preg_match_all(static::WORDS_PATTERN, ' ' . $this->value, $matches, PREG_SET_ORDER);
    $operator = $this->getConditionOperator('LIKE');
    foreach ($matches as $match) {
      $phrase = FALSE;
      // Strip off phrase quotes
      if ($match[2][0] == '"') {
        $match[2] = substr($match[2], 1, -1);
        $phrase = TRUE;
      }
      $words = trim($match[2], ',?!();:-');
      $words = $phrase ? [$words] : preg_split('/ /', $words, -1, PREG_SPLIT_NO_EMPTY);
      foreach ($words as $word) {
        $condition->condition($field, '%' . $this->connection->escapeLike(trim($word, " ,!?")) . '%', $operator);
      }
    }

    if ($condition->count() === 0) {
      return;
    }

    // previously this was a call_user_func_array but that's unnecessary
    // as views will unpack an array that is a single arg.
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opStartsWith($field) {
    $pattern = $this->connection->escapeLike($this->value) . '%';
    $operator = $this->getConditionOperator('LIKE');
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $pattern, $operator);
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opNotStartsWith($field) {
    $pattern = $this->connection->escapeLike($this->value) . '%';
    $operator = $this->getConditionOperator('NOT LIKE');
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $pattern, $operator);
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opEndsWith($field) {
    $pattern = '%' . $this->connection->escapeLike($this->value);
    $operator = $this->getConditionOperator('LIKE');
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $pattern, $operator);
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opNotEndsWith($field) {
    $pattern = '%' . $this->connection->escapeLike($this->value);
    $operator = $this->getConditionOperator('NOT LIKE');
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $pattern, $operator);
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opNotLike($field) {
    $pattern = '%' . $this->connection->escapeLike($this->value) . '%';
    $operator = $this->getConditionOperator('NOT LIKE');
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $pattern, $operator);
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opShorterThan($field) {
    $placeholder = $this->placeholder() . '_shorter_then';
    $this->query->addFieldLength($placeholder, '$' . $field);
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($placeholder, $this->value, '<');
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opLongerThan($field) {
    $placeholder = $this->placeholder() . '_longer_then';
    $this->query->addFieldLength($placeholder, '$' . $field);
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($placeholder, $this->value, '>');
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opRegex($field) {
    $operator = 'REGEXP';
    $condition = $this->query->getConnection()->condition('AND');
    $condition->condition($field, NULL, 'IS NOT NULL');
    $condition->condition($field, $this->value, $operator);
    $this->query->addCondition($this->options['group'], $condition);
  }

  /**
   * {@inheritdoc}
   */
  protected function opEmpty($field) {
    if ($this->operator == 'empty') {
      $operator = "IS NULL";
    }
    else {
      $operator = "IS NOT NULL";
    }

    $this->query->addCondition($this->options['group'], $field, NULL, $operator);
  }

}
