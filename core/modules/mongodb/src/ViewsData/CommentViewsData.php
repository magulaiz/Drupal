<?php

namespace Drupal\mongodb\ViewsData;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * The MongoDB implementation of \Drupal\comment\CommentViewsData.
 */
class CommentViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['comment']['table']['base']['help'] = t('Comments are responses to content.');
    $data['comment']['table']['base']['access query tag'] = 'comment_access';

    $data['comment']['table']['wizard_id'] = 'comment';

    $data['comment']['subject']['title'] = t('Title');
    $data['comment']['subject']['help'] = t('The title of the comment.');
    $data['comment']['subject']['field']['default_formatter'] = 'comment_permalink';

    $data['comment']['name']['title'] = t('Author');
    $data['comment']['name']['help'] = t("The name of the comment's author. Can be rendered as a link to the author's homepage.");
    $data['comment']['name']['field']['default_formatter'] = 'comment_username';

    $data['comment']['homepage']['title'] = t("Author's website");
    $data['comment']['homepage']['help'] = t("The website address of the comment's author. Can be rendered as a link. Will be empty if the author is a registered user.");

    $data['comment']['mail']['help'] = t('Email of user that posted the comment. Will be empty if the author is a registered user.');

    $data['comment']['created']['title'] = t('Post date');
    $data['comment']['created']['help'] = t('Date and time of when the comment was created.');

    $data['comment']['created_fulldata'] = [
      'title' => t('Created date'),
      'help' => t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_fulldate',
      ],
    ];

    $data['comment']['created_year_month'] = [
      'title' => t('Created year + month'),
      'help' => t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year_month',
      ],
    ];

    $data['comment']['created_year'] = [
      'title' => t('Created year'),
      'help' => t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_year',
      ],
    ];

    $data['comment']['created_month'] = [
      'title' => t('Created month'),
      'help' => t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_month',
      ],
    ];

    $data['comment']['created_day'] = [
      'title' => t('Created day'),
      'help' => t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_day',
      ],
    ];

    $data['comment']['created_week'] = [
      'title' => t('Created week'),
      'help' => t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'created',
        'id' => 'date_week',
      ],
    ];

    $data['comment']['changed']['title'] = t('Updated date');
    $data['comment']['changed']['help'] = t('Date and time of when the comment was last updated.');

    $data['comment']['changed_fulldata'] = [
      'title' => t('Changed date'),
      'help' => t('Date in the form of CCYYMMDD.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_fulldate',
      ],
    ];

    $data['comment']['changed_year_month'] = [
      'title' => t('Changed year + month'),
      'help' => t('Date in the form of YYYYMM.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year_month',
      ],
    ];

    $data['comment']['changed_year'] = [
      'title' => t('Changed year'),
      'help' => t('Date in the form of YYYY.'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_year',
      ],
    ];

    $data['comment']['changed_month'] = [
      'title' => t('Changed month'),
      'help' => t('Date in the form of MM (01 - 12).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_month',
      ],
    ];

    $data['comment']['changed_day'] = [
      'title' => t('Changed day'),
      'help' => t('Date in the form of DD (01 - 31).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_day',
      ],
    ];

    $data['comment']['changed_week'] = [
      'title' => t('Changed week'),
      'help' => t('Date in the form of WW (01 - 53).'),
      'argument' => [
        'field' => 'changed',
        'id' => 'date_week',
      ],
    ];

    $data['comment']['status']['title'] = t('Approved status');
    $data['comment']['status']['help'] = t('Whether the comment is approved (or still in the moderation queue).');
    $data['comment']['status']['filter']['label'] = t('Approved comment status');
    $data['comment']['status']['filter']['type'] = 'yes-no';

    $data['comment']['approve_comment'] = [
      'field' => [
        'title' => t('Link to approve comment'),
        'help' => t('Provide a simple link to approve the comment.'),
        'id' => 'comment_link_approve',
      ],
    ];

    $data['comment']['replyto_comment'] = [
      'field' => [
        'title' => t('Link to reply-to comment'),
        'help' => t('Provide a simple link to reply to the comment.'),
        'id' => 'comment_link_reply',
      ],
    ];

    $data['comment']['entity_id']['field']['id'] = 'commented_entity';
    unset($data['comment']['entity_id']['relationship']);

    $data['comment']['comment_bulk_form'] = [
      'title' => t('Comment operations bulk form'),
      'help' => t('Add a form element that lets you run operations on multiple comments.'),
      'field' => [
        'id' => 'comment_bulk_form',
      ],
    ];

    $data['comment']['thread']['field'] = [
      'title' => t('Depth'),
      'help' => t('Display the depth of the comment if it is threaded.'),
      'id' => 'comment_depth',
    ];
    $data['comment']['thread']['sort'] = [
      'title' => t('Thread'),
      'help' => t('Sort by the threaded order. This will keep child comments together with their parents.'),
      'id' => 'comment_thread',
    ];
    unset($data['comment']['thread']['filter']);
    unset($data['comment']['thread']['argument']);

    $entities_types = \Drupal::entityTypeManager()->getDefinitions();
    // Provide a relationship for each entity type except comment.
    foreach ($entities_types as $type => $entity_type) {
      if ($type == 'comment' || !$entity_type->entityClassImplements(ContentEntityInterface::class) || !$entity_type->getBaseTable()) {
        continue;
      }
      if ($fields = \Drupal::service('comment.manager')->getFields($type)) {
        $data['comment'][$type] = [
          'relationship' => [
            'title' => $entity_type->getLabel(),
            'help' => $this->t('The @entity_type to which the comment is a reply to.', ['@entity_type' => $entity_type->getLabel()]),
            'base' => $entity_type->getBaseTable(),
            'base field' => $entity_type->getKey('id'),
            'relationship field' => 'comment_translations.entity_id',
            'id' => 'standard',
            'label' => $entity_type->getLabel(),
            'extra' => [
              [
                // The left table in this join is comment and
                // the field entity_type is from that table therefore it Should
                // be "left_field" and not "field".
                'left_field' => 'comment_translations.entity_type',
                'value' => $type,
                'table' => 'comment',
              ],
            ],
          ],
        ];
      }
    }

    $data['comment']['uid']['title'] = t('Author uid');
    $data['comment']['uid']['help'] = t('If you need more fields than the uid add the comment: author relationship');
    $data['comment']['uid']['relationship']['title'] = t('Author');
    $data['comment']['uid']['relationship']['help'] = t("The User ID of the comment's author.");
    $data['comment']['uid']['relationship']['label'] = t('author');

    $data['comment']['pid']['title'] = t('Parent CID');
    $data['comment']['pid']['relationship']['title'] = t('Parent comment');
    $data['comment']['pid']['relationship']['help'] = t('The parent comment');
    $data['comment']['pid']['relationship']['label'] = t('parent');

    // Define the base group of this table. Fields that don't have a group defined
    // will go into this field by default.
    $data['comment_entity_statistics']['table']['group'] = t('Comment Statistics');

    // Provide a relationship for each entity type except comment.
    foreach ($entities_types as $type => $entity_type) {
      if ($type == 'comment' || !$entity_type->entityClassImplements(ContentEntityInterface::class) || !$entity_type->getBaseTable()) {
        continue;
      }
      // This relationship does not use the 'field id' column, if the entity has
      // multiple comment-fields, then this might introduce duplicates, in which
      // case the site-builder should enable aggregation and SUM the comment_count
      // field. We cannot create a relationship from the base table to
      // {comment_entity_statistics} for each field as multiple joins between
      // the same two tables is not supported.
      if (\Drupal::service('comment.manager')->getFields($type)) {
        $data['comment_entity_statistics']['table']['join'][$entity_type->getBaseTable()] = [
          'type' => 'INNER',
          'left_field' => $entity_type->getKey('id'),
          'field' => 'entity_id',
          'extra' => [
            [
              'field' => 'entity_type',
              'value' => $type,
            ],
          ],
        ];
      }
    }

    $data['comment_entity_statistics']['last_comment_timestamp'] = [
      'title' => t('Last comment time'),
      'help' => t('Date and time of when the last comment was posted.'),
      'field' => [
        'id' => 'comment_last_timestamp',
      ],
      'sort' => [
        'id' => 'date',
      ],
      'filter' => [
        'id' => 'date',
      ],
    ];

    $data['comment_entity_statistics']['last_comment_name'] = [
      'title' => t("Last comment author"),
      'help' => t('The name of the author of the last posted comment.'),
      'field' => [
        'id' => 'comment_ces_last_comment_name',
        'no group by' => TRUE,
      ],
      'sort' => [
        'id' => 'comment_ces_last_comment_name',
        'no group by' => TRUE,
      ],
    ];

    $data['comment_entity_statistics']['comment_count'] = [
      'title' => t('Comment count'),
      'help' => t('The number of comments an entity has.'),
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'standard',
      ],
    ];

    $data['comment_entity_statistics']['last_updated'] = [
      'title' => t('Updated/commented date'),
      'help' => t('The most recent of last comment posted or entity updated time.'),
      'field' => [
        'id' => 'comment_ces_last_updated',
        'no group by' => TRUE,
      ],
      'sort' => [
        'id' => 'comment_ces_last_updated',
        'no group by' => TRUE,
      ],
      'filter' => [
        'id' => 'comment_ces_last_updated',
      ],
    ];

    $data['comment_entity_statistics']['cid'] = [
      'title' => t('Last comment CID'),
      'help' => t('Display the last comment of an entity'),
      'relationship' => [
        'title' => t('Last comment'),
        'help' => t('The last comment of an entity.'),
        'group' => t('Comment'),
        'base' => 'comment',
        'base field' => 'cid',
        'id' => 'standard',
        'label' => t('Last Comment'),
      ],
    ];

    $data['comment_entity_statistics']['last_comment_uid'] = [
      'title' => t('Last comment uid'),
      'help' => t('The User ID of the author of the last comment of an entity.'),
      'relationship' => [
        'title' => t('Last comment author'),
        'base' => 'users',
        'base field' => 'uid',
        'id' => 'standard',
        'label' => t('Last comment author'),
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'field' => [
        'id' => 'numeric',
      ],
    ];

    $data['comment_entity_statistics']['entity_type'] = [
      'title' => t('Entity type'),
      'help' => t('The entity type to which the comment is a reply to.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];
    $data['comment_entity_statistics']['field_name'] = [
      'title' => t('Comment field name'),
      'help' => t('The field name from which the comment originated.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    return $data;
  }

}
