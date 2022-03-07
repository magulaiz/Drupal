/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words documentselection */
import { Command } from 'ckeditor5/src/core';
import getCommandGroupNameFromGroup from './utils';

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
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} styles
 *   All available Drupal Element Styles.
 *
 * @return {boolean}
 *   Does the schema contain the attribute?
 */
function schemaContainsAttribute(selectedElement, schema, styles) {
  // eslint-disable-next-line no-restricted-syntax
  for (const group of Object.keys(styles)) {
    const groupName = group[0].toUpperCase() + group.substring(1);
    return schema.checkAttribute(
      selectedElement,
      `drupalElementStyle${groupName}`,
    );
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
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} styles
 *   All available Drupal Element Styles.
 *
 * @return {null|module:engine/model/element~Element}
 *   The closest element that supports element styles.
 */
function getClosestElementWithElementStyleAttribute(selection, schema, styles) {
  const selectedElement = selection.getSelectedElement();
  if (
    selectedElement &&
    schemaContainsAttribute(selectedElement, schema, styles)
  ) {
    return selectedElement;
  }

  let parent = selection.getFirstPosition().parent;

  while (parent) {
    if (
      parent.is('element') &&
      schema.checkAttribute(parent, 'drupalElementStyle')
    ) {
      return parent;
    }

    parent = parent.parent;
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
   * @param {Drupal.CKEditor5~DrupalElementStyle[]} styles
   *   All available Drupal Element Styles.
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

    // The element needs to be checked against list of possible attributes then
    // update the value to include all drupalElementStyles selected for the element.
    if (this.isEnabled && this.containsAttribute(element)) {
      this.value = this.getGroupAndAttribute(element);
    } else {
      this.value = false;
      // If value is falsy, check if there is a default style to apply to the
      // element.
      if (!this.value) {
        // @todo need to add support for default styles.
        // eslint-disable-next-line no-restricted-syntax
        // for (const [name, style] of this._styles.entries()) {
        //   if (style.isDefault) {
        //     const appliesToCurrentElement = style.modelElements.find(
        //       (modelElement) => element.is('element', modelElement),
        //     );
        //     if (appliesToCurrentElement) {
        //       this.value = name;
        //       break;
        //     }
        //   }
        // }
      }
    }
  }

  /**
   * Checks if an element has a drupalElementStyle attribute.
   *
   * @param {module:engine/model/element~Element|null} element
   *   The element.
   *
   * @return {boolean}
   *   Does the element have a drupalElementStyle attribute?
   */
  containsAttribute(element) {
    // eslint-disable-next-line no-restricted-syntax
    for (const group of Object.keys(this.styles)) {
      const groupName = group[0].toUpperCase() + group.substring(1);
      if (element.hasAttribute(`drupalElementStyle${groupName}`)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Gets the group(s) and attribute(s) of the element.
   *
   * @example {drupalAlign: 'alignLeft', drupalViewMode: 'full'}
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
      const groupName = group[0].toUpperCase() + group.substring(1);
      const commandGroupName = getCommandGroupNameFromGroup(group);
      if (element.hasAttribute(`drupalElementStyle${groupName}`)) {
        groupAttr[`${commandGroupName}`] = element.getAttribute(
          `drupalElementStyle${groupName}`,
        );
      }
    });
    return groupAttr;
  }

  /**
   * Executes the command and applies the style to the selected model element.
   *
   * @example
   *    editor.execute('drupalElementStyle', { value: {drupalAlign: 'alignLeft' }, groupName: 'align' });
   *
   * @param {Object} options
   *   The command options.
   * @param {string} options.value
   *   The name of the style as configured in the Drupal Element style
   *   configuration.
   * @param {string} options.groupName
   *   The group name of the drupalElementStyle.
   */
  execute(options = {}) {
    const { editor } = this;
    const { model } = editor;
    const groupName = Object.values(options)[1];
    const groupNameCap = groupName[0].toUpperCase() + groupName.substring(1);
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
        this._styles[groupName].get(requestedStyle[modelGroupName]).isDefault
      ) {
        // Remove value from the object.
        writer.removeAttribute(`drupalElementStyle${groupNameCap}`, element);
      } else {
        // Extend the object with new value.
        writer.setAttribute(
          `drupalElementStyle${groupNameCap}`,
          requestedStyle[modelGroupName],
          element,
        );
      }
    });
  }
}
