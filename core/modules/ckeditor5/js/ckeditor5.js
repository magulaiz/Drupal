/**
 * @file
 * CKEditor 5 implementation of {@link Drupal.editors} API.
 */

<<<<<<< HEAD
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

(function (Drupal, debounce, CKEditor5, $, once) {
  if (!CKEditor5) {
    return;
  }

=======
((Drupal, debounce, CKEditor5, $, once) => {
  /**
   * The CKEditor 5 instances.
   *
   * @type {Map}
   */
>>>>>>> upstream/11.x
  Drupal.CKEditor5Instances = new Map();

  /**
   * The callback functions.
   *
   * @type {Map}
   */
  const callbacks = new Map();

  /**
   * List of element ids with the required attribute.
   *
   * @type {Set}
   */
  const required = new Set();

  /**
   * Get the value of the (deep) property on name from scope.
   *
   * @param {object} scope
   *  Object used to search for the function.
   * @param {string} name
   *  The path to access in the scope object.
   *
   * @return {null|function}
   *  The corresponding function from the scope object.
   */
  function findFunc(scope, name) {
    if (!scope) {
      return null;
    }
    const parts = name.includes('.') ? name.split('.') : name;

    if (parts.length > 1) {
      return findFunc(scope[parts.shift()], parts);
    }
    return typeof scope[parts[0]] === 'function' ? scope[parts[0]] : null;
  }

  /**
   * Transform a config key in a callback function or execute the function
   * to dynamically build the configuration entry.
   *
   * @param {object} config
   *  The plugin configuration object.
   *
   * @return {null|function|*}
   *  Resulting configuration value.
   */
  function buildFunc(config) {
    const { func } = config;
    // Assuming a global object.
    const fn = findFunc(window, func.name);
    if (typeof fn === 'function') {
      const result = func.invoke ? fn(...func.args) : fn;
      return result;
    }
    return null;
  }

  /**
   * Converts a string representing regexp to a RegExp object.
   *
   * @param {Object} config
   *   An object containing configuration.
   * @param {string} config.pattern
   *   The regexp pattern that is used to create the RegExp object.
   *
   * @return {RegExp}
   *   Regexp object built from the string regexp.
   */
  function buildRegexp(config) {
    const { pattern } = config.regexp;

    const main = pattern.match(/\/(.+)\/.*/)[1];
    const options = pattern.match(/\/.+\/(.*)/)[1];

    return new RegExp(main, options);
  }

  /**
   * Casts configuration items to correct types.
   *
   * @param {Object} config
   *   The config object.
   * @return {Object}
   *   The config object with items transformed to correct type.
   */
  function processConfig(config) {
    /**
     * Processes an array in config recursively.
     *
     * @param {Array} config
     *   An array that should be processed recursively.
     * @return {Array}
     *   An array that has been processed recursively.
     */
    function processArray(config) {
      return config.map((item) => {
        if (typeof item === 'object') {
          return processConfig(item);
        }

        return item;
      });
    }

<<<<<<< HEAD
    return Object.entries(config).reduce(function (processed, _ref) {
      var _ref2 = _slicedToArray(_ref, 2),
          key = _ref2[0],
          value = _ref2[1];

      if (_typeof(value) === 'object') {
        if (!value) {
          return processed;
        }

=======
    return Object.entries(config).reduce((processed, [key, value]) => {
      if (typeof value === 'object') {
        // Check for null values.
        if (!value) {
          return processed;
        }
>>>>>>> upstream/11.x
        if (value.hasOwnProperty('func')) {
          processed[key] = buildFunc(value);
        } else if (value.hasOwnProperty('regexp')) {
          processed[key] = buildRegexp(value);
        } else if (Array.isArray(value)) {
          processed[key] = processArray(value);
        } else {
          processed[key] = processConfig(value);
        }
      } else {
        processed[key] = value;
      }

      return processed;
    }, {});
  }

  /**
   * Set an id to a data-attribute for registering this element instance.
   *
   * @param {Element} element
   *   An element that should receive unique ID.
   *
   * @return {string}
   *   The id to use for this element.
   */
  const setElementId = (element) => {
    const id = Math.random().toString().slice(2, 9);
    element.setAttribute('data-ckeditor5-id', id);

    return id;
  };

  /**
   * Return a unique selector for the element.
   *
   * @param {HTMLElement} element
   *   An element which unique ID should be retrieved.
   *
   * @return {string}
   *   The id to use for this element.
   */
  const getElementId = (element) => element.getAttribute('data-ckeditor5-id');

  /**
   * Select CKEditor 5 plugin classes to include.
   *
   * Found in the CKEditor 5 global JavaScript object as {package.Class}.
   *
   * @param {Array} plugins
   *  List of package and Class name of plugins
   *
   * @return {Array}
   *   List of JavaScript Classes to add in the extraPlugins property of config.
   */
  function selectPlugins(plugins) {
    return plugins.map((pluginDefinition) => {
      const [build, name] = pluginDefinition.split('.');
      if (CKEditor5[build] && CKEditor5[build][name]) {
        return CKEditor5[build][name];
      }

      // eslint-disable-next-line no-console
      console.warn(`Failed to load ${build} - ${name}`);
      return null;
    });
  }

<<<<<<< HEAD
  function processRules(rulesGroup) {
    try {
      _toConsumableArray(rulesGroup.cssRules).forEach(ckeditor5SelectorProcessing);
    } catch (e) {
      console.warn("Stylesheet ".concat(rulesGroup.href, " not included in CKEditor reset due to the browser's CORS policy."));
    }
  }

  function ckeditor5SelectorProcessing(rule) {
    if (rule.cssRules) {
      processRules(rule);
    }

    if (!rule.selectorText) {
      return;
    }

    var offCanvasId = '#drupal-off-canvas';
    var CKEditorClass = '.ck';
    var styleFence = '[data-drupal-ck-style-fence]';

    if (rule.selectorText.includes(offCanvasId) || rule.selectorText.includes(CKEditorClass)) {
      rule.selectorText = rule.selectorText.split(/,/g).map(function (selector) {
        if (selector.includes(offCanvasId)) {
          return "".concat(selector.trim(), ":not(").concat(styleFence, " *)");
        }

        if (selector.includes(CKEditorClass)) {
          return [selector.trim(), selector.trim().replace(CKEditorClass, "".concat(offCanvasId, " ").concat(styleFence, " ").concat(CKEditorClass))];
        }

        return selector;
      }).flat().join(', ');
    }
  }

  function offCanvasCss(element) {
    var fenceName = 'data-drupal-ck-style-fence';
    var editor = Drupal.CKEditor5Instances.get(element.getAttribute('data-ckeditor5-id'));
    editor.ui.view.element.setAttribute(fenceName, '');

    if (once('ckeditor5-off-canvas-reset', 'body').length) {
      _toConsumableArray(document.styleSheets).forEach(processRules);

      var prefix = "#drupal-off-canvas [".concat(fenceName, "]");
      var addedCss = ["".concat(prefix, " .ck.ck-content {display:block;min-height:5rem;}"), "".concat(prefix, " .ck.ck-content * {display:initial;background:initial;color:initial;padding:initial;}"), "".concat(prefix, " .ck.ck-content li {display:list-item}"), "".concat(prefix, " .ck.ck-content ol li {list-style-type: decimal}"), "".concat(prefix, " .ck[contenteditable], ").concat(prefix, " .ck[contenteditable] * {-webkit-user-modify: read-write;-moz-user-modify: read-write;}")];
      var blockSelectors = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'ol', 'ul', 'address', 'article', 'aside', 'blockquote', 'body', 'dd', 'div', 'dl', 'dt', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'header', 'hgroup', 'hr', 'html', 'legend', 'main', 'menu', 'pre', 'section', 'xmp'].map(function (blockElement) {
        return "".concat(prefix, " .ck.ck-content ").concat(blockElement);
      }).join(', \n');
      var blockCss = "".concat(blockSelectors, " { display: block; }");
      var prefixedCss = [].concat(addedCss, [blockCss]).join('\n');
      var offCanvasCssStyle = document.createElement('style');
      offCanvasCssStyle.textContent = prefixedCss;
      offCanvasCssStyle.setAttribute('id', 'ckeditor5-off-canvas-reset');
      document.body.appendChild(offCanvasCssStyle);
    }
  }

  Drupal.editors.ckeditor5 = {
    attach: function attach(element, format) {
      var editorClassic = CKEditor5.editorClassic;
      var _format$editorSetting = format.editorSettings,
          toolbar = _format$editorSetting.toolbar,
          plugins = _format$editorSetting.plugins,
          config = _format$editorSetting.config,
          language = _format$editorSetting.language;
      var extraPlugins = selectPlugins(plugins);
      var pluginConfig = processConfig(config);

      var editorConfig = _objectSpread(_objectSpread({
        extraPlugins: extraPlugins,
        toolbar: toolbar
      }, pluginConfig), {}, {
        language: _objectSpread(_objectSpread({}, pluginConfig.language), language)
      });

      var id = setElementId(element);
      var ClassicEditor = editorClassic.ClassicEditor;
      ClassicEditor.create(element, editorConfig).then(function (editor) {
        Drupal.CKEditor5Instances.set(id, editor);

        if (element.hasAttribute('required')) {
          required.add(id);
          element.removeAttribute('required');
        }

        $(document).on("drupalViewportOffsetChange.ckeditor5.".concat(id), function (event, offsets) {
          editor.ui.viewportOffset = offsets;
        });
        editor.model.document.on('change:data', function () {
          var callback = callbacks.get(id);

          if (callback) {
            if (editor.plugins.has('SourceEditing')) {
              if (editor.plugins.get('SourceEditing').isSourceEditingMode) {
                callback();
                return;
              }
            }

            debounce(callback, 400)();
=======
  /**
   * Process a group of CSS rules.
   *
   * @param {CSSGroupingRule} rulesGroup
   *  A complete stylesheet or a group of nested rules like @media.
   */
  function processRules(rulesGroup) {
    try {
      // eslint-disable-next-line no-use-before-define
      [...rulesGroup.cssRules].forEach(ckeditor5SelectorProcessing);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.warn(
        `Stylesheet ${rulesGroup.href} not included in CKEditor reset due to the browser's CORS policy.`,
      );
    }
  }

  /**
   * Processes CSS rules dynamically to account for CKEditor 5 in off canvas.
   *
   * This is achieved by doing the following steps:
   * - Adding a donut scope to off canvas rules, so they don't apply within the
   *   editor element.
   * - Editor specific rules (i.e. those with .ck* selectors) are duplicated and
   *   prefixed with the off canvas selector to ensure they have higher
   *   specificity over the off canvas reset.
   *
   * The donut scope prevents off canvas rules from applying to the CKEditor 5
   * editor element. Transforms a:
   *  - #drupal-off-canvas strong
   * rule into:
   *  - #drupal-off-canvas strong:not([data-drupal-ck-style-fence] *)
   *
   * This means that the rule applies to all <strong> elements inside
   * #drupal-off-canvas, except for <strong> elements who have a with a parent
   * with the "data-drupal-ck-style-fence" attribute.
   *
   * For example:
   * <div id="drupal-off-canvas">
   *   <p>
   *     <strong>Off canvas reset</strong>
   *   </p>
   *   <p data-drupal-ck-style-fence>
   *     <!--
   *       this strong elements matches the `[data-drupal-ck-style-fence] *`
   *       selector and is excluded from the off canvas reset rule.
   *     -->
   *     <strong>Off canvas reset NOT applied.</strong>
   *   </p>
   * </div>
   *
   * The donut scope does not prevent CSS inheritance. There is CSS that resets
   * following properties to prevent inheritance: background, border,
   * box-sizing, margin, padding, position, text-decoration, transition,
   * vertical-align and word-wrap.
   *
   * All .ck* CSS rules are duplicated and prefixed with the off canvas selector
   * To ensure they have higher specificity and are not reset too aggressively.
   *
   * @param {CSSRule} rule
   *  A single CSS rule to be analyzed and changed if necessary.
   */
  function ckeditor5SelectorProcessing(rule) {
    // Handle nested rules in @media, @support, etc.
    if (rule.cssRules) {
      processRules(rule);
    }
    if (!rule.selectorText) {
      return;
    }
    const offCanvasId = '#drupal-off-canvas';
    const CKEditorClass = '.ck';
    const styleFence = '[data-drupal-ck-style-fence]';
    if (
      rule.selectorText.includes(offCanvasId) ||
      rule.selectorText.includes(CKEditorClass)
    ) {
      rule.selectorText = rule.selectorText
        .split(/,/g)
        .map((selector) => {
          // Only change rules that include #drupal-off-canvas in the selector.
          if (selector.includes(offCanvasId)) {
            return `${selector.trim()}:not(${styleFence} *)`;
>>>>>>> upstream/11.x
          }
          // Duplicate CKEditor 5 styles with higher specificity for proper
          // display in off canvas elements.
          if (selector.includes(CKEditorClass)) {
            // Return both rules to avoid replacing the existing rules.
            return [
              selector.trim(),
              selector
                .trim()
                .replace(
                  CKEditorClass,
                  `${offCanvasId} ${styleFence} ${CKEditorClass}`,
                ),
            ];
          }
          return selector;
        })
        .flat()
        .join(', ');
    }
  }

  /**
   * Adds CSS to ensure proper styling of CKEditor 5 inside off-canvas dialogs.
   *
   * @param {HTMLElement} element
   *   The element the editor is attached to.
   */
  function offCanvasCss(element) {
    const fenceName = 'data-drupal-ck-style-fence';
    const editor = Drupal.CKEditor5Instances.get(
      element.getAttribute('data-ckeditor5-id'),
    );
    editor.ui.view.element.setAttribute(fenceName, '');
    // Only proceed if the styles haven't been added yet.
    if (once('ckeditor5-off-canvas-reset', 'body').length) {
      // For all rules on the page, add the donut scope for
      // rules containing the #drupal-off-canvas selector.
      [...document.styleSheets].forEach(processRules);

      const prefix = `#drupal-off-canvas-wrapper [${fenceName}]`;
      // Additional styles that need to be explicity added in addition to the
      // prefixed versions of existing css in `existingCss`.
      const addedCss = [
        `${prefix} .ck.ck-content {display:block;min-height:5rem;}`,
        `${prefix} .ck.ck-content * {display:revert;background:revert;color:initial;padding:revert;}`,
        `${prefix} .ck.ck-content li {display:list-item}`,
        `${prefix} .ck.ck-content ol li {list-style-type: decimal}`,
      ];

      const prefixedCss = [...addedCss].join('\n');

      // Create a new style tag with the prefixed styles added above.
      const offCanvasCssStyle = document.createElement('style');
      offCanvasCssStyle.textContent = prefixedCss;
      offCanvasCssStyle.setAttribute('id', 'ckeditor5-off-canvas-reset');
      document.body.appendChild(offCanvasCssStyle);
    }
  }

  /**
   * Integration of CKEditor 5 with the Drupal editor API.
   *
   * @namespace
   *
   * @see Drupal.editorAttach
   */
  Drupal.editors.ckeditor5 = {
    /**
     * Editor attach callback.
     *
     * @param {HTMLElement} element
     *   The element to attach the editor to.
     * @param {string} format
     *   The text format for the editor.
     */
    attach(element, format) {
      const { editorClassic } = CKEditor5;
      const { toolbar, plugins, config, language } = format.editorSettings;
      const extraPlugins = selectPlugins(plugins);
      const pluginConfig = processConfig(config);
      const editorConfig = {
        extraPlugins,
        toolbar,
        ...pluginConfig,
        // Language settings have a conflict between the editor localization
        // settings and the "language" plugin.
        language: { ...pluginConfig.language, ...language },
      };
      // Set the id immediately so that it is available when onChange is called.
      const id = setElementId(element);
      const { ClassicEditor } = editorClassic;

      ClassicEditor.create(element, editorConfig)
        .then((editor) => {
          /**
           * Injects a temporary <p> into CKEditor and then calculates the entire
           * height of the amount of the <p> tags from the passed in rows value.
           *
           * This takes into account collapsing margins, and line-height of the
           * current theme.
           *
           * @param {number} - the number of rows.
           *
           * @returns {number} - the height of a div in pixels.
           */
          function calculateLineHeight(rows) {
            const element = document.createElement('p');
            element.setAttribute('style', 'visibility: hidden;');
            element.innerHTML = '&nbsp;';
            editor.ui.view.editable.element.append(element);

            const styles = window.getComputedStyle(element);
            const height = element.clientHeight;
            const marginTop = parseInt(styles.marginTop, 10);
            const marginBottom = parseInt(styles.marginBottom, 10);
            const mostMargin =
              marginTop >= marginBottom ? marginTop : marginBottom;

            element.remove();
            return (
              (height + mostMargin) * (rows - 1) +
              marginTop +
              height +
              marginBottom
            );
          }

          // Save a reference to the initialized instance.
          Drupal.CKEditor5Instances.set(id, editor);

          // Set the minimum height of the editable area to correspond with the
          // value of the number of rows. We attach this custom property to
          // the `.ck-editor` element, as that doesn't get its inline styles
          // cleared on focus. The editable element is then set to use this
          // property within the stylesheet.
          const rows = editor.sourceElement.getAttribute('rows');
          editor.ui.view.editable.element
            .closest('.ck-editor')
            .style.setProperty(
              '--ck-min-height',
              `${calculateLineHeight(rows)}px`,
            );

          // CKEditor 4 had a feature to remove the required attribute
          // see: https://www.drupal.org/project/drupal/issues/1954968
          if (element.hasAttribute('required')) {
            required.add(id);
            element.removeAttribute('required');
          }

          // If the textarea is disabled, enable CKEditor's read-only mode.
          if (element.hasAttribute('disabled')) {
            editor.enableReadOnlyMode('ckeditor5_disabled');
          }

          // Integrate CKEditor 5 viewport offset with Drupal displace.
          // @see \Drupal\Tests\ckeditor5\FunctionalJavascript\CKEditor5ToolbarTest
          // @see https://ckeditor.com/docs/ckeditor5/latest/api/module_core_editor_editorui-EditorUI.html#member-viewportOffset
          $(document).on(
            `drupalViewportOffsetChange.ckeditor5.${id}`,
            (event, offsets) => {
              editor.ui.viewportOffset = offsets;
            },
          );

          editor.model.document.on('change:data', () => {
            const callback = callbacks.get(id);
            if (callback) {
              if (editor.plugins.has('SourceEditing')) {
                // If the change:data is being called while in source editing
                // mode, it means that the form is being submitted. To avoid
                // race conditions, in this case the callback gets called
                // without decorating the callback with debounce.
                // @see https://www.drupal.org/i/3229174
                // @see Drupal.editorDetach
                if (editor.plugins.get('SourceEditing').isSourceEditingMode) {
                  callback();
                  return;
                }
              }

              // Marks the field as changed.
              // @see Drupal.editorAttach
              debounce(callback, 400)();
            }
          });

          const isOffCanvas = element.closest('#drupal-off-canvas');

          if (isOffCanvas) {
            offCanvasCss(element);
          }
        })
        .catch((error) => {
          // eslint-disable-next-line no-console
          console.info(
            'Debugging can be done with an unminified version of CKEditor by installing from the source file. Consult documentation at https://www.drupal.org/node/3258901',
          );
          // eslint-disable-next-line no-console
          console.error(error);
        });
    },

    /**
     * Editor detach callback.
     *
     * @param {HTMLElement} element
     *   The element to detach the editor from.
     * @param {string} format
     *   The text format used for the editor.
     * @param {string} trigger
     *   The event trigger for the detach.
     */
    detach(element, format, trigger) {
      const id = getElementId(element);
      const editor = Drupal.CKEditor5Instances.get(id);
      if (!editor) {
        return;
      }

<<<<<<< HEAD
      $(document).off("drupalViewportOffsetChange.ckeditor5.".concat(id));
=======
      $(document).off(`drupalViewportOffsetChange.ckeditor5.${id}`);
>>>>>>> upstream/11.x

      if (trigger === 'serialize') {
        editor.updateSourceElement();
      } else {
        element.removeAttribute('contentEditable');
        // Return the promise to allow external code to queue code to
        // execute after the destroy is complete.
        return editor
          .destroy()
          .then(() => {
            // Clean up stored references.
            Drupal.CKEditor5Instances.delete(id);
            callbacks.delete(id);
            if (required.has(id)) {
              element.setAttribute('required', 'required');
              required.delete(id);
            }
          })
          .catch((error) => {
            // eslint-disable-next-line no-console
            console.error(error);
          });
      }
    },

    /**
     * Registers a callback which CKEditor 5 will call on change:data event.
     *
     * @param {HTMLElement} element
     *   The element where the change occurred.
     * @param {function} callback
     *   Callback called with the value of the editor.
     */
    onChange(element, callback) {
      callbacks.set(getElementId(element), callback);
    },

    /**
     * Attaches an inline editor to a DOM element.
     *
     * @param {HTMLElement} element
     *   The element to attach the editor to.
     * @param {object} format
     *   The text format used in the editor.
     * @param {string} [mainToolbarId]
     *   The id attribute for the main editor toolbar, if any.
     */
    attachInlineEditor(element, format, mainToolbarId) {
      const { editorDecoupled } = CKEditor5;
      const {
        toolbar,
        plugins,
        config: pluginConfig,
        language,
      } = format.editorSettings;
      const extraPlugins = selectPlugins(plugins);
      const config = {
        extraPlugins,
        toolbar,
        language,
        ...processConfig(pluginConfig),
      };
      const id = setElementId(element);
      const { DecoupledEditor } = editorDecoupled;

      DecoupledEditor.create(element, config)
        .then((editor) => {
          Drupal.CKEditor5Instances.set(id, editor);
          const toolbar = document.getElementById(mainToolbarId);
          toolbar.appendChild(editor.ui.view.toolbar.element);
          editor.model.document.on('change:data', () => {
            const callback = callbacks.get(id);
            if (callback) {
              // Allow modules to update EditorModel by providing the current data.
              debounce(callback, 400)(editor.getData());
            }
          });
        })
        .catch((error) => {
          // eslint-disable-next-line no-console
          console.error(error);
        });
    },
  };

  /**
   * Public API for Drupal CKEditor 5 integration.
   *
   * @namespace
   */
  Drupal.ckeditor5 = {
    /**
     * Variable storing the current dialog's save callback.
     *
     * @type {?function}
     */
    saveCallback: null,

    /**
     * Open a dialog for a Drupal-based plugin.
     *
     * This dynamically loads jQuery UI (if necessary) using the Drupal AJAX
     * framework, then opens a dialog at the specified Drupal path.
     *
     * @param {string} url
     *   The URL that contains the contents of the dialog.
     * @param {function} saveCallback
     *   A function to be called upon saving the dialog.
     * @param {object} dialogSettings
     *   An object containing settings to be passed to the jQuery UI.
     */
    openDialog(url, saveCallback, dialogSettings) {
      // Add a consistent dialog class.
      const classes = dialogSettings.dialogClass
        ? dialogSettings.dialogClass.split(' ')
        : [];
      classes.push('ui-dialog--narrow');
      dialogSettings.dialogClass = classes.join(' ');
      dialogSettings.autoResize =
        window.matchMedia('(min-width: 600px)').matches;
      dialogSettings.width = 'auto';
<<<<<<< HEAD
      var ckeditorAjaxDialog = Drupal.ajax({
        dialog: dialogSettings,
        dialogType: 'modal',
        selector: '.ckeditor5-dialog-loading-link',
        url: url,
        progress: {
          type: 'fullscreen'
        },
=======

      const ckeditorAjaxDialog = Drupal.ajax({
        dialog: dialogSettings,
        dialogType: 'modal',
        selector: '.ckeditor5-dialog-loading-link',
        url,
        progress: { type: 'fullscreen' },
>>>>>>> upstream/11.x
        submit: {
          editor_object: {},
        },
      });
      ckeditorAjaxDialog.execute();
<<<<<<< HEAD
=======

      // Store the save callback to be executed when this dialog is closed.
>>>>>>> upstream/11.x
      Drupal.ckeditor5.saveCallback = saveCallback;
    },
  };

<<<<<<< HEAD
  function redirectTextareaFragmentToCKEditor5Instance() {
    var hash = window.location.hash.substr(1);
    var element = document.getElementById(hash);

    if (element) {
      var editorID = getElementId(element);
      var editor = Drupal.CKEditor5Instances.get(editorID);

      if (editor) {
        editor.sourceElement.nextElementSibling.setAttribute('id', "cke_".concat(hash));
        window.location.replace("#cke_".concat(hash));
=======
  // Redirect on hash change when the original hash has an associated CKEditor 5.
  function redirectTextareaFragmentToCKEditor5Instance() {
    const hash = window.location.hash.substr(1);
    const element = document.getElementById(hash);
    if (element) {
      const editorID = getElementId(element);
      const editor = Drupal.CKEditor5Instances.get(editorID);
      if (editor) {
        // Give the CKEditor 5 instance an ID.
        editor.sourceElement.nextElementSibling.setAttribute(
          'id',
          `cke_${hash}`,
        );
        window.location.replace(`#cke_${hash}`);
>>>>>>> upstream/11.x
      }
    }
  }

<<<<<<< HEAD
  $(window).on('hashchange.ckeditor', redirectTextareaFragmentToCKEditor5Instance);
  $(window).on('dialog:beforecreate', function () {
    $('.ckeditor5-dialog-loading').animate({
      top: '-40px'
    }, function removeDialogLoading() {
      $(this).remove();
    });
=======
  $(window).on(
    'hashchange.ckeditor',
    redirectTextareaFragmentToCKEditor5Instance,
  );

  // Respond to new dialogs that are opened by CKEditor, closing the AJAX loader.
  $(window).on('dialog:beforecreate', () => {
    $('.ckeditor5-dialog-loading').animate(
      { top: '-40px' },
      function removeDialogLoading() {
        $(this).remove();
      },
    );
>>>>>>> upstream/11.x
  });

  // Respond to dialogs that are saved, sending data back to CKEditor.
  $(window).on('editor:dialogsave', (e, values) => {
    if (Drupal.ckeditor5.saveCallback) {
      Drupal.ckeditor5.saveCallback(values);
    }
  });

  // Respond to dialogs that are closed, removing the current save handler.
  $(window).on('dialog:afterclose', () => {
    if (Drupal.ckeditor5.saveCallback) {
      Drupal.ckeditor5.saveCallback = null;
    }
  });
<<<<<<< HEAD
})(Drupal, Drupal.debounce, CKEditor5, jQuery, once);
=======
})(Drupal, Drupal.debounce, CKEditor5, jQuery, once);
>>>>>>> upstream/11.x
