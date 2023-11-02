/**
 * @file
 * Splitbutton feature.
 */
// cspell:ignore UIDOM

((Drupal, { computePosition, flip, autoUpdate }) => {
  /**
   * Constructs a splitbutton UI element.
   *
   * @param {HTMLElement} splitbutton
   *   Markup that includes actionable list items such as links or submit
   *   inputs, and a button that toggles their visibility.
   *
   * @return {Drupal.Splitbutton}
   *   Class representing a splitbutton UI element.
   */
  Drupal.Splitbutton = class {
    constructor(splitbutton) {
      const dataPrefix = 'data-drupal-splitbutton-';
      this.dataPrefix = dataPrefix;
      splitbutton.setAttribute('data-drupal-splitbutton-initialized', '');

      this.keyCode = Object.freeze({
        TAB: 9,
        RETURN: 13,
        ESC: 27,
        SPACE: 32,
        UP: 38,
        DOWN: 40,
      });
      this.listItems = [];

      this.splitbutton = splitbutton;
      this.list = splitbutton.querySelector(
        '[data-drupal-selector="splitbutton-item-list"]',
      );
      this.list.id = `splitbutton-${Drupal.splitbuttons.length}`;
      this.list.setAttribute('aria-labelledby', `${this.list.id}-toggle`);
      this.list.setAttribute('popover', '');
      this.trigger = splitbutton.querySelector(
        '[data-drupal-selector="splitbutton-trigger"]',
      );
      this.trigger.id = `${this.list.id}-toggle`;
      this.trigger.setAttribute('popovertarget', this.list.id);
      this.trigger.setAttribute('aria-controls', this.list.id);
      this.triggerBox = splitbutton.querySelector(
        `[data-drupal-selector="splitbutton-main"]`,
      );

      this.splitbutton.addEventListener('keydown', (e) => this.keydown(e));
      this.list.addEventListener('toggle', (e) => this.toggle(e));

      /**
       * Update positioning of the list with Floating UI.
       */
      autoUpdate(this.trigger, this.list, () => {
        computePosition(this.triggerBox, this.list, {
          placement: 'bottom-end',
          middleware: [flip({ mainAxis: false })],
        }).then(({ x, y }) => {
          Object.assign(this.list.style, {
            left: `${x}px`,
            top: `${y}px`,
            position: 'absolute',
            margin: 0,
            minWidth: `${this.triggerBox.clientWidth}px`,
          });
        });
      });
    }

    /**
     * Populate instance variables that facilitate keyboard navigation.
     */
    initListItems() {
      // If this.listItems is empty, the initialization hasn't occurred yet.
      if (this.listItems.length === 0) {
        const itemTags =
          this.list.getAttribute(`${this.dataPrefix}tags`) ||
          'a, input, button';
        this.list.querySelectorAll(itemTags).forEach((item, index) => {
          item.setAttribute('data-drupal-selector', 'splitbutton-item');
          this.listItems.push(item);
          this.lastItemIndex = index;
        });
      }
    }

    /**
     * Event handler for a splitbutton popover being toggled.
     *
     * @param {Event} e
     *   A toggle event.
     */
    toggle(e) {
      this.initListItems();
      this.splitbutton[
        e.newState === 'open' ? 'setAttribute' : 'removeAttribute'
      ](`${this.dataPrefix}open`, '');
      this.trigger.setAttribute('aria-expanded', e.newState === 'open');
    }

    /**
     * Keydown listener.
     *
     * @param {Event} e
     *   The keydown event.
     */
    keydown(e) {
      if (
        e.ctrlKey ||
        e.altKey ||
        e.metaKey ||
        e.keyCode === this.keyCode.SPACE ||
        e.keyCode === this.keyCode.RETURN ||
        (e.keyCode === this.keyCode.TAB &&
          e.target.getAttribute(`${this.dataPrefix}item`) === null)
      ) {
        return;
      }

      switch (e.keyCode) {
        case this.keyCode.ESC:
          this.focusTrigger();
          this.list.hidePopover();
          break;

        case this.keyCode.UP:
          if (!this.splitbutton.hasAttribute(`${this.dataPrefix}open`)) {
            this.list.showPopover();
          }
          this.advanceFocus(e, true);

          break;

        case this.keyCode.DOWN:
          if (!this.splitbutton.hasAttribute(`${this.dataPrefix}open`)) {
            this.list.showPopover();
          }
          this.advanceFocus(e);
          break;

        default:
          return;
      }

      e.stopPropagation();
      e.preventDefault();
    }

    /**
     * Move focus within the splitbutton elements
     * @param {Event} e
     *   A keydown event.
     * @param {boolean} reverse
     *   If focus should move up the list instead of the default down direction.
     */
    advanceFocus(e, reverse = false) {
      this.initListItems();
      const items = this.list.querySelectorAll(
        `[data-drupal-selector="splitbutton-item"]`,
      );
      const current = Array.from(items).indexOf(e.target);
      if (current < 0) {
        this.listItems[reverse ? this.lastItemIndex : 0].focus();
      } else {
        const advanceIndex = reverse
          ? parseInt(current, 10) - 1
          : parseInt(current, 10) + 1;
        if (typeof this.listItems[advanceIndex] === 'undefined') {
          this.focusListItem(reverse ? this.lastItemIndex : 0);
        } else {
          this.focusListItem(advanceIndex);
        }
      }
    }

    /**
     * Focus an item in the list.
     *
     * @param int index
     *   The index of the item to focus within the list.
     */
    focusListItem(index) {
      // Temporarily change the item to contenteditable so focus-visible styling
      // will work, even if it isn't an <input> element.
      this.listItems[index].contenteditable = true;
      this.listItems[index].focus();
      this.listItems[index].contenteditable = false;
    }

    /**
     * Assigns focus to the trigger element.
     */
    focusTrigger() {
      this.trigger.focus();
    }
  };
})(Drupal, window.FloatingUIDOM);
