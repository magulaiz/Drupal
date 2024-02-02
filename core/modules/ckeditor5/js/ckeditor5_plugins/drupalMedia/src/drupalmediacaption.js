/* eslint-disable import/no-extraneous-dependencies */
<<<<<<< HEAD
/* cspell:words drupalmediacaption drupalmediacaptionediting drupalmediacaptionui */
=======
/* cspell:ignore drupalmediacaption drupalmediacaptionediting drupalmediacaptionui */
>>>>>>> upstream/11.x
import { Plugin } from 'ckeditor5/src/core';
import DrupalMediaCaptionEditing from './drupalmediacaption/drupalmediacaptionediting';
import DrupalMediaCaptionUI from './drupalmediacaption/drupalmediacaptionui';

/**
 * Provides the caption feature on Drupal media elements.
 *
 * @private
 */
export default class DrupalMediaCaption extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalMediaCaptionEditing, DrupalMediaCaptionUI];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMediaCaption';
  }
}
