/* eslint-disable import/no-extraneous-dependencies */
import { Command } from 'ckeditor5/src/core';

import { getClosestSelectedDrupalMediaElement } from '../utils';

/**
 * The Drupal Media style command.
 *
 * This is used to apply Drupal Media style option to a selected Drupal Media.
 */
export default class DrupalMediaStyleCommand extends Command {
  /**
   * Constructs a new object.
   */
  constructor(editor, styles) {
    super(editor);
    this.styles = new Map(
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
    const element = getClosestSelectedDrupalMediaElement(
      this.editor.model.document.selection,
    );

    this.isEnabled = !!element;

    if (!this.isEnabled) {
      this.value = false;
    } else if (element.hasAttribute('drupalMediaStyle')) {
      this.value = element.getAttribute('drupalMediaStyle');
    } else {
      this.value = false;
    }
  }

  /**
   * Executes the command and applies the style to the selected Drupal Media.
   *
   * @example
   *    editor.execute('drupalMediaStyle', { value: 'alignLeft' });
   *
   * @param {Object} options
   * @param {string} options.value
   *   The name of the style as configured in the Drupal Media style
   *   configuration.
   */
  execute(options = {}) {
    const editor = this.editor;
    const model = editor.model;

    model.change((writer) => {
      const requestedStyle = options.value;

      const drupalMediaElement = getClosestSelectedDrupalMediaElement(
        model.document.selection,
      );

      if (!requestedStyle || this.styles.get(requestedStyle).isDefault) {
        writer.removeAttribute('drupalMediaStyle', drupalMediaElement);
      } else {
        writer.setAttribute(
          'drupalMediaStyle',
          requestedStyle,
          drupalMediaElement,
        );
      }
    });
  }
}
