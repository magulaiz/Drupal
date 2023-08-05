/* eslint-disable import/no-extraneous-dependencies */
import { Plugin } from 'ckeditor5/src/core';
import MediaLinkTextAlternativeEditing from './medialinktextalternative/medialinktextalternativeediting';
import MediaLinkTextAlternativeUi from './medialinktextalternative/medialinktextalternativeui';

/**
 * The media link text alternative plugin.
 *
 * @private
 */
export default class MediaLinkTextAlternative extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [MediaLinkTextAlternativeEditing, MediaLinkTextAlternativeUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'MediaLinkTextAlternative';
  }
}
