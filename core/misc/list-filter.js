/**
 * @file
 * Provide list filtering capabilities to admin UIs.
 */

(function ($, Drupal, drupalSettings) {

  /**
   * Filter lists or tables of text.
   *
   * Using the list_filter render element, any collection of items may be
   * filtered. Items which do not match the text entered into the filter
   * search box are hidden.
   *
   * The collection may optionally be grouped with either containing elements or
   * headers. When groups are present, if the result of a filter hides all the
   * rows belonging to a group, then the group is also hidden.
   *
   * The collection container, items, text source elements, and grouping
   * elements are specified as CSS selectors in the configuration of the
   * list_filter render element.
   *
   * Created listFilter instances may be modified with custom behaviors by
   * overriding the .filterList methods.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.listFilter = {
    attach(context, settings) {
      function initListFilter($container, base) {
        Drupal.listFilter[base] = new Drupal.listFilter(
          $container,
          settings.listFilter[base],
        );
      }

      // Find all listFilter configurations placed in drupalSettings by
      // list_filter render elements.
      Object.keys(settings.listFilter || {}).forEach((base) => {
        initListFilter($(once('listFilter', `#${base}`, context)), base);
      });
    },
  };

  /**
   * Provides filtering of collections.
   *
   * @constructor
   *
   * @param {jQuery} $container
   *   jQuery object for the container to search inside.
   * @param {object} listFilterSettings
   *   Settings for the filtering added via the list_filter render element.
   */
  Drupal.listFilter = function ($container, listFilterSettings) {
    /**
     * jQuery object for the filter container.
     *
     * All items and groups are within this container.
     */
    this.$container = $container;

    /**
     * jQuery object for the rows to search in.
     */
    this.$rows;

    /**
     * Array of text to search in. Indexes are the same as this.$rows.
     */
    this.sources = [];

    /**
     * jQuery object for the filter search box.
     */
    const $input = $('#' + listFilterSettings.search_field_id);

    function preventEnterKey(event) {
      if (event.which === 13) {
        event.preventDefault();
        event.stopPropagation();
      }
    }

    // Initialize rows.
    this.$rows = this.$container.find(listFilterSettings.list_item);

    if (this.$rows.length) {
      // Build up an array of text content, with the same indexes as the $rows.
      // This is to avoid having to concatenate any multiple text items on
      // every search.
      this.$rows.each(function (index, row) {
        let $sources = listFilterSettings.list_text ?
          $(row).find(listFilterSettings.list_text) :
          $(row);

        // Concatenate the textContent of the elements in the row, with a
        // space in between.
        let sourcesConcat = '';
        $sources.each((index, item) => {
          sourcesConcat += ' ' + item.textContent;
        });

        this.sources[index] = sourcesConcat;
      }.bind(this));

      // Filter rows when search text is entered.
      $input.on({
        keyup: Drupal.debounce(jQuery.proxy(this.filterList, this), 200),
        click: Drupal.debounce(jQuery.proxy(this.filterList, this), 200),
        keydown: preventEnterKey,
      });
    }
  };

  /**
   * Filters the list in response to typing in the search box.
   *
   * @param {*} e
   *  The event.
   */
  Drupal.listFilter.prototype.filterList = function (e) {
    const query = e.target.value;

    // Case insensitive expression to find query at the beginning of a word.
    const re = new RegExp(`\\b${query}`, 'i');

    // Reset table when the textbox is cleared.
    if (query.length === 0) {
      this.$rows.show();

      return;
    }

    // Search in all of the rows' sources and show or hide accordingly.
    this.sources.forEach((source, index) => {
      const match = source.search(re) !== -1;

      this.$rows.eq(index).toggle(match);
    });
  };


})(jQuery, Drupal, drupalSettings);

