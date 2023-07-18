// eslint-disable-next-line import/no-extraneous-dependencies
import { Plugin } from 'ckeditor5/src/core';

/**
 * Generates a callback that saves the width value to an attribute on
 * data downcast.
 *
 * @return {function}
 *  Callback that binds an event to its parameter.
 *
 * @private
 */
export function modelImageWidthToAttribute() {
  /**
   * Callback for the attribute:width event.
   *
   * Saves the width value to the width attribute.
   *
   * @type {converterHandler}
   */
  function converter(event, data, conversionApi) {
    const { item } = data;
    const { consumable, writer } = conversionApi;

    if (!consumable.consume(item, event.name)) {
      return;
    }

    const viewElement = conversionApi.mapper.toViewElement(item);
    const imageInFigure = Array.from(viewElement.getChildren()).find(
      (child) => child.name === 'img',
    );

    writer.setAttribute(
      'width',
      data.attributeNewValue.replace('px', ''),
      imageInFigure || viewElement,
    );
  }

  return (dispatcher) => {
    dispatcher.on('attribute:width:imageInline', converter, {
      priority: 'high',
    });
    dispatcher.on('attribute:width:imageBlock', converter, {
      priority: 'high',
    });
  };
}

/**
 * Generates a callback that saves the height value to an attribute on
 * data downcast.
 *
 * @return {function}
 *  Callback that binds an event to its parameter.
 *
 * @private
 */
export function modelImageHeightToAttribute() {
  /**
   * Callback for the attribute:height event.
   *
   * Saves the height value to the height attribute.
   *
   * @type {converterHandler}
   */
  function converter(event, data, conversionApi) {
    const { item } = data;
    const { consumable, writer } = conversionApi;

    if (!consumable.consume(item, event.name)) {
      return;
    }

    const viewElement = conversionApi.mapper.toViewElement(item);
    const imageInFigure = Array.from(viewElement.getChildren()).find(
      (child) => child.name === 'img',
    );

    writer.setAttribute(
      'height',
      data.attributeNewValue.replace('px', ''),
      imageInFigure || viewElement,
    );
  }

  return (dispatcher) => {
    dispatcher.on('attribute:height:imageInline', converter, {
      priority: 'high',
    });
    dispatcher.on('attribute:height:imageBlock', converter, {
      priority: 'high',
    });
  };
}

/**
 * The Drupal-specific image size plugin.
 *
 * @extends module:core/plugin~Plugin
 */
class DrupalImageSize extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImageSize';
  }
}
