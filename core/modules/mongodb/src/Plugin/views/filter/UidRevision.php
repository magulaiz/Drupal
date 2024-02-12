<?php

namespace Drupal\mongodb\Plugin\views\filter;

use Drupal\views\Views;

/**
 * Overriding the views filter plugin "node_uid_revision".
 */
class UidRevision extends UserName {

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $this->ensureMyTable();

    $placeholder = $this->placeholder() . '[]';

    $args = array_values($this->value);

    $def = [];
    $def['table'] = 'node';
    $def['field'] = 'nid';
    $def['left_table'] = 'node';
    $def['left_field'] = 'nid';
    if (!empty($args)) {
      foreach ($args as &$arg) {
        $arg = (int) $arg;
      }
      $def['extra'] = [
        [
          'field' => 'node_all_revisions.revision_uid',
          'value' => $args,
          'operator' => 'IN',
        ]
      ];
    }

    $join = Views::pluginManager('join')->createInstance('standard', $def);

    $this->alias = $this->query->addRelationship('user_revision', $join, 'node', $this->relationship);

    $condition = $this->query->getConnection()->condition('OR');
    $condition->condition('user_revision', NULL, 'IS NOT NULL'); // nid 1,2
    if (!empty($args)) {
      $condition->condition('node_current_revision.uid', $args, 'IN'); // nid 1
    }
    $this->query->addCondition($this->options['group'], $condition);
  }

}
