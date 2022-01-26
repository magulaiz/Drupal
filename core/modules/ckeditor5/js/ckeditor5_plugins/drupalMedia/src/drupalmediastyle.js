/* eslint-disable import/no-extraneous-dependencies */
import { Plugin } from 'ckeditor5/src/core';
import DrupalMediaStyleUi from './drupalmediastyle/drupalmediastyleui';
import DrupalMediaStyleEditing from './drupalmediastyle/drupalmediastyleediting';
import DrupalMedia from './drupalmedia';

export default class DrupalMediaStyle extends Plugin {
  /**
   * @inheritDoc
   *
   * @todo not sure why we need DrupalMedia here.
   */
  static get requires() {
    return [DrupalMedia, DrupalMediaStyleEditing, DrupalMediaStyleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMediaStyle';
  }
}
