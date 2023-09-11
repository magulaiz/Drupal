/**
 * @file
 * Dialog API inspired by HTML5 dialog element.
 *
 * @see http://www.whatwg.org/specs/web-apps/current-work/multipage/commands.html#the-dialog-element
 */

(function ($, Drupal, drupalSettings, bodyScrollLock) {
  /**
   * Default dialog options.
   *
   * @type {object}
   *
   * @prop {boolean} [autoOpen=true]
   * @prop {string} [dialogClass='']
   * @prop {string} [buttonClass='button']
   * @prop {string} [buttonPrimaryClass='button--primary']
   * @prop {function} close
   */
  drupalSettings.dialog = {
    autoOpen: true,
    dialogClass: '',
    // Drupal-specific extensions: see dialog.jquery-ui.js.
    buttonClass: 'button',
    buttonPrimaryClass: 'button--primary',
    // When using this API directly (when generating dialogs on the client
    // side), you may want to override this method and do
    // `jQuery(event.target).remove()` as well, to remove the dialog on
    // closing.
    close(dialog) {
      Drupal.detachBehaviors(dialog, null, 'unload');
    },
  };

  Drupal.theme.dialogTemplate = () => `
  <style>
    dialog {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: var(--dialog-background-color, #fff);
    }
    header {
      display: flex;
      align-items: start;
      justify-content: space-between;
      gap: 10px;
    }
    footer {
      display: flex;
      align-items: start;
      justify-content: start;
      gap: 10px;
    }
  </style>
  <dialog>
    <header>
      <slot name="title"></slot>
      <slot name="close"><button autofocus>X</button></slot>
    </header>
    <slot></slot>
    <footer>
      <slot name="actions"></slot>
    </footer>
  </dialog>`;

  class DialogWrapper extends HTMLElement {
    constructor() {
      super();
      this.attachShadow({ mode: 'open' });
      this.shadowRoot.innerHTML = Drupal.theme('dialogTemplate');
      this.dialog = this.shadowRoot.querySelector('dialog');
    }
    connectedCallback() {
      this.shadowRoot.querySelector('slot[name="close"]').onclick = () => {
        this.close();
      };
    }
    setFocus() {
      const focusTarget = this.dialog.querySelector('[autofocus]');
      focusTarget
        ? focusTarget.focus()
        : this.dialog.querySelector('button').focus();
    }
    close(value) {
      this.dialog.close();
      bodyScrollLock.clearBodyLocks();
      this.settings.close(this);
      this.returnValue = value;
    }
    show() {
      this.dialog.show();
      this.setFocus();
    }
    showModal() {
      this.dispatchEvent(
        new CustomEvent('dialog:beforecreate', {
          bubbles: true,
        }),
      );
      this.dialog.showModal();
      bodyScrollLock.lock(this);
      this.setFocus();
      this.dispatchEvent(
        new CustomEvent('dialog:aftercreate', {
          bubbles: true,
        }),
      );
    }
  }

  customElements.define('drupal-dialog', DialogWrapper);

  const createActions = (actions) =>
    actions.map((action) => {
      const button = document.createElement('button');
      action.class.split(' ').map((className) => {
        button.classList.add(className);
      });
      button.innerText = action.text;
      button.addEventListener('click', action.click);
      button.setAttribute('slot', 'actions');
      return button;
    });

  const createDialog = (element, settings) => {
    const dialog = document.createElement('drupal-dialog');
    settings.dialogClass.split(' ').map((className) => {
      dialog.classList.add(className);
    });
    const actions = createActions(settings.buttons);
    dialog.innerHTML = `<h2 slot="title" class="ui-visual-focus">${settings.title}</h2>`;
    element.setAttribute('data-default-slot', '');
    dialog.append(element);
    actions.map((action) => {
      dialog.append(action);
    });
    dialog.settings = settings;
    document.body.appendChild(dialog);
    return dialog;
  };

  /**
   * @typedef {object} Drupal.dialog~dialogDefinition
   *
   * @prop {boolean} open
   *   Is the dialog open or not.
   * @prop {*} returnValue
   *   Return value of the dialog.
   * @prop {function} show
   *   Method to display the dialog on the page.
   * @prop {function} showModal
   *   Method to display the dialog as a modal on the page.
   * @prop {function} close
   *   Method to hide the dialog from the page.
   */

  /**
   * Polyfill HTML5 dialog element with jQueryUI.
   *
   * @param {HTMLElement} element
   *   The element that holds the dialog.
   * @param {object} options
   *   jQuery UI options to be passed to the dialog.
   *
   * @return {Drupal.dialog~dialogDefinition}
   *   The dialog instance.
   */
  Drupal.dialog = function (element, options) {
    const $element = $(element);
    const settings = Object.assign({}, drupalSettings.dialog, options);
    const dialog = createDialog(element, settings);

    function closeDialog(value) {
      $(window).trigger('dialog:beforeclose', [dialog, $element]);
      // Unlocks the body when the dialog closes.

      $(window).trigger('dialog:afterclose', [dialog, $element]);
    }

    return dialog;
  };
})(jQuery, Drupal, drupalSettings, bodyScrollLock);
