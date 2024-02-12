<?php

namespace Drupal\mongodb\ViewsData;

/**
 * The MongoDB implementation of \Drupal\user\UserViewsData.
 */
class UserViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['users']['table']['base']['help'] = t('Users who have created accounts on your site.');
    $data['users']['table']['base']['access query tag'] = 'user_access';

    $data['users']['table']['wizard_id'] = 'user';

    $data['users']['uid']['argument']['id'] = 'user_uid';
    $data['users']['uid']['argument'] += [
      'name table' => 'users',
      'name field' => 'name',
      'empty field name' => \Drupal::config('user.settings')->get('anonymous'),
    ];
    $data['users']['uid']['filter']['id'] = 'user_name';
    $data['users']['uid']['filter']['title'] = t('Name (autocomplete)');
    $data['users']['uid']['filter']['help'] = t('The user or author name. Uses an autocomplete widget to find a user name, the actual filter uses the resulting user ID.');
    $data['users']['uid']['relationship'] = [
      'title' => t('Content authored'),
      'help' => t('Relate content to the user who created it. This relationship will create one record for each content item created by the user.'),
      'id' => 'standard',
      'base' => 'node',
      'base field' => 'uid',
      'field' => 'uid',
      'label' => t('nodes'),
    ];

    $data['users']['uid_raw'] = [
      'help' => t('The raw numeric user ID.'),
      'real field' => 'uid',
      'filter' => [
        'title' => t('The user ID'),
        'id' => 'numeric',
      ],
    ];

    $data['users']['uid_representative'] = [
      'relationship' => [
        'title' => t('Representative node'),
        'label'  => t('Representative node'),
        'help' => t('Obtains a single representative node for each user, according to a chosen sort criterion.'),
        'id' => 'groupwise_max',
        'relationship field' => 'uid',
        'outer field' => 'users.uid',
        'argument table' => 'users',
        'argument field' => 'uid',
        'base' => 'node',
        'field' => 'nid',
        'relationship' => 'node:uid',
      ],
    ];

    $data['users']['uid_current'] = [
      'real field' => 'uid',
      'title' => t('Current'),
      'help' => t('Filter the view to the currently logged in user.'),
      'filter' => [
        'id' => 'user_current',
        'type' => 'yes-no',
      ],
    ];

    $data['users']['name']['help'] = t('The user or author name.');
    $data['users']['name']['field']['default_formatter'] = 'user_name';
    $data['users']['name']['filter']['title'] = t('Name (raw)');
    $data['users']['name']['filter']['help'] = t('The user or author name. This filter does not check if the user exists and allows partial matching. Does not use autocomplete.');

    // Note that this field implements field level access control.
    $data['users']['mail']['help'] = t('Email address for a given user. This field is normally not shown to users, so be cautious when using it.');

    $data['users']['langcode']['help'] = t('Original language of the user information');
    $data['users']['langcode']['help'] = t('Language of the translation of user information');

    $data['users']['preferred_langcode']['title'] = t('Preferred language');
    $data['users']['preferred_langcode']['help'] = t('Preferred language of the user');
    $data['users']['preferred_admin_langcode']['title'] = t('Preferred admin language');
    $data['users']['preferred_admin_langcode']['help'] = t('Preferred administrative language of the user');

    $data['users']['created_fulldate'] = [
      'title' => t('Created date'),
      'help' => t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_fulldate',
      ],
    ];

    $data['users']['created_year_month'] = [
      'title' => t('Created year + month'),
      'help' => t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year_month',
      ],
    ];

    $data['users']['created_year'] = [
      'title' => t('Created year'),
      'help' => t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year',
      ],
    ];

    $data['users']['created_month'] = [
      'title' => t('Created month'),
      'help' => t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_month',
      ],
    ];

    $data['users']['created_day'] = [
      'title' => t('Created day'),
      'help' => t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_day',
      ],
    ];

    $data['users']['created_week'] = [
      'title' => t('Created week'),
      'help' => t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_week',
      ],
    ];

    $data['users']['status']['filter']['label'] = t('Active');
    $data['users']['status']['filter']['type'] = 'yes-no';

    $data['users']['changed']['title'] = t('Updated date');

    $data['users']['changed_fulldate'] = [
      'title' => t('Updated date'),
      'help' => t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_fulldate',
      ],
    ];

    $data['users']['changed_year_month'] = [
      'title' => t('Updated year + month'),
      'help' => t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year_month',
      ],
    ];

    $data['users']['changed_year'] = [
      'title' => t('Updated year'),
      'help' => t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year',
      ],
    ];

    $data['users']['changed_month'] = [
      'title' => t('Updated month'),
      'help' => t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_month',
      ],
    ];

    $data['users']['changed_day'] = [
      'title' => t('Updated day'),
      'help' => t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_day',
      ],
    ];

    $data['users']['changed_week'] = [
      'title' => t('Updated week'),
      'help' => t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_week',
      ],
    ];

    $data['users']['data'] = [
      'title' => t('Data'),
      'help' => t('Provides access to the user data service.'),
      'real field' => 'uid',
      'field' => [
        'id' => 'user_data',
      ],
    ];

    $data['users']['user_bulk_form'] = [
      'title' => t('Bulk update'),
      'help' => t('Add a form element that lets you run operations on multiple users.'),
      'field' => [
        'id' => 'user_bulk_form',
      ],
    ];

    $data['users']['roles_target_id']['title'] = $this->t('Roles');
    $data['users']['roles_target_id']['help'] = $this->t('Roles that a user belongs to.');

    // Alter the user roles target_id column.
    $data['users']['roles_target_id']['field']['id'] = 'user_roles';
    $data['users']['roles_target_id']['field']['no group by'] = TRUE;

    $data['users']['roles_target_id']['filter']['id'] = 'user_roles';
    $data['users']['roles_target_id']['filter']['allow empty'] = TRUE;

    $data['users']['roles_target_id']['argument'] = [
      'id' => 'user__roles_rid',
      'name table' => 'role',
      'name field' => 'name',
      'empty field name' => t('No role'),
      'zero is null' => TRUE,
      'numeric' => TRUE,
    ];

    $data['users']['permission'] = [
      'title' => t('Permission'),
      'help' => t('The user permissions.'),
      'field' => [
        'id' => 'user_permissions',
        'no group by' => TRUE,
      ],
      'filter' => [
        'id' => 'user_permissions',
        'real field' => 'roles_target_id',
      ],
    ];

    return $data;
  }

}
