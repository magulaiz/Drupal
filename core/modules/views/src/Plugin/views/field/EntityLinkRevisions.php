<?php

namespace Drupal\views\Plugin\views\field;

use Drupal\Core\GeneratedUrl;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\ResultRow;

/**
 * Field handler to present a link to edit an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_link_revisions")
 */
class EntityLinkRevisions extends EntityLink {

  /**
   * {@inheritdoc}
   */
  protected function getEntityLinkTemplate(): string {
    return 'version-history';
  }

  /**
   * {@inheritdoc}
   */
  protected function renderLink(ResultRow $row): GeneratedUrl|string {
    $this->options['alter']['query'] = $this->getDestinationArray();
    return parent::renderLink($row);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultLabel(): string|TranslatableMarkup {
    return $this->t('View revisions');
  }

}
