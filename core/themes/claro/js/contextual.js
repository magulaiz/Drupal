(function (Drupal) {
  Drupal.contextual.AuralView = Drupal.contextual.AuralView.extend({
    render: function render() {
      const isOpen = this.model.get('isOpen');

      // Use optional chaining to ensure the elements are present.
      const contextualLinks = this.el.querySelector('.contextual-links');
      if (contextualLinks) {
        contextualLinks.hidden = !isOpen;
      }

      const trigger = this.el.querySelector('.trigger');
      if (trigger) {
        const triggerText = Drupal.t('@action @title configuration options', {
          '@action': !isOpen
            ? this.options.strings.open
            : this.options.strings.close,
          '@title': this.model.get('title'),
        });

        trigger.innerHTML = Drupal.theme('contextualTriggerText', triggerText);
        trigger.setAttribute('aria-pressed', isOpen);
      }
    },
  });

  Drupal.theme.contextualTrigger = () => {
    return '<button class="contextual__trigger trigger visually-hidden focusable icon-link icon-link--small" type="button"></button>';
  };

  Drupal.theme.contextualTriggerText = (text) => {
    return `<span class="visually-hidden">${text}</span>`;
  };
})(window.Drupal);
