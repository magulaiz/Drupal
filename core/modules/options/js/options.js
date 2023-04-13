/**
 * @file
 * Block behaviors.
 */

(function ($, window, Drupal, once) {
  /**
   * Move a block in the blocks table between regions via select list.
   *
   * This behavior is dependent on the tableDrag behavior, since it uses the
   * objects initialized in that behavior to update the row.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the tableDrag behavior for blocks in block administration.
   */
  Drupal.behaviors.blockDrag = {
    attach(context, settings) {
      if (
        typeof Drupal.tableDrag === 'undefined' ||
        typeof Drupal.tableDrag['allowed-values-order'] === 'undefined'
      ) {
        return;
      }

      /**
       * Function to update the last placed row with the correct classes.
       *
       * @param {jQuery} table
       *   The jQuery object representing the table to inspect.
       * @param {jQuery} rowObject
       *   The jQuery object representing the table row.
       */
      function updateLastPlaced(table, rowObject) {
        // Remove the color-success class from new block if applicable.
        table.find('.color-success').removeClass('color-success');

        const $rowObject = $(rowObject);
        if (!$rowObject.is('.drag-previous')) {
          table.find('.drag-previous').removeClass('drag-previous');
          $rowObject.addClass('drag-previous');
        }
      }

      /**
       * Update block weights in the given region.
       *
       * @param {jQuery} table
       *   Table with draggable items.
       */
      function updateBlockWeights(table) {
        // Calculate minimum weight.
        let weight = -Math.round(table.find('.draggable').length / 2);
        // Update the block weights.
        table.find('select.weight').each(function () {
          // Increment the weight before assigning it to prevent using the
          // absolute minimum available weight. This way we always have an
          // unused upper and lower bound, which makes manually setting the
          // weights easier for users who prefer to do it that way.
          this.value = ++weight;
        });
      }

      const table = $('#allowed-values-order');
      // Get the blocks tableDrag object.
      const tableDrag = Drupal.tableDrag['allowed-values-order'];
      tableDrag.row.prototype.onSwap = function (swappedRow) {
        updateLastPlaced(table, this);
      };

      tableDrag.onDrop = function () {
        updateBlockWeights(table);
      };
    },
  };
})(jQuery, window, Drupal, once);
