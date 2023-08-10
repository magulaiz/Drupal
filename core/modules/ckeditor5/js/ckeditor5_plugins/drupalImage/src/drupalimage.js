/* eslint-disable import/no-extraneous-dependencies */
/* cspell:ignore drupalimageediting drupalimagealternativetext drupalimagetitle */

import { Plugin } from 'ckeditor5/src/core';
import DrupalImageEditing from './drupalimageediting';
import DrupalImageAlternativeText from './drupalimagealternativetext';
import DrupalImageTitle from './drupalimagetitle';

/**
 * @private
 */
class DrupalImage extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalImageEditing, DrupalImageAlternativeText, DrupalImageTitle];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImage';
  }
}

export default DrupalImage;
