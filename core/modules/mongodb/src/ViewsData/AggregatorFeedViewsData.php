<?php

namespace Drupal\mongodb\ViewsData;

/**
 * The MongoDB implementation of \Drupal\aggregator\AggregatorFeedViewsData.
 */
class AggregatorFeedViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['aggregator_feed']['table']['join'] = [
     'aggregator_item' => [
        'left_field' => 'fid',
        'field' => 'fid',
      ],
    ];

    $data['aggregator_feed']['fid']['help'] = t('The unique ID of the aggregator feed.');
    $data['aggregator_feed']['fid']['argument']['id'] = 'aggregator_fid';
    $data['aggregator_feed']['fid']['argument']['name field'] = 'title';
    $data['aggregator_feed']['fid']['argument']['numeric'] = TRUE;

    $data['aggregator_feed']['fid']['filter']['id'] = 'numeric';

    $data['aggregator_feed']['title']['help'] = t('The title of the aggregator feed.');
    $data['aggregator_feed']['title']['field']['default_formatter'] = 'aggregator_title';

    $data['aggregator_feed']['argument']['id'] = 'string';

    $data['aggregator_feed']['url']['help'] = t('The fully-qualified URL of the feed.');
    $data['aggregator_feed']['link']['help'] = t('The link to the source URL of the feed.');

    $data['aggregator_feed']['checked']['help'] = t('The date the feed was last checked for new content.');

    $data['aggregator_feed']['description']['help'] = t('The description of the aggregator feed.');
    $data['aggregator_feed']['description']['field']['click sortable'] = FALSE;

    $data['aggregator_feed']['modified']['help'] = t('The date of the most recent new content on the feed.');

    return $data;
  }

}
