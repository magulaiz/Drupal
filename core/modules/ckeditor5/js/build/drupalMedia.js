/*! cspell:disable */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CKEditor5"] = factory();
	else
		root["CKEditor5"] = root["CKEditor5"] || {}, root["CKEditor5"]["drupalMedia"] = factory();
})(self, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "ckeditor5/src/core.js":
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__("dll-reference CKEditor5.dll"))("./src/core.js");

/***/ }),

/***/ "ckeditor5/src/ui.js":
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__("dll-reference CKEditor5.dll"))("./src/ui.js");

/***/ }),

/***/ "ckeditor5/src/utils.js":
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__("dll-reference CKEditor5.dll"))("./src/utils.js");

/***/ }),

/***/ "ckeditor5/src/widget.js":
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__("dll-reference CKEditor5.dll"))("./src/widget.js");

/***/ }),

/***/ "dll-reference CKEditor5.dll":
/***/ (function(module) {

"use strict";
module.exports = CKEditor5.dll;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": function() { return /* binding */ src; }
});

// EXTERNAL MODULE: delegated ./core.js from dll-reference CKEditor5.dll
var delegated_corefrom_dll_reference_CKEditor5 = __webpack_require__("ckeditor5/src/core.js");
// EXTERNAL MODULE: delegated ./widget.js from dll-reference CKEditor5.dll
var delegated_widgetfrom_dll_reference_CKEditor5 = __webpack_require__("ckeditor5/src/widget.js");
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/insertdrupalmedia.js
/* eslint-disable import/no-extraneous-dependencies */


function createDrupalMedia(writer, attributes) {
  const drupalMedia = writer.createElement('drupalMedia', attributes);
  return drupalMedia;
}

/**
 * @internal
 */
/**
 * The insert media command.
 *
 * The command is registered by the `DrupalMediaEditing` plugin as
 * `insertDrupalMedia`.
 *
 * In order to insert media at the current selection position, execute the
 * command and pass the attributes desired in the drupal-media element:
 *
 *    editor.execute('insertDrupalMedia', {
 *      'alt': 'Alt text',
 *      'data-align': 'left',
 *      'data-caption': 'Caption text',
 *      'data-entity-type': 'media',
 *      'data-entity-uuid': 'media-entity-uuid',
 *      'data-view-mode': 'default',
 *    });
 */
class InsertDrupalMediaCommand extends delegated_corefrom_dll_reference_CKEditor5.Command {
  execute(attributes) {
    const mediaEditing = this.editor.plugins.get('DrupalMediaEditing');

    // Create object that contains supported data-attributes in view data by
    // flipping `DrupalMediaEditing.attrs` object (i.e. keys from object become
    // values and values from object become keys).
    const dataAttributeMapping = Object.entries(mediaEditing.attrs).reduce(
      (result, [key, value]) => {
        result[value] = key;
        return result;
      },
      {},
    );

    // \Drupal\media\Form\EditorMediaDialog returns data in keyed by
    // data-attributes used in view data. This converts data-attribute keys to
    // keys used in model.
    const modelAttributes = Object.keys(attributes).reduce(
      (result, attribute) => {
        if (dataAttributeMapping[attribute]) {
          result[dataAttributeMapping[attribute]] = attributes[attribute];
        }
        return result;
      },
      {},
    );

    // Check if there's Drupal Element Style matching the default attributes on
    // the media.
    // @see module:drupalMedia/drupalelementstyle/drupalelementstyleediting~DrupalElementStyleEditing
    if (this.editor.plugins.has('DrupalElementStyleEditing')) {
      const elementStyleEditing = this.editor.plugins.get(
        'DrupalElementStyleEditing',
      );

      const normStyles = elementStyleEditing.normalizedStyles;
      for (const group of Object.keys(normStyles)) {
        // eslint-disable-next-line no-restricted-syntax
        for (const style of elementStyleEditing.normalizedStyles[group]) {
          if (
            attributes[style.attributeName] &&
            style.attributeValue === attributes[style.attributeName]
          ) {
            modelAttributes.drupalElementStyle = style.name;
            break;
          }
        }
      }

      this.editor.model.change((writer) => {
        this.editor.model.insertContent(
          createDrupalMedia(writer, modelAttributes),
        );
      });
    }
  }

  refresh() {
    const model = this.editor.model;
    const selection = model.document.selection;
    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'drupalMedia',
    );
    this.isEnabled = allowedIn !== null;
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediaediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words insertdrupalmedia drupalmediaediting */






/**
 * @module drupalMedia/drupalmediaediting
 */

/**
 * @internal
 */
class DrupalMediaEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  static get requires() {
    return [delegated_widgetfrom_dll_reference_CKEditor5.Widget];
  }

  init() {
    this.attrs = {
      drupalMediaAlt: 'alt',
      drupalMediaCaption: 'data-caption',
      drupalMediaEntityType: 'data-entity-type',
      drupalMediaEntityUuid: 'data-entity-uuid',
      drupalViewMode: 'data-view-mode',
    };
    const options = this.editor.config.get('drupalMedia');
    if (!options) {
      return;
    }
    const { previewURL, themeError } = options;
    this.previewURL = previewURL;
    this.labelError = Drupal.t('Preview failed');
    this.themeError =
      themeError ||
      `
      <p>${Drupal.t(
        'An error occurred while trying to preview the media. Please save your work and reload this page.',
      )}<p>
    `;

    this._defineSchema();
    this._defineConverters();

    this.editor.commands.add(
      'insertDrupalMedia',
      new InsertDrupalMediaCommand(this.editor),
    );
  }

  async _fetchPreview(url, query) {
    const response = await fetch(`${url}?${new URLSearchParams(query)}`, {
      headers: {
        'X-Drupal-MediaPreview-CSRF-Token':
          this.editor.config.get('drupalMedia').previewCsrfToken,
      },
    });
    if (response.ok) {
      const label = response.headers.get('drupal-media-label');
      const preview = await response.text();
      return { label, preview };
    }

    return { label: this.labelError, preview: this.themeError };
  }

  _defineSchema() {
    const schema = this.editor.model.schema;
    schema.register('drupalMedia', {
      allowWhere: '$block',
      isObject: true,
      isContent: true,
      allowAttributes: Object.keys(this.attrs),
    });
  }

  _defineConverters() {
    const conversion = this.editor.conversion;
    conversion.for('upcast').elementToElement({
      view: {
        name: 'drupal-media',
      },
      model: 'drupalMedia',
    });

    conversion.for('dataDowncast').elementToElement({
      model: 'drupalMedia',
      view: {
        name: 'drupal-media',
      },
    });

    conversion.for('editingDowncast').elementToElement({
      model: 'drupalMedia',
      view: (modelElement, { writer: viewWriter }) => {
        const container = viewWriter.createContainerElement('div', {
          class: 'drupal-media',
        });
        const media = viewWriter.createRawElement(
          'div',
          { 'data-drupal-media-preview': 'loading' },
          (domElement) => {
            if (this.previewURL) {
              this._fetchPreview(this.previewURL, {
                text: this._renderElement(modelElement),
                uuid: modelElement.getAttribute('drupalMediaEntityUuid'),
              }).then(({ label, preview }) => {
                domElement.innerHTML = preview;
                domElement.setAttribute('aria-label', label);
                domElement.setAttribute('data-drupal-media-preview', 'ready');
              });
            } else {
              domElement.innerHTML = this.themeError;
              domElement.setAttribute('aria-label', 'drupal-media');
              domElement.setAttribute(
                'data-drupal-media-preview',
                'unavailable',
              );
            }
          },
        );
        viewWriter.insert(viewWriter.createPositionAt(container, 0), media);
        viewWriter.setCustomProperty('drupalMedia', true, container);
        return (0,delegated_widgetfrom_dll_reference_CKEditor5.toWidget)(container, viewWriter, { label: 'media widget' });
      },
    });

    conversion.for('editingDowncast').add((dispatcher) => {
      dispatcher.on(
        'attribute:drupalAlign:drupalMedia',
        (evt, data, conversionApi) => {
          const alignMapping = {
            // these are css classes
            alignLeft: 'drupal-media-style-align-left',
            alignRight: 'drupal-media-style-align-right',
            alignCenter: 'drupal-media-style-align-center',
          };
          const viewElement = conversionApi.mapper.toViewElement(data.item);
          const viewWriter = conversionApi.writer;

          // If the prior value is alignment related, it should be removed
          // whether or not the module property is consumed.
          if (alignMapping[data.attributeOldValue]) {
            console.log('hit');
            viewWriter.removeClass(
              alignMapping[data.attributeOldValue],
              viewElement,
            );
          }

          // If the new value is not alignment related, do not proceed.
          if (!alignMapping[data.attributeNewValue]) {
            return;
          }

          // The model property is already consumed, do not proceed.
          if (!conversionApi.consumable.consume(data.item, evt.name)) {
            return;
          }

          // Add the alignment class in the view that corresponds to the value
          // of the model's drupalElementStyle property.
          viewWriter.addClass(
            alignMapping[data.attributeNewValue],
            viewElement,
          );
        },
      );
    });

    // Set attributeToAttribute conversion for all supported attributes.
    Object.keys(this.attrs).forEach((modelKey) => {
      conversion.attributeToAttribute({
        model: {
          key: modelKey,
          name: 'drupalMedia',
        },
        view: {
          name: 'drupal-media',
          key: this.attrs[modelKey],
        },
      });
    });
  }

  /**
   * MediaFilterController::preview requires the saved element.
   *
   * Not previewing data-caption since it does not get updated by new changes.
   *
   * @param {module:engine/model/element~Element} modelElement
   *   The drupalMedia model element to be converted.
   * @return {string}
   *   The model element converted into HTML.
   *
   * @todo: is there a better way to get the rendered dataDowncast string
   *   https://www.drupal.org/project/ckeditor5/issues/3231337?
   */
  _renderElement(modelElement) {
    const attrs = modelElement.getAttributes();
    let element = '<drupal-media';
    Array.from(attrs).forEach((attr) => {
      if (this.attrs[attr[0]] && attr[0] !== 'drupalMediaCaption') {
        element += ` ${this.attrs[attr[0]]}="${attr[1]}"`;
      }
    });
    element += '></drupal-media>';

    return element;
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMediaEditing';
  }
}

// EXTERNAL MODULE: delegated ./ui.js from dll-reference CKEditor5.dll
var delegated_uifrom_dll_reference_CKEditor5 = __webpack_require__("ckeditor5/src/ui.js");
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/theme/icons/medialibrary.svg
/* harmony default export */ var medialibrary = ("<svg width=\"20\" height=\"20\" viewBox=\"0 0 20 20\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M19.1873 4.86414L10.2509 6.86414V7.02335H10.2499V15.5091C9.70972 15.1961 9.01793 15.1048 8.34069 15.3136C7.12086 15.6896 6.41013 16.8967 6.75322 18.0096C7.09631 19.1226 8.3633 19.72 9.58313 19.344C10.6666 19.01 11.3484 18.0203 11.2469 17.0234H11.2499V9.80173L18.1803 8.25067V14.3868C17.6401 14.0739 16.9483 13.9825 16.2711 14.1913C15.0513 14.5674 14.3406 15.7744 14.6836 16.8875C15.0267 18.0004 16.2937 18.5978 17.5136 18.2218C18.597 17.8877 19.2788 16.8982 19.1773 15.9011H19.1803V8.02687L19.1873 8.0253V4.86414Z\" fill=\"black\"/><path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M13.5039 0.743652H0.386932V12.1603H13.5039V0.743652ZM12.3379 1.75842H1.55289V11.1454H1.65715L4.00622 8.86353L6.06254 10.861L9.24985 5.91309L11.3812 9.22179L11.7761 8.6676L12.3379 9.45621V1.75842ZM6.22048 4.50869C6.22048 5.58193 5.35045 6.45196 4.27722 6.45196C3.20398 6.45196 2.33395 5.58193 2.33395 4.50869C2.33395 3.43546 3.20398 2.56543 4.27722 2.56543C5.35045 2.56543 6.22048 3.43546 6.22048 4.50869Z\" fill=\"black\"/></svg>\n");
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediaui.js
/* eslint-disable import/no-extraneous-dependencies */
// cspell:ignore medialibrary



