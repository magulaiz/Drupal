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
    trigger.setAttribute('aria-label', 'make password visible');
  };
  const unmaskButton = function unmaskButton(element) {
    const trigger = document.createElement('button');
    trigger.setAttribute('type', 'button');
    trigger.setAttribute('class', 'link toggle-password');
    trigger.setAttribute('class', 'button button--small');
    trigger.setAttribute('style', 'margin-inline-start:10px');
    trigger.setAttribute(
      'aria-checked',
      element.getAttribute('type') === 'password' ? 'true' : 'false',
    );
    trigger.setAttribute('aria-label', 'make password visible');
    trigger.setAttribute('role', 'switch');
    trigger.textContent = showPass;
    element.insertAdjacentElement('afterend', trigger);
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
