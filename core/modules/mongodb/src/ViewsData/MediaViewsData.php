<?php

namespace Drupal\mongodb\ViewsData;

/**
 * Provides the Views data for the media entity type.
 */
class MediaViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['media']['table']['wizard_id'] = 'media';

    $data['media']['revision_user']['help'] = $this->t('The user who created the revision.');
    $data['media']['revision_user']['relationship']['label'] = $this->t('revision user');
    $data['media']['revision_user']['filter']['id'] = 'user_name';

    $data['media']['status_extra'] = [
      'title' => $this->t('Published status or admin user'),
      'help' => $this->t('Filters out unpublished media if the current user cannot view it.'),
      'filter' => [
        'field' => 'status',
        'id' => 'media_status',
        'label' => $this->t('Published status or admin user'),
      ],
    ];

    return $data;
  }

}
