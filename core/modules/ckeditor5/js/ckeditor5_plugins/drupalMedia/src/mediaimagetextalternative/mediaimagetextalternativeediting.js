/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words mediaimagetextalternativecommand textalternativeformview drupalmediametadatarepository */

import { Plugin } from 'ckeditor5/src/core';
import { TooltipView, Template } from 'ckeditor5/src/ui';
import MediaImageTextAlternativeCommand from './mediaimagetextalternativecommand';
import DrupalMediaMetadataRepository from '../drupalmediametadatarepository';
import { isDrupalMedia } from '../utils';
import { METADATA_ERROR } from './utils';

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
          // drupalMediaIsImage attribute. This could potentially be moved
          // outside of this plugin once other plugins start using the metadata.
          metadataRepository
            .getMetadata(modelElement)
            .then((metadata) => {
              model.enqueueChange('transparent', (writer) => {
                writer.setAttribute(
                  'drupalMediaIsImage',
                  !!metadata.imageMetadata,
                  modelElement,
                );
              });
            })
            .catch((e) => {
              console.warn(e);
              model.enqueueChange('transparent', (writer) => {
                writer.setAttribute(
                  'drupalMediaIsImage',
                  METADATA_ERROR,
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

    conversion.for('downcast').add((dispatcher) => {
      dispatcher.on(
        'attribute:drupalMediaIsImage',
        (event, data, conversionApi) => {
          const { writer, mapper } = conversionApi;
          const container = mapper.toViewElement(data.item);

          if (data.attributeNewValue !== METADATA_ERROR) {
            const existingError = Array.from(container.getChildren()).find(
              (child) => child.getCustomProperty('drupalMediaMetadataError'),
            );
            // If the view contains an existing error, it should be removed since
            // retrieving metadata was successful.
            if (existingError) {
              writer.setCustomProperty(
                'widgetLabel',
                existingError.getCustomProperty(
                  'drupalMediaOriginalWidgetLabel',
                ),
                existingError,
              );
              writer.removeElement(existingError);
            }

            return;
          }

          const message = Drupal.t(
            'Functionality could be limited due to error on loading media metadata from the server',
          );

          const tooltip = new TooltipView();
          tooltip.text = message;
          tooltip.position = 'sw';

          const html = new Template({
            tag: 'span',
            children: [
              {
                tag: 'span',
                attributes: {
                  class: 'drupal-media__metadata-error-icon',
                },
              },
              tooltip,
            ],
          }).render();

          const error = writer.createRawElement(
            'div',
            {
              class: 'drupal-media__metadata-error',
            },
            (domElement, domConverter) => {
              domConverter.setContentOf(domElement, html.outerHTML);
            },
          );
          writer.setCustomProperty('drupalMediaMetadataError', true, error);

          // Edit widget label to ensure the current status of media embed is
          // available for screen reader users.
          const originalWidgetLabel =
            container.getCustomProperty('widgetLabel');
          writer.setCustomProperty(
            'drupalMediaOriginalWidgetLabel',
            originalWidgetLabel,
            error,
          );
          writer.setCustomProperty(
            'widgetLabel',
            `${originalWidgetLabel} (${message})`,
            container,
          );

          writer.insert(writer.createPositionAt(container, 0), error);
        },
        { priority: 'low' },
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
