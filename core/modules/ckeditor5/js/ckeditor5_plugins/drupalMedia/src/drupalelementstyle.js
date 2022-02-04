/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalelementstyle drupalelementstyleui drupalelementstyleediting imagestyle drupalmediatoolbar drupalmediaediting */
import { Plugin } from 'ckeditor5/src/core';
import DrupalElementStyleUi from './drupalelementstyle/drupalelementstyleui';
import DrupalElementStyleEditing from './drupalelementstyle/drupalelementstyleediting';

/**
 * @module drupalMedia/drupalelementstyle
 */

/**
 * The Drupal Element Style plugin.
 *
 * This plugin is internal because it was originally written for adding
 * `data-align` support to `<drupal-media>`, without tightly coupling it to
 * `<drupal-media>`. The intent is to make this plugin a starting point for
 * adding `data-align` support to other elements, because the `FilterAlign`
 * filter plugin PHP code also does not limit itself to a specific HTML element.
 * Nor is this plugin limited to just `data-align`, because other filters
 * plugins on the server side will also need to provide a great authoring
 * experience. Even supporting `data-align` just requires configuration to be
 * specified. The intent for this CKEditor 5 plugin is hence to make it simple
 * for more (PHP) filters to provide a good authoring experience without the
 * need for additional JavaScript code.
 *
 * To be able to change element styles in the UI, the model element needs to
 * have a toolbar where the element style buttons can be displayed.
 *
 * This plugin is inspired by the CKEditor 5 Image Style plugin.
 *
 * @see module:image/imagestyle~ImageStyle
 * @see core/modules/ckeditor5/css/media-alignment.css
 * @see module:drupalMedia/drupalmediaediting~DrupalMediaEditing
 * @see module:drupalMedia/drupalmediatoolbar~DrupalMediaToolbar
 *
 * @internal
 */
export default class DrupalElementStyle extends Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalElementStyleEditing, DrupalElementStyleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalElementStyle';
  }
}
