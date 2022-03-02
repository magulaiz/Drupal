/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalelementstyleediting splitbutton imagestyle componentfactory */
import { Plugin } from 'ckeditor5/src/core';
import { CKEditorError, Collection } from 'ckeditor5/src/utils';
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
import getCommandGroupNameFromGroup from './utils';
import { isObject } from '../utils';

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
 *   The name of the component.
 * @param {string} group
 *   The group of the component.
 * @return {string}
 *   The UI component name.
 *
 * @see module:ui/componentfactory~ComponentFactory
 */
function getUIComponentName(name, group) {
  return `drupalElementStyle:${group}:${name}`;
}

/**
 * A helper function that parses the resize options and returns list item definitions ready for use in the dropdown.
 *
 * @private
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
 *   A list of defined styles.
 * @param {module:drupalMedia/drupalelementstyle/drupalelementstylecommand} command The drupalElementStyle command.
 * @param {string} groupName The name of the group (ex. 'align', 'viewMode').
 * @return {Iterable.<module:ui/dropdown/utils~ListDropdownItemDefinition>} Dropdown item definitions.
 */
function getDropdownListItemDefinitions(
  definedStyles,
  command,
  groupName,
  editor,
) {
  const itemDefinitions = new Collection();
  const commandGroup = getCommandGroupNameFromGroup(groupName);
  definedStyles.forEach((style) => {
    const definition = {
      type: 'button',
      model: new Model({
        commandName: 'drupalElementStyle',
        commandGroup,
        groupName,
        commandValue: style.name,
        label: style.title,
        withText: true,
        class: '',
      }),
    };
    itemDefinitions.add(definition);

    editor.model.document.on('change', (eventInfo, batch) => {
      if (eventInfo.name === 'change') {
        const { selection } = editor.model.document;
        const modelElement = selection
          ? selection.getSelectedElement()
          : selection.getFirstPosition.findAncestor('drupalElementStyle');
        const bundleType = modelElement.getAttribute('drupalMediaBundle');
        const filteredDefinedStyles = definedStyles.filter(function (item) {
          return item.modelAttributes.drupalMediaBundle.includes(bundleType);
        });
        if (!filteredDefinedStyles.includes(style)) {
          // Hide button if view mode is not available for the bundle that the modelElement is.
          definition.model.set({ class: 'ck-hidden' });
        } else {
          // Un-hide button here after changing selection to a bundle that should have the view mode button visible.
          definition.model.set({ class: '' });
        }
      }
    });
  });
  return itemDefinitions;
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
     * @example
     *    config:
     *       drupalMedia:
     *         toolbar:
     *           - name: 'drupalMedia:alignment'
     *             display: 'toolbar'
     *             title: 'Custom title for the dropdown'
     *             items:
     *               - 'drupalElementStyle:align:alignLeft'
     *               - 'drupalElementStyle:align:alignCenter'
     *               - 'drupalElementStyle:align:alignRight'
     *             defaultItem: 'drupalElementStyle:align:alignCenter'
     *
     * @typedef {Object} Drupal.CKEditor5~drupalElementStyleDropdownDefinition
     *
     * @prop {string} name
     *   The name of the dropdown used for identifying the dropdown.
     * @prop {string} display
     *   The type of the dropdown used.
     * @prop {string[]} items
     *   The items displayed in the dropdown. These must be styles defined in
     *   `drupalElementStyles.options`.
     * @prop {string} defaultItem
     *   The default item of the dropdown. This must be a style defined in
     *   `drupalElementStyles.options`.
     * @prop {string} [title]
     *   The title of the dropdown.
     *
     * @see module:drupalMedia/drupalelementstyle/drupalelementstyleediting:DrupalElementStyleEditing
     */
    const definedDropdowns = toolbarConfig.filter(isObject);

    definedDropdowns.forEach((dropdownConfig) => {
      const groupName = dropdownConfig.name.split(':')[1];
      switch (dropdownConfig.display) {
        case 'toolbar':
          this._createDropdown(dropdownConfig, definedStyles[groupName]);
          break;
        case 'list':
          this._createListDropdown(dropdownConfig, definedStyles[groupName]);
          break;
        default:
          throw new Error('Toolbar display type must be specified.');
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
        view.bind('isOn').to(command, 'value', (value) => value === buttonName);
        view.on('execute', this._executeCommand.bind(this, buttonName, group));

        return view;
      },
    );
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

      const { defaultItem, items, title } = dropdownConfig;
      const groupName = dropdownConfig.name.split(':')[1];
      console.log('hit2');

      const buttonViews = items
        .filter((itemName) => {
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

      const dropdownView = createDropdown(locale, DropdownButtonView);
      const dropdownButtonView = dropdownView.buttonView;

      dropdownButtonView.set({
        label: getDropdownButtonTitle(title, defaultButton.label),
        class: null,
        tooltip: Drupal.t('Change view mode'),
        withText: true,
      });

      const command = this.editor.commands.get('drupalElementStyle');
      const commandGroupName = getCommandGroupNameFromGroup(groupName);

      // If style is selected, use the label of the selected style as the
      // default label of the split button.
      dropdownButtonView.bind('label').to(command, 'value', (commandValue) => {
        if (commandValue && commandValue[commandGroupName]) {
          // @todo Use the style title instead of the machine name.
          return commandValue[commandGroupName];
        }
        return dropdownConfig.defaultText;
      });

      dropdownView.bind('isOn').to(command);
      dropdownView.bind('isEnabled').to(this);

      addListToDropdown(
        dropdownView,
        getDropdownListItemDefinitions(
          definedStyles,
          command,
          groupName,
          this.editor,
        ),
      );
      // Execute command when an item from the dropdown is selected.
      this.listenTo(dropdownView, 'execute', (evt) => {
        const obj = {};
        const key = evt.source.commandGroup;
        obj[key] = evt.source.commandValue;
        this.editor.execute(evt.source.commandName, {
          value: obj,
          groupName: evt.source.groupName,
        });
        this.editor.editing.view.focus();
      });

      return dropdownView;
    });
  }
  // )};

  /**
   * Executes the Drupal Element Style command.
   *
   * @param {string} name
   *   The name of the style that should be applied.
   * @param {string} groupName
   *   The name of the group (ex. 'align', 'viewMode').
   *
   * @see module:drupalMedia/drupalelementstyle/drupalelementstylecommand~DrupalElementStyleCommand
   *
   * @private
   */
  _executeCommand(name, groupName) {
    const key = getCommandGroupNameFromGroup(groupName);
    const obj = {};
    obj[key] = name;
    this.editor.execute('drupalElementStyle', {
      value: obj,
      groupName,
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
