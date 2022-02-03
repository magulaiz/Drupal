/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalblockstyle drupalblockstyleui drupalblockstyleediting imagestyle */
import { Plugin } from 'ckeditor5/src/core';
import DrupalBlockStyleUi from './drupalblockstyle/drupalblockstyleui';
import DrupalBlockStyleEditing from './drupalblockstyle/drupalblockstyleediting';

/**
 * The Drupal Block Style plugin.
 *
 * This plugin is inspired by the CKEditor 5 Image Style plugin.
 *
 * @see module:image/imagestyle~ImageStyle
 *
 * @internal
 */
export default class DrupalBlockStyle extends Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalBlockStyleEditing, DrupalBlockStyleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalBlockStyle';
  }
}
