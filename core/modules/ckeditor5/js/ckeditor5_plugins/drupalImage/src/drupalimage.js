/* eslint-disable import/no-extraneous-dependencies */
<<<<<<< HEAD
/* cspell:words drupalimageediting drupalimagealternativetext */
=======
/* cspell:ignore drupalimageediting drupalimagealternativetext */
>>>>>>> upstream/11.x

import { Plugin } from 'ckeditor5/src/core';
import DrupalImageEditing from './drupalimageediting';
import DrupalImageAlternativeText from './drupalimagealternativetext';

/**
 * @private
 */
class DrupalImage extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalImageEditing, DrupalImageAlternativeText];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImage';
  }
}

export default DrupalImage;
