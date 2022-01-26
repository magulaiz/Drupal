import { Command } from 'ckeditor5/src/core';

import { getClosestSelectedDrupalMediaElement } from '../utils';

export default class DrupalMediaStyleCommand extends Command {
  constructor(editor, styles) {
    super(editor);
    this.styles = new Map(
      Object.values(styles).map((style) => {
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

  execute(options = {}) {
    const editor = this.editor;
    const model = editor.model;

    model.change((writer) => {
      const requestedStyle = options.value;

      const imageElement = getClosestSelectedDrupalMediaElement(
        model.document.selection,
      );

      if (!requestedStyle || this.styles.get(requestedStyle).isDefault) {
        writer.removeAttribute('drupalMediaStyle', imageElement);
      } else {
        writer.setAttribute('drupalMediaStyle', requestedStyle, imageElement);
      }
    });
  }
}
