/* eslint-disable import/no-extraneous-dependencies */
import { Command } from 'ckeditor5/src/core';
import { getClosestSelectedDrupalMediaElement } from '../utils';
import { METADATA_ERROR } from './utils';

/**
 * The media image title command.
 *
 * This is used to change the `title` attribute of `<drupalMedia>` elements.
 *
 * @see https://github.com/ckeditor/ckeditor5/blob/master/packages/ckeditor5-image/src/imagetitle/imagetitlecommand.js
 */
export default class MediaImageTitleCommand extends Command {
  /**
   * The command value: `false` if there is no `title` attribute, otherwise the value of the `title` attribute.

  /**
   * @inheritdoc
   */
  refresh() {
    const drupalMediaElement = getClosestSelectedDrupalMediaElement(
      this.editor.model.document.selection,
    );
    this.isEnabled =
      !!drupalMediaElement &&
      drupalMediaElement.getAttribute('drupalMediaIsImage') &&
      drupalMediaElement.getAttribute('drupalMediaIsImage') !== METADATA_ERROR;

    if (this.isEnabled) {
      this.value = drupalMediaElement.getAttribute('drupalMediaTitle');
    } else {
      this.value = false;
    }
  }

  /**
   * Executes the command.
   *
   * @param {Object} options
   *   An options object.
   * @param {String} options.newValue The new value of the `title` attribute to set.
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
          'drupalMediaTitle',
          options.newValue,
          drupalMediaElement,
        );
      } else {
        writer.removeAttribute('drupalMediaTitle', drupalMediaElement);
      }
    });
  }
}
