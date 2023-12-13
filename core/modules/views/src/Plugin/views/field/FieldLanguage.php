<?php

namespace Drupal\views\Plugin\views\field;

use Drupal\Core\Session\AccountInterface;

/**
 * Displays the language of an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("field_language")
 */
class FieldLanguage extends EntityField {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // Don't display the field in case the site is not multilingual, because
    // there is no point in doing so.
    if (!$this->languageManager->isMultilingual()) {
      return FALSE;
    }

    return parent::access($account);
  }

}
