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
    trigger.setAttribute('aria-label', 'make password visible');
  };
  const unmaskButton = function unmaskButton(element) {
    const wrapperButton = document.createElement('button');
    wrapperButton.setAttribute('type', 'button');
    wrapperButton.setAttribute('class', 'link password-wrapper');
    const trigger = document.createElement('span');
    wrapperButton.appendChild(trigger);
    trigger.setAttribute(
      'class',
      'action-link action-link--extrasmall action-link--icon-show toggle-password',
    );
    wrapperButton.setAttribute('style', 'margin-inline-start:10px');
    trigger.setAttribute(
      'aria-checked',
      element.getAttribute('type') === 'password' ? 'true' : 'false',
    );
    trigger.setAttribute('aria-label', 'make password visible');
    trigger.setAttribute('role', 'switch');
    trigger.textContent = showPass;
    element.insertAdjacentElement('afterend', wrapperButton);
    trigger.addEventListener('click', () => {
      return unmaskClickHandler(element, trigger);
    });
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
