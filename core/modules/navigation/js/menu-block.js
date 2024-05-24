/**
 * @file
 * Navigation menu block behaviors.
 */

((Drupal, once) => {
  /**
   * Manages the menu block client side caching strategy.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the navigation block placement highlighting.
   */
  Drupal.behaviors.navigationMenuBlock = {
    attach(context, settings) {
      if (!once('menu-block', '#admin-toolbar').length) {
        return;
      }
      const subtrees = drupalSettings.navigation.subtrees;
      const theme = drupalSettings.ajaxPageState.theme;

      Object.entries(subtrees).forEach(([subtreeHash, subtree]) => {
        const { menuName, level, depth } = subtree;
        const endpoint = Drupal.url(
          `navigation/subtrees/${menuName}/${level}/${depth}/${subtreeHash}`,
        );
        const cachedSubtreesHash = localStorage.getItem(
          `Drupal.navigation.subtreesHash.${subtreeHash}.${theme}`,
        );
        const cachedSubtrees = JSON.parse(
          localStorage.getItem(
            `Drupal.navigation.subtrees.${subtreeHash}.${theme}`,
          ),
        );

        if (subtreeHash === cachedSubtreesHash && cachedSubtrees) {
          Drupal.behaviors.navigationMenuBlock.replaceSubtree(
            subtreeHash,
            cachedSubtrees,
          );
        } else {
          // Remove the cached menu information.
          localStorage.removeItem(
            `Drupal.navigation.subtreesHash.${subtreeHash}.${theme}`,
          );
          localStorage.removeItem(
            `Drupal.navigation.subtrees.${subtreeHash}.${theme}`,
          );
          // The AJAX response's command will trigger the replaceSubtree method.
          Drupal.ajax({ url: endpoint }).execute();
          // Cache the hash for the subtrees locally.
          localStorage.setItem(
            `Drupal.navigation.subtreesHash.${subtreeHash}.${theme}`,
            subtreeHash,
          );
        }
      });
    },
    /**
     * Replaces the subtree in the native HTML with the one passed as parameter.
     */
    replaceSubtree(hash, subtree) {
      const subtreesToReplace = document.querySelectorAll(
        `[data-menu-hash="${hash}"]`,
      );

      subtreesToReplace.forEach((subtreeToReplace) => {
        subtreeToReplace.innerHTML = subtree;
      });
    },
  };

  /**
   * Ajax command to set the navigation menu block subtrees.
   *
   * @param {Drupal.Ajax} ajax
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   JSON response from the Ajax request.
   * @param {number} [status]
   *   XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.setNavigationSubtree = function (
    ajax,
    response,
    status,
  ) {
    const { hash, subtrees } = response;
    const theme = drupalSettings.ajaxPageState.theme;
    localStorage.setItem(
      `Drupal.navigation.subtrees.${hash}.${theme}`,
      JSON.stringify(subtrees),
    );
    Drupal.behaviors.navigationMenuBlock.replaceSubtree(hash, subtrees);
  };
})(Drupal, once);
