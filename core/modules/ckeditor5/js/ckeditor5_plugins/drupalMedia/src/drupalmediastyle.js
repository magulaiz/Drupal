/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediastyle drupalmediastyleui */
import { Plugin } from 'ckeditor5/src/core';
import DrupalMediaStyleUi from './drupalmediastyle/drupalmediastyleui';
import DrupalMediaStyleEditing from './drupalmediastyle/drupalmediastyleediting';
import DrupalMedia from './drupalmedia';

/**
 * The Drupal Media Style plugin.
 *
 * @internal
 */
export default class DrupalMediaStyle extends Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalMediaStyleEditing, DrupalMediaStyleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMediaStyle';
  }
}
