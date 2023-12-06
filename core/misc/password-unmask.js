/**
 * @file
 * Password Unmask.
 */
(function (Drupal, once) {
  const hidePass = Drupal.t('Hide password');
  const showPass = Drupal.t('Show password');
  const unmaskClickHandler = function unmaskClickHandler(element, trigger) {
    element.setAttribute(
      'type',
      element.getAttribute('type') === 'password' ? 'text' : 'password',
    );
    trigger.textContent =
      element.getAttribute('type') === 'password' ? showPass : hidePass;
    trigger.setAttribute(
      'aria-checked',
      element.getAttribute('type') === 'password' ? 'true' : 'false',
    );
    trigger.classList.add(
      element.getAttribute('type') === 'password'
        ? 'action-link--icon-show'
        : 'action-link--icon-hide',
    );
    trigger.classList.remove(
      element.getAttribute('type') === 'password'
        ? 'action-link--icon-hide'
        : 'action-link--icon-show',
    );
    element.setAttribute(
      'aria-description',
      element.getAttribute('type') === 'password'
        ? 'Password is hidden'
        : 'Password is visible',
    );
  };
  const unmaskButton = function unmaskButton(element) {
    const trigger = Drupal.theme.triggerElement(element);
    const wrapperButton = Drupal.theme.buttonWrapper(trigger);
    element.insertAdjacentElement('afterend', wrapperButton);
    trigger.addEventListener('click', () => {
      return unmaskClickHandler(element, trigger);
    });
  };
  /**
   * Theme function for a button-wrapper.
   *
   * @param {HTMLElement} triggerElement
   *   The trigger element.
   *
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.buttonWrapper = (triggerElement) => {
    const wrapperButton = document.createElement('button');
    wrapperButton.setAttribute('type', 'button');
    wrapperButton.setAttribute('class', 'link password-wrapper');
    wrapperButton.appendChild(triggerElement);
    wrapperButton.setAttribute('style', 'margin-inline-start:10px');
    return wrapperButton;
  };

  /**
   * Theme function for a trigger element.
   *
   * @param {HTMLElement} element
   *   The input element.
   *
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.triggerElement = (element) => {
    const trigger = document.createElement('span');
    trigger.setAttribute(
      'class',
      'action-link action-link--extrasmall action-link--icon-show toggle-password',
    );
    trigger.setAttribute(
      'aria-checked',
      element.getAttribute('type') === 'password' ? 'true' : 'false',
    );
    trigger.setAttribute('aria-label', Drupal.t('make password visible'));
    trigger.setAttribute('role', 'switch');
    trigger.textContent = showPass;
    return trigger;
  };

  Drupal.behaviors.passwordUnmask = {
    attach: function attach(context) {
      once(
        'password-unmask',
        'input[type=password][data-drupal-password-unmask]',
        context,
      ).forEach(function (password) {
        unmaskButton(password);
      });
    },
  };
})(Drupal, once);
