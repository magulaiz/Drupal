<?php

namespace Drupal\field_ui\FormElement;

use Drupal\config_translation\FormElement\ListElement;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;

/**
 * Adds translatable labels to entity_view_display elements.
 */
class EntityViewDisplayElement extends ListElement {
  use EntityDisplayElementTrait;

  /**
   * {@inheritdoc}
   */
  public function getTranslationBuild(
    LanguageInterface $source_language,
    LanguageInterface $translation_language,
    $source_config,
    $translation_config,
    array $parents,
    $base_key = NULL
  ) {
    $parent_build = parent::getTranslationBuild($source_language, $translation_language,
      $source_config, $translation_config, $parents,
      $base_key);

    $target_type_id = $source_config['targetEntityType'];
    $bundle_name = $source_config['bundle'];
    $components = $source_config['content'];

    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager */
    $field_manager = \Drupal::service('entity_field.manager');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $field_manager->getFieldDefinitions($target_type_id, $bundle_name);

    $element_names = [];
    if (isset($parent_build['content'])) {
      $parent_build['content']['#collapsible'] = FALSE;
      $element_names = array_intersect(array_keys($components), Element::children($parent_build['content']), array_keys($field_definitions));
      $element_names = array_fill_keys($element_names, FALSE);
    }

    if (empty($element_names)) {
      return $parent_build;
    }

    $this->addLabels($parent_build, $element_names, $components, $target_type_id, $bundle_name);

    return $parent_build;
  }

}
