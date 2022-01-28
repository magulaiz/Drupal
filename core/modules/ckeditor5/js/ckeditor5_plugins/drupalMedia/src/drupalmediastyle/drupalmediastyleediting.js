/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediastylecommand */
import { Plugin, icons } from 'ckeditor5/src/core';
import { first } from 'ckeditor5/src/utils';
import DrupalMediaStyleCommand from './drupalmediastylecommand';

const { objectLeft, objectRight, objectCenter } = icons;

/**
 * Gets style definition by name.
 *
 * @param {string} name
 *   The name of the style definition.
 * @param styles
 *   The styles to search from.
 * @return {Drupal.CKEditor5~drupalMediaStyle}
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
      if (oldStyle.drupalMediaAlign) {
        viewWriter.removeAttribute('data-align', viewElement);
      } else {
        viewWriter.removeClass(oldStyle.className, viewElement);
      }
    }

    if (newStyle) {
      if (newStyle.drupalMediaAlign) {
        viewWriter.setAttribute(
          'data-align',
          newStyle.drupalMediaAlign,
          viewElement,
        );
      } else {
        viewWriter.addClass(newStyle.className, viewElement);
      }
    }
  };
}

/**
 * Returns a view-to-model converter for Drupal Media styles.
 *
 * This view to model converted supports styles that are configured to use
 * either CSS classes or data-align.
 *
 * Note that only one style can be applied to each Drupal Media element.
 */
function viewToModelStyleAttribute(styles) {
  // Convert only non–default styles.
  const nonDefaultStyles = styles.filter((style) => !style.isDefault);

  return (evt, data, conversionApi) => {
    if (!data.modelRange) {
      return;
    }

    const viewElement = data.viewItem;
    const modelDrupalMediaElement = first(data.modelRange.getItems());

    // Run this converter only if a Drupal Media element has been found in the
    // model.
    if (!modelDrupalMediaElement) {
      return;
    }

    // Stop conversion early if the drupalMediaStyle attribute isn't allowed for
    // the element.
    if (
      !conversionApi.schema.checkAttribute(
        modelDrupalMediaElement,
        'drupalMediaStyle',
      )
    ) {
      return;
    }

    // Convert styles with CSS classes one by one.
    // eslint-disable-next-line no-restricted-syntax
    for (const style of nonDefaultStyles) {
      // Try to consume class corresponding with the style.
      if (style.className) {
        if (
          conversionApi.consumable.consume(viewElement, {
            classes: style.className,
          })
        ) {
          // And convert this style to model attribute.
          conversionApi.writer.setAttribute(
            'drupalMediaStyle',
            style.name,
            modelDrupalMediaElement,
          );
        }
      }
    }

    // Convert data-align attribute to a style.
    if (
      conversionApi.consumable.consume(viewElement, {
        attributes: ['data-align'],
      })
    ) {
      // eslint-disable-next-line no-restricted-syntax
      for (const style of nonDefaultStyles) {
        if (
          style.drupalMediaAlign &&
          style.drupalMediaAlign === viewElement.getAttribute('data-align')
        ) {
          conversionApi.writer.setAttribute(
            'drupalMediaStyle',
            style.name,
            modelDrupalMediaElement,
          );
        }
      }
    }
  };
}

const DEFAULT_STYLES = [
  {
    name: 'alignRight',
    title: 'Right aligned media',
    icon: objectRight,
    drupalMediaAlign: 'right',
  },
  {
    name: 'alignLeft',
    title: 'Left aligned media',
    icon: objectLeft,
    drupalMediaAlign: 'left',
  },
  {
    name: 'alignCenter',
    title: 'Centered media',
    icon: objectCenter,
    drupalMediaAlign: 'center',
  },
];

/**
 * The Drupal Media Style editing plugin.
 *
 * Additional Drupal Media styles can be defined with `drupalMedia.styles`
 * configuration key.
 *
 * @example
 *    config:
 *      drupalMedia:
 *        styles:
 *          - name: 'side'
 *            icon: 'objectBlockRight'
 *            title: 'Side image'
 *            className: 'image-side'
 *
 * @see Drupal.CKEditor5~drupalMediaStyle
 */
export default class DrupalMediaStyleEditing extends Plugin {
  /**
   * @inheritDoc
   */
  init() {
    const editor = this.editor;
    const schema = editor.model.schema;

    editor.config.define('drupalMedia.styles', { options: [] });
    // Ensure that the alignemnt styles exist always.
    const stylesConfig = [
      ...editor.config.get('drupalMedia.styles').options,
      ...DEFAULT_STYLES,
    ];

    /**
     * The Drupal Media Styles.
     *
     * @typedef {Object} Drupal.CKEditor5~drupalMediaStyle
     *
     * @prop {string} name
     *   The name of the style.
     * @prop {string} [drupalMediaAlign]
     *   The value that should be set on data-align attribute. This property
     *   cannot be set with `className`.
     * @prop {string} [className]
     *   The CSS class that should be applied on the element. This property
     *   cannot be set with `drupalMediaAlign`.
     * @prop {string} [icon]
     *   An icon for the style button. This needs to either refer to an icon in
     *   the CKEditor 5 core icons, or this can be the XML content of the icon.
     *
     * @type {Drupal.CKEditor5~drupalMediaStyle[]}
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
        if (style.drupalMediaAlign && style.className) {
          console.warn(
            'drupalMedia.styles items can only include either drupalMediaAlign or className property.',
          );
          return false;
        }
        if (!style.drupalMediaAlign && !style.className) {
          console.warn(
            'drupalMedia.styles items must include either drupalMediaAlign or className property.',
          );
          return false;
        }
        if (!style.name && !style.name) {
          console.warn('drupalMedia.styles items must include a name.');
          return false;
        }

        return true;
      });

    debugger;
    this._setupConversion();

    editor.commands.add(
      'drupalMediaStyle',
      new DrupalMediaStyleCommand(editor, this.normalizedStyles),
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
      'attribute:drupalMediaStyle',
      modelToViewConverter,
    );
    editor.data.downcastDispatcher.on(
      'attribute:drupalMediaStyle',
      modelToViewConverter,
    );

    schema.extend('drupalMedia', { allowAttributes: 'drupalMediaStyle' });

    // Converter for the img element from view to model.
    editor.data.upcastDispatcher.on(
      'element:drupal-media',
      viewToModelConverter,
      { priority: 'low' },
    );
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalMediaStyleEditing';
  }
}
