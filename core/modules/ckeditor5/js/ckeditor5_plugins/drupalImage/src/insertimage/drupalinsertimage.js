/* eslint-disable import/no-extraneous-dependencies */
import { Plugin, ImageInsert } from 'ckeditor5/src/core';
import { FileRepository } from 'ckeditor5/src/upload';

/**
 * Provides a toolbar item for inserting images.
 *
 * @private
 */
class DrupalInsertImage extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [ImageInsert];
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    // This component is a shell around CKEditor 5 upstream insertImage button
    // to retain backwards compatibility.
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
