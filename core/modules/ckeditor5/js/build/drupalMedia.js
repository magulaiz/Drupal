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

/***/ 704:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/core.js");

/***/ }),

/***/ 273:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/ui.js");

/***/ }),

/***/ 209:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/utils.js");

/***/ }),

/***/ 995:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/widget.js");

/***/ }),

/***/ 79:
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
var delegated_corefrom_dll_reference_CKEditor5 = __webpack_require__(704);
// EXTERNAL MODULE: delegated ./widget.js from dll-reference CKEditor5.dll
var delegated_widgetfrom_dll_reference_CKEditor5 = __webpack_require__(995);
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

    this.editor.model.change((writer) => {
      this.editor.model.insertContent(
        createDrupalMedia(writer, modelAttributes),
      );
    });
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

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediametadatarepository.js


class DrupalMediaMetadataRepository extends delegated_corefrom_dll_reference_CKEditor5.Plugin {

  init() {
    this._data = new WeakMap();
  }

  async _fetchMetadata(url, query) {
    // The `isMediaUrl` received from the server is guaranteed to already have
    // a query string (for the CSRF token).
    // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Media::getDynamicPluginConfig()
    const response = await fetch(`${url}&${query}`);
    if (response.ok) {
      return JSON.parse(await response.text());
    }

    return { label: this.labelError, preview: this.themeError };
  }

  getMetadata(modelElement) {
    if (this._data.get(modelElement)) {
      return new Promise((resolve) => {
        resolve(this._data.get(modelElement));
      });
    }

    debugger;
    const options = this.editor.config.get('drupalMedia');
    if (!options) {
      return;
    }

    if (!modelElement.hasAttribute('drupalMediaEntityUuid')) {
      return;
    }

    const { mediaEntityMetadataUrl } = options;
    const query = new URLSearchParams({
      uuid: modelElement.getAttribute('drupalMediaEntityUuid'),
    });

    return this._fetchMetadata(mediaEntityMetadataUrl, query).then((metadata) => {
      this._data.set(modelElement, metadata);
      return metadata;
    });
  }

  /**
   * {inheritDoc}
   */
  static get pluginName() {
    return 'DrupalMediaMetadataRepository';
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

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediaediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words insertdrupalmedia */








/**
 * @internal
 */
class DrupalMediaEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  static get requires() {
    return [delegated_widgetfrom_dll_reference_CKEditor5.Widget, DrupalMediaMetadataRepository];
  }

  init() {
    this.attrs = {
      drupalMediaAlt: 'alt',
      drupalMediaAlign: 'data-align',
      drupalMediaCaption: 'data-caption',
      drupalMediaEntityType: 'data-entity-type',
      drupalMediaEntityUuid: 'data-entity-uuid',
      drupalMediaViewMode: 'data-view-mode',
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
    const metadataRepository = this.editor.plugins.get('DrupalMediaMetadataRepository');

    conversion.for('upcast')
      .elementToElement({
        view: {
          name: 'drupal-media',
        },
        model: 'drupalMedia',
      })
      .add((dispatcher) => {
        // @todo decied if we want to pre-fetch metadata for all drupalMedia elements?
        return dispatcher.on('element:drupal-media', (event, data, conversionApi) => {
          const [modelElement] = data.modelRange.getItems();
          if (!isDrupalMedia(modelElement)) {
            return;
          }

          // Pre-fetch metadata for all drupalMedia elements.
          // @todo what should we do in case an error happens?
          metadataRepository.getMetadata(modelElement);
        }, { priority: 'lowest' });
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
var delegated_uifrom_dll_reference_CKEditor5 = __webpack_require__(273);
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

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediatoolbar.js
/* eslint-disable import/no-extraneous-dependencies */





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
    const editor = this.editor;
    const widgetToolbarRepository = editor.plugins.get(delegated_widgetfrom_dll_reference_CKEditor5.WidgetToolbarRepository);

    widgetToolbarRepository.register('drupalMedia', {
      ariaLabel: Drupal.t('Drupal Media toolbar'),
      items: editor.config.get('drupalMedia.toolbar') || [],
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
    const metadataRepository = this.editor.plugins.get('DrupalMediaMetadataRepository');

    this.isEnabled = isDrupalMedia(element) && element.hasAttribute('drupalMediaAlt');
    if (isDrupalMedia(element)) {
      metadataRepository.getMetadata(element).then(({ imageMetadata }) => {
        this.isEnabled = !!imageMetadata;
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

}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative/mediaimagetextalternativeediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words mediaimagetextalternativecommand textalternativeformview */





/**
 * The media image text alternative editing plugin.
 */
class MediaImageTextAlternativeEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {

  static get requires() {
    return [DrupalMediaMetadataRepository];
  }

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
var delegated_utilsfrom_dll_reference_CKEditor5 = __webpack_require__(209);
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
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
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
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
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

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/index.js
/* eslint-disable import/no-extraneous-dependencies */
// cspell:ignore mediaimagetextalternative



// cspell:ignore drupallinkmedia


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
});

}();
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});