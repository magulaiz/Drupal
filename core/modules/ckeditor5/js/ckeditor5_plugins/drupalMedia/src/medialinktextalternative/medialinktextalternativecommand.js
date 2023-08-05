/* eslint-disable import/no-extraneous-dependencies */
import { Command } from 'ckeditor5/src/core';
import { getClosestSelectedDrupalMediaElement } from '../utils';

/**
 * The media link text alternative command.
 *
 * This is used to change the `data-link-text` attribute of `<drupalMedia>` elements.
 */
export default class MediaLinkTextAlternativeCommand extends Command {
  /**
   * The command value: `false` if there is no `alt` attribute, otherwise the value of the `alt` attribute.

  /**
   * @inheritdoc
   */
  refresh() {
    const drupalMediaElement = getClosestSelectedDrupalMediaElement(
      this.editor.model.document.selection,
    );
    this.isEnabled =
      !!drupalMediaElement &&
      drupalMediaElement.getAttribute('drupalMediaIsFile');

    if (this.isEnabled) {
      this.value = drupalMediaElement.getAttribute('drupalMediaLinkText');
    } else {
      this.value = false;
    }
  }

  /**
   * Executes the command.
   *
   * @param {Object} options
   *   An options object.
   * @param {String} options.newValue The new value of the `alt` attribute to set.
   */
  execute(options) {
    const { model } = this.editor;
    const drupalMediaElement = getClosestSelectedDrupalMediaElement(
      model.document.selection,
    );

    options.newValue = options.newValue.trim();
    model.change((writer) => {
      if (options.newValue.length > 0) {
        writer.setAttribute(
          'drupalMediaLinkText',
          options.newValue,
          drupalMediaElement,
        );
      } else {
        writer.removeAttribute('drupalMediaLinkText', drupalMediaElement);
      }
    });
  }
}
