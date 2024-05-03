/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words imagetitle drupalimagetitleediting drupalimagetitlecommand */

/**
 * @module drupalImage/imagetitle/drupalimagetitleediting
 */

import { Plugin } from 'ckeditor5/src/core';
import DrupalImageTitleCommand from './drupalimagetitlecommand';

/**
 * The Drupal image title editing plugin.
 *
 * Registers the `imageTitle` command and the conversion.
 *
 * @extends module:core/plugin~Plugin
 *
 * @internal
 */
export default class DrupalImageTitleEditing extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return ['ImageUtils'];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImageTitleEditing';
  }

  constructor(editor) {
    super(editor);
  }

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;

    editor.commands.add(
      'imageTitle',
      new DrupalImageTitleCommand(this.editor),
    );
    editor.conversion
      .for('upcast')
      .attributeToAttribute({
        view: {
          name: 'img',
          key: 'title',
        },
        model: {
          key: 'title',
          value: (viewElement) => {
            return `${viewElement.getAttribute('title')}`;
          },
        },
      });
    editor.conversion
      .for('downcast')
      .attributeToAttribute({
        view: {
          name: 'img',
          key: 'title',
        },
        model: {
          key: 'title',
          value: (viewElement) => {
            return `${viewElement.getAttribute('title')}`;
          },
        },
      });
  }
}
