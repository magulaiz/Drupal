/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediacaption drupalmediacaptionediting drupalmediacaptionui */
import { Plugin } from 'ckeditor5/src/core';
import DrupalMediaCaptionEditing from './drupalmediacaption/drupalmediacaptionediting';
import DrupalMediaCaptionUI from './drupalmediacaption/drupalmediacaptionui';

/**
 * Provides the media caption feature on drupal media elements.
 *
 * @private
 */
export default class DrupalMediaCaption extends Plugin {
  static get requires() {
    return [DrupalMediaCaptionEditing, DrupalMediaCaptionUI];
  }

  static get pluginName() {
    return 'DrupalMediaCaption';
  }
}
