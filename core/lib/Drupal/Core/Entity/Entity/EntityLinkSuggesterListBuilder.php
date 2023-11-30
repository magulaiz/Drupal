<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Entity;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of entity link suggester entities.
 *
 * @see \Drupal\Core\Entity\Entity\EntityLinkSuggester
 */
class EntityLinkSuggesterListBuilder extends DraggableListBuilder {

  /**
   * Constructs a new EntityLinkSuggesterListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly EntityTypeBundleInfoInterface $entityTypeBundleInfo,
  ) {
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_link_suggesters_collection';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Administrative label');
    $header['suggestions'] = t('Provided link suggestions');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    assert($entity instanceof EntityLinkSuggesterInterface);
    $row['admin_label'] = $entity->label();
    $entity_types = $entity->getEntityTypes();
    if ($entity_types === NULL) {
      $row['suggestions']['data'] = ['#markup' => '<em>' . $this->t('Everything') . '</em>'];
    }
    else {
      $items = [];
      foreach ($entity_types as $entity_type_id => $detailed_settings) {
        $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
        $label = $entity_type->getCollectionLabel();
        $bundle_labels = $entity_type->getBundleEntityType()
          ? array_column($this->entityTypeBundleInfo->getBundleInfo($entity_type_id), 'label')
          : [];
        if (!$entity_type->getBundleEntityType()) {
          $items[] = $entity_type->getCollectionLabel();
        }
        else {
          if ($detailed_settings['bundles'] === NULL) {
            $items[] = $this->t('@linkable-entity-type-label <small>(<em>all</em> @bundle-label)</small>', [
              '@linkable-entity-type-label' => $label,
              '@bundle-label' => $this->entityTypeManager
                ->getDefinition($entity_type->getBundleEntityType())
                ->getPluralLabel(),
            ]);
          }
          else {
            $items[] = $this->t('@linkable-entity-type-label <small>(only @included-bundle-label-list)</small>', [
              '@linkable-entity-type-label' => $label,
              '@included-bundle-label-list' => implode(', ', array_intersect_key($bundle_labels, $detailed_settings['bundles'])),
            ]);
          }
        }
      }
      $row['suggestions']['data'] = [
        '#list_type' => 'ol',
        '#theme' => 'item_list',
        '#items' => $items,
      ];
    }
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->messenger->addStatus($this->t('The configuration options have been saved.'));
  }

}
