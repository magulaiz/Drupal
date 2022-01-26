/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediastyleediting splitbutton imagestyle */
import { Plugin } from 'ckeditor5/src/core';
import utils from '@ckeditor/ckeditor5-image/src/imagestyle/utils';
import {
  addToolbarToDropdown,
  ButtonView,
  createDropdown,
  SplitButtonView,
} from 'ckeditor5/src/ui';
import DrupalMediaStyleEditing from './drupalmediastyleediting';

import { isObject } from '../utils';

const identity = (value) => {
  return value;
};
const getDropdownButtonTitle = (dropdownTitle, buttonTitle) => {
  return (dropdownTitle ? `${dropdownTitle}: ` : '') + buttonTitle;
};
function getUIComponentName(name) {
  return `drupalMediaStyle:${name}`;
}

export default class DrupalMediaStyleUi extends Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalMediaStyleEditing];
  }

  /**
   * @inheritDoc
   */
  init() {
    const plugins = this.editor.plugins;
    const toolbarConfig = this.editor.config.get('drupalMedia.toolbar') || [];

    const definedStyles = Object.values(
      plugins.get('DrupalMediaStyleEditing').normalizedStyles,
    );

    definedStyles.forEach((styleConfig) => {
      this._createButton(styleConfig);
    });

    const definedDropdowns = [...toolbarConfig.filter(isObject)];

    definedDropdowns.forEach((dropdownConfig) => {
      this._createDropdown(dropdownConfig, definedStyles);
    });
  }

  _createDropdown(dropdownConfig, definedStyles) {
    const factory = this.editor.ui.componentFactory;

    factory.add(dropdownConfig.name, (locale) => {
      let defaultButton;

      const { defaultItem, items, title } = dropdownConfig;
      const buttonViews = items
        .filter((itemName) =>
          definedStyles.find(
            ({ name }) => getUIComponentName(name) === itemName,
          ),
        )
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

      splitButtonView.bind('icon').toMany(buttonViews, 'isOn', (...areOn) => {
        const index = areOn.findIndex(identity);

        return index < 0 ? defaultButton.icon : buttonViews[index].icon;
      });

      splitButtonView.bind('label').toMany(buttonViews, 'isOn', (...areOn) => {
        const index = areOn.findIndex(identity);

        return getDropdownButtonTitle(
          title,
          index < 0 ? defaultButton.label : buttonViews[index].label,
        );
      });

      splitButtonView
        .bind('isOn')
        .toMany(buttonViews, 'isOn', (...areOn) => areOn.some(identity));

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

  _createButton(buttonConfig) {
    const buttonName = buttonConfig.name;

    this.editor.ui.componentFactory.add(
      getUIComponentName(buttonName),
      (locale) => {
        const command = this.editor.commands.get('drupalMediaStyle');
        const view = new ButtonView(locale);

        view.set({
          label: buttonConfig.title,
          icon: buttonConfig.icon,
          tooltip: true,
          isToggleable: true,
        });

        view.bind('isEnabled').to(command, 'isEnabled');
        view.bind('isOn').to(command, 'value', (value) => value === buttonName);
        view.on('execute', this._executeCommand.bind(this, buttonName));

        return view;
      },
    );
  }

  _executeCommand(name) {
    this.editor.execute('drupalMediaStyle', { value: name });
    this.editor.editing.view.focus();
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalMediaStyleUi';
  }
}
