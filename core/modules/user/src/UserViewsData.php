<?php

namespace Drupal\user;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\views\EntityViewsData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the views data for the user entity type.
 */
class UserViewsData extends EntityViewsData implements ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * Constructs a new user views data instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type to provide views integration for.
   * @param \Drupal\Core\Entity\Sql\SqlEntityStorageInterface $storage_controller
   *   The storage handler used for this entity type.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   *   The translation manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   */
  public function __construct(EntityTypeInterface $entity_type, SqlEntityStorageInterface $storage_controller, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, TranslationInterface $translation_manager, EntityFieldManagerInterface $entity_field_manager, ContainerInterface $container) {
    parent::__construct($entity_type, $storage_controller, $entity_type_manager, $module_handler, $translation_manager, $entity_field_manager);
    $this->setContainer($container);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): self {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('string_translation'),
      $container->get('entity_field.manager'),
      $container,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['users_field_data']['table']['base']['help'] = $this->t('Users who have created accounts on your site.');
    $data['users_field_data']['table']['base']['access query tag'] = 'user_access';

    $data['users_field_data']['table']['wizard_id'] = 'user';

    $data['users_field_data']['uid']['argument']['id'] = 'user_uid';
    $data['users_field_data']['uid']['argument'] += [
      'name table' => 'users_field_data',
      'name field' => 'name',
      'empty field name' => \Drupal::config('user.settings')->get('anonymous'),
    ];
    $data['users_field_data']['uid']['filter']['id'] = 'user_name';
    $data['users_field_data']['uid']['filter']['title'] = $this->t('Name (autocomplete)');
    $data['users_field_data']['uid']['filter']['help'] = $this->t('The user or author name. Uses an autocomplete widget to find a user name, the actual filter uses the resulting user ID.');
    $data['users_field_data']['uid']['relationship'] = [
      'title' => $this->t('Content authored'),
      'help' => $this->t('Relate content to the user who created it. This relationship will create one record for each content item created by the user.'),
      'id' => 'standard',
      'base' => 'node_field_data',
      'base field' => 'uid',
      'field' => 'uid',
      'label' => $this->t('nodes'),
    ];

    $data['users_field_data']['uid_raw'] = [
      'help' => $this->t('The raw numeric user ID.'),
      'real field' => 'uid',
      'filter' => [
        'title' => $this->t('The user ID'),
        'id' => 'numeric',
      ],
    ];

    $data['users_field_data']['uid_representative'] = [
      'relationship' => [
        'title' => $this->t('Representative node'),
        'label'  => $this->t('Representative node'),
        'help' => $this->t('Obtains a single representative node for each user, according to a chosen sort criterion.'),
        'id' => 'groupwise_max',
        'relationship field' => 'uid',
        'outer field' => 'users_field_data.uid',
        'argument table' => 'users_field_data',
        'argument field' => 'uid',
        'base' => 'node_field_data',
        'field' => 'nid',
        'relationship' => 'node_field_data:uid',
      ],
    ];

    $data['users']['uid_current'] = [
      'real field' => 'uid',
      'title' => $this->t('Current'),
      'help' => $this->t('Filter the view to the currently logged in user.'),
      'filter' => [
        'id' => 'user_current',
        'type' => 'yes-no',
      ],
    ];

    $data['users_field_data']['name']['help'] = $this->t('The user or author name.');
    $data['users_field_data']['name']['field']['default_formatter'] = 'user_name';
    $data['users_field_data']['name']['filter']['title'] = $this->t('Name (raw)');
    $data['users_field_data']['name']['filter']['help'] = $this->t('The user or author name. This filter does not check if the user exists and allows partial matching. Does not use autocomplete.');

    // Note that this field implements field level access control.
    $data['users_field_data']['mail']['help'] = $this->t('Email address for a given user. This field is normally not shown to users, so be cautious when using it.');

    $data['users_field_data']['langcode']['help'] = $this->t('Original language of the user information');
    $data['users_field_data']['langcode']['help'] = $this->t('Language of the translation of user information');

    $data['users_field_data']['preferred_langcode']['title'] = $this->t('Preferred language');
    $data['users_field_data']['preferred_langcode']['help'] = $this->t('Preferred language of the user');
    $data['users_field_data']['preferred_admin_langcode']['title'] = $this->t('Preferred admin language');
    $data['users_field_data']['preferred_admin_langcode']['help'] = $this->t('Preferred administrative language of the user');

    $data['users_field_data']['created_fulldate'] = [
      'title' => $this->t('Created date'),
      'help' => $this->t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_fulldate',
      ],
    ];

    $data['users_field_data']['created_year_month'] = [
      'title' => $this->t('Created year + month'),
      'help' => $this->t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year_month',
      ],
    ];

    $data['users_field_data']['created_year'] = [
      'title' => $this->t('Created year'),
      'help' => $this->t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year',
      ],
    ];

    $data['users_field_data']['created_month'] = [
      'title' => $this->t('Created month'),
      'help' => $this->t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_month',
      ],
    ];

    $data['users_field_data']['created_day'] = [
      'title' => $this->t('Created day'),
      'help' => $this->t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_day',
      ],
    ];

    $data['users_field_data']['created_week'] = [
      'title' => $this->t('Created week'),
      'help' => $this->t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_week',
      ],
    ];

    $data['users_field_data']['status']['filter']['label'] = $this->t('Active');
    $data['users_field_data']['status']['filter']['type'] = 'yes-no';

    $data['users_field_data']['changed']['title'] = $this->t('Updated date');

    $data['users_field_data']['changed_fulldate'] = [
      'title' => $this->t('Updated date'),
      'help' => $this->t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_fulldate',
      ],
    ];

    $data['users_field_data']['changed_year_month'] = [
      'title' => $this->t('Updated year + month'),
      'help' => $this->t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year_month',
      ],
    ];

    $data['users_field_data']['changed_year'] = [
      'title' => $this->t('Updated year'),
      'help' => $this->t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year',
      ],
    ];

    $data['users_field_data']['changed_month'] = [
      'title' => $this->t('Updated month'),
      'help' => $this->t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_month',
      ],
    ];

    $data['users_field_data']['changed_day'] = [
      'title' => $this->t('Updated day'),
      'help' => $this->t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_day',
      ],
    ];

    $data['users_field_data']['changed_week'] = [
      'title' => $this->t('Updated week'),
      'help' => $this->t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_week',
      ],
    ];

    $data['users']['data'] = [
      'title' => $this->t('Data'),
      'help' => $this->t('Provides access to the user data service.'),
      'real field' => 'uid',
      'field' => [
        'id' => 'user_data',
      ],
    ];

    $data['users']['user_bulk_form'] = [
      'title' => $this->t('Bulk update'),
      'help' => $this->t('Add a form element that lets you run operations on multiple users.'),
      'field' => [
        'id' => 'user_bulk_form',
      ],
    ];

    // Alter the user roles target_id column.
    $data['user__roles']['roles_target_id']['field']['id'] = 'user_roles';
    $data['user__roles']['roles_target_id']['field']['no group by'] = TRUE;

    $data['user__roles']['roles_target_id']['filter']['id'] = 'user_roles';
    $data['user__roles']['roles_target_id']['filter']['allow empty'] = TRUE;

    $data['user__roles']['roles_target_id']['argument'] = [
      'id' => 'user__roles_rid',
      'name table' => 'role',
      'name field' => 'name',
      'empty field name' => $this->t('No role'),
      'zero is null' => TRUE,
      'numeric' => FALSE,
    ];

    $data['user__roles']['permission'] = [
      'title' => $this->t('Permission'),
      'help' => $this->t('The user permissions.'),
      'field' => [
        'id' => 'user_permissions',
        'no group by' => TRUE,
      ],
      'filter' => [
        'id' => 'user_permissions',
        'real field' => 'roles_target_id',
      ],
    ];

    // Unset the "pass" field because the access control handler for the user
    // entity type allows editing the password, but not viewing it.
    unset($data['users_field_data']['pass']);

    foreach ($this->getUserTimestampFields() as $field => $info) {
      // Only provide user timestamps Views support if the values are stored in
      // the database key/value store.
      if (!$this->usesUserDatabaseKeyValueStore($field)) {
        continue;
      }

      $data['users'][$field]['relationship'] = [
        'title' => $info['title'],
        'label' => $info['title'],
        'help' => $info['help'],
        'id' => 'standard',
        'base' => 'key_value',
        'real field' => 'uid',
        'base field' => 'name',
        'extra' => [
          [
            'field' => 'collection',
            'value' => "user.timestamp.$field",
          ],
        ],
      ];

      if (!isset($data['key_value'])) {
        $data['key_value'] = [
          'table' => [
            'group' => $this->t('User timestamps'),
            'provider' => 'user',
          ],
        ];
      }

      $data['key_value'][$field] = [
        'title' => $info['title'],
        'help' => $info['help'],
        'real field' => 'value',
        'field' => ['id' => 'date'],
        'filter' => ['id' => 'date'],
        'sort' => ['id' => 'date'],
        'argument' => ['id' => 'date'],
      ];
    }

    return $data;
  }

  /**
   * Returns a list of user timestamp fields for which to provide Views support.
   *
   * @return array[]
   *   Associative array keyed by field name and having an associative array
   *   containing the field's title/label and help translated strings.
   */
  protected function getUserTimestampFields(): array {
    return [
      'access' => [
        'title' => $this->t('User last access'),
        'help' => $this->t('The time that the user last accessed the site.'),
      ],
      'login' => [
        'title' => $this->t('User last login'),
        'help' => $this->t('The time that the user last login.'),
      ],
    ];
  }

  /**
   * Checks whether the standards database key/value factory is used.
   *
   * @param string $type
   *   The key/value collection suffix. Either 'access' or 'login'.
   *
   * @return bool
   *   Whether the standards database key/value factory is used.
   */
  protected function usesUserDatabaseKeyValueStore(string $type): bool {
    assert(in_array($type, ['access', 'login'], TRUE));
    $factory_keyvalue = $this->container->getParameter('factory.keyvalue');
    $keyvalue_store = $factory_keyvalue["user.timestamp.$type"] ?? NULL;
    return $keyvalue_store === 'user.keyvalue.database';
  }

}
