/**
 * @file
 * Customization of messages.
 */

((Drupal, once) => {
  /**
   * Removes duplicate close buttons from the message.
   */
  const removeDuplicateCloseButtons = () => {
    // Select all elements with class 'messages__button'.
    const messageCloseButton = document.querySelectorAll('.messages__button');
    if (messageCloseButton != null) {
      // Remove all elements except the first one.
      for (let i = 1; i < messageCloseButton.length; i++) {
        messageCloseButton[i].parentNode.removeChild(messageCloseButton[i]);
      }
    }
  };

  /**
   * Adds a close button to the message.
   *
   * @param {object} message
   *   The message object.
   */
  const closeMessage = (message) => {
    const messageContainer = message.querySelector(
      '[data-drupal-selector="messages-container"]',
    );

    const closeBtnWrapper = document.createElement('div');
    closeBtnWrapper.setAttribute('class', 'messages__button');

    const closeBtn = document.createElement('button');
    closeBtn.setAttribute('type', 'button');
    closeBtn.setAttribute('class', 'messages__close');

    const closeBtnText = document.createElement('span');
    closeBtnText.setAttribute('class', 'visually-hidden');
    closeBtnText.innerText = Drupal.t('Close message');

    messageContainer.appendChild(closeBtnWrapper);
    closeBtnWrapper.appendChild(closeBtn);
    closeBtn.appendChild(closeBtnText);
    removeDuplicateCloseButtons();

    closeBtn.addEventListener('click', () => {
      message.classList.add('hidden');
    });
  };

  /**
   * Get messages from context.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the close button behavior for messages.
   */
  Drupal.behaviors.messages = {
    attach(context) {
      once('messages', '[data-drupal-selector="messages"]', context).forEach(
        closeMessage,
      );
    },
  };

  Drupal.olivero.closeMessage = closeMessage;
})(Drupal, once);
