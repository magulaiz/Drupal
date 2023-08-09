/* eslint-disable import/no-extraneous-dependencies */
import { Plugin } from 'ckeditor5/src/core';
import MediaImageTitleEditing from './mediaimagetitle/mediaimagetitleediting';
import MediaImageTitleUi from './mediaimagetitle/mediaimagetitleui';

/**
 * The media image text alternative plugin.
 *
 * @private
 */
export default class MediaImageTitle extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [MediaImageTitleEditing, MediaImageTitleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'MediaImageTitle';
  }
}
