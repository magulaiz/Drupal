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

    /** @var \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager */
    $field_type_manager = \Drupal::service('plugin.manager.field.field_type');
    /** @var \Drupal\Core\Field\WidgetPluginManager $field_widget_manager */
    $field_widget_manager = \Drupal::service('plugin.manager.field.widget');
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager */
    $field_manager = \Drupal::service('entity_field.manager');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $field_manager->getFieldDefinitions($target_type_id, $bundle_name);

    $layout_builder = FALSE;
    if (isset($parent_build['third_party_settings']['layout_builder']['sections'])) {
      $layout_builder = TRUE;
      $element_names = $this->layoutBuilderGetElementNames($parent_build);
    }
    else {
      $parent_build['content']['#collapsible'] = FALSE;
      $element_names = array_intersect(array_keys($components), Element::children($parent_build['content']), array_keys($field_definitions));
    }

    foreach ($element_names as $component_name) {
      // Not considering the layout builder case.
      if ($layout_builder) {
        [$i, $j, $component_name] = explode(PluginBase::DERIVATIVE_SEPARATOR, $component_name, 3);
        // phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
        $item = &$parent_build['third_party_settings']['layout_builder']['sections'][$i]['components'][$j]['configuration']['formatter'];
      }
      else {
        $item = &$parent_build['content'][$component_name];
      }
      /** @var \Drupal\Core\Field\FieldDefinitionInterface $definition */
      $definition = $field_definitions[$component_name] ?? NULL;
      if ($definition) {
        $field_type = $field_type_manager->getDefinition($definition->getType());

        $item['#title'] = $definition->getLabel();
        $item['#description'] = t("Field: %name, type: @type", [
          '%name' => $component_name,
          '@type' => $field_type['label'],
        ]);
        // Set open state to let user reach settings without additional clicks.
        if (isset($item['#open'])) {
          $item['#open'] = TRUE;
        }

        $component_type = $layout_builder ? $field_type['id'] : $components[$component_name]['type'];
        if (isset($item['settings']['#open'])) {
          $item['settings']['#open'] = TRUE;
        }

        /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity */
        // Set widget name if available.
        $widget_options = $field_widget_manager->getOptions($definition->getType());
        if (isset($widget_options[$component_type]) && isset($item['settings'])) {
          $item['settings']['#title'] = t("%label widget settings", ['%label' => $widget_options[$component_type]]);
        }
      }
    }

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
    foreach ($entity->getSections() as $i => $section) {
      $section_components = $section->toArray()['components'];
      if (empty($section_components)) {
        continue;
      }
      foreach ($section_components as $j => $component) {
        if (!str_starts_with($component['configuration']['id'], 'field_block:')) {
          continue;
        }
        if (!isset($component['configuration']['formatter']['settings']) || empty($component['configuration']['formatter']['settings'])) {
          continue;
        }
        if (!isset($parent_build['third_party_settings']['layout_builder']['sections'][$i]['components'][$j]['configuration']['formatter']['settings'])) {
          continue;
        }
        try {
          [,,, $field_name] = explode(PluginBase::DERIVATIVE_SEPARATOR, $component['configuration']['id'], 4);
        }
        catch (\Exception) {
          continue;
        }
        $element_names[] = $i . PluginBase::DERIVATIVE_SEPARATOR . $j . PluginBase::DERIVATIVE_SEPARATOR . $field_name;
      }
    }
    return $element_names;
  }

}
