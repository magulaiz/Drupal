/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalelementstyleediting splitbutton imagestyle componentfactory */
import { Plugin } from 'ckeditor5/src/core';
import { Collection, toMap } from 'ckeditor5/src/utils';
import utils from '@ckeditor/ckeditor5-image/src/imagestyle/utils';
import {
  addToolbarToDropdown,
  addListToDropdown,
  ButtonView,
  createDropdown,
  DropdownButtonView,
  Model,
  SplitButtonView,
} from 'ckeditor5/src/ui';
import DrupalElementStyleEditing from './drupalelementstyleediting';
import {
  isDrupalMedia,
  isObject,
  getModelAttributeKeyFromGroup,
} from '../utils';
import { METADATA_ERROR } from '../mediaimagetextalternative/utils';
import { getClosestElementWithElementStyleAttribute } from './drupalelementstylecommand';

/**
 * @module drupalMedia/drupalelementstyle/drupalelementstyleui
 */

/**
 * Returns the first argument it receives.
 *
 * @param {*} value
 *   Any value to be returned by this function.
 * @return {*}
 *   Any value passed as the first argument.
 */
const identity = (value) => {
  return value;
};

/**
 * Gets the dropdown title.
 *
 * @param {string} dropdownTitle
 *   The dropdown title.
 * @param {string} buttonTitle
 *   The button title.
 * @return {string}
 *   The generated dropdown title.
 */
const getDropdownButtonTitle = (dropdownTitle, buttonTitle) => {
  return (dropdownTitle ? `${dropdownTitle}: ` : '') + buttonTitle;
};

/**
 * Gets the UI Component name.
 *
 * This is used for getting unique component names for registering the UI
 * components in the component factory.
 *
 * @param {string} name
 *   The name of the style
 * @param {string} group
 *   The group of the style.
 * @return {string}
 *   The UI component name.
 *
 * @see module:ui/componentfactory~ComponentFactory
 */
function getUIComponentName(name, group) {
  return `drupalElementStyle:${group}:${name}`;
}

/**
 * Updates the visibility of the correct view mode options depending on the selection's media type.
 *
 * @param {module:core/editor/editor~Editor} editor
 *   The editor instance.
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
 *   A list of defined styles.
 * @param {string} style
 *   The style to check be checked against the media type's specific styles.
 * @param {<module:ui/dropdown/utils~ListDropdownItemDefinition>} dropdownItemDefinition
 *   Dropdown item definition.
 * @param {string} modelAttribute
 *   The model attribute name of the drupalElementStyle.
 */
function updateOptionVisibility(
  editor,
  definedStyles,
  style,
  dropdownItemDefinition,
  modelAttribute,
) {
  const { selection } = editor.model.document;
  const modelElement = selection
    ? selection.getSelectedElement()
    : getClosestElementWithElementStyleAttribute(
        selection,
        editor.model.schema,
        definedStyles,
        modelAttribute,
      );

  const filteredDefinedStyles = definedStyles.filter(function (item) {
    // eslint-disable-next-line no-restricted-syntax
    for (const [key, value] of toMap(item.modelAttributes)) {
      if (modelElement.hasAttribute(key)) {
        return value.includes(modelElement.getAttribute(key));
      }
    }
    return true;
  });

  if (!filteredDefinedStyles.includes(style)) {
    // Hide the style option if view mode is not available for the media type that the modelElement is.
    dropdownItemDefinition.model.set({ class: 'ck-hidden' });
  } else {
    // Un-hide the style option here after changing selection to a media type that should have the view mode button visible.
    dropdownItemDefinition.model.set({ class: '' });
  }
}

/**
 * Upcast `type` from Drupal Media metadata.
 *
 * @param {module:engine/model/node~Node} modelElement
 *   The `drupalMedia` model element.
 * @param {module:core/editor/editor~Editor} editor
 *   The editor instance.
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
 *   A list of defined styles.
 * @param {string} style
 *   The style to check be checked against the bundle specific styles.
 * @param {<module:ui/dropdown/utils~ListDropdownItemDefinition>} definition
 *   Dropdown item definition.
 *
 * @see module:drupalMedia/drupalmediametadatarepository~DrupalMediaMetadataRepository
 *
 * @private
 */
