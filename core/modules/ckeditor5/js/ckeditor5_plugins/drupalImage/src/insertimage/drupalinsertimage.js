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
  static get requires() {
    return ['ImageUpload'];
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    editor.ui.componentFactory.add('drupalInsertImage', () => {
      return editor.ui.componentFactory.create('uploadImage');
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
