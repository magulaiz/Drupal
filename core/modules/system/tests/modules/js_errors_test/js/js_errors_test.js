/**
 * @param root0
 * @param root0.throwError
 * @param root0.behaviors
 * @file
 *  Testing tools for JavaScript errors.
 */
(function ({ throwError, behaviors }) {
  behaviors.testErrors = {
    attach: () => {
      throwError(new Error('A manually thrown error.'));
    },
  };
})(Drupal);
