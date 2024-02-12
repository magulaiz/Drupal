<?php

namespace Drupal\mongodb\Plugin\views\argument;

use Drupal\comment\Plugin\views\argument\UserUid;
use Drupal\views\Views;

/**
 * Overriding the views argument plugin "argument_comment_user_uid".
 */
class CommentUserUid extends UserUid {

  use CommentUserUidTrait;

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $this->ensureMyTable();
    if ($this->table == $this->view->storage->get('base_table')) {
      $field = $this->realField;
    }
    else {
      $field = "$this->tableAlias.$this->realField";
    }
    $this->argument = (int) $this->argument;

    if ($this->table != 'comment') {
      $entity_id = $this->definition['entity_id'];
      $entity_type = $this->definition['entity_type'];

      $def = [];
      $def['table'] = 'comment';
      $def['field'] = 'entity_id';
      $def['left_table'] = $this->tableAlias;
      $def['left_field'] = $entity_id;
      $def['extra'] = [
        [
          'field' => 'entity_type',
          'value' => $entity_type,
        ]
      ];

      $join = Views::pluginManager('join')->createInstance('standard', $def);

      $this->alias = $this->query->addRelationship('comment', $join, $this->tableAlias, $this->relationship);

      $condition = ($this->view->query->getConnection()->condition('OR'))
        ->condition($field, $this->argument)
        ->condition("$this->alias.comment_translations.uid", $this->argument);

      $this->query->addCondition(0, $condition);
    }
    else {
      $this->query->addCondition(0, $field, $this->argument);
    }
  }

}
