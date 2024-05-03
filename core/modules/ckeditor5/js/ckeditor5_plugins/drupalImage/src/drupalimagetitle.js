/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words imagetitle imagetitleediting drupalimagetitleediting drupalimagetitleui */

/**
 * @module drupalImage/imagetitle
 */

import { Plugin } from 'ckeditor5/src/core';
import DrupalImageTitleEditing from './imagetitle/drupalimagetitleediting';
import DrupalImageTitleUi from './imagetitle/drupalimagetitleui';

/**
 * The Drupal-specific image title plugin.
 *
 * This has been implemented based on the CKEditor 5 built in image alternative
 * text plugin.
 *
 * @see module:image/imagetitle~ImageTitle
 *
 * @extends module:core/plugin~Plugin
 */
export default class DrupalImageTitle extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalImageTitleEditing, DrupalImageTitleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImageTitle';
  }
}
