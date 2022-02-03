/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalelementstyle drupalelementstyleui drupalelementstyleediting imagestyle drupalmediatoolbar */
import { Plugin } from 'ckeditor5/src/core';
import DrupalElementStyleUi from './drupalelementstyle/drupalelementstyleui';
import DrupalBlockStyleEditing from './drupalelementstyle/drupalelementstyleediting';

/**
 * @module drupalMedia/drupalelementstyle
 */

/**
 * The Drupal Element Style plugin.
 *
 * This plugin currently is used for aligning drupalMedia model elements with
 * Drupal's built in filter system. However, this plugin has been written so
 * that it could be moved outside the drupalMedia plugin and used for other
 * model elements.
 *
 * To be able to change element styles in the UI, the model element needs to
 * have a toolbar where the element style buttons can be displayed.
 *
 * This plugin is inspired by the CKEditor 5 Image Style plugin.
 *
 * @see module:image/imagestyle~ImageStyle
 * @see module:drupalMedia/drupalmediatoolbar~DrupalMediaToolbar
 *
 * @internal
 */
export default class DrupalElementStyle extends Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalBlockStyleEditing, DrupalElementStyleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalElementStyle';
  }
}
