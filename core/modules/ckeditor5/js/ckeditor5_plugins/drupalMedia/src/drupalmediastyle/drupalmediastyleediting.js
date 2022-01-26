import { Plugin, icons } from 'ckeditor5/src/core';
import DrupalMediaStyleCommand from './drupalmediastylecommand';

const { objectLeft, objectRight, objectCenter } = icons;

export default class DrupalMediaStyleEditing extends Plugin {
  /**
   * @inheritDoc
   */
  init() {
    const editor = this.editor;
    const schema = editor.model.schema;

    this.normalizedStyles = {
      alignRight: {
        name: 'alignRight',
        title: 'Right aligned media',
        icon: objectRight,
        drupalMediaAlign: 'right',
      },
      alignLeft: {
        name: 'alignLeft',
        title: 'Left aligned media',
        icon: objectLeft,
        drupalMediaAlign: 'left',
      },
      alignCenter: {
        name: 'alignCenter',
        title: 'Centered media',
        icon: objectCenter,
        drupalMediaAlign: 'center',
      },
    };

    schema.extend('drupalMedia', { allowAttributes: 'drupalMediaStyle' });

    editor.commands.add(
      'drupalMediaStyle',
      new DrupalMediaStyleCommand(editor, this.normalizedStyles),
    );
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalMediaStyleEditing';
  }
}
