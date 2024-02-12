<?php

namespace Drupal\mongodb\ViewsData;

/**
 * The MongoDB implementation of \Drupal\block_content\BlockContentViewsData.
 */
class BlockContentViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['block_content']['id']['field']['id'] = 'field';

    $data['block_content']['info']['field']['id'] = 'field';
    $data['block_content']['info']['field']['link_to_entity default'] = TRUE;

    $data['block_content']['type']['field']['id'] = 'field';

    $data['block_content']['table']['wizard_id'] = 'block_content';

    $data['block_content']['block_content_listing_empty'] = [
      'title' => t('Empty block library behavior'),
      'help' => t('Provides a link to add a new block.'),
      'area' => [
        'id' => 'block_content_listing_empty',
      ],
    ];
    // Advertise this table as a possible base table.
    $data['block_content']['table']['base']['help'] = t('Block Content revision is a history of changes to block content.');
    $data['block_content']['table']['base']['defaults']['title'] = 'info';
/*
    // @todo EntityViewsData should add these relationships by default.
    //   https://www.drupal.org/node/2410275
    $data['block_content']['id']['relationship']['id'] = 'standard';
    $data['block_content']['id']['relationship']['base'] = 'block_content';
    $data['block_content']['id']['relationship']['base field'] = 'id';
    $data['block_content']['id']['relationship']['title'] = t('Block Content');
    $data['block_content']['id']['relationship']['label'] = t('Get the actual block content from a block content revision.');

    $data['block_content']['revision_id']['relationship']['id'] = 'standard';
    $data['block_content']['revision_id']['relationship']['base'] = 'block_content';
    $data['block_content']['revision_id']['relationship']['base field'] = 'revision_id';
    $data['block_content']['revision_id']['relationship']['title'] = t('Block Content');
    $data['block_content']['revision_id']['relationship']['label'] = t('Get the actual block content from a block content revision.');
*/

    $data['block_content']['revision_user']['help'] = $this->t('The user who created the revision.');
    $data['block_content']['revision_user']['relationship']['label'] = $this->t('revision user');
    $data['block_content']['revision_user']['filter']['id'] = 'user_name';

    return $data;
  }

}
