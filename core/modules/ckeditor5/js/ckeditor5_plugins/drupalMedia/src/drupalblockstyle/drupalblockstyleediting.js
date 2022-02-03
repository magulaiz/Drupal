/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalblockstylecommand */
import { Plugin, icons } from 'ckeditor5/src/core';
import { first } from 'ckeditor5/src/utils';
import DrupalBlockStyleCommand from './drupalblockstylecommand';

/**
 * @module drupalMedia/drupalblockstyle/drupalblockstyleediting
 */

/**
 * Gets style definition by name.
 *
 * @param {string} name
 *   The name of the style definition.
 * @param styles
 *   The styles to search from.
 * @return {Drupal.CKEditor5~drupalBlockStyle}
 */
function getStyleDefinitionByName(name, styles) {
  // eslint-disable-next-line no-restricted-syntax
  for (const style of styles) {
    if (style.name === name) {
      return style;
    }
  }
}

/**
 * Returns a model-to-view converted for Drupal Media styles.
 *
 * This model to view converter supports downcasting model to either a CSS class
 * or a data-align attribute.
 *
 * Note that only one style can be applied to a single Drupal Media element.
 */
function modelToViewStyleAttribute(styles) {
  return (evt, data, conversionApi) => {
    if (!conversionApi.consumable.consume(data.item, evt.name)) {
      return;
    }

    // Check if there is a style associated with given value.
    const newStyle = getStyleDefinitionByName(data.attributeNewValue, styles);
    const oldStyle = getStyleDefinitionByName(data.attributeOldValue, styles);

    const viewElement = conversionApi.mapper.toViewElement(data.item);
    const viewWriter = conversionApi.writer;

    if (oldStyle) {
      if (oldStyle.attributeName === 'class') {
        viewWriter.removeClass(oldStyle.attributeValue, viewElement);
      } else {
        viewWriter.removeAttribute(oldStyle.attributeName, viewElement);
      }
    }

    if (newStyle) {
      if (newStyle.attributeName === 'class') {
        viewWriter.addClass(newStyle.attributeValue, viewElement);
      } else {
        viewWriter.setAttribute(
          newStyle.attributeName,
          newStyle.attributeValue,
          viewElement,
        );
      }
    }
  };
}

/**
 * Returns a view-to-model converter for Drupal Block styles.
 *
 * This view to model converted supports styles that are configured to use
 * either CSS classes or data-align.
 *
 * Note that only one style can be applied to each model element.
 */
function viewToModelStyleAttribute(styles) {
  // Convert only non–default styles.
  const nonDefaultStyles = styles.filter((style) => !style.isDefault);

  return (evt, data, conversionApi) => {
    if (!data.modelRange) {
      return;
    }

    const viewElement = data.viewItem;
    const modelElement = first(data.modelRange.getItems());

    // Run this converter only if a model element has been found from the model.
    if (!modelElement) {
      return;
    }

    // Stop conversion early if the drupalBlockStyle attribute isn't allowed for
    // the element.
    if (
      !conversionApi.schema.checkAttribute(modelElement, 'drupalBlockStyle')
    ) {
      return;
    }

    // Convert styles with CSS classes one by one.
    // eslint-disable-next-line no-restricted-syntax
    for (const style of nonDefaultStyles) {
      // Try to consume class corresponding with the style.
      if (style.attributeName === 'class') {
        if (
          conversionApi.consumable.consume(viewElement, {
            classes: style.attributeValue,
          })
        ) {
          // And convert this style to model attribute.
          conversionApi.writer.setAttribute(
            'drupalBlockStyle',
            style.name,
            modelElement,
          );
        }
      } else if (
        conversionApi.consumable.consume(viewElement, {
          attributes: [style.attributeName],
        })
      ) {
        // eslint-disable-next-line no-restricted-syntax
        for (const style of nonDefaultStyles) {
          if (
            style.attributeValue ===
            viewElement.getAttribute(style.attributeName)
          ) {
            conversionApi.writer.setAttribute(
              'drupalBlockStyle',
              style.name,
              modelElement,
            );
          }
        }
      }
    }
  };
}