function upcastDrupalMediaType(
  modelElement,
  editor,
  definedStyles,
  style,
  definition,
) {
  const metadataRepository = editor.plugins.get(
    'DrupalMediaMetadataRepository',
  );
  // Get all metadata for drupalMedia elements to set value for
  // drupalMediaType attribute. When other plugins start using the
  // metadata, this functionality will be handled more generically.
  metadataRepository
    .getMetadata(modelElement)
    .then((metadata) => {
      if (!modelElement) {
        // Nothing to do if model element has been removed before
        // promise was resolved.
        return;
      }
      // Enqueue a model change in `transparent` batch to make it
      // invisible to the undo/redo functionality.
      editor.model.enqueueChange({ isUndoable: false }, (writer) => {
        writer.setAttribute('drupalMediaType', metadata.type, modelElement);
      });
    })
    .catch((e) => {
      if (!modelElement) {
        // Nothing to do if model element has been removed before
        // promise was resolved.
        return;
      }
      console.warn(e.toString());
      editor.model.enqueueChange({ isUndoable: false }, (writer) => {
        writer.setAttribute('drupalMediaType', METADATA_ERROR, modelElement);
      });
    });
  updateOptionVisibility(editor, definedStyles, style, definition);
}

/**
 * The Drupal Element Style UI plugin.
 *
 * @extends module:core/plugin~Plugin
 *
 * @internal
 */
