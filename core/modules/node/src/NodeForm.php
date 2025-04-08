<?php

namespace Drupal\node;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\node\Form\NodeForm as BaseNodeForm;

/**
 * Form handler for the node edit forms.
 *
 * @internal
 *
 * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0.
 * Use \Drupal\node\Form\NodeForm instead.
 *
 * @see https://www.drupal.org/node/3517871
 */
class NodeForm extends BaseNodeForm {

  /**
   * Constructs a new NodeForm instance.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, PrivateTempStoreFactory $temp_store_factory, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, AccountInterface $current_user, DateFormatterInterface $date_formatter) {
    @trigger_error(__CLASS__ . ' is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. Use \Drupal\node\Form\NodeForm instead. See https://www.drupal.org/node/3517871', E_USER_DEPRECATED);
    parent::__construct($entity_repository, $temp_store_factory, $entity_type_bundle_info, $time, $current_user, $date_formatter);
  }

}
