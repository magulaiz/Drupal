<?php

namespace Drupal\mongodb\ViewsData;

// cspell:ignore behaviour

/**
 * The MongoDB implementation of \Drupal\node\NodeViewsData.
 */
class NodeViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['node']['table']['base']['weight'] = -10;
    $data['node']['table']['base']['access query tag'] = 'node_access';
    $data['node']['table']['wizard_id'] = 'node';

    $data['node']['nid']['argument'] = [
      'id' => 'node_nid',
      'name field' => 'title',
      'numeric' => TRUE,
      'validate type' => 'nid',
    ];

    $data['node']['title']['field']['default_formatter_settings'] = ['link_to_entity' => TRUE];

    $data['node']['title']['field']['link_to_node default'] = TRUE;

    $data['node']['type']['argument']['id'] = 'node_type';

    $data['node']['langcode']['help'] = t('The language of the content or translation.');

    $data['node']['status']['filter']['label'] = t('Published status');
    $data['node']['status']['filter']['type'] = 'yes-no';
    // Use status = 1 instead of status <> 0 in WHERE statement.
    $data['node']['status']['filter']['use_equal'] = TRUE;

    $data['node']['status_extra'] = [
      'title' => t('Published status or admin user'),
      'help' => t('Filters out unpublished content if the current user cannot view it.'),
      'filter' => [
        'field' => 'status',
        'id' => 'node_status',
        'label' => t('Published status or admin user'),
      ],
    ];

    $data['node']['promote']['help'] = t('A boolean indicating whether the node is visible on the front page.');
    $data['node']['promote']['filter']['label'] = t('Promoted to front page status');
    $data['node']['promote']['filter']['type'] = 'yes-no';

    $data['node']['sticky']['help'] = t('A boolean indicating whether the node should sort to the top of content lists.');
    $data['node']['sticky']['filter']['label'] = t('Sticky status');
    $data['node']['sticky']['filter']['type'] = 'yes-no';
    $data['node']['sticky']['sort']['help'] = t('Whether or not the content is sticky. To list sticky content first, set this to descending.');

    $data['node']['node_bulk_form'] = [
      'title' => t('Node operations bulk form'),
      'help' => t('Add a form element that lets you run operations on multiple nodes.'),
      'field' => [
        'id' => 'node_bulk_form',
      ],
    ];

    // Bogus fields for aliasing purposes.

    // @todo Add similar support to any date field
    // @see https://www.drupal.org/node/2337507
    $data['node']['created_fulldate'] = [
      'title' => t('Created date'),
      'help' => t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_fulldate',
      ],
    ];

    $data['node']['created_year_month'] = [
      'title' => t('Created year + month'),
      'help' => t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year_month',
      ],
    ];

    $data['node']['created_year'] = [
      'title' => t('Created year'),
      'help' => t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year',
      ],
    ];

    $data['node']['created_month'] = [
      'title' => t('Created month'),
      'help' => t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_month',
      ],
    ];

    $data['node']['created_day'] = [
      'title' => t('Created day'),
      'help' => t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_day',
      ],
    ];

    $data['node']['created_week'] = [
      'title' => t('Created week'),
      'help' => t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_week',
      ],
    ];

    $data['node']['changed_fulldate'] = [
      'title' => t('Updated date'),
      'help' => t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_fulldate',
      ],
    ];

    $data['node']['changed_year_month'] = [
      'title' => t('Updated year + month'),
      'help' => t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year_month',
      ],
    ];

    $data['node']['changed_year'] = [
      'title' => t('Updated year'),
      'help' => t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year',
      ],
    ];

    $data['node']['changed_month'] = [
      'title' => t('Updated month'),
      'help' => t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_month',
      ],
    ];

    $data['node']['changed_day'] = [
      'title' => t('Updated day'),
      'help' => t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_day',
      ],
    ];

    $data['node']['changed_week'] = [
      'title' => t('Updated week'),
      'help' => t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_week',
      ],
    ];

    $data['node']['uid']['help'] = t('The user authoring the content. If you need more fields than the uid add the content: author relationship');
    $data['node']['uid']['filter']['id'] = 'user_name';
    $data['node']['uid']['relationship']['title'] = t('Content author');
    $data['node']['uid']['relationship']['help'] = t('Relate content to the user who created it.');
    $data['node']['uid']['relationship']['label'] = t('author');
    // The next line is added for MongoDB. No idea why!
    $data['node']['uid']['relationship']['base'] = 'users';

    $data['node']['node_listing_empty'] = [
      'title' => t('Empty Node Frontpage behavior'),
      'help' => t('Provides a link to the node add overview page.'),
      'area' => [
        'id' => 'node_listing_empty',
      ],
    ];

    $data['node']['uid_revision']['title'] = t('User has a revision');
    $data['node']['uid_revision']['help'] = t('All nodes where a certain user has a revision');
    $data['node']['uid_revision']['real field'] = 'nid';
    $data['node']['uid_revision']['filter']['id'] = 'node_uid_revision';
    $data['node']['uid_revision']['argument']['id'] = 'node_uid_revision';

    // $data['node_field_revision']['table']['wizard_id'] = 'node_revision';

    // Advertise this table as a possible base table.
    // $data['node_field_revision']['table']['base']['help'] = t('Content revision is a history of changes to content.');
    // $data['node_field_revision']['table']['base']['defaults']['title'] = 'title';

    // $data['node_field_revision']['nid']['argument'] = [
    // 'id' => 'node_nid',
    // 'numeric' => TRUE,
    // ];
    // @todo the NID field needs different behaviour on revision/non-revision
    //   tables. It would be neat if this could be encoded in the base field
    //   definition.
    // $data['node_field_revision']['nid']['relationship']['id'] = 'standard';
    // $data['node_field_revision']['nid']['relationship']['base'] = 'node';
    // $data['node_field_revision']['nid']['relationship']['base field'] = 'nid';
    // $data['node_field_revision']['nid']['relationship']['title'] = t('Content');
    // $data['node_field_revision']['nid']['relationship']['label'] = t('Get the actual content from a content revision.');
    // $data['node_field_revision']['nid']['relationship']['extra'][] = [
    // 'field' => 'langcode',
    // 'left_field' => 'langcode',
    // ];

    // $data['node_field_revision']['vid'] = [
    // 'argument' => [
    // 'id' => 'node_vid',
    // 'numeric' => TRUE,
    // ],
    // 'relationship' => [
    // 'id' => 'standard',
    // 'base' => 'node',
    // 'base field' => 'vid',
    // 'title' => t('Content'),
    // 'label' => t('Get the actual content from a content revision.'),
    // 'extra' => [
    // [
    // 'field' => 'langcode',
    // 'left_field' => 'langcode',
    // ],
    // ],
    // ],
    // ];

    // $data['node_field_revision']['langcode']['help'] = t('The language the original content is in.');

    $data['node']['revision_uid']['help'] = t('The user who created the revision.');
    $data['node']['revision_uid']['relationship']['label'] = t('revision user');
    $data['node']['revision_uid']['filter']['id'] = 'user_name';

    // $data['node_field_revision']['table']['wizard_id'] = 'node_field_revision';

    // $data['node_field_revision']['table']['join']['node']['left_field'] = 'vid';
    // $data['node_field_revision']['table']['join']['node']['field'] = 'vid';

    // $data['node_field_revision']['status']['filter']['label'] = t('Published');
    // $data['node_field_revision']['status']['filter']['type'] = 'yes-no';
    // $data['node_field_revision']['status']['filter']['use_equal'] = TRUE;

    // $data['node_field_revision']['promote']['help'] = t('A boolean indicating whether the node is visible on the front page.');

    // $data['node_field_revision']['sticky']['help'] = t('A boolean indicating whether the node should sort to the top of content lists.');

    // $data['node_field_revision']['langcode']['help'] = t('The language of the content or translation.');

    $data['node']['link_to_revision'] = [
      'field' => [
        'title' => t('Link to revision'),
        'help' => t('Provide a simple link to the revision.'),
        'id' => 'node_revision_link',
        'click sortable' => FALSE,
      ],
    ];

    $data['node']['revert_revision'] = [
      'field' => [
        'title' => t('Link to revert revision'),
        'help' => t('Provide a simple link to revert to the revision.'),
        'id' => 'node_revision_link_revert',
        'click sortable' => FALSE,
      ],
    ];

    $data['node']['delete_revision'] = [
      'field' => [
        'title' => t('Link to delete revision'),
        'help' => t('Provide a simple link to delete the content revision.'),
        'id' => 'node_revision_link_delete',
        'click sortable' => FALSE,
      ],
    ];

    // Define the base group of this table. Fields that don't have a group defined
    // will go into this field by default.
    $data['node_access']['table']['group'] = t('Content access');

    // For other base tables, explain how we join.
    $data['node_access']['table']['join'] = [
      'node' => [
        'left_field' => 'nid',
        'field' => 'nid',
      ],
    ];
    $data['node_access']['nid'] = [
      'title' => t('Access'),
      'help' => t('Filter by access.'),
      'filter' => [
        'id' => 'node_access',
        'help' => t('Filter for content by view access. <strong>Not necessary if you are using node as your base table.</strong>'),
      ],
    ];

    // Add search table, fields, filters, etc., but only if a page using the
    // node_search plugin is enabled.
    if (\Drupal::moduleHandler()->moduleExists('search')) {
      $enabled = FALSE;
      $search_page_repository = \Drupal::service('search.search_page_repository');
      foreach ($search_page_repository->getActiveSearchPages() as $page) {
        if ($page->getPlugin()->getPluginId() == 'node_search') {
          $enabled = TRUE;
          break;
        }
      }

      if ($enabled) {
        $data['node_search_index']['table']['group'] = t('Search');

        // Automatically join to the node table (or actually, node).
        // Use a Views table alias to allow other modules to use this table too,
        // if they use the search index.
        $data['node_search_index']['table']['join'] = [
          'node' => [
            'left_field' => 'nid',
            'field' => 'sid',
            'table' => 'search_index',
            'extra' => [
              [
                'field' => 'type',
                'value' => 'node_search',
              ],
              [
                'field' => 'langcode',
                'left_field' => 'node_current_revision.langcode',
              ],
            ],
          ],
        ];

        $data['node_search_total']['table']['join'] = [
          'node_search_index' => [
            'left_field' => 'word',
            'field' => 'word',
          ],
        ];

        $data['node_search_dataset']['table']['join'] = [
          'node' => [
            'left_field' => 'sid',
            'left_table' => 'node_search_index',
            'field' => 'sid',
            'table' => 'search_dataset',
            'extra' => 'node_search_index.type = node_search_dataset.type AND node_search_index.langcode = node_search_dataset.langcode',
            'type' => 'INNER',
          ],
        ];

        // unset($data['node_search_dataset']['table']['join']['node']['left_field']);
        // unset($data['node_search_dataset']['table']['join']['node']['left_table']);
        // unset($data['node_search_dataset']['table']['join']['node']['extra']);
        // unset($data['node_search_dataset']['table']['join']['node']['type']);

        $data['node_search_index']['score'] = [
          'title' => t('Score'),
          'help' => t('The score of the search item. This will not be used if the search filter is not also present.'),
          'field' => [
            'id' => 'search_score',
            'float' => TRUE,
            'no group by' => TRUE,
          ],
          'sort' => [
            'id' => 'search_score',
            'no group by' => TRUE,
          ],
        ];

        $data['node_search_index']['keys'] = [
          'title' => t('Search Keywords'),
          'help' => t('The keywords to search for.'),
          'filter' => [
            'id' => 'search_keywords',
            'no group by' => TRUE,
            'search_type' => 'node_search',
          ],
          'argument' => [
            'id' => 'search',
            'no group by' => TRUE,
            'search_type' => 'node_search',
          ],
        ];

      }
    }

    $data['node']['path'] = [
      'field' => [
        'title' => $this->t('Path'),
        'help' => $this->t('The aliased path to this content.'),
        'id' => 'node_path',
      ],
    ];

    return $data;
  }

}
