<?php

namespace Drupal\field_ui\FormElement;

use Drupal\Component\Plugin\PluginBase;
use Drupal\config_translation\FormElement\ListElement;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;

/**
 *
 */
class EntityViewDisplayElement extends ListElement {
  use EntityDisplayElementTrait;

  /**
   *
   */
  public function getTranslationBuild(
    LanguageInterface $source_language,
    LanguageInterface $translation_language,
    $source_config,
    $translation_config,
    array $parents,
    $base_key = NULL
  ) {
    // @todo Would this be a way we could get rid of
    //   field_ui_form_config_translation_form_alter() or at least make it
    //   simpler?
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
    $build_element_names = [];
    if (isset($parent_build['third_party_settings']['layout_builder']['sections'])) {
      $build_element_names = $this->layoutBuilderGetElementNames($parent_build);
      $build_element_names = array_fill_keys($build_element_names, TRUE);
    }
    if (isset($parent_build['content'])) {
      $parent_build['content']['#collapsible'] = FALSE;
      $element_names = array_intersect(array_keys($components), Element::children($parent_build['content']), array_keys($field_definitions));
      $element_names = array_fill_keys($element_names, FALSE);
      $element_names = array_merge($build_element_names, $element_names);
    }

    if (empty($element_names)) {
      return $parent_build;
    }

    $this->addLabels($parent_build, $element_names, $components, $target_type_id, $bundle_name);

    return $parent_build;
  }

  /**
   * Returns the layout builder form element names.
   */
  public function layoutBuilderGetElementNames($parent_build): array {
    // Configuration name will be also used as element name.
    $entities = LayoutBuilderEntityViewDisplay::loadMultiple();
    $element_name = $this->element->getName();
    $entity = $entities[str_replace("core.entity_view_display.", "", $element_name)];
    /** @var \Drupal\layout_builder\Section $section */
    foreach ($entity->getSections() as $section_index => $section) {
      $section_components = $section->getComponents();
      if (empty($section_components)) {
        continue;
      }
      foreach ($section_components as $component) {
        if (!str_starts_with($component->getPluginId(), 'field_block:') && !str_starts_with($component->getPluginId(), 'extra_field_block:')) {
          continue;
        }
        if (!isset($component->get('configuration')['formatter']['settings']) || empty($component->get('configuration')['formatter']['settings'])) {
          continue;
        }
        if (!isset($parent_build['third_party_settings']['layout_builder']['sections'][$section_index]['components'][$component->getUuid()]['configuration']['formatter']['settings'])) {
          continue;
        }
        try {
          [,,, $field_name] = explode(PluginBase::DERIVATIVE_SEPARATOR, $component->getPluginId(), 4);
        }
        catch (\Exception) {
          continue;
        }
        $element_names[] = $section_index . PluginBase::DERIVATIVE_SEPARATOR . $component->getUuid() . PluginBase::DERIVATIVE_SEPARATOR . $field_name;
      }
    }
    return $element_names;
  }

}
