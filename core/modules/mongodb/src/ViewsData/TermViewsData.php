<?php

namespace Drupal\mongodb\ViewsData;

/**
 * The MongoDB implementation of \Drupal\taxonomy\TermViewsData.
 */
class TermViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['taxonomy_term_data']['table']['base']['help'] = t('Taxonomy terms are attached to nodes.');
    $data['taxonomy_term_data']['table']['base']['access query tag'] = 'taxonomy_term_access';
    $data['taxonomy_term_data']['table']['wizard_id'] = 'taxonomy_term';

    $data['taxonomy_term_data']['table']['join'] = [
      // This is provided for the many_to_one argument.
      'taxonomy_index' => [
        'field' => 'tid',
        'left_field' => 'tid',
      ],
    ];

    $data['taxonomy_term_data']['tid']['help'] = t('The tid of a taxonomy term.');

    $data['taxonomy_term_data']['tid']['argument']['id'] = 'taxonomy';
    $data['taxonomy_term_data']['tid']['argument']['name field'] = 'name';
    $data['taxonomy_term_data']['tid']['argument']['zero is null'] = TRUE;

    $data['taxonomy_term_data']['tid']['filter']['id'] = 'taxonomy_index_tid';
    $data['taxonomy_term_data']['tid']['filter']['title'] = t('Term');
    $data['taxonomy_term_data']['tid']['filter']['help'] = t('Taxonomy term chosen from autocomplete or select widget.');
    $data['taxonomy_term_data']['tid']['filter']['hierarchy table'] = 'taxonomy_term_data';
    $data['taxonomy_term_data']['tid']['filter']['numeric'] = TRUE;

    $data['taxonomy_term_data']['tid_raw'] = [
      'title' => t('Term ID'),
      'help' => t('The tid of a taxonomy term.'),
      'real field' => 'tid',
      'filter' => [
        'id' => 'numeric',
        'allow empty' => TRUE,
      ],
    ];

    $data['taxonomy_term_data']['tid_representative'] = [
      'relationship' => [
        'title' => t('Representative node'),
        'label'  => t('Representative node'),
        'help' => t('Obtains a single representative node for each term, according to a chosen sort criterion.'),
        'id' => 'groupwise_max',
        'relationship field' => 'tid',
        'outer field' => 'taxonomy_term_data.tid',
        'argument table' => 'taxonomy_term_data',
        'argument field' => 'tid',
        'base'   => 'node',
        'field'  => 'nid',
        'relationship' => 'node:term_node_tid',
      ],
    ];

    $data['taxonomy_term_data']['vid']['help'] = t('Filter the results of "Taxonomy: Term" to a particular vocabulary.');
    $data['taxonomy_term_data']['vid']['field']['help'] = t('The vocabulary name.');
    $data['taxonomy_term_data']['vid']['argument']['id'] = 'vocabulary_vid';
    unset($data['taxonomy_term_data']['vid']['sort']);

    $data['taxonomy_term_data']['name']['field']['id'] = 'term_name';
    $data['taxonomy_term_data']['name']['argument']['many to one'] = TRUE;
    $data['taxonomy_term_data']['name']['argument']['empty field name'] = t('Uncategorized');

    $data['taxonomy_term_data']['description__value']['field']['click sortable'] = FALSE;

    $data['taxonomy_term_data']['changed']['title'] = t('Updated date');
    $data['taxonomy_term_data']['changed']['help'] = t('The date the term was last updated.');

    $data['taxonomy_term_data']['changed_fulldate'] = [
      'title' => t('Updated date'),
      'help' => t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_fulldate',
      ],
    ];

    $data['taxonomy_term_data']['changed_year_month'] = [
      'title' => t('Updated year + month'),
      'help' => t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year_month',
      ],
    ];

    $data['taxonomy_term_data']['changed_year'] = [
      'title' => t('Updated year'),
      'help' => t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year',
      ],
    ];

    $data['taxonomy_term_data']['changed_month'] = [
      'title' => t('Updated month'),
      'help' => t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_month',
      ],
    ];

    $data['taxonomy_term_data']['changed_day'] = [
      'title' => t('Updated day'),
      'help' => t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_day',
      ],
    ];

    $data['taxonomy_term_data']['changed_week'] = [
      'title' => t('Updated week'),
      'help' => t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_week',
      ],
    ];

    $data['taxonomy_index']['table']['group'] = t('Taxonomy term');

    $data['taxonomy_index']['table']['join'] = [
      'taxonomy_term_data' => [
        // links directly to taxonomy_term_data via tid
        'left_field' => 'tid',
        'field' => 'tid',
      ],
      'node' => [
        // links directly to node via nid
        'left_field' => 'nid',
        'field' => 'nid',
      ],
    ];

    $data['taxonomy_index']['nid'] = [
      'title' => t('Content with term'),
      'help' => t('Relate all content tagged with a term.'),
      'relationship' => [
        'id' => 'standard',
        'base' => 'node',
        'base field' => 'nid',
        'label' => t('node'),
        'skip base' => 'node',
      ],
    ];

    // @todo This stuff needs to move to a node field since really it's all
    //   about nodes.
    $data['taxonomy_index']['tid'] = [
      'group' => t('Content'),
      'title' => t('Has taxonomy term ID'),
      'help' => t('Display content if it has the selected taxonomy terms.'),
      'argument' => [
        'id' => 'taxonomy_index_tid',
        'name table' => 'taxonomy_term_data',
        'name field' => 'name',
        'empty field name' => t('Uncategorized'),
        'numeric' => TRUE,
        'skip base' => 'taxonomy_term_data',
      ],
      'filter' => [
        'title' => t('Has taxonomy term'),
        'id' => 'taxonomy_index_tid',
        'hierarchy table' => 'taxonomy_term_data',
        'numeric' => TRUE,
        'skip base' => 'taxonomy_term_data',
        'allow empty' => TRUE,
      ],
    ];

    $data['taxonomy_index']['status'] = [
      'title' => t('Publish status'),
      'help' => t('Whether or not the content related to a term is published.'),
      'filter' => [
        'id' => 'boolean',
        'label' => t('Published status'),
        'type' => 'yes-no',
      ],
    ];

    $data['taxonomy_index']['sticky'] = [
      'title' => t('Sticky status'),
      'help' => t('Whether or not the content related to a term is sticky.'),
      'filter' => [
        'id' => 'boolean',
        'label' => t('Sticky status'),
        'type' => 'yes-no',
      ],
      'sort' => [
        'id' => 'standard',
        'help' => t('Whether or not the content related to a term is sticky. To list sticky content first, set this to descending.'),
      ],
    ];

    $data['taxonomy_index']['created'] = [
      'title' => t('Post date'),
      'help' => t('The date the content related to a term was posted.'),
      'sort' => [
        'id' => 'date',
      ],
      'filter' => [
        'id' => 'date',
      ],
    ];

    // Link to self through left.parent = right.tid (going down in depth).
    $data['taxonomy_term__parent']['table']['join']['taxonomy_term__parent'] = [
      'left_field' => 'entity_id',
      'field' => 'parent_target_id',
    ];

    $data['taxonomy_term__parent']['parent_target_id']['help'] = t('The parent term of the term. This can produce duplicate entries if you are using a vocabulary that allows multiple parents.');
    $data['taxonomy_term__parent']['parent_target_id']['relationship']['label'] = t('Parent');
    $data['taxonomy_term__parent']['parent_target_id']['argument']['id'] = 'taxonomy';

    return $data;
  }

}
