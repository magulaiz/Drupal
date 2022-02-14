/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words documentselection */
import { Command } from 'ckeditor5/src/core';

/**
 * @module drupalMedia/drupalelementstyle/drupalelementstylecommand
 */

function schemaContainsAttribute(selectedElement, schema, styles) {
  for (const group of Object.keys(styles)) {
    const groupName = group[0].toUpperCase() + group.substring(1);
    return schema.checkAttribute(selectedElement, `drupal${groupName}`);
  }
  return false;
}

/**
 * Gets closest element that has any DrupalElementStyle attribute in schema.
 *
 * @param {module:engine/model/documentselection~DocumentSelection} selection
 *   The current document selection.
 * @param {module:engine/model/schema~Schema} schema
 *   The model schema.
 *
 * @return {null|module:engine/model/element~Element}
 *   The closest element that supports element styles.
 */
// find the closest element with the drupal element style
// also checks the ancestor
function getClosestElementWithElementStyleAttribute(selection, schema, styles) {
  // dynamically check for attributes
  const selectedElement = selection.getSelectedElement();
  console.log(selectedElement);

  return selectedElement &&
    // here checks schema for if any of the drupal element styles with this attribute name exists
    schemaContainsAttribute(selectedElement, schema, styles)
    ? selectedElement
    : selection
        // if false find the closest element that has drupal style element allowed
      // todo: need to change this
        .getFirstPosition()
        .findAncestor((element) =>
          schema.checkAttribute(element, 'drupalElementStyle'),
        );
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
    for (const group of Object.keys(styles)) {
      // eslint-disable-next-line no-restricted-syntax
      this._styles[group] = new Map(
        styles[group].map((style) => {
          return [style.name, style];
        }),
      );
    }
  }

  /**
   * @inheritDoc
   */
  // this is called every time the model changes
  // to make sure command has the correct state
  refresh() {
    const { editor } = this;
    const element = getClosestElementWithElementStyleAttribute(
      editor.model.document.selection,
      editor.model.schema,
      this.styles,
    );
    console.log('element ', element);

    this.isEnabled = !!element;

    if (!this.isEnabled) {
      console.log('not enabled');
      this.value = false;
      // here element needs to be checked against list of possible attributes
      // and then update the value to include all drupal element styles selected for the element
    } else if (this.containsAttribute(element)) {
      console.log('true');
      this.value = this.getGroupAndAttribute(element);
    } else {
      this.value = false;
    }
  }

  containsAttribute(element) {
    for (const group of Object.keys(this.styles)) {
      const groupName = group[0].toUpperCase() + group.substring(1);
      return element.hasAttribute(`drupal${groupName}`);
    }
    return false;
  }

  getGroupAndAttribute(element) {
    // const drupalStyles = { drupalAlign: '', drupalViewMode: '' };
    const groupAttr = {};
    for (const group of Object.keys(this.styles)) {
      const groupName = group[0].toUpperCase() + group.substring(1);
      if (element.hasAttribute(`drupal${groupName}`)) {
        groupAttr[`drupal${groupName}`] = element.getAttribute(
          `drupal${groupName}`,
        );
      }
    }
    console.log('groupAttr ', groupAttr);
    return groupAttr;
  }

  /**
   * Executes the command and applies the style to the selected model element.
   *
   * @example
   *    editor.execute('drupalElementStyle', { value: 'alignLeft' });
   *
   * @param {Object} options
   *   The command options.
   * @param {string} options.value
   *   The name of the style as configured in the Drupal Element style
   *   configuration.
   */
  // makes the actual change in the MODEL
  // execute needs to change to take value and the group
  execute(options = {}) {
    console.log('execute');
    const { editor } = this;
    const { model } = editor;

    model.change((writer) => {
      const requestedStyle = options.value;
      const element = getClosestElementWithElementStyleAttribute(
        model.document.selection,
        model.schema,
      );

      // handle group, retrieve style from correct group
      if (!requestedStyle || this._styles.get(requestedStyle).isDefault) {
        writer.removeAttribute('drupalElementStyle', element);
      } else {
        writer.setAttribute('drupalElementStyle', requestedStyle, element);
      }
    });
  }
}
