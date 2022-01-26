/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediastylecommand */
import { Plugin, icons } from 'ckeditor5/src/core';
import { first } from 'ckeditor5/src/utils';
import DrupalMediaStyleCommand from './drupalmediastylecommand';

const { objectLeft, objectRight, objectCenter } = icons;

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

export default class DrupalMediaStyleEditing extends Plugin {
  /**
   * @inheritDoc
   */
  init() {
    const editor = this.editor;
    const schema = editor.model.schema;

    editor.config.define('drupalMedia.styles', {
      options: [
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
      ],
    });

    // @todo validate and normalize styles.
    this.normalizedStyles = editor.config.get('drupalMedia.styles').options;

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
