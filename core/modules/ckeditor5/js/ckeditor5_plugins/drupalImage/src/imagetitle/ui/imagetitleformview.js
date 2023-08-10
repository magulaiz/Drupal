/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words focustracker keystrokehandler labeledfield labeledfieldview buttonview viewcollection focusables focuscycler switchbuttonview imagetitleformview imagetitle */

/**
 * @module drupalImage/imagetitle/ui/imagetitleformview
 */

import {
  ButtonView,
  FocusCycler,
  LabeledFieldView,
  SwitchButtonView,
  View,
  ViewCollection,
  createLabeledInputText,
  injectCssTransitionDisabler,
  submitHandler,
} from 'ckeditor5/src/ui';
import { FocusTracker, KeystrokeHandler } from 'ckeditor5/src/utils';
import { icons } from 'ckeditor5/src/core';

/**
 * A class rendering title form view.
 *
 * @extends module:ui/view~View
 *
 * @internal
 */
export default class ImageTitleFormView extends View {
  /**
   * @inheritdoc
   */
  constructor(locale) {
    super(locale);

    /**
     * Tracks information about the DOM focus in the form.
     *
     * @readonly
     * @member {module:utils/focustracker~FocusTracker}
     */
    this.focusTracker = new FocusTracker();

    /**
     * An instance of the {@link module:utils/keystrokehandler~KeystrokeHandler}.
     *
     * @readonly
     * @member {module:utils/keystrokehandler~KeystrokeHandler}
     */
    this.keystrokes = new KeystrokeHandler();

    /**
     * An input with a label.
     *
     * @member {module:ui/labeledfield/labeledfieldview~LabeledFieldView} #labeledInput
     */
    this.labeledInput = this._createLabeledInputView();

    /**
     * A button used to submit the form.
     *
     * @member {module:ui/button/buttonview~ButtonView} #saveButtonView
     */
    this.saveButtonView = this._createButton(
      Drupal.t('Save'),
      icons.check,
      'ck-button-save',
    );
    this.saveButtonView.type = 'submit';
    // Save button is disabled when titley is empty
    this.saveButtonView
      .bind('isEnabled')
      .to(
        this.labeledInput,
        'isEmpty',
        (isLabeledInputEmpty) =>
          !isLabeledInputEmpty,
      );

    /**
     * A button used to cancel the form.
     *
     * @member {module:ui/button/buttonview~ButtonView} #cancelButtonView
     */
    this.cancelButtonView = this._createButton(
      Drupal.t('Cancel'),
      icons.cancel,
      'ck-button-cancel',
      'cancel',
    );

    /**
     * A collection of views which can be focused in the form.
     *
     * @member {module:ui/viewcollection~ViewCollection}
     *
     * @readonly
     * @protected
     */
    this._focusables = new ViewCollection();

    /**
     * Helps cycling over focusables in the form.
     *
     * @member {module:ui/focuscycler~FocusCycler}
     *
     * @readonly
     * @protected
     */
    this._focusCycler = new FocusCycler({
      focusables: this._focusables,
      focusTracker: this.focusTracker,
      keystrokeHandler: this.keystrokes,
      actions: {
        // Navigate form fields backwards using the Shift + Tab keystroke.
        focusPrevious: 'shift + tab',

        // Navigate form fields forwards using the Tab key.
        focusNext: 'tab',
      },
    });

    this.setTemplate({
      tag: 'form',

      attributes: {
        class: [
          'ck',
          'ck-text-alternative-form',
          'ck-responsive-form',
        ],

        // https://github.com/ckeditor/ckeditor5-image/issues/40
        tabindex: '-1',
      },

      children: [
        this.labeledInput,
        this.saveButtonView,
        this.cancelButtonView,
      ],
    });

    injectCssTransitionDisabler(this);
  }

  /**
   * @inheritdoc
   */
  render() {
    super.render();

    this.keystrokes.listenTo(this.element);

    submitHandler({ view: this });

    [
      this.labeledInput,
      this.saveButtonView,
      this.cancelButtonView,
    ].forEach((v) => {
      // Register the view as focusable.
      this._focusables.add(v);

      // Register the view in the focus tracker.
      this.focusTracker.add(v.element);
    });
  }

  /**
   * @inheritdoc
   */
  destroy() {
    super.destroy();

    this.focusTracker.destroy();
    this.keystrokes.destroy();
  }

  /**
   * Creates the button view.
   *
   * @param {String} label
   *   The button label
   * @param {String} icon
   *   The button's icon.
   * @param {String} className
   *   The additional button CSS class name.
   * @param {String} [eventName]
   *   The event name that the ButtonView#execute event will be delegated to.
   * @returns {module:ui/button/buttonview~ButtonView}
   *   The button view instance.
   *
   * @private
   */
  _createButton(label, icon, className, eventName) {
    const button = new ButtonView(this.locale);

    button.set({
      label,
      icon,
      tooltip: true,
    });

    button.extendTemplate({
      attributes: {
        class: className,
      },
    });

    if (eventName) {
      button.delegate('execute').to(this, eventName);
    }

    return button;
  }

  /**
   * Creates an input with a label.
   *
   * @returns {module:ui/labeledfield/labeledfieldview~LabeledFieldView}
   *   Labeled field view instance.
   *
   * @private
   */
  _createLabeledInputView() {
    const labeledInput = new LabeledFieldView(
      this.locale,
      createLabeledInputText,
    );

    labeledInput.label = Drupal.t('Title text');

    return labeledInput;
  }

}
