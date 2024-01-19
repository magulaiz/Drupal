/* eslint-disable import/no-extraneous-dependencies */
/* cspell:ignore drupallinkmediaediting drupallinkmediaui */

import { Plugin } from 'ckeditor5/src/core';
import DrupalLinkMediaEditing from './drupallinkmediaediting.js';
import DrupalLinkMediaUI from './drupallinkmediaui.js';

/**
 * @private
 */
export default class DrupalLinkMedia extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalLinkMediaEditing, DrupalLinkMediaUI];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalLinkMedia';
  }
}
