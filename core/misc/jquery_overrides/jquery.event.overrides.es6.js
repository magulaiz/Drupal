/**
 * @file
 * Overrides jQuery event functions.
 */

(($, Drupal) => {
  const autocompleteEvents = {
    autocompletechange: 'autocomplete-change',
    autocompleteclose: 'autocomplete-close',
    autocompletecreate: 'autocomplete-created',
    autocompletefocus: 'autocomplete-highlight',
    autocompleteopen: 'autocomplete-open',
    autocompleteresponse: 'autocomplete-response',
    autocompletesearch: 'autocomplete-pre-search',
    autocompleteselect: 'autocomplete-select',
  };

  /**
   * Replicate jQuery UI autocomplete normalization.
   *
   * Do not create a new object to allow changes to propagate.
   *
   * @param {object|string} item
   *  The suggestion object.
   * @return {{label: string, value: string}}
   *  The normalized suggestion.
   */
  const normalizeItem = (item) => {
    if (typeof item === 'string') {
      return { label: item, value: item };
    }
    item.label = item.label || item.value || item;
    item.value = item.value || item.label || item;
    // Return the original item to allow modifications to propagate.
    return item;
  };

  /**
   * Create a callback that transforms arguments from events coming from
   * @drupal/autocomplete into arguments expected by jQuery UI Autocomplete
   * event listeners.
   *
   * @param {object} handleObj
   *  jQuery internal handler object.
   * @param {function} handleObj.handler
   *  The custom function that handler the event listener.
   * @param {string} handleObj.type
   *  The name of the jQuery UI event type.
   *
   * @return {function}
   *  The event listener callback bound to the @drupal/autocomplete event.
   */
  function shimHandler({ handler, type: eventName }) {
    return function shimHandlerCallback(e) {
      // Note the several of the event handlers include populating the
      // `originalEvent` property of the event object. This is not populated by
      // the actual original event, which would not fit the expectations of
      // jQuery UI's tests. Instead, it is populated with "blank" events of the
      // expected type. There is no reason to believe originalEvent is used
      // outside of tests, but we acknowledge the backwards compatibility of
      // originalEvent is limited to it being the expected event type.
      const ui = {};
      if (eventName === 'autocompleteresponse') {
        // We need to keep the reference of the original array, do not
        // create a new one with .map(), normalize items in place.
        e.detail.list.forEach((item, index) => {
          e.detail.list[index] = normalizeItem(item);
        });
        ui.content = e.detail.list;
      }
      if (eventName === 'autocompletechange') {
        e.originalEvent = $.Event('blur');
        const instance = e.detail.autocomplete._internal_object;
        // Do not normalize falsy values.
        instance.selected = instance.selected
          ? normalizeItem(instance.selected)
          : instance.selected;
        ui.item = instance.selected;
      }
      if (eventName === 'autocompletefocus') {
        e.detail.selected = normalizeItem(e.detail.selected);
        ui.item = e.detail.selected;
        e.originalEvent = $.Event('menufocus');
      }
      if (eventName === 'autocompleteselect') {
        e.detail.selected = normalizeItem(e.detail.selected);
        ui.item = e.detail.selected;
        e.originalEvent = $.Event('menuselect');
      }
      if (eventName === 'autocompleteclose') {
        e.originalEvent = $.Event('menuselect');
      }
      e.type = eventName;

      const eventReturn = handler.apply(this, [
        { ...$.Event(eventName, e), type: eventName },
        ui,
      ]);

      if (eventReturn === false) {
        e.preventDefault();
      }
      return eventReturn;
    };
  }

  /**
   * When a jQuery event listener is bound, bind an event listener to the
   * @drupal/autocomplete event and map the various jQuery UI properties
   * expected.
   *
   * @param {object} handleObj
   *  jQuery internal handler object.
   * @param {function} handleObj.handler
   *  The custom function that handler the event listener.
   * @param {string} handleObj.type
   *  The name of the jQuery UI event type.
   * @param {string} handleObj.selector
   *  The selector to filter elements with when the event listener is delegated.
   */
  function shimAdd(handleObj) {
    const element = this;
    element.addEventListener(
      autocompleteEvents[handleObj.type],
      shimHandler(handleObj),
    );
  }

  // Special jQuery feature that allows altering how events are managed.
  // In this case when those events are bound we create an additional event
  // listener that is bound to the corresponding @drupal/autocomplete event.
  $.extend($.event.special, {
    autocompletechange: { add: shimAdd },
    autocompleteclose: { add: shimAdd },
    autocompletecreate: { add: shimAdd },
    autocompletefocus: { add: shimAdd },
    autocompleteopen: { add: shimAdd },
    autocompleteresponse: { add: shimAdd },
    autocompletesearch: { add: shimAdd },
    autocompleteselect: { add: shimAdd },
  });
})(jQuery, Drupal);
