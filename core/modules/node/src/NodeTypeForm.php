<?php

namespace Drupal\node;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\node\Form\NodeTypeForm as BaseNodeTypeForm;

/**
 * Form handler for node type forms.
 *
 * @internal
 *
 * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0.
 *   Use \Drupal\node\Form\NodeTypeForm instead.
 *
 * @see https://www.drupal.org/node/3517871
 */
class NodeTypeForm extends BaseNodeTypeForm {

  /**
   * Constructs a new NodeTypeForm instance.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    @trigger_error(__CLASS__ . ' is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use \Drupal\node\Form\NodeTypeForm instead. See https://www.drupal.org/node/3517871', E_USER_DEPRECATED);
    parent::__construct($entity_field_manager);
  }

}
