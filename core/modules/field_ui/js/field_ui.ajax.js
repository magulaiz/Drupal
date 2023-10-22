(function (Drupal) {
  /**
   * Ajax command to open URL in a modal dialog.
   *
   * @param {Drupal.Ajax} [ajax]
   *   An Ajax object.
   * @param {object} response
   *   The Ajax response.
   * @param {string} [status]
   *   The XHR status code.
   */
  Drupal.AjaxCommands.prototype.fieldUiOpenModalWithUrl = function (
    ajax,
    response,
    status,
  ) {
    const dialogOptions = response.dialogOptions || {};
    const elementSettings = {
      progress: { type: 'fullscreen' },
      dialogType: 'modal',
      dialog: dialogOptions,
      url: response.url,
      httpMethod: 'GET',
    };
    Drupal.ajax(elementSettings).execute();
  };
})(Drupal);
