<?php

namespace Drupal\mongodb\ViewsData;

/**
 * The MongoDB implementation of \Drupal\aggregator\AggregatorItemViewsData.
 */
class AggregatorItemViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['aggregator_item']['table']['base']['help'] = t('Aggregator items are imported from external RSS and Atom news feeds.');

    $data['aggregator_item']['iid']['help'] = t('The unique ID of the aggregator item.');
    $data['aggregator_item']['iid']['argument']['id'] = 'aggregator_iid';
    $data['aggregator_item']['iid']['argument']['name field'] = 'title';
    $data['aggregator_item']['iid']['argument']['numeric'] = TRUE;

    $data['aggregator_item']['title']['help'] = t('The title of the aggregator item.');
    $data['aggregator_item']['title']['field']['default_formatter'] = 'aggregator_title';

    $data['aggregator_item']['link']['help'] = t('The link to the original source URL of the item.');

    $data['aggregator_item']['author']['help'] = t('The author of the original imported item.');

    $data['aggregator_item']['author']['field']['default_formatter'] = 'aggregator_xss';

    $data['aggregator_item']['guid']['help'] = t('The guid of the original imported item.');

    $data['aggregator_item']['description']['help'] = t('The actual content of the imported item.');
    $data['aggregator_item']['description']['field']['default_formatter'] = 'aggregator_xss';
    $data['aggregator_item']['description']['field']['click sortable'] = FALSE;

    $data['aggregator_item']['timestamp']['help'] = t('The date the original feed item was posted. (With some feeds, this will be the date it was imported.)');

    return $data;
  }

}
