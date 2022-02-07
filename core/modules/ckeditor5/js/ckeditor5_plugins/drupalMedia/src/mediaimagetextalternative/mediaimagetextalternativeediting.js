/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words mediaimagetextalternativecommand textalternativeformview */

import { Plugin } from 'ckeditor5/src/core';
import MediaImageTextAlternativeCommand from './mediaimagetextalternativecommand';
import DrupalMediaMetadataRepository from '../drupalmediametadatarepository';

/**
 * The media image text alternative editing plugin.
 */
export default class MediaImageTextAlternativeEditing extends Plugin {

  static get requires() {
    return [DrupalMediaMetadataRepository];
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'MediaImageTextAlternativeEditing';
  }

  /**
   * @inheritDoc
   */
  init() {
    this.editor.commands.add(
      'mediaImageTextAlternative',
      new MediaImageTextAlternativeCommand(this.editor),
    );
  }
}
