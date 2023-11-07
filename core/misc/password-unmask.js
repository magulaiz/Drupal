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
    trigger.setAttribute(
      'aria-pressed',
      element.getAttribute('type') === 'password' ? 'false' : 'true',
    );
    trigger.textContent =
      element.getAttribute('type') === 'password' ? hidePass : showPass;
  };
  const unmaskButton = function unmaskButton(element) {
    const trigger = document.createElement('button');
    trigger.setAttribute('type', 'button');
    trigger.setAttribute('class', 'link toggle-password');
    trigger.setAttribute('aria-pressed', 'false');
    trigger.textContent = showPass;
    element.insertAdjacentElement('afterend', trigger);
    trigger.addEventListener('click', function () {
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
