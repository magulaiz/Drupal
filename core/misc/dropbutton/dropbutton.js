/**
 * @file
 * Dropbutton feature.
 */

customElements.define(
  'drupal-dropbutton',
  class DrupalDropbutton extends HTMLElement {
    connectedCallback() {
      const settings = drupalSettings?.dropbutton;
      // Merge defaults with settings.
      const options = {
        title: Drupal.t('List additional actions'),
        ...settings,
      };

      const actions = this.querySelectorAll('.dropbutton li');

      // Add the special dropdown only if there are hidden actions.
      if (actions.length > 1) {
        // Identify the first element of the collection.
        const primary = actions[0];

        this.classList.add('dropbutton-multiple');
        actions.forEach((li) =>
          li.classList.add('dropbutton-action', 'secondary-action'),
        );
        primary.classList.remove('secondary-action');
        // Add toggle link.
        primary.insertAdjacentHTML(
          'afterend',
          DrupalDropbutton.dropbuttonToggle(options),
        );

        this.addEventListener('click', this);
        this.addEventListener('mouseleave', this);
        this.addEventListener('mouseenter', this);
        this.addEventListener('focusout', this);
        this.addEventListener('focusin', this);
      } else {
        this.classList.add('dropbutton-single');
      }
    }

    disconnectedCallback() {
      this.removeEventListener('click', this);
      this.removeEventListener('mouseleave', this);
      this.removeEventListener('mouseenter', this);
      this.removeEventListener('focusout', this);
      this.removeEventListener('focusin', this);
    }

    handleEvent(event) {
      if (
        event.type === 'click' &&
        event.target.matches('[data-drupal-dropbutton-toggle]')
      ) {
        event.preventDefault();
        this.toggle();
      } else if (['mouseleave', 'focusout'].includes(event.type)) {
        this.hoverOut();
      } else if (['mouseenter', 'focusin'].includes(event.type)) {
        this.hoverIn();
      }
    }

    /**
     * Toggle the dropbutton open and closed.
     *
     * @param {boolean} [show]
     *   Force the dropbutton to open by passing true or to close by
     *   passing false.
     */
    toggle(show) {
      const isBool = typeof show === 'boolean';
      show = isBool ? show : !this.classList.contains('open');
      this.classList.toggle('open', show);
    }

    /**
     * @method
     */
    hoverIn() {
      // Clear any previous timer we were using.
      if (this.timerID) {
        window.clearTimeout(this.timerID);
      }
    }

    /**
     * @method
     */
    hoverOut() {
      // Wait half a second before closing.
      this.timerID = window.setTimeout(() => this.close(), 500);
    }

    /**
     * @method
     */
    open() {
      this.toggle(true);
    }

    /**
     * @method
     */
    close() {
      this.toggle(false);
    }

    static dropbuttonToggle(options) {
      if (Drupal?.theme?.dropbuttonToggle) {
        return Drupal.theme.dropbuttonToggle(options);
      }
      return `<li class="dropbutton-toggle"><button type="button" data-drupal-dropbutton-toggle><span class="dropbutton-arrow"><span class="visually-hidden">${options.title}</span></span></button></li>`;
    }
  },
);
