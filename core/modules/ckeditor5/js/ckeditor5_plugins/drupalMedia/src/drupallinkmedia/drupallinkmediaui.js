/* eslint-disable import/no-extraneous-dependencies */
// cspell:ignore linkui
import { Plugin } from 'ckeditor5/src/core';
// import { LINK_KEYSTROKE } from '@ckeditor/ckeditor5-link/src/utils';
import { ButtonView } from 'ckeditor5/src/ui';
// import linkIcon from '../../../../../icons/link.svg';
const linkIcon =
  '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="m11.077 15 .991-1.416a.75.75 0 1 1 1.229.86l-1.148 1.64a.748.748 0 0 1-.217.206 5.251 5.251 0 0 1-8.503-5.955.741.741 0 0 1 .12-.274l1.147-1.639a.75.75 0 1 1 1.228.86L4.933 10.7l.006.003a3.75 3.75 0 0 0 6.132 4.294l.006.004zm5.494-5.335a.748.748 0 0 1-.12.274l-1.147 1.639a.75.75 0 1 1-1.228-.86l.86-1.23a3.75 3.75 0 0 0-6.144-4.301l-.86 1.229a.75.75 0 0 1-1.229-.86l1.148-1.64a.748.748 0 0 1 .217-.206 5.251 5.251 0 0 1 8.503 5.955zm-4.563-2.532a.75.75 0 0 1 .184 1.045l-3.155 4.505a.75.75 0 1 1-1.229-.86l3.155-4.506a.75.75 0 0 1 1.045-.184z"/></svg>\n';

// Explicitly added this as it originally came from
// @ckeditor/ckeditor5-link/src/utils but not clear how to
// get this from the browser build.
const LINK_KEYSTROKE = 'Ctrl+K';
/**
 * The link media UI plugin.
 *
 * @private
 */
export default class DrupalLinkMediaUI extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return ['LinkEditing', 'LinkUI', 'DrupalMediaEditing'];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalLinkMediaUi';
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    const viewDocument = editor.editing.view.document;

    this.listenTo(
      viewDocument,
      'click',
      (evt, data) => {
        if (this._isSelectedLinkedMedia(editor.model.document.selection)) {
          // Prevent browser navigation when clicking a linked media.
          data.preventDefault();

          // Block the `LinkUI` plugin when a media was clicked. In such a case,
          // we'd like to display the media toolbar.
          evt.stop();
        }
      },
      { priority: 'high' },
    );
    this._createToolbarLinkMediaButton();
  }

  /**
   * Creates a `DrupalLinkMediaUI` button view.
   *
   * Clicking this button shows a {@link module:link/linkui~LinkUI#_balloon}
   * attached to the selection. When an media is already linked, the view shows
   * {@link module:link/linkui~LinkUI#actionsView} or
   * {@link module:link/linkui~LinkUI#formView} if it is not.
   */
  _createToolbarLinkMediaButton() {
    const { editor } = this;

    editor.ui.componentFactory.add('drupalLinkMedia', (locale) => {
      const button = new ButtonView(locale);
      const plugin = editor.plugins.get('LinkUI');
      const linkCommand = editor.commands.get('link');

      button.set({
        isEnabled: true,
        label: Drupal.t('Link media'),
        icon: linkIcon,
        keystroke: LINK_KEYSTROKE,
        tooltip: true,
        isToggleable: true,
      });

      // Bind button to the command.
      button.bind('isEnabled').to(linkCommand, 'isEnabled');
      button.bind('isOn').to(linkCommand, 'value', (value) => !!value);

      // Show the actionsView or formView (both from LinkUI) on button click
      // depending on whether the media is already linked.
      this.listenTo(button, 'execute', () => {
        if (this._isSelectedLinkedMedia(editor.model.document.selection)) {
          plugin._addActionsView();
        } else {
          plugin._showUI(true);
        }
      });

      return button;
    });
  }

  /**
   * Returns true if a linked media is the only selected element in the model.
   *
   * @param {module:engine/model/selection~Selection} selection
   * @return {Boolean}
   */
  // eslint-disable-next-line class-methods-use-this
  _isSelectedLinkedMedia(selection) {
    const selectedModelElement = selection.getSelectedElement();
    return (
      !!selectedModelElement &&
      selectedModelElement.is('element', 'drupalMedia') &&
      selectedModelElement.hasAttribute('linkHref')
    );
  }
}