/**
 * The Drupal Block Style editing plugin.
 *
 * Additional Drupal Media styles can be defined with `drupalBlockStyles`
 * configuration key.
 *
 * @example
 *    config:
 *      drupalBlockStyles:
 *         options:
 *           - name: 'side'
 *             icon: 'objectBlockRight'
 *             title: 'Side image'
 *             attributeName: 'class'
 *             attributeValue: 'image-side'
 *
 * @see Drupal.CKEditor5~drupalBlockStyle
 *
 * @extends module:core/plugin~Plugin
 *
 * @internal
 */
export default class DrupalBlockStyleEditing extends Plugin {
  /**
   * @inheritDoc
   */
  init() {
    const editor = this.editor;

    if (!editor.plugins.has('DrupalMedia')) {
      console.warn(
        'DrupalMediaStyle plugin requires DrupalMedia to be enabled.',
      );
      return;
    }

    // Ensure that the styles.options exists always.
    editor.config.define('drupalBlockStyles', { options: [] });
    const stylesConfig = editor.config.get('drupalBlockStyles').options;

    /**
     * The Drupal Block Styles.
     *
     * @typedef {Object} Drupal.CKEditor5~drupalBlockStyle
     *
     * @prop {string} name
     *   The name of the style used for identifying the button.
     * @prop {string} title
     *   The title of the style displayed in the UI.
     * @prop {string} [attributeName]
     *   @todo
     * @prop {string} [attributeValue]
     *   @todo
     * @prop {string[]} [modelElements]
     *   @todo
     * @prop {string} [icon]
     *   An icon for the style button. This needs to either refer to an icon in
     *   the CKEditor 5 core icons, or this can be the XML content of the icon.
     *
     * @type {Drupal.CKEditor5~drupalBlockStyle[]}
     */
    this.normalizedStyles = stylesConfig
      .map((style) => {
        // Allow defining style icon as a string that is referring to the
        // CKEditor 5 default icons.
        if (typeof style.icon === 'string') {
          if (icons[style.icon]) {
            style.icon = icons[style.icon];
          }
        }
        return style;
      })
      .filter((style) => {
        if (!style.attributeName || !style.attributeValue) {
          console.warn(
            'drupalBlockStyles options must include attributeName and attributeValue.',
          );
          return false;
        }
        if (!style.modelElements || !Array.isArray(style.modelElements)) {
          console.warn(
            'drupalBlockStyles options must include an array of supported modelElements.',
          );
          return false;
        }

        if (!style.name && !style.name) {
          console.warn('drupalBlockStyles items must include a name.');
          return false;
        }

        return true;
      });

    this._setupConversion();

    editor.commands.add(
      'drupalBlockStyle',
      new DrupalBlockStyleCommand(editor, this.normalizedStyles),
    );
  }

  /**
   * Sets up conversion for Drupal Media Styles.
   *
   * @see modelToViewStyleAttribute()
   * @see viewToModelStyleAttribute()
   *
   * @private
   */
  _setupConversion() {
    const editor = this.editor;
    const schema = editor.model.schema;

    const modelToViewConverter = modelToViewStyleAttribute(
      this.normalizedStyles,
    );
    const viewToModelConverter = viewToModelStyleAttribute(
      this.normalizedStyles,
    );

    editor.editing.downcastDispatcher.on(
      'attribute:drupalBlockStyle',
      modelToViewConverter,
    );
    editor.data.downcastDispatcher.on(
      'attribute:drupalBlockStyle',
      modelToViewConverter,
    );

    // Allow drupalBlockStyle on all model elements that have associated styles.
    const modelElements = [
      ...new Set(
        this.normalizedStyles
          .map((style) => {
            return style.modelElements;
          })
          .flat(),
      ),
    ];
    modelElements.forEach((modelElement) => {
      schema.extend(modelElement, { allowAttributes: 'drupalBlockStyle' });
    });

    // View to model converter that runs on all elements.
    editor.data.upcastDispatcher.on(
      'element',
      viewToModelConverter,
      // This needs to be set as low priority to ensure this runs always after
      // the element has been converted to a model element.
      { priority: 'low' },
    );
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalBlockStyleEditing';
  }
}
