<?php

namespace Drupal\field_ui\FormElement;

use Drupal\config_translation\FormElement\ListElement;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;

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

    $element_names = array_intersect(array_keys($components), Element::children($parent_build['content']), array_keys($field_definitions));

    foreach ($element_names as $component_name) {
      // Not considering the layout builder case.
      $item = $parent_build['content'][$component_name];
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

        $component_type = $components[$component_name]['type'];
        if (isset($item['settings']['#open'])) {
          $item['settings']['#open'] = TRUE;
        }

        /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity */
        // Set widget name if available.
        $widget_options = $field_widget_manager->getOptions($definition->getType());
        if (isset($widget_options[$component_type]) && isset($item['settings'])) {
          $item['settings']['#title'] = t("%label widget settings", ['%label' => $widget_options[$component_type]]);
        }

        $parent_build['content'][$component_name] = $item;
      }
    }

    return $parent_build;
  }

}
