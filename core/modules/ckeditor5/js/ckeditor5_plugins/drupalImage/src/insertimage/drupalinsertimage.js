/* eslint-disable import/no-extraneous-dependencies */
import { Plugin } from 'ckeditor5/src/core';

/**
 * Provides a toolbar item for inserting images.
 *
 * @private
 */
class DrupalInsertImage extends Plugin {
  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    editor.ui.componentFactory.add('drupalInsertImage', () => {
      return editor.ui.componentFactory.create('insertImage');
    });
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalInsertImage';
  }
}

export default DrupalInsertImage;
