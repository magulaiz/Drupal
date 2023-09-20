<?php

namespace Drupal\field_ui\FormElement;

/**
 * Provides common functionality for entity display elements.
 */
trait EntityDisplayElementTrait {

  /**
   * Adds labels to the components.
   *
   * @param array $parent_build
   *   Parent translation build data.
   * @param array $element_names
   *   Associative array of element names and layout builder flag.
   * @param array $components
   *   The components.
   * @param string $target_type_id
   *   The target type id.
   * @param string $bundle_name
   *   The bundle name.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function addLabels(array &$parent_build, array $element_names, array $components, string $target_type_id, string $bundle_name): void {

    /** @var \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager */
    $field_type_manager = \Drupal::service('plugin.manager.field.field_type');
    /** @var \Drupal\Core\Field\WidgetPluginManager $field_widget_manager */
    $field_widget_manager = \Drupal::service('plugin.manager.field.widget');
    /** @var \Drupal\Core\Field\FormatterPluginManager $field_formatter_manager */
    $field_formatter_manager = \Drupal::service('plugin.manager.field.formatter');
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager */
    $field_manager = \Drupal::service('entity_field.manager');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $field_manager->getFieldDefinitions($target_type_id, $bundle_name);

    foreach ($element_names as $component_name => $layout_builder) {
      // Not considering the layout builder case.
      $item = &$parent_build['content'][$component_name];
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

        if (str_starts_with($this->element->getName(), 'core.entity_view_display')) {
          /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $entity */
          // Set formatter type name if available.
          $formatter_options = $field_formatter_manager->getOptions($definition->getType());
          if (isset($formatter_options[$component_type]) && isset($item['settings'])) {
            $item['settings']['#title'] = t("%label format settings", ['%label' => $formatter_options[$component_type]]);
          }
        }
        else {
          /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity */
          // Set widget name if available.
          $widget_options = $field_widget_manager->getOptions($definition->getType());
          if (isset($widget_options[$component_type]) && isset($item['settings'])) {
            $item['settings']['#title'] = t("%label widget settings", ['%label' => $widget_options[$component_type]]);
          }
        }

      }
    }

  }

}
