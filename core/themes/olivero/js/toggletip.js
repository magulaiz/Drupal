/**
 * @file
 * Toggletip overrides.
 */

((Drupal) => {
  /**
   * Theme function for a button.
   *
   * @param {string} descriptionId
   *   The descriptionId.
   * @param {string} tipId
   *   The tipId.
   * @param {string} toggleId
   *   The toggleId.
   * @param {object} config
   *   The config.
   * @return {HTMLElement}
   *   A DOM Node.
   */
  Drupal.theme.toggletipButton = (descriptionId, tipId, toggleId, config) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.setAttribute('data-drupal-tip-toggle-button', config.content);
    button.setAttribute('aria-expanded', false);
    button.setAttribute('aria-labelledby', descriptionId);
    button.setAttribute('aria-controls', tipId);
    button.classList.add('toggletip');
    button.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"><path fill="#55565B" d="M7.194 2.073a1.729 1.729 0 0 0-.131.677c0 .24.042.463.127.67.085.205.214.394.384.566.171.17.361.3.568.386.209.085.436.128.68.128.24 0 .461-.044.666-.13a1.74 1.74 0 0 0 .562-.389c.172-.172.3-.362.385-.566a1.71 1.71 0 0 0 .127-.665c0-.243-.042-.47-.128-.675a1.7 1.7 0 0 0-.384-.564 1.79 1.79 0 0 0-.564-.382A1.72 1.72 0 0 0 8.822 1a1.73 1.73 0 0 0-1.236.51 1.696 1.696 0 0 0-.392.563ZM7.689 12.582l.001-.006 2.091-7.1.004-.025a.078.078 0 0 0-.08-.076l-4.935.159a.076.076 0 0 0-.074.058l-.254.95-.005.027c0 .043.035.079.077.079h.966c.475 0 .666.29.613.738-.032.268-.122.555-.192.816l-1.106 3.441c-.22.722-.447 1.561-.17 2.3.442 1.168 1.98 1.231 2.954.844.35-.14.691-.35 1.025-.627l.005-.004c.333-.277.662-.627.986-1.05.322-.419.64-.91.956-1.473a.08.08 0 0 0-.02-.106l-.684-.523a.075.075 0 0 0-.105.028c-.202.347-.396.658-.58.928a8.133 8.133 0 0 1-.536.708 2.95 2.95 0 0 1-.45.441c-.122.09-.223.137-.303.137-.365 0-.246-.436-.184-.664Z"/></svg> <span id=${descriptionId} class="visually-hidden">${config.atDescription}</span>`;
    button.id = toggleId;
    button.setAttribute('popovertarget', tipId);
    return button;
  };
})(Drupal);
