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
    const wrapperButton = Drupal.theme.buttonWrapper(element);
    element.insertAdjacentElement('afterend', wrapperButton);
    wrapperButton.addEventListener('click', () => {
      return unmaskClickHandler(element, wrapperButton);
    });
  };

  /**
   * Theme function for a button-wrapper.
   *
   * @param {HTMLElement} element
   *   The input element.
   *
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.buttonWrapper = (element) => {
    const wrapperButton = document.createElement('button');
    wrapperButton.setAttribute('type', 'button');
    wrapperButton.setAttribute('class', 'link password-wrapper');
    wrapperButton.setAttribute('style', 'margin-inline-start:10px');
    wrapperButton.setAttribute(
      'class',
      'action-link action-link--extrasmall action-link--icon-show toggle-password',
    );
    wrapperButton.setAttribute(
      'aria-checked',
      element.getAttribute('type') === 'password' ? 'true' : 'false',
    );
    wrapperButton.textContent = showPass;
    wrapperButton.setAttribute('aria-label', Drupal.t('make password visible'));
    wrapperButton.setAttribute('role', 'switch');
    return wrapperButton;
  };

  Drupal.behaviors.passwordUnmask = {
    attach: function attach(context) {
      once(
        'password-unmask',
        'input[type=password][data-drupal-password-unmask]',
        context,
      ).forEach((password) => {
        unmaskButton(password);
      });
    },
  };
})(Drupal, once);
