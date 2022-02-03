/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words documentselection */
import { Command } from 'ckeditor5/src/core';

/**
 * @module drupalMedia/drupalblockstyle/drupalblockstylecommand
 */

/**
 * Gets closest element that has drupalBlockStyle attribute in schema.
 *
 * @param {module:engine/model/documentselection~DocumentSelection} selection
 *   The current document selection.
 * @param {module:engine/model/schema~Schema} schema
 *   The model schema.
 *
 * @return {null|module:engine/model/element~Element}
 */
function getClosestElementWithBlockStyleAttribute(selection, schema) {
  const selectedElement = selection.getSelectedElement();

  return selectedElement &&
    schema.checkAttribute(selectedElement, 'drupalBlockStyle')
    ? selectedElement
    : selection
        .getFirstPosition()
        .findAncestor((element) =>
          schema.checkAttribute(element, 'drupalBlockStyle'),
        );
}

/**
 * The Drupal Block style command.
 *
 * This is used to apply Drupal Block style option to supported model elements.
 *
 * @extends module:core/command~Command
 *
 * @internal
 */
export default class DrupalBlockStyleCommand extends Command {
  /**
   * Constructs a new object.
   */
  constructor(editor, styles) {
    super(editor);
    this._styles = new Map(
      styles.map((style) => {
        return [style.name, style];
      }),
    );
  }

  /**
   * @inheritDoc
   */
  refresh() {
    const editor = this.editor;
    const element = getClosestElementWithBlockStyleAttribute(
      editor.model.document.selection,
      editor.model.schema,
    );

    this.isEnabled = !!element;

    if (!this.isEnabled) {
      this.value = false;
    } else if (element.hasAttribute('drupalBlockStyle')) {
      this.value = element.getAttribute('drupalBlockStyle');
    } else {
      this.value = false;
    }
  }

  /**
   * Executes the command and applies the style to the selected model element.
   *
   * @example
   *    editor.execute('drupalBlockStyle', { value: 'alignLeft' });
   *
   * @param {Object} options
   * @param {string} options.value
   *   The name of the style as configured in the Drupal Block style
   *   configuration.
   */
  execute(options = {}) {
    const editor = this.editor;
    const model = editor.model;

    model.change((writer) => {
      const requestedStyle = options.value;
      const element = getClosestElementWithBlockStyleAttribute(
        model.document.selection,
        model.schema,
      );

      if (!requestedStyle || this._styles.get(requestedStyle).isDefault) {
        writer.removeAttribute('drupalBlockStyle', element);
      } else {
        writer.setAttribute('drupalBlockStyle', requestedStyle, element);
      }
    });
  }
}