export default class DrupalElementStyleUi extends Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalElementStyleEditing];
  }

  /**
   * @inheritDoc
   */
  init() {
    const { plugins } = this.editor;
    const toolbarConfig = this.editor.config.get('drupalMedia.toolbar') || [];
    const definedStyles = plugins.get(
      'DrupalElementStyleEditing',
    ).normalizedStyles;

    Object.keys(definedStyles).forEach((group) => {
      definedStyles[group].forEach((style) => {
        this._createButton(style, group);
      });
    });

    /**
     * A Drupal Element Style dropdown definition.
     *
     * List dropdown display configuration.
     * @example
     *    config:
     *       drupalMedia:
     *        toolbar:
     *          - name: 'drupalMedia:viewMode'
     *            display: 'listDropdown'
     *            items:
     *              - 'drupalElementStyle:viewMode:full'
     *              - 'drupalElementStyle:viewMode:media_library'
     *              - 'drupalElementStyle:viewMode:compact'
     *            defaultItem: 'drupalElementStyle:viewMode:default'
     *
     * Split button dropdown display configuration.
     * @example
     *    config:
     *       drupalMedia:
     *        toolbar:
     *          - name: 'drupalMedia:side'
     *            display: 'splitButton'
     *            items:
     *              - 'drupalElementStyle:side:right'
     *              - 'drupalElementStyle:side:left'
     *            defaultItem: 'drupalElementStyle:side:right'
     *
     * Toolbar buttons configuration (non-dropdown).
     * @example
     *    config:
     *       drupalMedia:
     *        toolbar:
     *          - 'drupalElementStyle:align:breakText'
     *          - 'drupalElementStyle:align:left'
     *          - 'drupalElementStyle:align:center'
     *          - 'drupalElementStyle:align:right'
     *
     * @typedef {Object} Drupal.CKEditor5~drupalElementStyleDropdownDefinition
     *
     * These properties are needed for a list or split button dropdown configuration. Buttons directly on the toolbar
     * without a dropdown can be configured like in the align example above.
     * @prop {string} name
     *   The name of the dropdown used for identifying the dropdown, either as a list or icons.
     * @prop {string} display
     *   The type of the dropdown used.
     * @prop {string[]} items
     *   The items displayed in the dropdown. These must be styles defined in
     *   `drupalElementStyles`.
     * @prop {string} defaultItem
     *   The default item of the dropdown. This must be a style defined in
     *   `drupalElementStyles`.
     * @prop {string} [title]
     *   The title of the dropdown.
     *
     * @see module:drupalMedia/drupalelementstyle/drupalelementstyleediting:DrupalElementStyleEditing
     */
    const definedDropdowns = toolbarConfig.filter(isObject).filter((obj) => {
      if (!obj.display) {
        console.warn(
          'dropdown configuration must include a display key specifying either listDropdown or splitButton.',
        );
        return false;
      }
      return true;
    });

    definedDropdowns.forEach((dropdownConfig) => {
      // Only create dropdowns if there are 2 or more items.
      if (dropdownConfig.items.length >= 2) {
        const groupName = dropdownConfig.name.split(':')[1];
        switch (dropdownConfig.display) {
          case 'iconDropdown':
            this._createDropdown(dropdownConfig, definedStyles[groupName]);
            break;
          case 'listDropdown':
            this._createListDropdown(dropdownConfig, definedStyles[groupName]);
            break;
          default:
            break;
        }
      }
    });
  }

  /**
   * Creates a dropdown and stores it in the component factory.
   *
   * @param {Drupal.CKEditor5~drupalElementStyleDropdownDefinition} dropdownConfig
   *   The dropdown configuration.
   * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
   *   A list of defined styles.
   *
   * @see module:ui/componentfactory~ComponentFactory
   *
   * @private
   */
  _createDropdown(dropdownConfig, definedStyles) {
    const factory = this.editor.ui.componentFactory;

    factory.add(dropdownConfig.name, (locale) => {
      let defaultButton;

      const { defaultItem, items, title } = dropdownConfig;
      const buttonViews = items
        .filter((itemName) => {
          const groupName = itemName.split(':')[1];
          return definedStyles.find(
            ({ name }) => getUIComponentName(name, groupName) === itemName,
          );
        })
        .map((buttonName) => {
          const button = factory.create(buttonName);

          if (buttonName === defaultItem) {
            defaultButton = button;
          }

          return button;
        });

      if (items.length !== buttonViews.length) {
        utils.warnInvalidStyle({ dropdown: dropdownConfig });
      }

      const dropdownView = createDropdown(locale, SplitButtonView);
      const splitButtonView = dropdownView.buttonView;

      addToolbarToDropdown(dropdownView, buttonViews);

      splitButtonView.set({
        label: getDropdownButtonTitle(title, defaultButton.label),
        class: null,
        tooltip: true,
      });

      // If style is selected, show the currently selected style as the default
      // button of the split button.
      splitButtonView.bind('icon').toMany(buttonViews, 'isOn', (...areOn) => {
        const index = areOn.findIndex(identity);

        return index < 0 ? defaultButton.icon : buttonViews[index].icon;
      });

      // If style is selected, use the label of the selected style as the
      // default label of the split button.
      splitButtonView.bind('label').toMany(buttonViews, 'isOn', (...areOn) => {
        const index = areOn.findIndex(identity);

        return getDropdownButtonTitle(
          title,
          index < 0 ? defaultButton.label : buttonViews[index].label,
        );
      });

      // If one of the style is selected, render the split button as selected.
      splitButtonView
        .bind('isOn')
        .toMany(buttonViews, 'isOn', (...areOn) => areOn.some(identity));

      // If one of the styles is selected, add a CSS class to the split button
      // which modifies the styles to indicate that the splitbutton default
      // option is currently selected.
      splitButtonView
        .bind('class')
        .toMany(buttonViews, 'isOn', (...areOn) =>
          areOn.some(identity) ? 'ck-splitbutton_flatten' : null,
        );

      splitButtonView.on('execute', () => {
        if (!buttonViews.some(({ isOn }) => isOn)) {
          defaultButton.fire('execute');
        } else {
          dropdownView.isOpen = !dropdownView.isOpen;
        }
      });

      dropdownView
        .bind('isEnabled')
        .toMany(buttonViews, 'isEnabled', (...areEnabled) =>
          areEnabled.some(identity),
        );

      return dropdownView;
    });
  }

  /**
   * Creates a button and stores it in the editor component factory.
   *
   * @param {Drupal.CKEditor5~DrupalElementStyle} buttonConfig
   *   The button configuration.
   * @param {string} group
   *   The name of the group (ex. 'align', 'viewMode').
   *
   * @see module:ui/componentfactory~ComponentFactory
   *
   * @private
   */
  _createButton(buttonConfig, group) {
    const buttonName = buttonConfig.name;

    this.editor.ui.componentFactory.add(
      getUIComponentName(buttonName, group),
      (locale) => {
        const command = this.editor.commands.get('drupalElementStyle');
        const view = new ButtonView(locale);

        view.set({
          label: buttonConfig.title,
          icon: buttonConfig.icon,
          tooltip: true,
          isToggleable: true,
        });

        view.bind('isEnabled').to(command, 'isEnabled');
        view.bind('isOn').to(command, 'value', (value) => {
          return value && value[group] === buttonName;
        });

        view.on('execute', this._executeCommand.bind(this, buttonName, group));

        return view;
      },
    );
  }

  /**
   * A helper function that parses the different dropdown options and returns list item definitions ready for use in the dropdown.
   *
   * @private
   * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
   *   A list of defined styles.
   * @param {module:drupalMedia/drupalelementstyle/drupalelementstylecommand} command The drupalElementStyle command.
   * @param {string} group The name of the group (ex. 'align', 'viewMode').
   * @return {Iterable.<module:ui/dropdown/utils~ListDropdownItemDefinition>} Dropdown item definitions.
   */
  getDropdownListItemDefinitions(definedStyles, command, group, editor) {
    const itemDefinitions = new Collection();
    const modelAttribute = getModelAttributeKeyFromGroup(group);
    definedStyles.forEach((style) => {
      const definition = {
        type: 'button',
        model: new Model({
          commandName: 'drupalElementStyle',
          group,
          commandValue: style.name,
          label: style.title,
          withText: true,
          class: '',
        }),
      };
      itemDefinitions.add(definition);

      // Handles inserted content's list dropdown button's visibility.
      editor.model.on('insertContent', (eventInfo, [modelElement]) => {
        if (!isDrupalMedia(modelElement)) {
          return;
        }
        // Need to upcast DrupalMediaType to model so it can be used to show
        // correct buttons based on bundle. Calls updateOptionVisibility function inside below method.
        upcastDrupalMediaType(
          modelElement,
          editor,
          definedStyles,
          style,
          definition,
        );
      });

      // Handles selecting another element's list dropdown button's visibility.
      // We need to listen to editor UI changes instead of selection because
      // visibility of the styles can be impacted by either selection or
      // changes to the model.
      this.listenTo(editor.ui, 'update', () => {
        const selection = editor.model.document.selection;
        const modelElement = selection
          ? selection.getSelectedElement()
          : getClosestElementWithElementStyleAttribute(
              selection,
              editor.model.schema,
              definedStyles,
              modelAttribute,
            );
        if (!isDrupalMedia(modelElement)) {
          return;
        }
        updateOptionVisibility(
          editor,
          definedStyles,
          style,
          definition,
          modelAttribute,
        );
      });
    });
    return itemDefinitions;
  }

  /**
   * A helper function that creates a list dropdown component for the plugin containing all the style options defined in
   * the editor configuration.
   *
   * @private
   * @param {Drupal.CKEditor5~drupalElementStyleDropdownDefinition} dropdownConfig
   *   The dropdown configuration.
   * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
   *   A list of defined styles.
   */
  _createListDropdown(dropdownConfig, definedStyles) {
    const factory = this.editor.ui.componentFactory;
    factory.add(dropdownConfig.name, (locale) => {
      let defaultButton;

      const { defaultItem, items, title, defaultText } = dropdownConfig;
      const group = dropdownConfig.name.split(':')[1];
      const buttonViews = items
        .filter((itemName) => {
          return definedStyles.find(
            ({ name }) => getUIComponentName(name, group) === itemName,
          );
        })
        .map((buttonName) => {
          const button = factory.create(buttonName);

          if (buttonName === defaultItem) {
            defaultButton = button;
          }

          return button;
        });

      if (items.length !== buttonViews.length) {
        utils.warnInvalidStyle({ dropdown: dropdownConfig });
      }

      const dropdownView = createDropdown(locale, DropdownButtonView);
      const dropdownButtonView = dropdownView.buttonView;

      dropdownButtonView.set({
        label: getDropdownButtonTitle(title, defaultText),
        class: null,
        tooltip: defaultText,
        withText: true,
      });

      const command = this.editor.commands.get('drupalElementStyle');

      // If style is selected, use the label of the selected style as the
      // default label of the splitbutton.
      dropdownButtonView.bind('label').to(command, 'value', (commandValue) => {
        if (commandValue && commandValue[group]) {
          // eslint-disable-next-line no-restricted-syntax
          for (const style of definedStyles) {
            if (style.name === commandValue[group]) {
              return style.title;
            }
          }
        }
        return defaultText;
      });

      dropdownView.bind('isOn').to(command);
      dropdownView.bind('isEnabled').to(this);

      addListToDropdown(
        dropdownView,
        this.getDropdownListItemDefinitions(
          definedStyles,
          command,
          group,
          this.editor,
        ),
      );
      // Execute command when an item from the dropdown is selected.
      this.listenTo(dropdownView, 'execute', (evt) => {
        this._executeCommand(evt.source.commandValue, evt.source.group);
      });
      return dropdownView;
    });
  }

  /**
   * Executes the Drupal Element Style command.
   *
   * @param {string} name
   *   The name of the style that should be applied.
   * @param {string} group
   *   The name of the group (ex. 'align', 'viewMode').
   *
   * @see module:drupalMedia/drupalelementstyle/drupalelementstylecommand~DrupalElementStyleCommand
   *
   * @private
   */
  _executeCommand(name, group) {
    const obj = {};
    obj[group] = name;
    const modelAttribute = getModelAttributeKeyFromGroup(group);

    this.editor.execute('drupalElementStyle', {
      value: obj,
      group,
      modelAttribute,
    });
    this.editor.editing.view.focus();
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalElementStyleUi';
  }
}
