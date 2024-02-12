<?php

namespace Drupal\mongodb\ViewsData;

/**
 * The MongoDB implementation of \Drupal\file\FileViewsData.
 */
class FileViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // @TODO There is no corresponding information in entity metadata.
    $data['file_managed']['table']['base']['help'] = t('Files maintained by Drupal and various modules.');
    $data['file_managed']['table']['base']['defaults']['field'] = 'filename';
    $data['file_managed']['table']['wizard_id'] = 'file_managed';

    $data['file_managed']['fid']['argument'] = [
      'id' => 'file_fid',
      // The field to display in the summary.
      'name field' => 'filename',
      'numeric' => TRUE,
    ];
    $data['file_managed']['fid']['relationship'] = [
      'title' => t('File usage'),
      'help' => t('Relate file entities to their usage.'),
      'id' => 'standard',
      'base' => 'file_usage',
      'base field' => 'fid',
      'field' => 'fid',
      'label' => t('File usage'),
    ];

    $data['file_managed']['uri']['field']['default_formatter'] = 'file_uri';

    $data['file_managed']['filemime']['field']['default_formatter'] = 'file_filemime';

    $data['file_managed']['extension'] = [
      'title' => t('Extension'),
      'help' => t('The extension of the file.'),
      'real field' => 'filename',
      'field' => [
        'entity_type' => 'file',
        'field_name' => 'filename',
        'default_formatter' => 'file_extension',
        'id' => 'field',
        'click sortable' => FALSE,
      ],
    ];

    $data['file_managed']['filesize']['field']['default_formatter'] = 'file_size';

    $data['file_managed']['status']['field']['default_formatter_settings'] = [
      'format' => 'custom',
      'format_custom_false' => t('Temporary'),
      'format_custom_true' => t('Permanent'),
    ];
    $data['file_managed']['status']['filter']['id'] = 'file_status';

    $data['file_managed']['uid']['relationship']['title'] = t('User who uploaded');
    $data['file_managed']['uid']['relationship']['label'] = t('User who uploaded');

    $data['file_usage']['table']['group'] = t('File Usage');

    // Provide field-type-things to several base tables; on the core files table
    // ("file_managed") so that we can create relationships from files to
    // entities, and then on each core entity type base table so that we can
    // provide general relationships between entities and files.
    $data['file_usage']['table']['join'] = [
      'file_managed' => [
        'field' => 'fid',
        'left_field' => 'fid',
        // For MongoDB the join result needs to be unwound.
        'one_to_many' => TRUE,
      ],
      // Link ourselves to the {node} table
      // so we can provide node->file relationships.
      'node' => [
        'field' => 'id',
        'left_field' => 'nid',
        'extra' => [['field' => 'type', 'value' => 'node']],
      ],
      // Link ourselves to the {users} table
      // so we can provide user->file relationships.
      'users' => [
        'field' => 'id',
        'left_field' => 'uid',
        'extra' => [['field' => 'type', 'value' => 'user']],
      ],
      // Link ourselves to the {comment} table
      // so we can provide comment->file relationships.
      'comment' => [
        'field' => 'id',
        'left_field' => 'cid',
        'extra' => [['field' => 'type', 'value' => 'comment']],
      ],
      // Link ourselves to the {taxonomy_term_data} table
      // so we can provide taxonomy_term->file relationships.
      'taxonomy_term_data' => [
        'field' => 'id',
        'left_field' => 'tid',
        'extra' => [['field' => 'type', 'value' => 'taxonomy_term']],
      ],
    ];

    // Provide a relationship between the files table and each entity type,
    // and between each entity type and the files table. Entity->file
    // relationships are type-restricted in the joins declared above, and
    // file->entity relationships are type-restricted in the relationship
    // declarations below.

    // Describes relationships between files and nodes.
    $data['file_usage']['file_to_node'] = [
      'title' => t('Content'),
      'help' => t('Content that is associated with this file, usually because this file is in a field on the content.'),
      // Only provide this field/relationship/etc.,
      // when the 'file_managed' base table is present.
      'skip base' => ['node', 'node_field_revision', 'users', 'comment', 'taxonomy_term_data'],
      'real field' => 'id',
      'relationship' => [
        'title' => t('Content'),
        'label' => t('Content'),
        'base' => 'node',
        'base field' => 'nid',
        'relationship field' => 'id',
        'extra' => [['table' => 'file_usage', 'field' => 'type', 'operator' => '=', 'value' => 'node']],
      ],
    ];
    $data['file_usage']['node_to_file'] = [
      'title' => t('File'),
      'help' => t('A file that is associated with this node, usually because it is in a field on the node.'),
      // Only provide this field/relationship/etc.,
      // when the 'node' base table is present.
      'skip base' => ['file_managed', 'users', 'comment', 'taxonomy_term_data'],
      'real field' => 'fid',
      'relationship' => [
        'title' => t('File'),
        'label' => t('File'),
        'base' => 'file_managed',
        'base field' => 'fid',
        'relationship field' => 'fid',
      ],
    ];

    // Describes relationships between files and users.
    $data['file_usage']['file_to_user'] = [
      'title' => t('User'),
      'help' => t('A user that is associated with this file, usually because this file is in a field on the user.'),
      // Only provide this field/relationship/etc.,
      // when the 'file_managed' base table is present.
      'skip base' => ['node', 'node_field_revision', 'users', 'comment', 'taxonomy_term_data'],
      'real field' => 'id',
      'relationship' => [
        'title' => t('User'),
        'label' => t('User'),
        'base' => 'users',
        'base field' => 'uid',
        'relationship field' => 'id',
        'extra' => [['table' => 'file_usage', 'field' => 'type', 'operator' => '=', 'value' => 'user']],
      ],
    ];
    $data['file_usage']['user_to_file'] = [
      'title' => t('File'),
      'help' => t('A file that is associated with this user, usually because it is in a field on the user.'),
      // Only provide this field/relationship/etc.,
      // when the 'users' base table is present.
      'skip base' => ['file_managed', 'node', 'node_field_revision', 'comment', 'taxonomy_term_data'],
      'real field' => 'fid',
      'relationship' => [
        'title' => t('File'),
        'label' => t('File'),
        'base' => 'file_managed',
        'base field' => 'fid',
        'relationship field' => 'fid',
      ],
    ];

    // Describes relationships between files and comments.
    $data['file_usage']['file_to_comment'] = [
      'title' => t('Comment'),
      'help' => t('A comment that is associated with this file, usually because this file is in a field on the comment.'),
      // Only provide this field/relationship/etc.,
      // when the 'file_managed' base table is present.
      'skip base' => ['node', 'node_field_revision', 'users', 'comment', 'taxonomy_term_data'],
      'real field' => 'id',
      'relationship' => [
        'title' => t('Comment'),
        'label' => t('Comment'),
        'base' => 'comment',
        'base field' => 'cid',
        'relationship field' => 'id',
        'extra' => [['table' => 'file_usage', 'field' => 'type', 'operator' => '=', 'value' => 'comment']],
      ],
    ];
    $data['file_usage']['comment_to_file'] = [
      'title' => t('File'),
      'help' => t('A file that is associated with this comment, usually because it is in a field on the comment.'),
      // Only provide this field/relationship/etc.,
      // when the 'comment' base table is present.
      'skip base' => ['file_managed', 'node', 'node_field_revision', 'users', 'taxonomy_term_data'],
      'real field' => 'fid',
      'relationship' => [
        'title' => t('File'),
        'label' => t('File'),
        'base' => 'file_managed',
        'base field' => 'fid',
        'relationship field' => 'fid',
      ],
    ];

    // Describes relationships between files and taxonomy_terms.
    $data['file_usage']['file_to_taxonomy_term'] = [
      'title' => t('Taxonomy Term'),
      'help' => t('A taxonomy term that is associated with this file, usually because this file is in a field on the taxonomy term.'),
      // Only provide this field/relationship/etc.,
      // when the 'file_managed' base table is present.
      'skip base' => ['node', 'node_field_revision', 'users', 'comment', 'taxonomy_term__data'],
      'real field' => 'id',
      'relationship' => [
        'title' => t('Taxonomy Term'),
        'label' => t('Taxonomy Term'),
        'base' => 'taxonomy_term_data',
        'base field' => 'tid',
        'relationship field' => 'id',
        'extra' => [['table' => 'file_usage', 'field' => 'type', 'operator' => '=', 'value' => 'taxonomy_term']],
      ],
    ];
    $data['file_usage']['taxonomy_term_to_file'] = [
      'title' => t('File'),
      'help' => t('A file that is associated with this taxonomy term, usually because it is in a field on the taxonomy term.'),
      // Only provide this field/relationship/etc.,
      // when the 'taxonomy_term_data' base table is present.
      'skip base' => ['file_managed', 'node', 'node_field_revision', 'users', 'comment'],
      'real field' => 'fid',
      'relationship' => [
        'title' => t('File'),
        'label' => t('File'),
        'base' => 'file_managed',
        'base field' => 'fid',
        'relationship field' => 'fid',
      ],
    ];

    // Provide basic fields from the {file_usage} table to all of the base tables
    // we've declared joins to, because there is no 'skip base' property on these
    // fields.
    $data['file_usage']['module'] = [
      'title' => t('Module'),
      'help' => t('The module managing this file relationship.'),
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
    $data['file_usage']['type'] = [
      'title' => t('Entity type'),
      'help' => t('The type of entity that is related to the file.'),
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
    $data['file_usage']['id'] = [
      'title' => t('Entity ID'),
      'help' => t('The ID of the entity that is related to the file.'),
      'field' => [
        'id' => 'numeric',
      ],
      'argument' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];
    $data['file_usage']['count'] = [
      'title' => t('Use count'),
      'help' => t('The number of times the file is used by this entity.'),
      'field' => [
        'id' => 'numeric',
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];
    $data['file_usage']['entity_label'] = [
      'title' => t('Entity label'),
      'help' => t('The label of the entity that is related to the file.'),
      'real field' => 'id',
      'field' => [
        'id' => 'entity_label',
        'entity type field' => 'type',
      ],
    ];

    return $data;
  }

}
