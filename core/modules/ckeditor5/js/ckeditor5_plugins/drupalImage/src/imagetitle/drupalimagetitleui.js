/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalimagetitleui contextualballoon componentfactory imagetitleformview imagetitleui imagetitle */

/**
 * @module drupalImage/imagetitle/drupalimagetitleui
 */

import { Plugin, icons } from 'ckeditor5/src/core';
import {
  ButtonView,
  ContextualBalloon,
  clickOutsideHandler,
} from 'ckeditor5/src/ui';
import {
  repositionContextualBalloon,
  getBalloonPositionData,
} from '@ckeditor/ckeditor5-image/src/image/ui/utils';
import ImageTitleFormView from './ui/imagetitleformview';

/**
 * The Drupal-specific image title UI plugin.
 *
 * This plugin is based on a version of the upstream alternative text UI plugin.
 * This override enhances the UI with a new form element.
 *
 * The logic related to visibility, positioning, and keystrokes are unchanged
 * from the upstream implementation.
 *
 * The plugin uses the contextual balloon.
 *
 * @see module:image/imagetextalternative/imagetextalternativeui~ImageTextAlternativeUI
 * @see module:ui/panel/balloon/contextualballoon~ContextualBalloon
 *
 * @extends module:core/plugin~Plugin
 *
 * @internal
 */
export default class DrupalImageTitletUi extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [ContextualBalloon];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImageTitleUI';
  }

  /**
   * @inheritdoc
   */
  init() {
    this._createButton();
    this._createForm();

    const showTitleForm = () => {
      const imageUtils = this.editor.plugins.get('ImageUtils');
      // Show form after upload if there's an image widget in the current
      // selection.
      if (
        imageUtils.getClosestSelectedImageWidget(
          this.editor.editing.view.document.selection,
        )
      ) {
        this._showForm();
      }
    };

    if (this.editor.commands.get('insertImage')) {
      const insertImage = this.editor.commands.get('insertImage');
      insertImage.on('execute', showTitleForm);
    }
    if (this.editor.plugins.has('ImageUploadEditing')) {
      const imageUploadEditing = this.editor.plugins.get('ImageUploadEditing');
      imageUploadEditing.on('uploadComplete', showTitleForm);
    }
  }

  /**
   * @inheritdoc
   */
  destroy() {
    super.destroy();

    // Destroy created UI components as they are not automatically destroyed
    // @see https://github.com/ckeditor/ckeditor5/issues/1341
    this._form.destroy();
  }

  /**
   * Creates a button showing the balloon panel for changing the image text
   * alternative and registers it in the editor component factory.
   *
   * @see module:ui/componentfactory~ComponentFactory
   *
   * @private
   */
  _createButton() {
    const editor = this.editor;
    editor.ui.componentFactory.add('drupalImageTitle', (locale) => {
      const command = editor.commands.get('imageTitle');
      const view = new ButtonView(locale);

      view.set({
        label: Drupal.t('Change image title text'),
        icon: icons.pencil,
        tooltip: true,
      });

      view.bind('isEnabled').to(command, 'isEnabled');

      this.listenTo(view, 'execute', () => {
        this._showForm();
      });

      return view;
    });
  }

  /**
   * Creates the title form view.
   *
   * @private
   */
  _createForm() {
    const editor = this.editor;
    const view = editor.editing.view;
    const viewDocument = view.document;
    const imageUtils = editor.plugins.get('ImageUtils');

    /**
     * The contextual balloon plugin instance.
     *
     * @private
     * @member {module:ui/panel/balloon/contextualballoon~ContextualBalloon}
     */
    this._balloon = this.editor.plugins.get('ContextualBalloon');

    /**
     * A form used for changing the `title` text value.
     *
     * @member {module:drupalImage/imagetitle/ui/imagetitleformview~ImageTitleFormView}
     */
    this._form = new ImageTitleFormView(editor.locale);

    // Render the form so its #element is available for clickOutsideHandler.
    this._form.render();

    this.listenTo(this._form, 'submit', () => {
      editor.execute('imageTitle', {
        newValue: this._form.labeledInput.fieldView.element.value,
      });

      this._hideForm(true);
    });

    this.listenTo(this._form, 'cancel', () => {
      this._hideForm(true);
    });

    // Close the form on Esc key press.
    this._form.keystrokes.set('Esc', (data, cancel) => {
      this._hideForm(true);
      cancel();
    });

    // Reposition the balloon or hide the form if an image widget is no longer
    // selected.
    this.listenTo(editor.ui, 'update', () => {
      if (!imageUtils.getClosestSelectedImageWidget(viewDocument.selection)) {
        this._hideForm(true);
      } else if (this._isVisible) {
        repositionContextualBalloon(editor);
      }
    });

    // Close on click outside of balloon panel element.
    clickOutsideHandler({
      emitter: this._form,
      activator: () => this._isVisible,
      contextElements: [this._balloon.view.element],
      callback: () => this._hideForm(),
    });
  }

  /**
   * Shows the form in the balloon.
   *
   * @private
   */
  _showForm() {
    if (this._isVisible) {
      return;
    }

    const editor = this.editor;
    const command = editor.commands.get('imageTitle');
    const labeledInput = this._form.labeledInput;

    this._form.disableCssTransitions();

    if (!this._isInBalloon) {
      this._balloon.add({
        view: this._form,
        position: getBalloonPositionData(editor),
      });
    }

    // Make sure that each time the panel shows up, the field remains in sync
    // with the value of the command. If the user typed in the input, then
    // canceled the balloon (`labeledInput#value` stays unaltered) and re-opened
    // it without changing the value of the command, they would see the old
    // value instead of the actual value of the command.
    // https://github.com/ckeditor/ckeditor5-image/issues/114
    labeledInput.fieldView.element.value = command.value || '';
    labeledInput.fieldView.value = labeledInput.fieldView.element.value;


    labeledInput.fieldView.select();


    this._form.enableCssTransitions();
  }

  /**
   * Removes the form from the balloon.
   *
   * @param {Boolean} [focusEditable=false]
   *   Controls whether the editing view is focused afterwards.
   *
   * @private
   */
  _hideForm(focusEditable) {
    if (!this._isInBalloon) {
      return;
    }

    // Blur the input element before removing it from DOM to prevent issues in
    // some browsers.
    // See https://github.com/ckeditor/ckeditor5/issues/1501.
    if (this._form.focusTracker.isFocused) {
      this._form.saveButtonView.focus();
    }

    this._balloon.remove(this._form);

    if (focusEditable) {
      this.editor.editing.view.focus();
    }
  }

  /**
   * Returns `true` when the form is the visible view in the balloon.
   *
   * @type {Boolean}
   *
   * @private
   */
  get _isVisible() {
    return this._balloon.visibleView === this._form;
  }

  /**
   * Returns `true` when the form is in the balloon.
   *
   * @type {Boolean}
   *
   * @private
   */
  get _isInBalloon() {
    return this._balloon.hasView(this._form);
  }
}
