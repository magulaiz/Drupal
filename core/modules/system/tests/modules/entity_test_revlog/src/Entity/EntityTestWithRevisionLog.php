<?php

declare(strict_types=1);

namespace Drupal\entity_test_revlog\Entity;

use Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;
use Drupal\Core\Entity\Form\RevisionRevertForm;
use Drupal\Core\Entity\Form\RevisionDeleteForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the test entity class.
 */
#[\Drupal\Core\Entity\Attribute\ContentEntityType(id: 'entity_test_revlog', label: new TranslatableMarkup('Test entity - revisions log'), handlers: ['access' => 'Drupal\entity_test_revlog\EntityTestRevlogAccessControlHandler', 'form' => ['default' => ContentEntityForm::class, 'revision-delete' => RevisionDeleteForm::class, 'revision-revert' => RevisionRevertForm::class], 'route_provider' => ['html' => DefaultHtmlRouteProvider::class, 'revision' => RevisionHtmlRouteProvider::class]], base_table: 'entity_test_revlog', revision_table: 'entity_test_revlog_revision', translatable: FALSE, entity_keys: ['id' => 'id', 'uuid' => 'uuid', 'revision' => 'revision_id', 'bundle' => 'type', 'label' => 'name'], revision_metadata_keys: ['revision_user' => 'revision_user', 'revision_created' => 'revision_created', 'revision_log_message' => 'revision_log_message'], links: ['add-form' => '/entity_test_revlog/add', 'canonical' => '/entity_test_revlog/manage/{entity_test_revlog}', 'delete-form' => '/entity_test/delete/entity_test_revlog/{entity_test_revlog}', 'delete-multiple-form' => '/entity_test_revlog/delete_multiple', 'edit-form' => '/entity_test_revlog/manage/{entity_test_revlog}/edit', 'revision' => '/entity_test_revlog/{entity_test_revlog}/revision/{entity_test_revlog_revision}/view', 'revision-delete-form' => '/entity_test_revlog/{entity_test_revlog}/revision/{entity_test_revlog_revision}/delete', 'revision-revert-form' => '/entity_test_revlog/{entity_test_revlog}/revision/{entity_test_revlog_revision}/revert', 'version-history' => '/entity_test_revlog/{entity_test_revlog}/revisions'])]
class EntityTestWithRevisionLog extends RevisionableContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the test entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 64)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ]);

    return $fields;
  }

  /**
   * Sets the name.
   *
   * @param string $name
   *   Name of the entity.
   *
   * @return $this
   */
  public function setName(string $name) {
    $this->set('name', $name);
    return $this;
  }

}
