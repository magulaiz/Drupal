/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words mediaimagetextalternativecommand textalternativeformview */

import { Plugin } from 'ckeditor5/src/core';
import MediaImageTextAlternativeCommand from './mediaimagetextalternativecommand';
import DrupalMediaMetadataRepository from '../drupalmediametadatarepository';
import { isDrupalMedia } from '../utils';

/**
 * The media image text alternative editing plugin.
 */
export default class MediaImageTextAlternativeEditing extends Plugin {

  /**
   * @inheritDoc
   */
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
    const {
      editor,
      editor: { model, plugins, conversion },
    } = this;
    const metadataRepository = plugins.get('DrupalMediaMetadataRepository');

    conversion.for('upcast').add((dispatcher) => {
      return dispatcher.on(
        'element:drupal-media',
        (event, data) => {
          const [modelElement] = data.modelRange.getItems();
          if (!isDrupalMedia(modelElement)) {
            return;
          }

          // Get all metadata for drupalMedia elements to set value for
          // drupalMediaIsImage attribute.
          // @todo what should we do in case an error happens?
          metadataRepository.getMetadata(modelElement).then((metadata) => {
            model.enqueueChange('transparent', (writer) => {
              writer.setAttribute(
                'drupalMediaIsImage',
                !!metadata.imageMetadata,
                modelElement,
              );
            });
          });
        },
        // This converter needs to have the lowest priority to ensure that the
        // model element and its attributes have been converted.
        { priority: 'lowest' },
      );
    });

    model.schema.extend('drupalMedia', {
      allowAttributes: ['drupalMediaIsImage'],
    });

    editor.commands.add(
      'mediaImageTextAlternative',
      new MediaImageTextAlternativeCommand(this.editor),
    );
  }
}
