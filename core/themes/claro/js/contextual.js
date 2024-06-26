(function (Drupal) {
  Drupal.contextual.AuralView = Drupal.contextual.AuralView.extend({
    render: function render() {
      const isOpen = this.model.get('isOpen');
      this.el.querySelector('.contextual-links').hidden = !isOpen;
      const triggerText = Drupal.t('@action @title configuration options', {
        '@action': !isOpen
          ? this.options.strings.open
          : this.options.strings.close,
        '@title': this.model.get('title'),
      });
      this.el.querySelector('.trigger').innerHTML = Drupal.theme(
        'contextualTriggerText',
        triggerText,
      );
      this.el.querySelector('.trigger').setAttribute('aria-pressed', isOpen);
    },
  });

  Drupal.theme.contextualTrigger = () => {
    return '<button class="contextual__trigger trigger visually-hidden focusable icon-link icon-link--small" type="button"></button>';
  };

  Drupal.theme.contextualTriggerText = (text) => {
    return '<span class="visually-hidden">'.concat(text, '</span>');
  };
})(window.Drupal);
