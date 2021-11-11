/**
 * @file
 * Defines the behavior of the Drupal administration toolbar.
 */

(function ($, Drupal, drupalSettings) {
  // Merge run-time settings with the defaults.
  const options = $.extend(
    {
      breakpoints: {
        'toolbar.narrow': '',
        'toolbar.standard': '',
        'toolbar.wide': '',
      },
    },
    drupalSettings.toolbar,
    // Merge strings on top of drupalSettings so that they are not mutable.
    {
      strings: {
        horizontal: Drupal.t('Horizontal orientation'),
        vertical: Drupal.t('Vertical orientation'),
      },
    },
  );

  const toolbarBehaviors = {
    /**
     * @type {object}
     *
     * @prop subtrees
     * @prop activeTab
     * @prop activeTray
     * @prop isOriented
     * @prop isFixed
     * @prop areSubtreesLoaded
     * @prop isViewportOverflowConstrained
     * @prop orientation
     * @prop locked
     * @prop isTrayToggleVisible
     * @prop height
     * @prop offsets
     *
     * @type {object}
     */
    _subtrees: {},
    get subtrees() {
      return this._subtrees;
    },
    set subtrees(value) {
      console.log('set subtrees activetab is', this.activeTab);
      this._subtrees = value;
      this.renderMenu();
      this.renderBody();
    },

    /**
     * validate something something
     *
     * @type {bool}
     */
    _validate: null,
    get validate() {
      return this._validate;
    },
    set validate(value) {
      this._validate = value;
    },

    /**
     * The active toolbar tab. All other tabs should be inactive under
     * normal circumstances. It will remain active across page loads. The
     * active item is stored as an ID selector e.g. '#toolbar-item--1'.
     *
     * @type {string}
     */
    _activeTab: JSON.parse(localStorage.getItem('Drupal.toolbar.activeTabID')),
    get activeTab() {
      return this._activeTab;
    },
    set activeTab(value) {
      console.log('activeTab is being set: ', value);
      if (value !== this.activeTab) {
        // Deactivate the previous tab if there was one
        if (this.activeTab) {
          const tabElement = document.querySelector(this.activeTab);
          console.log('tabelement: ', tabElement);
          tabElement.classList.remove('is-active');
          tabElement.ariaPressed = false;
        }
        // Deactivate the previous tray if there was one.
        if (this.activeTray) {
          const trayElement = document.querySelector(this.activeTray);
          trayElement.classList.remove('is-active');
        }
        this._activeTab = value;
        this.renderToolbar();
        this.updateToolbarHeight();
        // Broadcast model changes to other modules.
        $(document).trigger('drupalToolbarTabChange', this.activeTab);
      }
    },

    /**
     * Represents whether a tray is open or not. Stored as an ID selector e.g.
     * '#toolbar-item--1-tray'.
     *
     * @type {string}
     */
    _activeTray: null,
    get activeTray() {
      return this._activeTray;
    },
    set activeTray(value) {
      console.log('what is activetray before set: ', this.activeTray);
      console.log('what is the activetray trying to be: ,', value);
      if (value !== this.activeTray) {
        this._activeTray = value;
        this.renderBody();
        // Broadcast model changes to other modules.
        $(document).trigger('drupalToolbarTrayChange', this.activeTray);
      }
    },

    /**
     * Indicates whether the toolbar is displayed in an oriented fashion,
     * either horizontal or vertical.
     *
     * @type {bool}
     */
    _isOriented: false,

    get isOriented() {
      return this._isOriented;
    },

    set isOriented(value) {
      if (value !== this.isOriented) {
        console.log('set isOriented');
        this._isOriented = value;
        this.renderToolbar();
        this.updateToolbarHeight();
      }
    },

    /**
     * Indicates whether the toolbar is positioned absolute (false) or fixed
     * (true).
     *
     * @type {bool}
     */
    _isFixed: false,
    get isFixed() {
      return this._isFixed;
    },
    set isFixed(value) {
      this._isFixed = value;
      this.isToolbarFixed();
    },

    /**
     * Menu subtrees are loaded through an AJAX request only when the Toolbar
     * is set to a vertical orientation.
     *
     * @type {bool}
     */
    _areSubtreesLoaded: false,

    get areSubtreesLoaded() {
      return this._areSubtreesLoaded;
    },

    set areSubtreesLoaded(value) {
      this._areSubtreesLoaded = value;
    },

    /**
     * If the viewport overflow becomes constrained, isFixed must be true so
     * that elements in the trays aren't lost off-screen and impossible to
     * get to.
     *
     * @type {bool}
     */
    _isViewportOverflowConstrained: false,
    get isViewportOverflowConstrained() {
      return this._isViewportOverflowConstrained;
    },

    set isViewportOverflowConstrained(value) {
      // put it here (broadcast) check to see if its actually changed
      this._isViewportOverflowConstrained = value;
      this.isToolbarFixed();
    },

    /**
     * The orientation of the active tray.
     *
     * @type {string}
     */
    _orientation: 'horizontal',

    get orientation() {
      return this._orientation;
    },

    set orientation(value) {
      // put it here (broadcast) check to see if its actually changed
      if (value !== this.orientation && value !== null) {
        console.log('set orientation');
        this._orientation = value;
        this.renderToolbar();
        this.updateToolbarHeight();
        // Broadcast model changes to other modules.
        $(document).trigger('drupalToolbarOrientationChange', this.orientation);
      }
    },

    /**
     * A tray is locked if a user toggled it to vertical. Otherwise a tray
     * will switch between vertical and horizontal orientation based on the
     * configured breakpoints. The locked state will be maintained across page
     * loads.
     *
     * @type {bool}
     */
    _locked: false,

    /**
     * Indicates whether the tray orientation toggle is visible.
     *
     * @type {bool}
     */
    _isTrayToggleVisible: true,
    get isTrayToggleVisible() {
      return this._isTrayToggleVisible;
    },
    set isTrayToggleVisible(value) {
      if (value !== this.isTrayToggleVisible && value !== null) {
        console.log('set isTrayToggleVisible');
        this._isTrayToggleVisible = value;
        this.renderToolbar();
      }
    },

    /**
     * The height of the toolbar.
     *
     * @type {number}
     */
    _height: null,

    get height() {
      return this._height;
    },

    set height(value) {
      this._height = value;
    },

    /**
     * The current viewport offsets determined by {@link Drupal.displace}. The
     * offsets suggest how a module might position is components relative to
     * the viewport.
     *
     * @type {object}
     *
     * @prop {number} top
     * @prop {number} right
     * @prop {number} bottom
     * @prop {number} left
     */
    _offsets: {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
    },
    get offsets() {
      return this._offsets;
    },
    set offsets(value) {
      this._offsets = value;
      this.adjustPlacement();
    },

    onTabClick(e) {
      console.log('vanilla: onTabClick()');
      e.preventDefault();
      e.stopPropagation();
      // If this tab has a tray associated with it, it is considered an
      // activatable tab.
      if (e.target.hasAttribute('data-toolbar-tray')) {
        const { activeTab } = toolbarBehaviors;
        const clickedTab = e.currentTarget;

        // Set the event target as the active item if it is not already.
        if (!activeTab || clickedTab !== activeTab) {
          console.log('clickedTAB: ', clickedTab);
          this.activeTab = clickedTab;
        }
      }
    },
    renderToolbar() {
      console.log('vanilla: renderToolbar()');
      this.updateTabs();
      this.updateTrayOrientation();
      this.updateBarAttributes();

      document.querySelector('body').classList.remove('toolbar-loading');

      // Load the subtrees if the orientation of the toolbar is changed to
      // vertical. This condition responds to the case that the toolbar switches
      // from horizontal to vertical orientation. The toolbar starts in a
      // vertical orientation by default and then switches to horizontal during
      // initialization if the media query conditions are met. Simply checking
      // that the orientation is vertical here would result in the subtrees
      // always being loaded, even when the toolbar initialization ultimately
      // results in a horizontal orientation.
      //
      // @see Drupal.behaviors.toolbar.attach() where admin menu subtrees
      // loading is invoked during initialization after media query conditions
      // have been processed.
      if (this.orientation === 'vertical' || this.activeTab) {
        this.loadSubtrees();
      }
      // might not need to return a toolbarvisualview
      // return this;
    },
    renderMenu() {
      console.log('vanilla: render() in MenuVisualView');
      const { subtrees } = toolbarBehaviors;
      const menu = document.querySelector('.toolbar-menu-administration');
      // Add subtrees.
      Object.keys(subtrees || {}).forEach((id) => {
        $(
          once('toolbar-subtrees', menu.querySelector(`#toolbar-link-${id}`)),
        ).after(subtrees[id]);
      });
      // Render the main menu as a nested, collapsible accordion.
      if ('drupalToolbarMenu' in $.fn) {
        $(menu).children('.toolbar-menu').drupalToolbarMenu();
      }
    },
    /**
     * Updates the display of the tabs: toggles a tab and the associated tray.
     */
    updateTabs(trayName) {
      console.log('vanilla: updateTabs() in ToolbarVisualView');

      const tab = this.activeTab;

      // Activate the selected tab.
      if (tab.length > 0) {
        const tabElement = document.querySelector(tab);
        tabElement.classList.add('is-active');
        // Mark the tab as pressed.
        tabElement.ariaPressed = true;

        // const name = this.activeTab;
        // Store the active tab name or remove the setting.
        const { id } = tab;
        if (id) {
          localStorage.setItem(
            'Drupal.toolbar.activeTabID',
            JSON.stringify(id),
          );
        }

        const name = document
          .querySelector(this.activeTab)
          .getAttribute('data-toolbar-tray');
        console.log('tab element and name', tabElement, name);

        // Activate the associated tray.
        const tray = `[data-toolbar-tray="${name}"].toolbar-tray`;
        const trayElement = document.querySelector(tray);
        console.log('tray:', tray);
        console.log('trayElement:', trayElement);

        if (trayElement) {
          trayElement.classList.add('is-active');
          this.activeTray = tray;
        } else {
          // There is no active tray.
          this.activeTray = null;
        }
      } else {
        // There is no active tray.
        this.activeTray = null;
        localStorage.removeItem('Drupal.toolbar.activeTabID');
      }
    },
    // TODO: check aural works
    onActiveTrayChange(model, tray) {
      console.log(
        'vanilla: onActiveTrayChange in ToolbarAuralView this needs to be filled out',
      );
      const relevantTray = tray === null ? this.activeTray : tray;
      // Current activeTray and previous activeTray are empty, no state change
      // to announce.
      if (!relevantTray) {
        return;
      }
      const action = tray === null ? Drupal.t('closed') : Drupal.t('opened');
      const trayNameElement = relevantTray.querySelector('.toolbar-tray-name');
      let text;
      if (trayNameElement !== null) {
        text = Drupal.t('Tray "@tray" @action.', {
          '@tray': trayNameElement.textContent,
          '@action': action,
        });
      } else {
        text = Drupal.t('Tray @action.', { '@action': action });
      }
      Drupal.announce(text);
    },
    renderBody() {
      console.log('vanilla: renderBody');
      $('body')
        // Toggle the toolbar-tray-open class on the body element. The class is
        // applied when a toolbar tray is active. Padding might be applied to
        // the body element to prevent the tray from overlapping content.
        .toggleClass('toolbar-tray-open', !!this.activeTray);
    },
    /**
     * Updates the orientation of the active tray if necessary.
     */
    // TODO: remove jquery maybe
    updateTrayOrientation() {
      console.log('vanilla: updateTrayOrientation() in ToolbarVisualView');
      const { orientation } = toolbarBehaviors;

      // The antiOrientation is used to render the view of action buttons like
      // the tray orientation toggle.
      const antiOrientation =
        orientation === 'vertical' ? 'horizontal' : 'vertical';

      // Toggle toolbar's parent classes before other toolbar classes to avoid
      // potential flicker and re-rendering.
      $('body')
        .toggleClass('toolbar-vertical', orientation === 'vertical')
        .toggleClass('toolbar-horizontal', orientation === 'horizontal');

      const removeClass =
        antiOrientation === 'horizontal'
          ? 'toolbar-tray-horizontal'
          : 'toolbar-tray-vertical';
      const toolbar = document.querySelector('#toolbar-administration');
      toolbar.querySelectorAll('.toolbar-tray').forEach((tray) => {
        tray.classList.remove(removeClass);
        tray.classList.add(`toolbar-tray-${orientation}`);
      });
      const trays = toolbar.querySelectorAll('.toolbar-tray');

      // Update the tray orientation toggle button.
      const iconClass = `toolbar-icon-toggle-${orientation}`;
      const iconAntiClass = `toolbar-icon-toggle-${antiOrientation}`;
      const $orientationToggle = $('body').find('.toolbar-toggle-orientation');

      toolbar
        .querySelectorAll('.toolbar-toggle-orientation')
        .forEach((tray) => {
          if (this.isTrayToggleVisible) {
            tray.hidden = false;
          } else {
            tray.hidden = true;
          }
        });

      $orientationToggle
        .find('button')
        .val(antiOrientation)
        .attr('title', options.strings[antiOrientation])
        .text(options.strings[antiOrientation])
        .removeClass(iconClass)
        .addClass(iconAntiClass);

      // Update data offset attributes for the trays.
      const { dir } = document.documentElement;
      const edge = dir === 'rtl' ? 'right' : 'left';
      // Remove data-offset attributes from the trays so they can be refreshed.
      $(trays).removeAttr('data-offset-left data-offset-right data-offset-top');
      // If an active vertical tray exists, mark it as an offset element.
      $(trays)
        .filter('.toolbar-tray-vertical.is-active')
        .attr(`data-offset-${edge}`, '');
      // If an active horizontal tray exists, mark it as an offset element.
      $(trays)
        .filter('.toolbar-tray-horizontal.is-active')
        .attr('data-offset-top', '');
    },
    updateBarAttributes() {
      console.log('vanilla: updateBarAttributes() in ToolbarVisualView');
      const { isOriented } = toolbarBehaviors;
      const toolbar = document.querySelector('#toolbar-administration');
      if (isOriented) {
        toolbar
          .querySelector('#toolbar-bar')
          .setAttribute('data-offset-top', '');
      } else {
        toolbar
          .querySelector('#toolbar-bar')
          .removeAttribute('data-offset-top');
      }
      // Toggle between a basic vertical view and a more sophisticated
      // horizontal and vertical display of the toolbar bar and trays.
      if (isOriented) {
        toolbar.classList.add('toolbar-oriented');
      } else {
        toolbar.classList.remove('toolbar-oriented');
      }
    },
    updateToolbarHeight() {
      console.log('vanilla: updateToolbarHeight() in ToolbarVisualView');
      const toolbarTabOuterHeight =
        $('#toolbar-bar').find('.toolbar-tab').outerHeight() || 0;
      const toolbarTrayHorizontalOuterHeight =
        $('.is-active.toolbar-tray-horizontal').outerHeight() || 0;
      this.height = toolbarTabOuterHeight + toolbarTrayHorizontalOuterHeight;

      $('body').css({
        'padding-top': this.height,
      });
      $('html').css({
        'scroll-padding-top': this.height,
      });

      this.triggerDisplace();
    },
    triggerDisplace() {
      console.log('vanilla: triggerDisplace() in ToolbarVisualView');
      _.defer(() => {
        Drupal.displace(true);
      });
    },
    /**
     * Sets the tops of the trays so that they align with the bottom of the bar.
     */
    adjustPlacement() {
      console.log('vanilla: adjustPlacement() in ToolbarVisualView');
      const toolbar = document.querySelector('#toolbar-administration');

      if (!this.isOriented) {
        toolbar.querySelectorAll('.toolbar-tray').forEach((tray) => {
          tray.classList.remove('toolbar-tray-horizontal');
          tray.classList.add('toolbar-tray-vertical');
        });
      }
    },
    onOrientationToggleClick() {
      console.log('vanilla: onOrientationToggleClick()');
    },
    onOrientationChange() {
      console.log('vanilla: onOrientationChange() in ToolbarAuralView');
    },
    isToolbarFixed() {
      console.log('backbone: isToolbarFixed() in BodyVisualView.es6');
      // When the toolbar is fixed, it will not scroll with page scrolling.
      const { isViewportOverflowConstrained } = this;
      $('body').toggleClass(
        'toolbar-fixed',
        isViewportOverflowConstrained || this.isFixed,
      );
    },
    loadSubtrees() {
      console.log('vanilla: loadSubtrees()');
      const $activeTab = $(this.activeTab);
      const { orientation } = toolbarBehaviors;
      // Only load and render the admin menu subtrees if:
      //   (1) They have not been loaded yet.
      //   (2) The active tab is the administration menu tab, indicated by the
      //       presence of the data-drupal-subtrees attribute.
      //   (3) The orientation of the tray is vertical.
      if (
        !this.areSubtreesLoaded &&
        typeof $activeTab.data('drupal-subtrees') !== 'undefined' &&
        orientation === 'vertical'
      ) {
        // console.log($activeTab.data('drupal-subtrees'));
        const { subtreesHash } = drupalSettings.toolbar;
        const { theme } = drupalSettings.ajaxPageState;
        const endpoint = Drupal.url(`toolbar/subtrees/${subtreesHash}`);
        const cachedSubtreesHash = localStorage.getItem(
          `Drupal.toolbar.subtreesHash.${theme}`,
        );
        const cachedSubtrees = JSON.parse(
          localStorage.getItem(`Drupal.toolbar.subtrees.${theme}`),
        );
        const isVertical = this.orientation === 'vertical';
        // If we have the subtrees in localStorage and the subtree hash has not
        // changed, then use the cached data.
        if (
          isVertical &&
          subtreesHash === cachedSubtreesHash &&
          cachedSubtrees
        ) {
          Drupal.toolbar.setSubtrees.resolve(cachedSubtrees);
        }
        // Only make the call to get the subtrees if the orientation of the
        // toolbar is vertical.
        else if (isVertical) {
          // Remove the cached menu information.
          localStorage.removeItem(`Drupal.toolbar.subtreesHash.${theme}`);
          localStorage.removeItem(`Drupal.toolbar.subtrees.${theme}`);
          // The AJAX response's command will trigger the resolve method of the
          // Drupal.toolbar.setSubtrees Promise.
          Drupal.ajax({ url: endpoint }).execute();
          // Cache the hash for the subtrees locally.
          localStorage.setItem(
            `Drupal.toolbar.subtreesHash.${theme}`,
            subtreesHash,
          );
        }
      }
    },
  };

  /**
   * Registers tabs with the toolbar.
   *
   * The Drupal toolbar allows modules to register top-level tabs. These may
   * point directly to a resource or toggle the visibility of a tray.
   *
   * Modules register tabs with hook_toolbar().
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the toolbar rendering functionality to the toolbar element.
   */
  Drupal.behaviors.toolbar = {
    attach(context) {
      // Verify that the user agent understands media queries. Complex admin
      // toolbar layouts require media query support.
      if (!window.matchMedia('only screen').matches) {
        return;
      }
      // Process the administrative toolbar.
      once('toolbar', '#toolbar-administration', context).forEach(() => {
        // Add the tray orientation toggles.
        const bar = document.querySelector('#toolbar-administration');
        const trays = bar.querySelectorAll('.toolbar-tray .toolbar-lining');
        const toggleHTML = document.createElement('div');
        toggleHTML.textContent = Drupal.theme('toolbarOrientationToggle');
        Array.from(trays).forEach((item) => {
          item.insertAdjacentHTML(
            'beforeend',
            Drupal.theme('toolbarOrientationToggle'),
          );
        });

        // If the toolbar's orientation is horizontal and no active tab is
        // defined then show the tray of the first toolbar tab by default (but
        // not the first 'Home' toolbar tab).
        if (
          toolbarBehaviors.orientation === 'horizontal' &&
          toolbarBehaviors.activeTab === null
        ) {
          toolbarBehaviors.activeTab =
            '.toolbar-bar .toolbar-tab:not(.home-toolbar-tab) a';
        }
        console.log(this.activeTray);

        // Trigger an activeTab change so that listening scripts can respond on
        // page load. This will call render.
        // TODO: dsjf
        // toolbarBehaviors.renderToolbar();

        document
          .querySelectorAll('.toolbar-bar .toolbar-tab .trigger')
          .forEach((toolbarTab) => {
            toolbarTab.addEventListener('click', (e) =>
              toolbarBehaviors.onTabClick(e),
            );
            toolbarTab.addEventListener('click', () =>
              toolbarBehaviors.renderToolbar(),
            );
            toolbarTab.addEventListener('click', () =>
              toolbarBehaviors.onActiveTrayChange(),
            );
            toolbarTab.addEventListener('click', () =>
              toolbarBehaviors.renderBody(),
            );
            toolbarTab.addEventListener('click', () =>
              toolbarBehaviors.updateToolbarHeight(),
            );
            toolbarTab.addEventListener('click', () =>
              toolbarBehaviors.triggerDisplace(),
            );
            toolbarTab.addEventListener('click', () =>
              toolbarBehaviors.adjustPlacement(),
            );
          });

        // TODO:
        // Establish the toolbar models and views.
        // const model = new Drupal.toolbar.ToolbarModel({
        //   locked: JSON.parse(
        //     localStorage.getItem('Drupal.toolbar.trayVerticalLocked'),
        //   ),
        //   activeTab: document.getElementById(
        //     JSON.parse(localStorage.getItem('Drupal.toolbar.activeTabID')),
        //   ),
        //   height: $('#toolbar-administration').outerHeight(),
        // });

        toolbarBehaviors.locked = JSON.parse(
          localStorage.getItem('Drupal.toolbar.trayVerticalLocked'),
        );
        // toolbarBehaviors.activeTab = JSON.parse(
        //   localStorage.getItem('Drupal.toolbar.activeTabID'),
        // );

        toolbarBehaviors.height = $('#toolbar-administration').outerHeight();

        // Drupal.toolbar.models.toolbarModel = model;
        Drupal.toolbar.models.toolbarModel = toolbarBehaviors;

        // TODO:
        // Attach a listener to the configured media query breakpoints.
        // Executes it before Drupal.toolbar.views to avoid extra rendering.
        Object.keys(options.breakpoints).forEach((label) => {
          const mq = options.breakpoints[label];
          const mql = window.matchMedia(mq);
          Drupal.toolbar.mql[label] = mql;
          // Curry the model and the label of the media query breakpoint to
          // the mediaQueryChangeHandler function.
          mql.addListener(
            Drupal.toolbar.mediaQueryChangeHandler.bind(
              null,
              toolbarBehaviors,
              label,
            ),
          );
          // Fire the mediaQueryChangeHandler for each configured breakpoint
          // so that they process once.
          Drupal.toolbar.mediaQueryChangeHandler.call(
            null,
            toolbarBehaviors,
            label,
            mql,
          );
        });

        // Drupal.toolbar.views.toolbarVisualView =
        //   new Drupal.toolbar.ToolbarVisualView({
        //     el: toolbar,
        //     model,
        //     strings: options.strings,
        //   });

        // Drupal.toolbar.views.toolbarAuralView =
        //   new Drupal.toolbar.ToolbarAuralView({
        //     el: toolbar,
        //     model,
        //     strings: options.strings,
        //   });

        // Drupal.toolbar.views.bodyVisualView = new Drupal.toolbar.BodyVisualView(
        //   {
        //     el: toolbar,
        //     model,
        //   },
        // );

        // Force layout render to fix mobile view. Only needed on load, not
        // for every media query match.
        // TODO: see whats different on mobile view without these
        // change states of isFixed, activeTray
        // model.trigger('change:isFixed', model, model.get('isFixed'));
        // model.trigger('change:activeTray', model, model.get('activeTray'));

        const toolbarOrientationButton = document.querySelector(
          '.toolbar-toggle-orientation button',
        );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.onOrientationToggleClick(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.renderToolbarVisualView(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.updateTabs(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.updateTrayOrientation(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.updateBarAttributes(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.updateToolbarHeight(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.triggerDisplace(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.onOrientationChange(),
        // );
        // toolbarOrientationButton.addEventListener('click', () =>
        //   ToolbarBehaviors.adjustPlacement(),
        // );

        // Render collapsible menus.
        const menuModel = new Drupal.toolbar.MenuModel();
        Drupal.toolbar.models.menuModel = menuModel;
        Drupal.toolbar.views.menuVisualView = new Drupal.toolbar.MenuVisualView(
          {
            el: $(toolbar).find('.toolbar-menu-administration').get(0),
            model: menuModel,
            strings: options.strings,
          },
        );

        // Handle the resolution of Drupal.toolbar.setSubtrees.
        // This is handled with a deferred so that the function may be invoked
        // asynchronously.
        Drupal.toolbar.setSubtrees.done((subtrees) => {
          toolbarBehaviors.subtrees = subtrees;
          const { theme } = drupalSettings.ajaxPageState;
          localStorage.setItem(
            `Drupal.toolbar.subtrees.${theme}`,
            JSON.stringify(subtrees),
          );
          // Indicate on the toolbarModel that subtrees are now loaded.
          toolbarBehaviors.areSubtreesLoaded = true;
        });

        // Trigger an initial attempt to load menu subitems. This first attempt
        // is made after the media query handlers have had an opportunity to
        // process. The toolbar starts in the vertical orientation by default,
        // unless the viewport is wide enough to accommodate a horizontal
        // orientation. Thus we give the Toolbar a chance to determine if it
        // should be set to horizontal orientation before attempting to load
        // menu subtrees.
        // Drupal.toolbar.views.toolbarVisualView.loadSubtrees();
        toolbarBehaviors.loadSubtrees();

        $(document)
          // Update the model when the viewport offset changes.
          .on('drupalViewportOffsetChange.toolbar', (event, offsets) => {
            toolbarBehaviors.offsets = offsets;
          });

        $(window).on({
          'dialog:aftercreate': (event, dialog, $element, settings) => {
            const $toolbar = $('#toolbar-bar');
            $toolbar.css('margin-top', '0');

            // When off-canvas is positioned in top, toolbar has to be moved down.
            if (settings.drupalOffCanvasPosition === 'top') {
              const height = Drupal.offCanvas
                .getContainer($element)
                .outerHeight();
              $toolbar.css('margin-top', `${height}px`);

              $element.on('dialogContentResize.off-canvas', () => {
                const newHeight = Drupal.offCanvas
                  .getContainer($element)
                  .outerHeight();
                $toolbar.css('margin-top', `${newHeight}px`);
              });
            }
          },
          'dialog:beforeclose': () => {
            $('#toolbar-bar').css('margin-top', '0');
          },
        });
      });
    },
  };

  /**
   * Toolbar methods of Backbone objects.
   *
   * @namespace
   */
  Drupal.toolbar = {
    /**
     * A hash of View instances.
     *
     * @type {object.<string, Backbone.View>}
     */
    views: {},

    /**
     * A hash of Model instances.
     *
     * @type {object.<string, Backbone.Model>}
     */
    models: {},

    /**
     * A hash of MediaQueryList objects tracked by the toolbar.
     *
     * @type {object.<string, object>}
     */
    mql: {},

    /**
     * Accepts a list of subtree menu elements.
     *
     * A deferred object that is resolved by an inlined JavaScript callback.
     *
     * @type {jQuery.Deferred}
     *
     * @see toolbar_subtrees_jsonp().
     */
    setSubtrees: new $.Deferred(),

    /**
     * Respond to configured narrow media query changes.
     *
     * @param {Drupal.toolbar.ToolbarModel} model
     *   A toolbar model
     * @param {string} label
     *   Media query label.
     * @param {object} mql
     *   A MediaQueryList object.
     */
    mediaQueryChangeHandler(model, label, mql) {
      console.log('mediaQueryChangeHandler in toolbar.es6');
      switch (label) {
        case 'toolbar.narrow':
          toolbarBehaviors.isOriented = mql.matches;
          toolbarBehaviors.isTrayToggleVisible = false;
          // If the toolbar doesn't have an explicit orientation yet, or if the
          // narrow media query doesn't match then set the orientation to
          // vertical.
          if (!mql.matches || !toolbarBehaviors.orientation) {
            toolbarBehaviors.orientation = 'vertical';
            toolbarBehaviors.validate = true;
          }
          break;

        case 'toolbar.standard':
          toolbarBehaviors.isFixed = mql.matches;
          break;

        case 'toolbar.wide':
          toolbarBehaviors.orientation =
            mql.matches && !toolbarBehaviors.locked ? 'horizontal' : 'vertical';
          toolbarBehaviors.validate = true;
          // The tray orientation toggle visibility does not need to be
          // validated.
          toolbarBehaviors.isTrayToggleVisible = mql.matches;
          break;

        default:
          break;
      }
    },
  };

  /**
   * A toggle is an interactive element often bound to a click handler.
   *
   * @return {string}
   *   A string representing a DOM fragment.
   */
  Drupal.theme.toolbarOrientationToggle = function () {
    return (
      '<div class="toolbar-toggle-orientation"><div class="toolbar-lining">' +
      '<button class="toolbar-icon" type="button"></button>' +
      '</div></div>'
    );
  };

  /**
   * Ajax command to set the toolbar subtrees.
   *
   * @param {Drupal.Ajax} ajax
   *   {@link Drupal.Ajax} object created by {@link Drupal.ajax}.
   * @param {object} response
   *   JSON response from the Ajax request.
   * @param {number} [status]
   *   XMLHttpRequest status.
   */
  Drupal.AjaxCommands.prototype.setToolbarSubtrees = function (
    ajax,
    response,
    status,
  ) {
    Drupal.toolbar.setSubtrees.resolve(response.subtrees);
  };
})(jQuery, Drupal, drupalSettings);
