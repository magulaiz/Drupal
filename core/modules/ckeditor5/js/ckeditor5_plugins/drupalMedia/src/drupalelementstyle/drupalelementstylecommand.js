/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words documentselection */
import { Command } from 'ckeditor5/src/core';
import { getModelAttributeKeyFromGroup } from '../utils';

/**
 * @module drupalMedia/drupalelementstyle/drupalelementstylecommand
 */

/**
 * Checks the schema if any of the drupalElementStyles with the given attribute name exists.
 *
 * @param {module:engine/model/element~Element|null} selectedElement
 *   The selected element.
 * @param {module:engine/model/schema~Schema} schema
 *   The model schema.
 * @param {string[]} modelAttributes
 *   Array of model attribute keys.
 *
 * @return {boolean}
 *   Does the schema contain the attribute?
 */
function schemaContainsAttribute(selectedElement, schema, modelAttributes) {
  // eslint-disable-next-line no-restricted-syntax
  for (const modelAttribute of modelAttributes) {
    if (schema.checkAttribute(selectedElement, modelAttribute)) {
      return true;
    }
  }
  return false;
}

/**
 * Gets closest element that has any drupalElementStyle attribute in schema.
 *
 * @param {module:engine/model/documentselection~DocumentSelection} selection
 *   The current document selection.
 * @param {module:engine/model/schema~Schema} schema
 *   The model schema.
 * @param {Drupal.CKEditor5~DrupalElementStyles} styles
 *   All available Drupal Element Styles.
 *
 * @return {null|module:engine/model/element~Element}
 *   The closest element that supports element styles.
 */
export function getClosestElementWithElementStyleAttribute(
  selection,
  schema,
  styles,
) {
  const modelAttributes = [];
  // eslint-disable-next-line no-restricted-syntax
  for (const group of Object.keys(styles)) {
    const modelAttribute = getModelAttributeKeyFromGroup(group);
    // Generate list of model attributes.
    modelAttributes.push(modelAttribute);
  }
  const selectedElement = selection.getSelectedElement();
  if (
    selectedElement &&
    schemaContainsAttribute(selectedElement, schema, modelAttributes)
  ) {
    return selectedElement;
  }

  let { parent } = selection.getFirstPosition();

  while (parent) {
    // eslint-disable-next-line no-restricted-syntax
    for (const modelAttribute of modelAttributes) {
      if (
        parent.is('element') &&
        schema.checkAttribute(parent, modelAttribute)
      ) {
        return parent;
      }
      parent = parent.parent;
    }
  }

  return null;
}

/**
 * The Drupal Element style command.
 *
 * This is used to apply Drupal Element style option to supported model elements.
 *
 * @extends module:core/command~Command
 *
 * @internal
 */
export default class DrupalElementStyleCommand extends Command {
  /**
   * Constructs a new object.
   *
   * @param {module:core/editor/editor~Editor} editor
   *   The editor instance.
   * @param {Drupal.CKEditor5~DrupalElementStyles} styles
   *   All available Drupal Element Styles.
   *
   */
  constructor(editor, styles) {
    super(editor);
    this.styles = styles;
    this._styles = {};
    Object.keys(styles).forEach((group) => {
      this._styles[group] = new Map(
        styles[group].map((style) => {
          return [style.name, style];
        }),
      );
    });
  }

  /**
   * @inheritDoc
   */
  refresh() {
    const { editor } = this;
    const element = getClosestElementWithElementStyleAttribute(
      editor.model.document.selection,
      editor.model.schema,
      this.styles,
    );

    this.isEnabled = !!element;

    if (this.isEnabled) {
      // Assign value to be corresponding command value based on the element's modelAttribute.
      this.value = this.getGroupAndAttribute(element);
    } else {
      this.value = false;
    }
  }

  /**
   * Gets the group(s) and attribute(s) of the element in the form of a command.
   *
   * @example {drupalAlign: 'left', drupalViewMode: 'full'}
   *
   * @param {module:engine/model/element~Element|null} element
   *   The element.
   *
   * @return {Object}
   * The group(s) and attribute(s) in the form of an object.
   */
  getGroupAndAttribute(element) {
    const groupAttr = {};
    Object.keys(this.styles).forEach((group) => {
      const modelAttribute = getModelAttributeKeyFromGroup(group);
      if (element.hasAttribute(modelAttribute)) {
        groupAttr[group] = element.getAttribute(modelAttribute);
      } else {
        // eslint-disable-next-line no-restricted-syntax
        for (const style of this.styles[group]) {
          // If there is no drupalElementStyle for a group, set to to the default.
          if (style.isDefault) {
            groupAttr[group] = style.name;
          }
        }
      }
    });
    return groupAttr;
  }

  /**
   * Executes the command and applies the style to the selected model element.
   *
   * @example
   *    editor.execute('drupalElementStyle', { value: { align: 'left' }, group: 'align'});
   *
   * @param {Object} options
   *   The command options.
   * @param {string} options.value
   *   The name of the style as configured in the Drupal Element style
   *   configuration.
   * @param {string} options.group
   *   The group name of the drupalElementStyle.
   */
  execute(options = {}) {
    const {
      editor: { model },
    } = this;
    const { group } = options;
    const modelAttribute = getModelAttributeKeyFromGroup(group);
    model.change((writer) => {
      const modelGroupName = Object.keys(options.value)[0];
      const requestedStyle = options.value;
      const element = getClosestElementWithElementStyleAttribute(
        model.document.selection,
        model.schema,
        this.styles,
      );
      if (
        !requestedStyle ||
        this._styles[group].get(requestedStyle[modelGroupName]).isDefault
      ) {
        // Remove attribute from the element.
        writer.removeAttribute(modelAttribute, element);
      } else {
        // Set or add the new attribute on the element.
        writer.setAttribute(
          modelAttribute,
          requestedStyle[modelGroupName],
          element,
        );
      }
    });
  }
}