// cspell:ignore medialibrary


/**
 * @internal
 */
class DrupalMediaUI extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  init() {
    const editor = this.editor;
    const options = this.editor.config.get('drupalMedia');
    if (!options) {
      return;
    }

    const { libraryURL, openDialog, dialogSettings = {} } = options;
    if (!libraryURL || typeof openDialog !== 'function') {
      return;
    }

    editor.ui.componentFactory.add('drupalMedia', (locale) => {
      const command = editor.commands.get('insertDrupalMedia');
      const buttonView = new delegated_uifrom_dll_reference_CKEditor5.ButtonView(locale);

      buttonView.set({
        label: Drupal.t('Insert Drupal Media'),
        icon: medialibrary,
        tooltip: true,
      });

      buttonView.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');
      this.listenTo(buttonView, 'execute', () => {
        openDialog(
          libraryURL,
          ({ attributes }) => {
            editor.execute('insertDrupalMedia', attributes);
          },
          dialogSettings,
        );
      });

      return buttonView;
    });
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/utils.js
/* eslint-disable import/no-extraneous-dependencies */


/**
 * Checks if the provided model element is `drupalMedia`.
 *
 * @param {module:engine/model/element~Element} modelElement
 *   The model element to be checked.
 * @return {boolean}
 *   A boolean indicating whether element is drupalMedia element.
 *
 * @internal
 */
function isDrupalMedia(modelElement) {
  return !!modelElement && modelElement.is('element', 'drupalMedia');
}

/**
 * Checks if view element is <drupal-media> element.
 *
 * @param {module:engine/view/element~Element} viewElement
 *   The view element.
 * @return {boolean}
 *   A boolean indicating whether element is <drupal-media> element.
 *
 * @internal
 */
function isDrupalMediaWidget(viewElement) {
  return (
    (0,delegated_widgetfrom_dll_reference_CKEditor5.isWidget)(viewElement) && !!viewElement.getCustomProperty('drupalMedia')
  );
}

/**
 * Gets selected Drupal Media widget if only Drupal Media is currently selected.
 *
 * @param {module:engine/model/selection~Selection} selection
 *   The current selection.
 * @return {module:engine/view/element~Element|null}
 *   The currently selected Drupal Media widget or null.
 *
 * @internal
 */
function getSelectedDrupalMediaWidget(selection) {
  const viewElement = selection.getSelectedElement();
  if (viewElement && isDrupalMediaWidget(viewElement)) {
    return viewElement;
  }

  return null;
}

/**
 * Checks if value is a JavaScript object.
 *
 * This will return true for any type of JavaScript object. (e.g. arrays,
 * functions, objects, regexes, new Number(0), and new String(''))
 *
 * @param value
 *   Value to check.
 * @return {boolean}
 *   True if value is an object, else false.
 */
function isObject(value) {
  const type = typeof value;
  return value != null && (type === 'object' || type === 'function');
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediatoolbar.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediatoolbar */





/**
 * @module drupalMedia/drupalmediatoolbar
 */

/**
 * Convert dropdown definitions to keys registered in the ComponentFactory.
 *
 * The registration process should be handled by the plugin which handles the UI
 * of a particular feature.
 *
 * @param {Array.<string|Object>} config
 *   The drupalMedia.toolbar configuration.
 *
 * @return {string[]}
 *   A normalized toolbar item list.
 */
function normalizeDeclarativeConfig(config) {
  return config.map((item) => (isObject(item) ? item.name : item));
}

/**
 * @internal
 */
class DrupalMediaToolbar extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  static get requires() {
    return [delegated_widgetfrom_dll_reference_CKEditor5.WidgetToolbarRepository];
  }

  static get pluginName() {
    return 'DrupalMediaToolbar';
  }

  afterInit() {
    const { editor } = this;
    const widgetToolbarRepository = editor.plugins.get(delegated_widgetfrom_dll_reference_CKEditor5.WidgetToolbarRepository);

    widgetToolbarRepository.register('drupalMedia', {
      ariaLabel: Drupal.t('Drupal Media toolbar'),
      items:
        normalizeDeclarativeConfig(editor.config.get('drupalMedia.toolbar')) ||
        [],
      // Get the selected image or an image containing the figcaption with the selection inside.
      getRelatedElement: (selection) => getSelectedDrupalMediaWidget(selection),
    });
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative/mediaimagetextalternativecommand.js
/* eslint-disable import/no-extraneous-dependencies */



/**
 * The media image text alternative command.
 *
 * This is used to change the `alt` attribute of `<drupalMedia>` elements.
 *
 * @see https://github.com/ckeditor/ckeditor5/blob/master/packages/ckeditor5-image/src/imagetextalternative/imagetextalternativecommand.js
 */
class MediaImageTextAlternativeCommand extends delegated_corefrom_dll_reference_CKEditor5.Command {
  /**
   * The command value: `false` if there is no `alt` attribute, otherwise the value of the `alt` attribute.

  /**
   * @inheritDoc
   */
  refresh() {
    const element = this.editor.model.document.selection.getSelectedElement();

    this.isEnabled = false;
    if (isDrupalMedia(element)) {
      this._isMediaImage(element).then((hasImageField) => {
        this.isEnabled = hasImageField;
      });
    }

    if (isDrupalMedia(element) && element.hasAttribute('drupalMediaAlt')) {
      this.value = element.getAttribute('drupalMediaAlt');
    } else {
      this.value = false;
    }
  }

  /**
   * Executes the command.
   *
   * @param {Object} options
   *   An options object.
   * @param {String} options.newValue The new value of the `alt` attribute to set.
   */
  execute(options) {
    const { model } = this.editor;
    const imageElement = model.document.selection.getSelectedElement();

    options.newValue = options.newValue.trim();
    model.change((writer) => {
      if (options.newValue.length > 0) {
        writer.setAttribute('drupalMediaAlt', options.newValue, imageElement);
      } else {
        writer.removeAttribute('drupalMediaAlt', imageElement);
      }
    });
  }

  async _isMediaImage(modelElement) {
    const options = this.editor.config.get('drupalMedia');
    if (!options) {
      return null;
    }

    const { isMediaUrl } = options;
    const query = new URLSearchParams({
      uuid: modelElement.getAttribute('drupalMediaEntityUuid'),
    });
    // The `isMediaUrl` received from the server is guaranteed to already have
    // a query string (for the CSRF token).
    // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Media::getDynamicPluginConfig()
    const response = await fetch(`${isMediaUrl}&${query}`);
    if (response.ok) {
      return JSON.parse(await response.text());
    }

    return null;
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative/mediaimagetextalternativeediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words mediaimagetextalternativecommand textalternativeformview */




/**
 * The image text alternative editing plugin.
 */
class MediaImageTextAlternativeEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'MediaImageTextAlternativeEditing';
  }

  /**
   * @inheritDoc
   */
  init() {
    this.editor.commands.add(
      'mediaImageTextAlternative',
      new MediaImageTextAlternativeCommand(this.editor),
    );
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/ui/utils.js
/* eslint-disable import/no-extraneous-dependencies */




/**
 * Returns the positioning options that control the geometry of the contextual
 * balloon with respect to the selected element in the editor content.
 *
 * @param {module:core/editor/editor~Editor} editor
 *   The editor instance.
 * @return {Object}
 *   The options.
 *
 * @internal
 */
function getBalloonPositionData(editor) {
  const editingView = editor.editing.view;
  const defaultPositions = delegated_uifrom_dll_reference_CKEditor5.BalloonPanelView.defaultPositions;

  return {
    target: editingView.domConverter.viewToDom(
      editingView.document.selection.getSelectedElement(),
    ),
    positions: [
      defaultPositions.northArrowSouth,
      defaultPositions.northArrowSouthWest,
      defaultPositions.northArrowSouthEast,
      defaultPositions.southArrowNorth,
      defaultPositions.southArrowNorthWest,
      defaultPositions.southArrowNorthEast,
    ],
  };
}

/**
 * A helper utility that positions the contextual balloon instance with respect
 * to the image in the editor content, if one is selected.
 *
 * @param {module:core/editor/editor~Editor} editor
 *   The editor instance.
 *
 * @internal
 */
function repositionContextualBalloon(editor) {
  const balloon = editor.plugins.get('ContextualBalloon');

  if (getSelectedDrupalMediaWidget(editor.editing.view.document.selection)) {
    const position = getBalloonPositionData(editor);

    balloon.updatePosition(position);
  }
}

// EXTERNAL MODULE: delegated ./utils.js from dll-reference CKEditor5.dll
var delegated_utilsfrom_dll_reference_CKEditor5 = __webpack_require__("ckeditor5/src/utils.js");
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative/ui/textalternativeformview.js
/* eslint-disable import/no-extraneous-dependencies */

// cspell:ignore focusables





// cspell:ignore focusables

class TextAlternativeFormView extends delegated_uifrom_dll_reference_CKEditor5.View {
  /**
   * @inheritdoc
   */
  constructor(locale) {
    super(locale);

    /**
     * Tracks information about the DOM focus in the form.
     */
    this.focusTracker = new delegated_utilsfrom_dll_reference_CKEditor5.FocusTracker();

    /**
     * An instance of the KeystrokeHandler.
     */
    this.keystrokes = new delegated_utilsfrom_dll_reference_CKEditor5.KeystrokeHandler();

    /**
     * An input with a label.
     */
    this.labeledInput = this._createLabeledInputView();

    /**
     * A button used to submit the form.
     */
    this.saveButtonView = this._createButton(
      Drupal.t('Save'),
      delegated_corefrom_dll_reference_CKEditor5.icons.check,
      'ck-button-save',
    );
    this.saveButtonView.type = 'submit';

    /**
     * A button used to cancel the form.
     */
    this.cancelButtonView = this._createButton(
      Drupal.t('Cancel'),
      delegated_corefrom_dll_reference_CKEditor5.icons.cancel,
      'ck-button-cancel',
      'cancel',
    );

    /**
     * A collection of views which can be focused in the form.
     */
    this._focusables = new delegated_uifrom_dll_reference_CKEditor5.ViewCollection();

    /**
     * Helps cycling over focusables in the form.
     */
    this._focusCycler = new delegated_uifrom_dll_reference_CKEditor5.FocusCycler({
      focusables: this._focusables,
      focusTracker: this.focusTracker,
      keystrokeHandler: this.keystrokes,
      actions: {
        // Navigate form fields backwards using the Shift + Tab keystroke.
        focusPrevious: 'shift + tab',

        // Navigate form fields forwards using the Tab key.
        focusNext: 'tab',
      },
    });

    this.setTemplate({
      tag: 'form',

      attributes: {
        class: ['ck', 'ck-text-alternative-form', 'ck-responsive-form'],
        tabindex: '-1',
      },

      children: [this.labeledInput, this.saveButtonView, this.cancelButtonView],
    });

    (0,delegated_uifrom_dll_reference_CKEditor5.injectCssTransitionDisabler)(this);
  }

  /**
   * @inheritdoc
   */
  render() {
    super.render();

    this.keystrokes.listenTo(this.element);

    (0,delegated_uifrom_dll_reference_CKEditor5.submitHandler)({ view: this });

    [this.labeledInput, this.saveButtonView, this.cancelButtonView].forEach(
      (v) => {
        // Register the view as focusable.
        this._focusables.add(v);

        // Register the view in the focus tracker.
        this.focusTracker.add(v.element);
      },
    );
  }

  /**
   * Creates the button view.
   *
   * @param {String} label
   *   The button label
   * @param {String} icon
   *   The button's icon.
   * @param {String} className
   *   The additional button CSS class name.
   * @param {String} [eventName]
   *   The event name that the ButtonView#execute event will be delegated to.
   * @return {module:ui/view~View}
   *   The button view instance.
   */
  _createButton(label, icon, className, eventName) {
    const button = new delegated_uifrom_dll_reference_CKEditor5.ButtonView(this.locale);

    button.set({
      label,
      icon,
      tooltip: true,
    });

    button.extendTemplate({
      attributes: {
        class: className,
      },
    });

    if (eventName) {
      button.delegate('execute').to(this, eventName);
    }

    return button;
  }

  /**
   * Creates an input with a label.
   *
   * @return {module:ui/view~View}
   *   Labeled field view instance.
   */
  _createLabeledInputView() {
    const labeledInput = new delegated_uifrom_dll_reference_CKEditor5.LabeledFieldView(
      this.locale,
      delegated_uifrom_dll_reference_CKEditor5.createLabeledInputText,
    );

    labeledInput.label = Drupal.t('Override text alternative');

    return labeledInput;
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative/mediaimagetextalternativeui.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words textalternativeformview */









/**
 * The media image text alternative UI plugin.
 *
 * @see https://github.com/ckeditor/ckeditor5/blob/master/packages/ckeditor5-image/src/imagetextalternative/imagetextalternativeui.js
 */
class MediaImageTextAlternativeUi extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [delegated_uifrom_dll_reference_CKEditor5.ContextualBalloon];
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'MediaImageTextAlternativeUi';
  }

  /**
   * @inheritDoc
   */
  init() {
    this._createButton();
    this._createForm();
  }

  /**
   * @inheritDoc
   */
  destroy() {
    super.destroy();
    this._form.destroy();
  }

  /**
   * Creates a button showing the balloon panel for changing the image text
   * alternative and registers it in the editor ComponentFactory.
   */
  _createButton() {
    const editor = this.editor;

    editor.ui.componentFactory.add('mediaImageTextAlternative', (locale) => {
      const command = editor.commands.get('mediaImageTextAlternative');
      const view = new delegated_uifrom_dll_reference_CKEditor5.ButtonView(locale);

      view.set({
        label: Drupal.t('Override media image text alternative'),
        icon: delegated_corefrom_dll_reference_CKEditor5.icons.lowVision,
        tooltip: true,
      });

      view.bind('isVisible').to(command, 'isEnabled');

      this.listenTo(view, 'execute', () => {
        this._showForm();
      });

      return view;
    });
  }

  /**
   * Creates the {@link module:image/imagetextalternative/ui/textalternativeformview~TextAlternativeFormView}
   * form.
   *
   * @private
   */
  _createForm() {
    const editor = this.editor;
    const view = editor.editing.view;
    const viewDocument = view.document;

    /**
     * The contextual balloon plugin instance.
     */
    this._balloon = this.editor.plugins.get('ContextualBalloon');

    /**
     * A form containing a textarea and buttons, used to change the `alt` text value.
     */
    this._form = new TextAlternativeFormView(editor.locale);

    // Render the form so its #element is available for clickOutsideHandler.
    this._form.render();

    this.listenTo(this._form, 'submit', () => {
      editor.execute('mediaImageTextAlternative', {
        newValue: this._form.labeledInput.fieldView.element.value,
      });

      this._hideForm(true);
    });

    this.listenTo(this._form, 'cancel', () => {
      this._hideForm(true);
    });

    // Close the form on Esc key press.
    this._form.keystrokes.set('Esc', (data, cancel) => {
      this._hideForm(true);
      cancel();
    });

    // Reposition the balloon or hide the form if an image widget is no longer selected.
    this.listenTo(editor.ui, 'update', () => {
      if (!getSelectedDrupalMediaWidget(viewDocument.selection)) {
        this._hideForm(true);
      } else if (this._isVisible) {
        repositionContextualBalloon(editor);
      }
    });

    // Close on click outside of balloon panel element.
    (0,delegated_uifrom_dll_reference_CKEditor5.clickOutsideHandler)({
      emitter: this._form,
      activator: () => this._isVisible,
      contextElements: [this._balloon.view.element],
      callback: () => this._hideForm(),
    });
  }

  /**
   * Shows the form in a balloon.
   */
  _showForm() {
    if (this._isVisible) {
      return;
    }

    const editor = this.editor;
    const command = editor.commands.get('mediaImageTextAlternative');
    const labeledInput = this._form.labeledInput;

    this._form.disableCssTransitions();

    if (!this._isInBalloon) {
      this._balloon.add({
        view: this._form,
        position: getBalloonPositionData(editor),
      });
    }

    // Make sure that each time the panel shows up, the field remains in sync with the value of
    // the command. If the user typed in the input, then canceled the balloon (`labeledInput#value`
    // stays unaltered) and re-opened it without changing the value of the command, they would see the
    // old value instead of the actual value of the command.
    // https://github.com/ckeditor/ckeditor5-image/issues/114
    labeledInput.fieldView.element.value = command.value || '';
    labeledInput.fieldView.value = labeledInput.fieldView.element.value;

    this._form.labeledInput.fieldView.select();

    this._form.enableCssTransitions();
  }

  /**
   * Removes the {@link #_form} from the {@link #_balloon}.
   *
   * @param {Boolean} [focusEditable=false] Controls whether the editing view is focused afterwards.
   * @private
   */
  _hideForm(focusEditable) {
    if (!this._isInBalloon) {
      return;
    }

    // Blur the input element before removing it from DOM to prevent issues in some browsers.
    // See https://github.com/ckeditor/ckeditor5/issues/1501.
    if (this._form.focusTracker.isFocused) {
      this._form.saveButtonView.focus();
    }

    this._balloon.remove(this._form);

    if (focusEditable) {
      this.editor.editing.view.focus();
    }
  }

  /**
   * Returns `true` when the form is the visible view in the balloon.
   *
   * @type {Boolean}
   */
  get _isVisible() {
    return this._balloon.visibleView === this._form;
  }

  /**
   * Returns `true` when the form is in the balloon.
   *
   * @type {Boolean}
   */
  get _isInBalloon() {
    return this._balloon.hasView(this._form);
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative.js
/* eslint-disable import/no-extraneous-dependencies */




/**
 * @internal
 */
/**
 * The media image text alternative plugin.
 */
class MediaImageTextAlternative extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [MediaImageTextAlternativeEditing, MediaImageTextAlternativeUi];
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'MediaImageTextAlternative';
  }
}

;// CONCATENATED MODULE: ./node_modules/@ckeditor/ckeditor5-html-support/src/conversionutils.js
/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module html-support/conversionutils
 */



/**
* Helper function for downcast converter. Sets attributes on the given view element.
*
* @param {module:engine/view/downcastwriter~DowncastWriter} writer
* @param {Object} viewAttributes
* @param {module:engine/view/element~Element} viewElement
*/
function setViewAttributes( writer, viewAttributes, viewElement ) {
	if ( viewAttributes.attributes ) {
		for ( const [ key, value ] of Object.entries( viewAttributes.attributes ) ) {
			writer.setAttribute( key, value, viewElement );
		}
	}

	if ( viewAttributes.styles ) {
		writer.setStyle( viewAttributes.styles, viewElement );
	}

	if ( viewAttributes.classes ) {
		writer.addClass( viewAttributes.classes, viewElement );
	}
}

/**
* Merges view element attribute objects.
*
* @param {Object} target
* @param {Object} source
* @returns {Object}
*/
function mergeViewElementAttributes( target, source ) {
	const result = cloneDeep( target );

	for ( const key in source ) {
		// Merge classes.
		if ( Array.isArray( source[ key ] ) ) {
			result[ key ] = Array.from( new Set( [ ...target[ key ], ...source[ key ] ] ) );
		}

		// Merge attributes or styles.
		else {
			result[ key ] = { ...target[ key ], ...source[ key ] };
		}
	}

	return result;
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediageneralhtmlsupport.js
/* eslint-disable import/no-extraneous-dependencies */
// cSpell:words conversionutils datafilter



/**
 * View-to-model conversion helper preserving allowed attributes on the Drupal Media model.
 *
 * @param {module:html-support/datafilter~DataFilter} dataFilter
 *   The General HTML support data filter.
 *
 * @return {function}
 *   function that adds an event listener to upcastDispatcher.
 */
function viewToModelDrupalMediaAttributeConverter(dataFilter) {
  return (dispatcher) => {
    dispatcher.on(
      'element:drupal-media',
      (evt, data, conversionApi) => {
        function preserveElementAttributes(viewElement, attributeName) {
          const viewAttributes = dataFilter._consumeAllowedAttributes(
            viewElement,
            conversionApi,
          );

          if (viewAttributes) {
            conversionApi.writer.setAttribute(
              attributeName,
              viewAttributes,
              data.modelRange,
            );
          }
        }
        function preserveLinkAttributes(linkElement) {
          preserveElementAttributes(linkElement, 'htmlLinkAttributes');
        }

        const viewMediaElement = data.viewItem;
        const viewContainerElement = viewMediaElement.parent;

        if (viewContainerElement.is('element', 'a')) {
          preserveLinkAttributes(viewContainerElement);
        }
      },
      { priority: 'low' },
    );
  };
}

/**
 * Gets descendant element from a container.
 *
 * @param {module:engine/model/writer~Writer} writer
 *   The writer.
 * @param {module:engine/view/element~Element} containerElement
 *   The container element.
 * @param {string} elementName
 *   The element name.
 * @return {module:engine/view/element~Element|undefined}
 *   The descendant element matching element name or undefined if not found.
 */
function getDescendantElement(writer, containerElement, elementName) {
  const range = writer.createRangeOn(containerElement);

  // eslint-disable-next-line no-restricted-syntax
  for (const { item } of range.getWalker()) {
    if (item.is('element', elementName)) {
      return item;
    }
  }
}

/**
 * Model to editing view attribute converter.
 *
 * @return {function}
 *   A function that adds an event listener to downcastDispatcher.
 */
function modelToEditingViewAttributeConverter() {
  return (dispatcher) =>
    dispatcher.on(
      'attribute:linkHref:drupalMedia',
      (evt, data, conversionApi) => {
        if (
          !conversionApi.consumable.consume(
            data.item,
            'attribute:htmlLinkAttributes:drupalMedia',
          )
        ) {
          return;
        }

        const containerElement = conversionApi.mapper.toViewElement(data.item);
        const viewElement = getDescendantElement(
          conversionApi.writer,
          containerElement,
          'a',
        );

        setViewAttributes(
          conversionApi.writer,
          data.item.getAttribute('htmlLinkAttributes'),
          viewElement,
        );
      },
      { priority: 'low' },
    );
}

/**
 * Model to data view attribute converter.
 *
 * @return {function}
 *   function that adds an event listener to downcastDispatcher.
 */
function modelToDataViewAttributeConverter() {
  return (dispatcher) =>
    dispatcher.on(
      'attribute:linkHref:drupalMedia',
      (evt, data, conversionApi) => {
        if (
          !conversionApi.consumable.consume(
            data.item,
            'attribute:htmlLinkAttributes:drupalMedia',
          )
        ) {
          return;
        }

        const mediaElement = conversionApi.mapper.toViewElement(data.item);
        const linkElement = mediaElement.parent;
        setViewAttributes(
          conversionApi.writer,
          data.item.getAttribute('htmlLinkAttributes'),
          linkElement,
        );
      },
      { priority: 'low' },
    );
}

/**
 * Integrates Drupal Media with General HTML Support.
 *
 * @internal
 */
class DrupalMediaGeneralHtmlSupport extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;

    // This plugin is only needed if General HTML Support plugin is loaded.
    if (!editor.plugins.has('GeneralHtmlSupport')) {
      return;
    }

    const { schema } = editor.model;
    const { conversion } = editor;
    const dataFilter = editor.plugins.get('DataFilter');

    schema.extend('drupalMedia', {
      allowAttributes: ['htmlLinkAttributes'],
    });

    conversion
      .for('upcast')
      .add(viewToModelDrupalMediaAttributeConverter(dataFilter));
    conversion
      .for('editingDowncast')
      .add(modelToEditingViewAttributeConverter());
    conversion.for('dataDowncast').add(modelToDataViewAttributeConverter());
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMediaGeneralHtmlSupport';
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmedia.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediaediting drupalmediageneralhtmlsupport drupalmediaui drupalmediatoolbar mediaimagetextalternative */









/**
 * @internal
 */
class DrupalMedia extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  static get requires() {
    return [
      DrupalMediaEditing,
      DrupalMediaGeneralHtmlSupport,
      DrupalMediaUI,
      DrupalMediaToolbar,
      MediaImageTextAlternative,
    ];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMedia';
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupallinkmedia/drupallinkmediaediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupallinkmediaediting linkediting */


/**
 * Returns the first drupal-media element in a given view element.
 *
 * @param {module:engine/view/element~Element} viewElement
 *   The view element.
 *
 * @return {module:engine/view/element~Element|undefined}
 *   The first <drupal-media> element or undefined if the element doesn't have
 *   <drupal-media> as a child element.
 */
function getFirstMedia(viewElement) {
  return Array.from(viewElement.getChildren()).find(
    (child) => child.name === 'drupal-media',
  );
}

/**
 * Returns a converter that consumes the `href` attribute if a link contains a <drupal-media>.
 *
 * @return {Function}
 *   A function that adds an event listener to upcastDispatcher.
 */
function upcastMediaLink() {
  return (dispatcher) => {
    dispatcher.on(
      'element:a',
      (evt, data, conversionApi) => {
        const viewLink = data.viewItem;
        const mediaInLink = getFirstMedia(viewLink);

        if (!mediaInLink) {
          return;
        }

        // There's an <drupal-media> inside an <a> element - we consume it so it
        // won't be picked up by the Link plugin.
        const consumableAttributes = { attributes: ['href'] };

        // Consume the `href` attribute so the default one will not convert it to
        // $text attribute.
        if (!conversionApi.consumable.consume(viewLink, consumableAttributes)) {
          // Might be consumed by something else - i.e. other converter with
          // priority=highest - a standard check.
          return;
        }

        const linkHref = viewLink.getAttribute('href');

        // Missing the `href` attribute.
        if (!linkHref) {
          return;
        }

        const conversionResult = conversionApi.convertItem(
          mediaInLink,
          data.modelCursor,
        );

        // Set media range as conversion result.
        data.modelRange = conversionResult.modelRange;

        // Continue conversion where <drupal-media> conversion ends.
        data.modelCursor = conversionResult.modelCursor;

        const modelElement = data.modelCursor.nodeBefore;

        if (modelElement && modelElement.is('element', 'drupalMedia')) {
          // Set the `linkHref` attribute from <a> element on model drupalMedia
          // element.
          conversionApi.writer.setAttribute('linkHref', linkHref, modelElement);
        }
      },
      { priority: 'high' },
    );
  };
}

/**
 * Return a converter that adds the <a> element to view data.
 *
 * @return {Function}
 *   A function that adds an event listener to downcastDispatcher.
 */
function dataDowncastMediaLink() {
  return (dispatcher) => {
    dispatcher.on(
      'attribute:linkHref:drupalMedia',
      (evt, data, conversionApi) => {
        const { writer } = conversionApi;
        if (!conversionApi.consumable.consume(data.item, evt.name)) {
          return;
        }

        // The drupalMedia will be already converted - so it will be present in
        // the view.
        const mediaElement = conversionApi.mapper.toViewElement(data.item);

        // If so, update the attribute if it's defined or remove the entire link
        // if the attribute is empty. But if it does not exist. Let's wrap already
        // converted drupalMedia by newly created link element.
        // 1. Create an empty <a> element.
        const linkElement = writer.createContainerElement('a', {
          href: data.attributeNewValue,
        });

        // 2. Insert <a> before the <drupal-media> element.
        writer.insert(writer.createPositionBefore(mediaElement), linkElement);

        // 3. Move the drupal-media element inside the <a>.
        writer.move(
          writer.createRangeOn(mediaElement),
          writer.createPositionAt(linkElement, 0),
        );
      },
      { priority: 'high' },
    );
  };
}

/**
 * Return a converter that adds the <a> element to editing view.
 *
 * @return {Function}
 *   A function that adds an event listener to downcastDispatcher.
 *
 * @see https://github.com/ckeditor/ckeditor5/blob/v31.0.0/packages/ckeditor5-link/src/linkimageediting.js#L180
 */
function editingDowncastMediaLink() {
  return (dispatcher) => {
    dispatcher.on(
      'attribute:linkHref:drupalMedia',
      (evt, data, conversionApi) => {
        const { writer } = conversionApi;
        if (!conversionApi.consumable.consume(data.item, evt.name)) {
          return;
        }

        // The drupalMedia will be already converted - so it will be present in
        // the view.
        const mediaContainer = conversionApi.mapper.toViewElement(data.item);
        const linkInMedia = Array.from(mediaContainer.getChildren()).find(
          (child) => child.name === 'a',
        );

        // If link already exists, instead of creating new link from scratch,
        // update the existing link. This makes the UI rendering much smoother.
        if (linkInMedia) {
          // If attribute has a new value, update it. If new value doesn't exist,
          // the link will be removed.
          if (data.attributeNewValue) {
            writer.setAttribute('href', data.attributeNewValue, linkInMedia);
          } else {
            // This is triggering elementToElement conversion for drupalMedia
            // element which makes caused re-render of the media preview, making
            // the media preview flicker once when media is unlinked.
            // @todo ensure that this doesn't cause flickering after
            //   https://www.drupal.org/i/3246380 has been addressed.
            writer.move(
              writer.createRangeIn(linkInMedia),
              writer.createPositionAt(mediaContainer, 0),
            );
            writer.remove(linkInMedia);
          }
        } else {
          const mediaPreview = Array.from(mediaContainer.getChildren()).find(
            (child) => child.getAttribute('data-drupal-media-preview'),
          );
          // 1. Create an empty <a> element.
          const linkElement = writer.createContainerElement('a', {
            href: data.attributeNewValue,
          });

          // 2. Insert <a> inside the media container.
          writer.insert(
            writer.createPositionAt(mediaContainer, 0),
            linkElement,
          );

          // 3. Move the media preview inside the <a>.
          writer.move(
            writer.createRangeOn(mediaPreview),
            writer.createPositionAt(linkElement, 0),
          );
        }
      },
      { priority: 'high' },
    );
  };
}

/**
 * Model to view and view to model conversions for linked media elements.
 *
 * @internal
 *
 * @see https://github.com/ckeditor/ckeditor5/blob/v31.0.0/packages/ckeditor5-link/src/linkimage.js
 */
class DrupalLinkMediaEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return ['LinkEditing', 'DrupalMediaEditing'];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalLinkMediaEditing';
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    editor.model.schema.extend('drupalMedia', {
      allowAttributes: ['linkHref'],
    });

    editor.conversion.for('upcast').add(upcastMediaLink());
    editor.conversion.for('editingDowncast').add(editingDowncastMediaLink());
    editor.conversion.for('dataDowncast').add(dataDowncastMediaLink());
  }
}

;// CONCATENATED MODULE: ./node_modules/@ckeditor/ckeditor5-link/src/utils.js
/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module link/utils
 */

/* global window */



const ATTRIBUTE_WHITESPACES = /[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205f\u3000]/g; // eslint-disable-line no-control-regex
const SAFE_URL = /^(?:(?:https?|ftps?|mailto):|[^a-z]|[a-z+.-]+(?:[^a-z+.:-]|$))/i;

// Simplified email test - should be run over previously found URL.
const EMAIL_REG_EXP = /^[\S]+@((?![-_])(?:[-\w\u00a1-\uffff]{0,63}[^-_]\.))+(?:[a-z\u00a1-\uffff]{2,})$/i;

// The regex checks for the protocol syntax ('xxxx://' or 'xxxx:')
// or non-word characters at the beginning of the link ('/', '#' etc.).
const PROTOCOL_REG_EXP = /^((\w+:(\/{2,})?)|(\W))/i;

/**
 * A keystroke used by the {@link module:link/linkui~LinkUI link UI feature}.
 */
const LINK_KEYSTROKE = 'Ctrl+K';

/**
 * Returns `true` if a given view node is the link element.
 *
 * @param {module:engine/view/node~Node} node
 * @returns {Boolean}
 */
function isLinkElement( node ) {
	return node.is( 'attributeElement' ) && !!node.getCustomProperty( 'link' );
}

/**
 * Creates a link {@link module:engine/view/attributeelement~AttributeElement} with the provided `href` attribute.
 *
 * @param {String} href
 * @param {module:engine/conversion/downcastdispatcher~DowncastConversionApi} conversionApi
 * @returns {module:engine/view/attributeelement~AttributeElement}
 */
function createLinkElement( href, { writer } ) {
	// Priority 5 - https://github.com/ckeditor/ckeditor5-link/issues/121.
	const linkElement = writer.createAttributeElement( 'a', { href }, { priority: 5 } );
	writer.setCustomProperty( 'link', true, linkElement );

	return linkElement;
}

/**
 * Returns a safe URL based on a given value.
 *
 * A URL is considered safe if it is safe for the user (does not contain any malicious code).
 *
 * If a URL is considered unsafe, a simple `"#"` is returned.
 *
 * @protected
 * @param {*} url
 * @returns {String} Safe URL.
 */
function ensureSafeUrl( url ) {
	url = String( url );

	return isSafeUrl( url ) ? url : '#';
}

// Checks whether the given URL is safe for the user (does not contain any malicious code).
//
// @param {String} url URL to check.
function isSafeUrl( url ) {
	const normalizedUrl = url.replace( ATTRIBUTE_WHITESPACES, '' );

	return normalizedUrl.match( SAFE_URL );
}

/**
 * Returns the {@link module:link/link~LinkConfig#decorators `config.link.decorators`} configuration processed
 * to respect the locale of the editor, i.e. to display the {@link module:link/link~LinkDecoratorManualDefinition label}
 * in the correct language.
 *
 * **Note**: Only the few most commonly used labels are translated automatically. Other labels should be manually
 * translated in the {@link module:link/link~LinkConfig#decorators `config.link.decorators`} configuration.
 *
 * @param {module:utils/locale~Locale#t} t shorthand for {@link module:utils/locale~Locale#t Locale#t}
 * @param {Array.<module:link/link~LinkDecoratorDefinition>} The decorator reference
 * where the label values should be localized.
 * @returns {Array.<module:link/link~LinkDecoratorDefinition>}
 */
function getLocalizedDecorators( t, decorators ) {
	const localizedDecoratorsLabels = {
		'Open in a new tab': t( 'Open in a new tab' ),
		'Downloadable': t( 'Downloadable' )
	};

	decorators.forEach( decorator => {
		if ( decorator.label && localizedDecoratorsLabels[ decorator.label ] ) {
			decorator.label = localizedDecoratorsLabels[ decorator.label ];
		}
		return decorator;
	} );

	return decorators;
}

/**
 * Converts an object with defined decorators to a normalized array of decorators. The `id` key is added for each decorator and
 * is used as the attribute's name in the model.
 *
 * @param {Object.<String, module:link/link~LinkDecoratorDefinition>} decorators
 * @returns {Array.<module:link/link~LinkDecoratorDefinition>}
 */
function normalizeDecorators( decorators ) {
	const retArray = [];

	if ( decorators ) {
		for ( const [ key, value ] of Object.entries( decorators ) ) {
			const decorator = Object.assign(
				{},
				value,
				{ id: `link${ upperFirst( key ) }` }
			);
			retArray.push( decorator );
		}
	}

	return retArray;
}

/**
 * Returns `true` if the specified `element` can be linked (the element allows the `linkHref` attribute).
 *
 * @params {module:engine/model/element~Element|null} element
 * @params {module:engine/model/schema~Schema} schema
 * @returns {Boolean}
 */
function isLinkableElement( element, schema ) {
	if ( !element ) {
		return false;
	}

	return schema.checkAttribute( element.name, 'linkHref' );
}

/**
 * Returns `true` if the specified `value` is an email.
 *
 * @params {String} value
 * @returns {Boolean}
 */
function isEmail( value ) {
	return EMAIL_REG_EXP.test( value );
}

/**
 * Adds the protocol prefix to the specified `link` when:
 *
 * * it does not contain it already, and there is a {@link module:link/link~LinkConfig#defaultProtocol `defaultProtocol` }
 * configuration value provided,
 * * or the link is an email address.
 *
 *
 * @params {String} link
 * @params {String} defaultProtocol
 * @returns {Boolean}
 */
function addLinkProtocolIfApplicable( link, defaultProtocol ) {
	const protocol = isEmail( link ) ? 'mailto:' : defaultProtocol;
	const isProtocolNeeded = !!protocol && !PROTOCOL_REG_EXP.test( link );

	return link && isProtocolNeeded ? protocol + link : link;
}

/**
 * Opens the link in a new browser tab.
 *
 * @param {String} link
 */
function openLink( link ) {
	window.open( link, '_blank', 'noopener' );
}

;// CONCATENATED MODULE: ./modules/ckeditor5/icons/link.svg
/* harmony default export */ var icons_link = ("<svg viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"m11.077 15 .991-1.416a.75.75 0 1 1 1.229.86l-1.148 1.64a.748.748 0 0 1-.217.206 5.251 5.251 0 0 1-8.503-5.955.741.741 0 0 1 .12-.274l1.147-1.639a.75.75 0 1 1 1.228.86L4.933 10.7l.006.003a3.75 3.75 0 0 0 6.132 4.294l.006.004zm5.494-5.335a.748.748 0 0 1-.12.274l-1.147 1.639a.75.75 0 1 1-1.228-.86l.86-1.23a3.75 3.75 0 0 0-6.144-4.301l-.86 1.229a.75.75 0 0 1-1.229-.86l1.148-1.64a.748.748 0 0 1 .217-.206 5.251 5.251 0 0 1 8.503 5.955zm-4.563-2.532a.75.75 0 0 1 .184 1.045l-3.155 4.505a.75.75 0 1 1-1.229-.86l3.155-4.506a.75.75 0 0 1 1.045-.184z\"/></svg>\n");
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupallinkmedia/drupallinkmediaui.js
/* eslint-disable import/no-extraneous-dependencies */
// cSpell:words linkui





/**
 * The link media UI plugin.
 *
 * @internal
 */
class DrupalLinkMediaUI extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return ['LinkEditing', 'LinkUI', 'DrupalMediaEditing'];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalLinkMediaUi';
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    const viewDocument = editor.editing.view.document;

    this.listenTo(
      viewDocument,
      'click',
      (evt, data) => {
        if (this._isSelectedLinkedMedia(editor.model.document.selection)) {
          // Prevent browser navigation when clicking a linked media.
          data.preventDefault();

          // Block the `LinkUI` plugin when a media was clicked. In such a case,
          // we'd like to display the media toolbar.
          evt.stop();
        }
      },
      { priority: 'high' },
    );
    this._createToolbarLinkMediaButton();
  }

  /**
   * Creates a `DrupalLinkMediaUI` button view.
   *
   * Clicking this button shows a {@link module:link/linkui~LinkUI#_balloon}
   * attached to the selection. When an media is already linked, the view shows
   * {@link module:link/linkui~LinkUI#actionsView} or
   * {@link module:link/linkui~LinkUI#formView} if it is not.
   */
  _createToolbarLinkMediaButton() {
    const { editor } = this;

    editor.ui.componentFactory.add('drupalLinkMedia', (locale) => {
      const button = new delegated_uifrom_dll_reference_CKEditor5.ButtonView(locale);
      const plugin = editor.plugins.get('LinkUI');
      const linkCommand = editor.commands.get('link');

      button.set({
        isEnabled: true,
        label: Drupal.t('Link media'),
        icon: icons_link,
        keystroke: LINK_KEYSTROKE,
        tooltip: true,
        isToggleable: true,
      });

      // Bind button to the command.
      button.bind('isEnabled').to(linkCommand, 'isEnabled');
      button.bind('isOn').to(linkCommand, 'value', (value) => !!value);

      // Show the actionsView or formView (both from LinkUI) on button click
      // depending on whether the media is already linked.
      this.listenTo(button, 'execute', () => {
        if (this._isSelectedLinkedMedia(editor.model.document.selection)) {
          plugin._addActionsView();
        } else {
          plugin._showUI(true);
        }
      });

      return button;
    });
  }

  /**
   * Returns true if a linked media is the only selected element in the model.
   *
   * @param {module:engine/model/selection~Selection} selection
   * @return {Boolean}
   */
  // eslint-disable-next-line class-methods-use-this
  _isSelectedLinkedMedia(selection) {
    const selectedModelElement = selection.getSelectedElement();
    return (
      !!selectedModelElement &&
      selectedModelElement.is('element', 'drupalMedia') &&
      selectedModelElement.hasAttribute('linkHref')
    );
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupallinkmedia/drupallinkmedia.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupallinkmediaediting drupallinkmediaui */





/**
 * @internal
 */
class DrupalLinkMedia extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalLinkMediaEditing, DrupalLinkMediaUI];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalLinkMedia';
  }
}

;// CONCATENATED MODULE: ./node_modules/@ckeditor/ckeditor5-image/src/imagestyle/utils.js
/**
 * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module image/imagestyle/utils
 */




const {
	objectFullWidth,
	objectInline,
	objectLeft,	objectRight, objectCenter,
	objectBlockLeft, objectBlockRight
} = delegated_corefrom_dll_reference_CKEditor5.icons;

/**
 * Default image style options provided by the plugin that can be referred in the {@link module:image/image~ImageConfig#styles}
 * configuration.
 *
 * There are available 5 styles focused on formatting:
 *
 * * **`'alignLeft'`** aligns the inline or block image to the left and wraps it with the text using the `image-style-align-left` class,
 * * **`'alignRight'`** aligns the inline or block image to the right and wraps it with the text using the `image-style-align-right` class,
 * * **`'alignCenter'`** centers the block image using the `image-style-align-center` class,
 * * **`'alignBlockLeft'`** aligns the block image to the left using the `image-style-block-align-left` class,
 * * **`'alignBlockRight'`** aligns the block image to the right using the `image-style-block-align-right` class,
 *
 * and 3 semantic styles:
 *
 * * **`'inline'`** is an inline image without any CSS class,
 * * **`'block'`** is a block image without any CSS class,
 * * **`'side'`** is a block image styled with the `image-style-side` CSS class.
 *
 * @readonly
 * @type {Object.<String,module:image/imagestyle~ImageStyleOptionDefinition>}
 */
const DEFAULT_OPTIONS = {
	// This style represents an image placed in the line of text.
	inline: {
		name: 'inline',
		title: 'In line',
		icon: objectInline,
		modelElements: [ 'imageInline' ],
		isDefault: true
	},

	// This style represents an image aligned to the left and wrapped with text.
	alignLeft: {
		name: 'alignLeft',
		title: 'Left aligned image',
		icon: objectLeft,
		modelElements: [ 'imageBlock', 'imageInline' ],
		className: 'image-style-align-left'
	},

	// This style represents an image aligned to the left.
	alignBlockLeft: {
		name: 'alignBlockLeft',
		title: 'Left aligned image',
		icon: objectBlockLeft,
		modelElements: [ 'imageBlock' ],
		className: 'image-style-block-align-left'
	},

	// This style represents a centered image.
	alignCenter: {
		name: 'alignCenter',
		title: 'Centered image',
		icon: objectCenter,
		modelElements: [ 'imageBlock' ],
		className: 'image-style-align-center'
	},

	// This style represents an image aligned to the right and wrapped with text.
	alignRight: {
		name: 'alignRight',
		title: 'Right aligned image',
		icon: objectRight,
		modelElements: [ 'imageBlock', 'imageInline' ],
		className: 'image-style-align-right'
	},

	// This style represents an image aligned to the right.
	alignBlockRight: {
		name: 'alignBlockRight',
		title: 'Right aligned image',
		icon: objectBlockRight,
		modelElements: [ 'imageBlock' ],
		className: 'image-style-block-align-right'
	},

	// This option is equal to the situation when no style is applied.
	block: {
		name: 'block',
		title: 'Centered image',
		icon: objectCenter,
		modelElements: [ 'imageBlock' ],
		isDefault: true
	},

	// This represents a side image.
	side: {
		name: 'side',
		title: 'Side image',
		icon: objectRight,
		modelElements: [ 'imageBlock' ],
		className: 'image-style-side'
	}
};

/**
 * Default image style icons provided by the plugin that can be referred in the {@link module:image/image~ImageConfig#styles}
 * configuration.
 *
 * See {@link module:image/imagestyle~ImageStyleOptionDefinition#icon} to learn more.
 *
 * There are 7 default icons available: `'full'`, `'left'`, `'inlineLeft'`, `'center'`, `'right'`, `'inlineRight'`, and `'inline'`.
 *
 * @readonly
 * @type {Object.<String,String>}
 */
const DEFAULT_ICONS = {
	full: objectFullWidth,
	left: objectBlockLeft,
	right: objectBlockRight,
	center: objectCenter,
	inlineLeft: objectLeft,
	inlineRight: objectRight,
	inline: objectInline
};

/**
 * Default drop-downs provided by the plugin that can be referred in the {@link module:image/image~ImageConfig#toolbar}
 * configuration. The drop-downs are containers for the {@link module:image/imagestyle~ImageStyleConfig#options image style options}.
 *
 * If both of the `ImageEditing` plugins are loaded, there are 2 predefined drop-downs available:
 *
 * * **`'imageStyle:wrapText'`**, which contains the `alignLeft` and `alignRight` options, that is,
 * those that wraps the text around the image,
 * * **`'imageStyle:breakText'`**, which contains the `alignBlockLeft`, `alignCenter` and `alignBlockRight` options, that is,
 * those that breaks the text around the image.
 *
 * @readonly
 * @type {Array.<module:image/imagestyle/imagestyleui~ImageStyleDropdownDefinition>}
 */
const DEFAULT_DROPDOWN_DEFINITIONS = [ {
	name: 'imageStyle:wrapText',
	title: 'Wrap text',
	defaultItem: 'imageStyle:alignLeft',
	items: [ 'imageStyle:alignLeft', 'imageStyle:alignRight' ]
}, {
	name: 'imageStyle:breakText',
	title: 'Break text',
	defaultItem: 'imageStyle:block',
	items: [ 'imageStyle:alignBlockLeft', 'imageStyle:block', 'imageStyle:alignBlockRight' ]
} ];

/**
 * Returns a list of the normalized and validated image style options.
 *
 * @protected
 * @param {Object} config
 * @param {Boolean} config.isInlinePluginLoaded
 * Determines whether the {@link module:image/image/imageblockediting~ImageBlockEditing `ImageBlockEditing`} plugin has been loaded.
 * @param {Boolean} config.isBlockPluginLoaded
 * Determines whether the {@link module:image/image/imageinlineediting~ImageInlineEditing `ImageInlineEditing`} plugin has been loaded.
 * @param {module:image/imagestyle~ImageStyleConfig} config.configuredStyles
 * The image styles configuration provided in the image styles {@link module:image/image~ImageConfig#styles configuration}
 * as a default or custom value.
 * @returns {module:image/imagestyle~ImageStyleConfig}
 * * Each of options contains a complete icon markup.
 * * The image style options not supported by any of the loaded plugins are filtered out.
 */
function normalizeStyles( config ) {
  console.log('this functino is called');
	const configuredStyles = config.configuredStyles.options || [];

	const styles = configuredStyles
		.map( arrangement => normalizeDefinition( arrangement ) )
		.filter( arrangement => isValidOption( arrangement, config ) );

	return styles;
}

/**
 * Returns the default image styles configuration depending on the loaded image editing plugins.
 * @protected
 *
 * @param {Boolean} isInlinePluginLoaded
 * Determines whether the {@link module:image/image/imageblockediting~ImageBlockEditing `ImageBlockEditing`} plugin has been loaded.
 *
 * @param {Boolean} isBlockPluginLoaded
 * Determines whether the {@link module:image/image/imageinlineediting~ImageInlineEditing `ImageInlineEditing`} plugin has been loaded.
 *
 * @returns {Object<String,Array>}
 * It returns an object with the lists of the image style options and groups defined as strings related to the
 * {@link module:image/imagestyle/utils~DEFAULT_OPTIONS default options}
 */
function getDefaultStylesConfiguration( isBlockPluginLoaded, isInlinePluginLoaded ) {
	if ( isBlockPluginLoaded && isInlinePluginLoaded ) {
		return {
			options: [
				'inline', 'alignLeft', 'alignRight',
				'alignCenter', 'alignBlockLeft', 'alignBlockRight',
				'block', 'side'
			]
		};
	} else if ( isBlockPluginLoaded ) {
		return {
			options: [ 'block', 'side' ]
		};
	} else if ( isInlinePluginLoaded ) {
		return {
			options: [ 'inline', 'alignLeft', 'alignRight' ]
		};
	}

	return {};
}

/**
 * Returns a list of the available predefined drop-downs' definitions depending on the loaded image editing plugins.
 * @protected
 *
 * @param {module:core/plugincollection~PluginCollection} pluginCollection
 * @returns {Array.<module:image/imagestyle/imagestyleui~ImageStyleDropdownDefinition>}
 */
function getDefaultDropdownDefinitions( pluginCollection ) {
	if ( pluginCollection.has( 'ImageBlockEditing' ) && pluginCollection.has( 'ImageInlineEditing' ) ) {
		return [ ...DEFAULT_DROPDOWN_DEFINITIONS ];
	} else {
		return [];
	}
}

// Normalizes an image style option or group provided in the {@link module:image/image~ImageConfig#styles}
// and returns it in a {@link module:image/imagestyle~ImageStyleOptionDefinition}/
//
// @param {Object|String} definition
//
// @returns {module:image/imagestyle~ImageStyleOptionDefinition}}
function normalizeDefinition( definition ) {
	if ( typeof definition === 'string' ) {
		// Just the name of the style has been passed, but none of the defaults.
		if ( !DEFAULT_OPTIONS[ definition ] ) {
			// Normalize the style anyway to prevent errors.
			definition = { name: definition };
		}
		// Just the name of the style has been passed and it's one of the defaults, just use it.
		// Clone the style to avoid overriding defaults.
		else {
			definition = { ...DEFAULT_OPTIONS[ definition ] };
		}
	} else {
		// If an object style has been passed and if the name matches one of the defaults,
		// extend it with defaults – the user wants to customize a default style.
		// Note: Don't override the user–defined style object, clone it instead.
		definition = extendStyle( DEFAULT_OPTIONS[ definition.name ], definition );
	}

	// If an icon is defined as a string and correspond with a name
	// in default icons, use the default icon provided by the plugin.
	if ( typeof definition.icon === 'string' ) {
		definition.icon = DEFAULT_ICONS[ definition.icon ] || definition.icon;
	}

	return definition;
}

// Checks if the image style option is valid:
// * if it has the modelElements fields defined and filled,
// * if the defined modelElements are supported by any of the loaded image editing plugins.
// It also displays a console warning these conditions are not met.
//
// @param {module:image/imagestyle~ImageStyleOptionDefinition} image style option
// @param {Object.<String,Boolean>} { isBlockPluginLoaded, isInlinePluginLoaded }
//
// @returns Boolean
function isValidOption( option, { isBlockPluginLoaded, isInlinePluginLoaded } ) {
	const { modelElements, name } = option;

	if ( !modelElements || !modelElements.length || !name ) {
		warnInvalidStyle( { style: option } );

		return false;
	} else {
		const supportedElements = [ isBlockPluginLoaded ? 'imageBlock' : null, isInlinePluginLoaded ? 'imageInline' : null ];

		// Check if the option is supported by any of the loaded plugins.
		if ( !modelElements.some( elementName => supportedElements.includes( elementName ) ) ) {
			/**
			 * In order to work correctly, each image style {@link module:image/imagestyle~ImageStyleOptionDefinition option}
			 * requires specific model elements (also: types of images) to be supported by the editor.
			 *
			 * Model element names to which the image style option can be applied are defined in the
			 * {@link module:image/imagestyle~ImageStyleOptionDefinition#modelElements} property of the style option
			 * definition.
			 *
			 * Explore the warning in the console to find out precisely which option is not supported and which editor plugins
			 * are missing. Make sure these plugins are loaded in your editor to get this image style option working.
			 *
			 * @error image-style-missing-dependency
			 * @param {String} [option] The name of the unsupported option.
			 * @param {String} [missingPlugins] The names of the plugins one of which has to be loaded for the particular option.
			 */
			(0,delegated_utilsfrom_dll_reference_CKEditor5.logWarning)( 'image-style-missing-dependency', {
				style: option,
				missingPlugins: modelElements.map( name => name === 'imageBlock' ? 'ImageBlockEditing' : 'ImageInlineEditing' )
			} );

			return false;
		}
	}

	return true;
}

// Extends the default style with a style provided by the developer.
// Note: Don't override the custom–defined style object, clone it instead.
//
// @param {module:image/imagestyle~ImageStyleOptionDefinition} source
// @param {Object} style
//
// @returns {module:image/imagestyle~ImageStyleOptionDefinition}
function extendStyle( source, style ) {
	const extendedStyle = { ...style };

	for ( const prop in source ) {
		if ( !Object.prototype.hasOwnProperty.call( style, prop ) ) {
			extendedStyle[ prop ] = source[ prop ];
		}
	}

	return extendedStyle;
}

// Displays a console warning with the 'image-style-configuration-definition-invalid' error.
// @param {Object} info
function warnInvalidStyle( info ) {
	/**
	 * The image style definition provided in the configuration is invalid.
	 *
	 * Please make sure the definition implements properly one of the following:
	 *
	 * * {@link module:image/imagestyle~ImageStyleOptionDefinition image style option definition},
	 * * {@link module:image/imagestyle/imagestyleui~ImageStyleDropdownDefinition image style dropdown definition}
	 *
	 * @error image-style-configuration-definition-invalid
	 * @param {String} [dropdown] The name of the invalid drop-down
	 * @param {String} [style] The name of the invalid image style option
	 */
	(0,delegated_utilsfrom_dll_reference_CKEditor5.logWarning)( 'image-style-configuration-definition-invalid', info );
}

/* harmony default export */ var utils = ({
	normalizeStyles,
	getDefaultStylesConfiguration,
	getDefaultDropdownDefinitions,
	warnInvalidStyle,
	DEFAULT_OPTIONS,
	DEFAULT_ICONS,
	DEFAULT_DROPDOWN_DEFINITIONS
});

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalelementstyle/drupalelementstylecommand.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words documentselection */


/**
 * @module drupalMedia/drupalelementstyle/drupalelementstylecommand
 */

function schemaContainsAttribute(selectedElement, schema, styles) {
  console.log(styles);
  for (const group of Object.keys(styles)) {
    const groupName = group[0].toUpperCase() + group.substring(1);
    return schema.checkAttribute(selectedElement, `drupal${groupName}`);
  }
  return false;
}

/**
 * Gets closest element that has any DrupalElementStyle attribute in schema.
 *
 * @param {module:engine/model/documentselection~DocumentSelection} selection
 *   The current document selection.
 * @param {module:engine/model/schema~Schema} schema
 *   The model schema.
 *
 * @return {null|module:engine/model/element~Element}
 *   The closest element that supports element styles.
 */
// find the closest element with the drupal element style
// also checks the ancestor
function getClosestElementWithElementStyleAttribute(selection, schema, styles) {
  console.log('line 32: ', styles);
  // dynamically check for attributes
  const selectedElement = selection.getSelectedElement();

  return selectedElement &&
    // here checks schema for if any of the drupal element styles with this attribute name exists
    schemaContainsAttribute(selectedElement, schema, styles)
    ? selectedElement
    : selection
        // if false find the closest element that has drupal style element allowed
      // todo: need to change this
        .getFirstPosition()
        .findAncestor((element) =>
          schema.checkAttribute(element, 'drupalElementStyle'),
        );
}

/**
 * The Drupal Element style command.
 *
 * This is used to apply Drupal Element style option to supported model elements.
 *
 * @extends module:core/command~Command
 *
 * @internal
 */
class DrupalElementStyleCommand extends delegated_corefrom_dll_reference_CKEditor5.Command {
  /**
   * Constructs a new object.
   *
   * @param {module:core/editor/editor~Editor} editor
   *   The editor instance.
   * @param {Drupal.CKEditor5~DrupalElementStyle[]} styles
   *   All available Drupal Element Styles.
   */
  constructor(editor, styles) {
    super(editor);
    this.styles = styles;
    this._styles = {};
    for (const group of Object.keys(styles)) {
      // eslint-disable-next-line no-restricted-syntax
      this._styles[group] = new Map(
        styles[group].map((style) => {
          return [style.name, style];
        }),
      );
    }
  }

  /**
   * @inheritDoc
   */
  // this is called every time the model changes
  // to make sure command has the correct state
  refresh() {
    const { editor } = this;
    const element = getClosestElementWithElementStyleAttribute(
      editor.model.document.selection,
      editor.model.schema,
      this.styles,
    );

    this.isEnabled = !!element;

    if (!this.isEnabled) {
      this.value = false;
      // here element needs to be checked against list of possible attributes
      // and then update the value to include all drupal element styles selected for the element
    } else if (this.containsAttribute(element)) {
      console.log('true');
      this.value = this.getGroupAndAttribute(element);
    } else {
      this.value = false;
    }
  }

  containsAttribute(element) {
    for (const group of Object.keys(this.styles)) {
      const groupName = group[0].toUpperCase() + group.substring(1);
      return element.hasAttribute(`drupal${groupName}`);
    }
    return false;
  }

  getGroupAndAttribute(element) {
    // const drupalStyles = { drupalAlign: '', drupalViewMode: '' };
    const groupAttr = {};
    for (const group of Object.keys(this.styles)) {
      const groupName = group[0].toUpperCase() + group.substring(1);
      if (element.hasAttribute(`drupal${groupName}`)) {
        groupAttr[`drupal${groupName}`] = element.getAttribute(
          `drupal${groupName}`,
        );
      }
    }
    return groupAttr;
  }

  /**
   * Executes the command and applies the style to the selected model element.
   *
   * @example
   *    editor.execute('drupalElementStyle', { value: 'alignLeft' });
   *
   * @param {Object} options
   *   The command options.
   * @param {string} options.value
   *   The name of the style as configured in the Drupal Element style
   *   configuration.
   */
  // makes the actual change in the MODEL
  // execute needs to change to take value and the group
  execute(options = {}) {
    console.log('execute');
    const { editor } = this;
    const { model } = editor;

    model.change((writer) => {
      const requestedStyle = options.value;
      console.log('requestedStyle', requestedStyle);
      const element = getClosestElementWithElementStyleAttribute(
        model.document.selection,
        model.schema,
        this.styles,
      );

      // handle group, retrieve style from correct group
      console.log('this._styles', this._styles.align.get(requestedStyle.drupalAlign));
      // todo: ask what this isDefault is and remove hardcode
      if (!requestedStyle || this._styles.align.get(requestedStyle.drupalAlign).isDefault) {
        // instead of removing drupalemlementstyle remove value from the object
        writer.removeAttribute('drupalAlign', element);
      } else {
        // instead of overriding extend the object with new value
        writer.setAttribute('drupalAlign', requestedStyle.drupalAlign, element);
      }
    });
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalelementstyle/drupalelementstyleediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalelementstylecommand */




/**
 * @module drupalMedia/drupalelementstyle/drupalelementstyleediting
 */

/**
 * Gets style definition by name.
 *
 * @param {string} name
 *   The name of the style definition.
 * @param styles
 *   The styles to search from.
 * @return {Drupal.CKEditor5~DrupalElementStyle}
 */
function getStyleDefinitionByName(name, styles) {
  // eslint-disable-next-line no-restricted-syntax
  for (const style of styles) {
    if (style.name === name) {
      return style;
    }
  }
}

/**
 * Returns a model-to-view converted for Drupal Element styles.
 *
 * This model to view converter supports downcasting model to either a CSS class
 * or attribute.
 *
 * Note that only one style can be applied to a single model element.
 */
function modelToViewStyleAttribute(styles) {
  return (evt, data, conversionApi) => {
    if (!conversionApi.consumable.consume(data.item, evt.name)) {
      return;
    }

    // Check if there is a style associated with given value.
    const newStyle = getStyleDefinitionByName(data.attributeNewValue, styles);
    const oldStyle = getStyleDefinitionByName(data.attributeOldValue, styles);

    const viewElement = conversionApi.mapper.toViewElement(data.item);
    const viewWriter = conversionApi.writer;

    if (oldStyle) {
      if (oldStyle.attributeName === 'class') {
        viewWriter.removeClass(oldStyle.attributeValue, viewElement);
      } else {
        viewWriter.removeAttribute(oldStyle.attributeName, viewElement);
      }
    }

    if (newStyle) {
      console.log('new style attr name ', newStyle.attributeName);
      if (newStyle.attributeName === 'class') {
        viewWriter.addClass(newStyle.attributeValue, viewElement);
      } else {
        viewWriter.setAttribute(
          newStyle.attributeName,
          newStyle.attributeValue,
          viewElement,
        );
      }
    }
  };
}

/**
 * Returns a view-to-model converter for Drupal Element styles.
 *
 * This view to model converted supports styles that are configured to use
 * either CSS class or an attribute.
 *
 * Note that only one style can be applied to each model element.
 */
function viewToModelStyleAttribute(styles, groupName) {
  // Convert only non–default styles.
  const nonDefaultStyles = styles.filter((style) => !style.isDefault);

  return (evt, data, conversionApi) => {
    if (!data.modelRange) {
      return;
    }

    const viewElement = data.viewItem;
    const modelElement = (0,delegated_utilsfrom_dll_reference_CKEditor5.first)(data.modelRange.getItems());

    // Run this converter only if a model element has been found from the model.
    if (!modelElement) {
      return;
    }

    // Stop conversion early if the drupalElementStyle attribute isn't allowed
    // for the element.
    if (
      !conversionApi.schema.checkAttribute(modelElement, `drupal${groupName}`)
    ) {
      return;
    }

    // Convert styles with CSS classes one by one.
    // eslint-disable-next-line no-restricted-syntax
    for (const style of nonDefaultStyles) {
      // Try to consume class corresponding with the style.
      if (style.attributeName === 'class') {
        if (
          conversionApi.consumable.consume(viewElement, {
            classes: style.attributeValue,
          })
        ) {
          // And convert this style to model attribute.
          conversionApi.writer.setAttribute(
            `drupal${groupName}`,
            style.name,
            modelElement,
          );
        }
      } else if (
        conversionApi.consumable.consume(viewElement, {
          attributes: [style.attributeName],
        })
      ) {
        // eslint-disable-next-line no-restricted-syntax
        for (const style of nonDefaultStyles) {
          if (
            style.attributeValue ===
            viewElement.getAttribute(style.attributeName)
          ) {
            conversionApi.writer.setAttribute(
              `drupal${groupName}`,
              style.name,
              modelElement,
            );
          }
        }
      }
    }
  };
}

/**
 * The Drupal Element Style editing plugin.
 *
 * Additional Drupal Element styles can be defined with `drupalElementStyles`
 * configuration key.
 *
 * Additional Drupal Element styles can support multiple axes (ex. media alignment and media view modes)
 * by adding the new group under the 'options' key.
 *
 * @example
 *    config:
 *      drupalElementStyles:
 *         options:
 *            side:
 *              - name: 'side'
 *                icon: 'objectBlockRight'
 *                title: 'Side image'
 *                attributeName: 'class'
 *                attributeValue: 'image-side'
 *                modelElements: ['drupalMedia']
 *            align:
 *              - name: 'alignRight'
 *                title: 'Right aligned media'
 *                icon: 'objectRight'
 *                attributeName: 'data-align'
 *                modelElements: [ 'drupalMedia' ]
 *            viewMode:
 *              - name: 'View mode A'
 *                title: 'A'
 *                attributeName: 'data-view-mode'
 *                attributeValue: 'A'
 *                modelElements: [ 'drupalMedia' ]
 *
 * @see Drupal.CKEditor5~DrupalElementStyle
 *
 * @extends module:core/plugin~Plugin
 *
 * @internal
 */
class DrupalElementStyleEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
  init() {
    const {editor} = this;

    // Ensure that the drupalElementStyles.options exists always.
    editor.config.define('drupalElementStyles', { options: [] });
    const stylesConfig = editor.config.get('drupalElementStyles').options;

    /**
     * The Drupal Element Styles.
     *
     * @typedef {Object} Drupal.CKEditor5~DrupalElementStyle
     *
     * @prop {string} name
     *   The name of the style used for identifying the button.
     * @prop {string} title
     *   The title of the style displayed in the UI.
     * @prop {string} attributeName
     *   The name of the attribute in view.
     * @prop {string} attributeValue
     *   The value of the attribute in view.
     * @prop {string[]} modelElements
     *   A list of model elements that the style can be attached to.
     * @prop {string} [icon]
     *   An icon for the style button. This needs to either refer to an icon in
     *   the CKEditor 5 core icons, or this can be the XML content of the icon.
     *
     * @type {Drupal.CKEditor5~DrupalElementStyle[]}
     */
    Object.keys(stylesConfig)
      .map((group) => {
        stylesConfig[group] // array of styles
          .map((style) => {
            // Allow defining style icon as a string that is referring to the
            // CKEditor 5 default icons.
            if (typeof style.icon === 'string') {
              if (delegated_corefrom_dll_reference_CKEditor5.icons[style.icon]) {
                style.icon = delegated_corefrom_dll_reference_CKEditor5.icons[style.icon];
              }
            }
            return style;
          })
          .filter((style) => {
            if (!style.attributeName || !style.attributeValue) {
              console.warn(
                'drupalElementStyles options must include attributeName and attributeValue.',
              );
              return false;
            }
            if (!style.modelElements || !Array.isArray(style.modelElements)) {
              console.warn(
                'drupalElementStyles options must include an array of supported modelElements.',
              );
              return false;
            }

            if (!style.name) {
              console.warn('drupalElementStyles options must include a name.');
              return false;
            }

            return true;
          });
      })
      .filter(Boolean);
    this.normalizedStyles = stylesConfig;
    console.log('norm: ', this.normalizedStyles);

    this._setupConversion();

    editor.commands.add(
      'drupalElementStyle',
      new DrupalElementStyleCommand(editor, this.normalizedStyles),
    );
  }

  /**
   * Sets up conversion for Drupal Element Styles.
   *
   * @see modelToViewStyleAttribute()
   * @see viewToModelStyleAttribute()
   *
   * @private
   */
  _setupConversion() {
    const {editor} = this;
    const {schema} = editor.model;

    const groupNamesArr = Object.keys(this.normalizedStyles);

    for (let i = 0; i < groupNamesArr.length; i++) {
      // Capitalize first letter for attribute naming purposes.
      const group = groupNamesArr[i];
      console.log('group', group);
      const groupName = group[0].toUpperCase() + group.substring(1);

      const modelToViewConverter = modelToViewStyleAttribute(
        this.normalizedStyles[group],
      );
      const viewToModelConverter = viewToModelStyleAttribute(
        this.normalizedStyles[group],
        groupName,
      );

      // loop thru group here and do separately
      // use group name to generate attribute
      editor.editing.downcastDispatcher.on(
        `attribute:drupal${groupName}`,
        modelToViewConverter,
      );
      editor.data.downcastDispatcher.on(
        `attribute:drupal${groupName}`,
        modelToViewConverter,
      );

      // Allow drupalElementStyle on all model elements that have associated
      // styles.
      const modelElements = [
        ...new Set(
          this.normalizedStyles[group]
            .map((style) => {
              return style.modelElements;
            })
            .flat(),
        ),
      ];
      modelElements.forEach((modelElement) => {
        schema.extend(modelElement, { allowAttributes: `drupal${groupName}` });
      });
      // View to model converter that runs on all elements.
      editor.data.upcastDispatcher.on(
        'element',
        viewToModelConverter,
        // This needs to be set as low priority to ensure this runs always after
        // the element has been converted to a model element.
        {priority: 'low'},
      );
    }
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalElementStyleEditing';
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalelementstyle/drupalelementstyleui.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalelementstyleediting splitbutton imagestyle componentfactory */







/**
 * @module drupalMedia/drupalelementstyle/drupalelementstyleui
 */

/**
 * Returns the first argument it receives.
 *
 * @param {*} value
 *   Any value to be returned by this function.
 * @return {*}
 *   Any value passed as the first argument.
 */
const identity = (value) => {
  return value;
};

/**
 * Gets the dropdown title.
 *
 * @param {string} dropdownTitle
 *   The dropdown title.
 * @param {string} buttonTitle
 *   The button title.
 * @return {string}
 *   The generated dropdown title.
 */
const getDropdownButtonTitle = (dropdownTitle, buttonTitle) => {
  return (dropdownTitle ? `${dropdownTitle}: ` : '') + buttonTitle;
};

/**
 * Gets the UI Component name.
 *
 * This is used for getting unique component names for registering the UI
 * components in the component factory.
 *
 * @param {string} name
 *   The name of the component.
 * @return {string}
 *   The UI component name.
 *
 * @see module:ui/componentfactory~ComponentFactory
 */
function getUIComponentName(name) {
  console.log(`name, ${name}`);
  // todo: change this to take group too
  return `drupalElementStyle:align:${name}`;
}

/**
 * The Drupal Element Style UI plugin.
 *
 * @extends module:core/plugin~Plugin
 *
 * @internal
 */
class DrupalElementStyleUi extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalElementStyleEditing];
  }

  /**
   * @inheritDoc
   */
  init() {
    const { plugins } = this.editor;
    const toolbarConfig = this.editor.config.get('drupalMedia.toolbar') || [];

    // update on group basis
    const definedStyles = Object.values(
      plugins.get('DrupalElementStyleEditing').normalizedStyles,
    );
    console.log('definedStyles: ', definedStyles);

    definedStyles.forEach((group) => {
      // todo:.normalizedStyles returns array of arrays without 'align' or 'viewMode' key so need to fix hardcode
      if (group[0].name.includes('align')) {
        group.forEach((styleConfig) => {
          this._createButton(styleConfig);
        });
      } else {
        console.log('create buttons for list dropdown');
      }
    });
    // loop thru the dropdowns and if its a list, create a collection of buttons out of all the options
    // separate process for list dropdown

    // update this documentation to have the new display
    /**
     * A Drupal Element Style dropdown definition.
     *
     * @example
     *    config:
     *       drupalMedia:
     *         toolbar:
     *           - name: 'drupalMedia:alignment'
     *             title: 'Custom title for the dropdown'
     *             items:
     *               - 'drupalElementStyle:alignLeft'
     *               - 'drupalElementStyle:alignCenter'
     *               - 'drupalElementStyle:alignRight'
     *             defaultItem: 'drupalElementStyle:alignCenter'
     *
     * @typedef {Object} Drupal.CKEditor5~drupalElementStyleDropdownDefinition
     *
     * @prop {string} name
     *   The name of the dropdown used for identifying the dropdown.
     * @prop {string[]} items
     *   The items displayed in the dropdown. These must be styles defined in
     *   `drupalElementStyles.options`.
     * @prop {string} defaultItem
     *   The default item of the dropdown. This must be a style defined in
     *   `drupalElementStyles.options`.
     * @prop {string} [title]
     *   The title of the dropdown.
     *
     * @see module:drupalMedia/drupalelementstyle/drupalelementstyleediting:DrupalElementStyleEditing
     */
    const definedDropdowns = toolbarConfig.filter(isObject);
    const definedStylesByGroup = [];

    definedDropdowns.forEach((dropdownConfig) => {
      if (dropdownConfig.display === 'toolbar') {
        definedStyles.forEach((group) => {
          // todo: this needs to change to not just check align
          if (group[0].name.includes('align')) {
            // definedStylesByGroup.push(group);
            // console.log('definedStylesByGroup ', definedStylesByGroup);
            // todo: fix hardcord 0 position
            this._createDropdown(dropdownConfig, group);
          }
        });
      } else {
        console.log('create list dropdown');
      }
    });
  }

  /**
   * Creates a dropdown and stores it in the component factory.
   *
   * @param {Drupal.CKEditor5~drupalElementStyleDropdownDefinition} dropdownConfig
   *   The dropdown configuration.
   * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
   *   A list of defined styles.
   *
   * @see module:ui/componentfactory~ComponentFactory
   *
   * @private
   */
  _createDropdown(dropdownConfig, definedStyles) {
    console.log('dropdownConfig, ', dropdownConfig);
    console.log('definedStyles, ', definedStyles);
    // make sure definedstyles to reading from the correct group
    const factory = this.editor.ui.componentFactory;

    factory.add(dropdownConfig.name, (locale) => {
      let defaultButton;
      console.log('peeeeeee');

      const { defaultItem, items, title } = dropdownConfig;
      console.log('dropdownConfig.items ', dropdownConfig.items);
      const buttonViews = items.filter((itemName) => {
          // const splitItemName = itemName.split(':');
          // console.log('split, ', splitItemName[2]);
          return definedStyles.find(
            ({ name }) => getUIComponentName(name) === itemName,
          );
        })
        .map((buttonName) => {
          console.log('buttonName: ', buttonName);
          const button = factory.create(buttonName);

          if (buttonName === defaultItem) {
            defaultButton = button;
          }

          return button;
        });

      if (items.length !== buttonViews.length) {
        utils.warnInvalidStyle({ dropdown: dropdownConfig });
      }

      const dropdownView = (0,delegated_uifrom_dll_reference_CKEditor5.createDropdown)(locale, delegated_uifrom_dll_reference_CKEditor5.SplitButtonView);
      const splitButtonView = dropdownView.buttonView;

      (0,delegated_uifrom_dll_reference_CKEditor5.addToolbarToDropdown)(dropdownView, buttonViews);

      splitButtonView.set({
        label: getDropdownButtonTitle(title, defaultButton.label),
        class: null,
        tooltip: true,
      });

      // If style is selected, show the currently selected style as the default
      // button of the split button.
      splitButtonView.bind('icon').toMany(buttonViews, 'isOn', (...areOn) => {
        const index = areOn.findIndex(identity);

        return index < 0 ? defaultButton.icon : buttonViews[index].icon;
      });

      // If style is selected, use the label of the selected style as the
      // default label of the split button.
      splitButtonView.bind('label').toMany(buttonViews, 'isOn', (...areOn) => {
        const index = areOn.findIndex(identity);

        return getDropdownButtonTitle(
          title,
          index < 0 ? defaultButton.label : buttonViews[index].label,
        );
      });

      // If one of the style is selected, render the split button as selected.
      splitButtonView
        .bind('isOn')
        .toMany(buttonViews, 'isOn', (...areOn) => areOn.some(identity));

      // If one of the styles is selected, add a CSS class to the split button
      // which modifies the styles to indicate that the splitbutton default
      // option is currently selected.
      splitButtonView
        .bind('class')
        .toMany(buttonViews, 'isOn', (...areOn) =>
          areOn.some(identity) ? 'ck-splitbutton_flatten' : null,
        );

      splitButtonView.on('execute', () => {
        if (!buttonViews.some(({ isOn }) => isOn)) {
          defaultButton.fire('execute');
        } else {
          dropdownView.isOpen = !dropdownView.isOpen;
        }
      });

      dropdownView
        .bind('isEnabled')
        .toMany(buttonViews, 'isEnabled', (...areEnabled) =>
          areEnabled.some(identity),
        );

      return dropdownView;
    });
  }

  /**
   * Creates a button and stores it in the editor component factory.
   *
   * @param {Drupal.CKEditor5~DrupalElementStyle} buttonConfig
   *   The button configuration.
   *
   * @see module:ui/componentfactory~ComponentFactory
   *
   * @private
   */
  _createButton(buttonConfig) {
    const buttonName = buttonConfig.name;

    this.editor.ui.componentFactory.add(
      getUIComponentName(buttonName),
      (locale) => {
        // change to take account of groups
        const command = this.editor.commands.get('drupalElementStyle');
        console.log('command ', command);
        const view = new delegated_uifrom_dll_reference_CKEditor5.ButtonView(locale);

        view.set({
          label: buttonConfig.title,
          icon: buttonConfig.icon,
          tooltip: true,
          isToggleable: true,
        });

        view.bind('isEnabled').to(command, 'isEnabled');
        view.bind('isOn').to(command, 'value', (value) => value === buttonName);
        const group = '';
        console.log('buttonName', buttonName);
        view.on('execute', this._executeCommand.bind(this, buttonName, group));

        return view;
      },
    );
  }

  /**
   * Executes the Drupal Element Style command.
   *
   * @param {string} name
   *   The name of the style that should be applied.
   *
   * @see module:drupalMedia/drupalelementstyle/drupalelementstylecommand~DrupalElementStyleCommand
   *
   * @private
   */
  _executeCommand(name, group) {
    const key = 'drupalAlign';
    const obj = {};
    obj[key] = name;
    console.log(obj);
    this.editor.execute('drupalElementStyle', { value: obj });
    this.editor.editing.view.focus();
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalElementStyleUi';
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalelementstyle.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalelementstyle drupalelementstyleui drupalelementstyleediting imagestyle drupalmediatoolbar drupalmediaediting */




/**
 * @module drupalMedia/drupalelementstyle
 */

/**
 * The Drupal Element Style plugin.
 *
 * This plugin is internal and it is currently only used for providing
 * `data-align` support to `<drupal-media>`. However, this plugin isn't tightly
 * coupled to `<drupal-media>` or `data-align`. The intent is to make this
 * plugin a starting point for adding `data-align` support to other elements,
 * because the `FilterAlign` filter plugin PHP code also does not limit itself
 * to a specific HTML element. This could be also used for other filters to
 * provide same authoring experience as `FilterAlign` without the need for
 * additional JavaScript code.
 *
 * To be able to change element styles in the UI, the model element needs to
 * have a toolbar where the element style buttons can be displayed.
 *
 * This plugin is inspired by the CKEditor 5 Image Style plugin.
 *
 * @see module:image/imagestyle~ImageStyle
 * @see core/modules/ckeditor5/css/media-alignment.css
 * @see module:drupalMedia/drupalmediaediting~DrupalMediaEditing
 * @see module:drupalMedia/drupalmediatoolbar~DrupalMediaToolbar
 *
 * @internal
 */
class DrupalElementStyle extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [DrupalElementStyleEditing, DrupalElementStyleUi];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalElementStyle';
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/index.js
/* eslint-disable import/no-extraneous-dependencies */
// cspell:ignore mediaimagetextalternative



// cspell:ignore drupallinkmedia


// cspell:ignore drupalelementstyle


// cspell:ignore mediaimagetextalternative




/**
 * @internal
 */
/* harmony default export */ var src = ({
  DrupalMedia: DrupalMedia,
  MediaImageTextAlternative: MediaImageTextAlternative,
  MediaImageTextAlternativeEditing: MediaImageTextAlternativeEditing,
  MediaImageTextAlternativeUi: MediaImageTextAlternativeUi,
  DrupalLinkMedia: DrupalLinkMedia,
  DrupalElementStyle: DrupalElementStyle,
});

}();
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});