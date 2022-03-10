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

/***/ 492:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/engine.js");

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
// cSpell:words insertdrupalmediacommand


/**
 * @module drupalMedia/insertdrupalmediacommand
 */

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

      const { normalizedStyles } = elementStyleEditing;
      Object.keys(normalizedStyles).forEach((group) => {
        elementStyleEditing.normalizedStyles[group].forEach((style) => {
          if (
            attributes[style.attributeName] &&
            style.attributeValue === attributes[style.attributeName]
          ) {
            modelAttributes.drupalElementStyle = style.name;
          }
        });
      });

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

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/utils.js
/* eslint-disable import/no-extraneous-dependencies */
// cSpell:words documentselection


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
 * Gets `drupalMedia` element from selection.
 *
 * @param {module:engine/model/selection~Selection|module:engine/model/documentselection~DocumentSelection} selection
 *   The current selection.
 * @returns {module:engine/model/element~Element|null}
 *   The `drupalMedia` element which could be either the current selected an
 *   ancestor of the selection. Returns null if the selection has no Drupal
 *   Media element.
 *
 * @internal
 */
function getClosestSelectedDrupalMediaElement(selection) {
  const selectedElement = selection.getSelectedElement();

  return isDrupalMedia(selectedElement)
    ? selectedElement
    : selection.getFirstPosition().findAncestor('drupalMedia');
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
function getClosestSelectedDrupalMediaWidget(selection) {
  const viewElement = selection.getSelectedElement();
  if (viewElement && isDrupalMediaWidget(viewElement)) {
    return viewElement;
  }

  let parent = selection.getFirstPosition().parent;

  while (parent) {
    if (parent.is('element') && isDrupalMediaWidget(parent)) {
      return parent;
    }

    parent = parent.parent;
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

/**
 * Gets preview container element from the media element.
 *
 * @param {Iterable.<module:engine/view/element~Element>} children
 *   The child elements.
 * @return {null|module:engine/view/element~Element}
 *   The preview child element if available.
 */
function getPreviewContainer(children) {
  // eslint-disable-next-line no-restricted-syntax
  for (const child of children) {
    if (child.hasAttribute('data-drupal-media-preview')) {
      return child;
    }

    if (child.childCount) {
      const recursive = getPreviewContainer(child.getChildren());
      // Return only if preview container was found within this element's
      // children.
      if (recursive) {
        return recursive;
      }
    }
  }

  return null;
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
      drupalMediaEntityType: 'data-entity-type',
      drupalMediaBundle: null,
      drupalMediaEntityUuid: 'data-entity-uuid',
      drupalElementStyleViewMode: 'data-view-mode',
    };
    const options = this.editor.config.get('drupalMedia');
    if (!options) {
      return;
    }
    const { previewURL, themeError } = options;
    this.previewUrl = previewURL;
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

  /**
   * Fetches preview from the server.
   *
   * @param {module:engine/model/element~Element} modelElement
   *   The model element which preview should be loaded.
   * @return {Promise<{preview: string, label: string}>}
   *   A promise that returns an object.
   *
   * @private
   */
  async _fetchPreview(modelElement) {
    const query = {
      text: this._renderElement(modelElement),
      uuid: modelElement.getAttribute('drupalMediaEntityUuid'),
    };

    const response = await fetch(
      `${this.previewUrl}?${new URLSearchParams(query)}`,
      {
        headers: {
          'X-Drupal-MediaPreview-CSRF-Token':
            this.editor.config.get('drupalMedia').previewCsrfToken,
        },
      },
    );
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
    const metadataRepository = this.editor.plugins.get(
      'DrupalMediaMetadataRepository',
    );

    conversion
      .for('upcast')
      .elementToElement({
        view: {
          name: 'drupal-media',
        },
        model: 'drupalMedia',
      })
      .add((dispatcher) => {
        dispatcher.on(
          'element:drupal-media',
          (evt, data) => {
            const [modelElement] = data.modelRange.getItems();
            if (!isDrupalMedia(modelElement)) {
              return;
            }
            metadataRepository
              .getMetadata(modelElement)
              .then((metadata) => {
                if (!modelElement) {
                  return;
                }
                // Enqueue a model change after getting modelElement.
                this.editor.model.enqueueChange('transparent', (writer) => {
                  writer.setAttribute(
                    'drupalMediaBundle',
                    metadata.bundleType,
                    modelElement,
                  );
                });
              })
              .catch((e) => {
                // There isn't any UI indication for errors because this should be
                // always called after the Drupal Media has been upcast, which would
                // already display an error in the UI.
                console.warn(e.toString());
              });
          },
          // This converter needs to have the lowest priority to ensure that the
          // model element and its attributes have been converted.
          { priority: 'lowest' },
        );
      });

    conversion.for('dataDowncast').elementToElement({
      model: 'drupalMedia',
      view: {
        name: 'drupal-media',
      },
    });
    conversion
      .for('editingDowncast')
      .elementToElement({
        model: 'drupalMedia',
        view: (modelElement, { writer }) => {
          const container = writer.createContainerElement('figure', {
            class: 'drupal-media',
          });
          if (!this.previewUrl) {
            // If preview URL isn't available, insert empty preview element
            // which indicates that preview couldn't be loaded.
            const mediaPreview = writer.createRawElement('div', {
              'data-drupal-media-preview': 'unavailable',
            });
            writer.insert(writer.createPositionAt(container, 0), mediaPreview);
          }
          writer.setCustomProperty('drupalMedia', true, container);

          return (0,delegated_widgetfrom_dll_reference_CKEditor5.toWidget)(container, writer, {
            label: Drupal.t('Media widget'),
          });
        },
      })
      .add((dispatcher) => {
        const converter = (event, data, conversionApi) => {
          const viewWriter = conversionApi.writer;
          const modelElement = data.item;
          const container = conversionApi.mapper.toViewElement(data.item);

          // Search for preview container recursively from its children because
          // the preview container could be wrapped with an element such as
          // `<a>`.
          let media = getPreviewContainer(container.getChildren());

          // Use pre-existing media preview container if one exists. If the
          // preview element doesn't exist, create a new element.
          if (media) {
            // Stop processing if media preview is unavailable or a preview is
            // already loading.
            if (media.getAttribute('data-drupal-media-preview') !== 'ready') {
              return;
            }

            // Preview was ready meaning that a new preview can be loaded.
            // "Change the attribute to loading to prepare for the loading of
            // the updated preview. Preview is kept intact so that it remains
            // interactable in the UI until the new preview has been rendered.
            viewWriter.setAttribute(
              'data-drupal-media-preview',
              'loading',
              media,
            );
          } else {
            media = viewWriter.createRawElement('div', {
              'data-drupal-media-preview': 'loading',
            });
            viewWriter.insert(viewWriter.createPositionAt(container, 0), media);
          }

          this._fetchPreview(modelElement).then(({ label, preview }) => {
            if (!media) {
              // Nothing to do if associated preview wrapped no longer exist.
              return;
            }
            // CKEditor 5 doesn't support async view conversion. Therefore, once
            // the promise is fulfilled, the editing view needs to be modified
            // manually.
            this.editor.editing.view.change((writer) => {
              const mediaPreview = writer.createRawElement(
                'div',
                { 'data-drupal-media-preview': 'ready', 'aria-label': label },
                (domElement) => {
                  domElement.innerHTML = preview;
                },
              );
              // Insert the new preview before the previous preview element to
              // ensure that the location remains same even if it is wrapped
              // with another element.
              writer.insert(writer.createPositionBefore(media), mediaPreview);
              writer.remove(media);
            });
          });
        };

        // List all attributes that should trigger re-rendering of the
        // preview.
        dispatcher.on('attribute:drupalMediaEntityUuid:drupalMedia', converter);
        dispatcher.on(
          'attribute:drupalElementStyleViewMode:drupalMedia',
          converter,
        );
        dispatcher.on('attribute:drupalMediaEntityType:drupalMedia', converter);
        dispatcher.on('attribute:drupalMediaAlt:drupalMedia', converter);

        return dispatcher;
      });

    conversion.for('editingDowncast').add((dispatcher) => {
      dispatcher.on(
        'attribute:drupalElementStyleAlign:drupalMedia',
        (evt, data, conversionApi) => {
          const alignMapping = {
            // This is a map of CSS classes representing Drupal element styles for alignments.
            alignLeft: 'drupal-media-style-align-left',
            alignRight: 'drupal-media-style-align-right',
            alignCenter: 'drupal-media-style-align-center',
          };
          const viewElement = conversionApi.mapper.toViewElement(data.item);
          const viewWriter = conversionApi.writer;

          // If the prior value is alignment related, it should be removed
          // whether or not the module property is consumed.
          if (alignMapping[data.attributeOldValue]) {
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
      // Omit drupalMediaBundle from downcast because it is unnecessary for the view.
      if (modelKey !== 'drupalMediaBundle') {
        const attributeMapping = {
          model: {
            key: modelKey,
            name: 'drupalMedia',
          },
          view: {
            name: 'drupal-media',
            key: this.attrs[modelKey],
          },
        };
        // Attributes should be rendered only in dataDowncast to avoid having
        // unfiltered data-attributes on the Drupal Media widget.
        conversion.for('dataDowncast').attributeToAttribute(attributeMapping);
        conversion.for('upcast').attributeToAttribute(attributeMapping);
      }
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
      getRelatedElement: (selection) =>
        getClosestSelectedDrupalMediaWidget(selection),
    });
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative/utils.js
/* eslint-disable import/prefer-default-export */
/**
 * Used for indicating metadata errors in model.
 *
 * @type {string}
 *
 * @see \Drupal\ckeditor5\Controller\CKEditor5MediaController
 */
const METADATA_ERROR = 'METADATA_ERROR';

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
    const drupalMediaElement = getClosestSelectedDrupalMediaElement(
      this.editor.model.document.selection,
    );
    this.isEnabled =
      !!drupalMediaElement &&
      drupalMediaElement.getAttribute('drupalMediaIsImage') &&
      drupalMediaElement.getAttribute('drupalMediaIsImage') !== METADATA_ERROR;

    if (this.isEnabled) {
      this.value = drupalMediaElement.getAttribute('drupalMediaAlt');
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
    const drupalMediaElement = getClosestSelectedDrupalMediaElement(
      model.document.selection,
    );

    options.newValue = options.newValue.trim();
    model.change((writer) => {
      if (options.newValue.length > 0) {
        writer.setAttribute(
          'drupalMediaAlt',
          options.newValue,
          drupalMediaElement,
        );
      } else {
        writer.removeAttribute('drupalMediaAlt', drupalMediaElement);
      }
    });
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediametadatarepository.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediametadatarepository */



/**
 * @module drupalMedia/drupalmediametadatarepository
 */

/**
 * Fetch metadata from the backend.
 *
 * @param {string} url
 *   The URL used for retrieving the metadata.
 * @return {Promise<Object>}
 *   Promise containing response content.
 *
 * @private
 */
const _fetchMetadata = async (url) => {
  const response = await fetch(url);
  if (response.ok) {
    return JSON.parse(await response.text());
  }

  throw new Error('Fetching media embed metadata from the server failed.');
};

/**
 * @internal
 */
class DrupalMediaMetadataRepository extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  init() {
    this._data = new WeakMap();
  }

  /**
   * Gets metadata for `drupalMedia` model element.
   *
   * @param {module:engine/model/element~Element} modelElement
   *   The model element which metadata should be retrieved.
   * @return {Promise<Object>}
   */
  getMetadata(modelElement) {
    // If metadata was retrieved earlier for the model element, return the
    // cached value.
    if (this._data.get(modelElement)) {
      return new Promise((resolve) => {
        resolve(this._data.get(modelElement));
      });
    }

    const options = this.editor.config.get('drupalMedia');
    if (!options) {
      return new Promise((resolve, reject) => {
        reject(
          new Error(
            'drupalMedia configuration is required for parsing metadata.',
          ),
        );
      });
    }

    if (!modelElement.hasAttribute('drupalMediaEntityUuid')) {
      return new Promise((resolve, reject) => {
        reject(
          new Error(
            'drupalMedia element must have drupalMediaEntityUuid attribute to retrieve metadata.',
          ),
        );
      });
    }

    const { metadataUrl } = options;
    const query = new URLSearchParams({
      uuid: modelElement.getAttribute('drupalMediaEntityUuid'),
    });
    // The `metadataUrl` received from the server already includes a query
    // string (for the CSRF token).
    // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Media::getDynamicPluginConfig()
    const url = `${metadataUrl}&${query}`;

    return _fetchMetadata(url).then((metadata) => {
      this._data.set(modelElement, metadata);
      return metadata;
    });
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMediaMetadataRepository';
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/mediaimagetextalternative/mediaimagetextalternativeediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words mediaimagetextalternativecommand drupalmediametadatarepository insertdrupalmediacommand */








/**
 * @module drupalMedia/mediaimagetextalternative/mediaimagetextalternativeediting
 */

/**
 * The media image text alternative editing plugin.
 */
class MediaImageTextAlternativeEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
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
   * Upcasts `drupalMediaIsImage` from Drupal Media metadata.
   *
   * @param {module:engine/model/node~Node} modelElement
   *   The `drupalMedia` model element.
   *
   * @see module:drupalMedia/drupalmediametadatarepository~DrupalMediaMetadataRepository
   *
   * @private
   */
  _upcastDrupalMediaIsImage(modelElement) {
    const { model, plugins } = this.editor;
    const metadataRepository = plugins.get('DrupalMediaMetadataRepository');

    // Get all metadata for drupalMedia elements to set value for
    // drupalMediaIsImage attribute. When other plugins start using the
    // metadata, this functionality will be handled more generically.
    metadataRepository
      .getMetadata(modelElement)
      .then((metadata) => {
        if (!modelElement) {
          // Nothing to do if model element has been removed before
          // promise was resolved.
          return;
        }
        // Enqueue a model change that is not visible to the undo/redo feature.
        model.enqueueChange({ isUndoable: false }, (writer) => {
          writer.setAttribute(
            'drupalMediaIsImage',
            !!metadata.imageSourceMetadata,
            modelElement,
          );
        });
      })
      .catch((e) => {
        if (!modelElement) {
          // Nothing to do if model element has been removed before
          // promise was resolved.
          return;
        }
        console.warn(e.toString());
        model.enqueueChange({ isUndoable: false }, (writer) => {
          writer.setAttribute(
            'drupalMediaIsImage',
            METADATA_ERROR,
            modelElement,
          );
        });
      });
  }

  /**
   * @inheritDoc
   */
  init() {
    const {
      editor,
      editor: { model, conversion },
    } = this;

    model.schema.extend('drupalMedia', {
      allowAttributes: ['drupalMediaIsImage'],
    });

    // Listen to `insertContent` event on the model to set `drupalMediaIsImage`
    // attribute when `drupalMedia` model element is inserted directly to the
    // model.
    // @see module:drupalMedia/insertdrupalmediacommand~InsertDrupalMediaCommand
    this.listenTo(model, 'insertContent', (evt, [modelElement]) => {
      if (!isDrupalMedia(modelElement)) {
        return;
      }

      this._upcastDrupalMediaIsImage(modelElement);
    });

    // On upcast, get `drupalMediaIsImage` attribute value from media metadata
    // repository.
    conversion.for('upcast').add((dispatcher) => {
      dispatcher.on(
        'element:drupal-media',
        (event, data) => {
          const [modelElement] = data.modelRange.getItems();
          if (!isDrupalMedia(modelElement)) {
            return;
          }

          this._upcastDrupalMediaIsImage(modelElement);
        },
        // This converter needs to have the lowest priority to ensure that the
        // model element and its attributes have been converted.
        { priority: 'lowest' },
      );
    });

    // Display error in the editor if fetching Drupal Media metadata failed.
    conversion.for('editingDowncast').add((dispatcher) => {
      dispatcher.on(
        'attribute:drupalMediaIsImage',
        (event, data, conversionApi) => {
          const { writer, mapper } = conversionApi;
          const container = mapper.toViewElement(data.item);

          if (data.attributeNewValue !== METADATA_ERROR) {
            const existingError = Array.from(container.getChildren()).find(
              (child) => child.getCustomProperty('drupalMediaMetadataError'),
            );
            // If the view contains an existing error, it should be removed
            // since retrieving metadata was successful.
            if (existingError) {
              writer.setCustomProperty(
                'widgetLabel',
                existingError.getCustomProperty(
                  'drupalMediaOriginalWidgetLabel',
                ),
                existingError,
              );
              writer.removeElement(existingError);
            }

            return;
          }

          const message = Drupal.t(
            'Not all functionality may be available because some information could not be retrieved.',
          );

          const tooltip = new delegated_uifrom_dll_reference_CKEditor5.TooltipView();
          tooltip.text = message;
          tooltip.position = 'sw';

          const html = new delegated_uifrom_dll_reference_CKEditor5.Template({
            tag: 'span',
            children: [
              {
                tag: 'span',
                attributes: {
                  class: 'drupal-media__metadata-error-icon',
                },
              },
              tooltip,
            ],
          }).render();

          const error = writer.createRawElement(
            'div',
            {
              class: 'drupal-media__metadata-error',
            },
            (domElement, domConverter) => {
              domConverter.setContentOf(domElement, html.outerHTML);
            },
          );
          writer.setCustomProperty('drupalMediaMetadataError', true, error);

          // Edit widget label to ensure the current status of media embed is
          // available for screen reader users.
          const originalWidgetLabel =
            container.getCustomProperty('widgetLabel');
          writer.setCustomProperty(
            'drupalMediaOriginalWidgetLabel',
            originalWidgetLabel,
            error,
          );
          writer.setCustomProperty(
            'widgetLabel',
            `${originalWidgetLabel} (${message})`,
            container,
          );

          writer.insert(writer.createPositionAt(container, 0), error);
        },
        { priority: 'low' },
      );
    });

    editor.commands.add(
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

  if (
    getClosestSelectedDrupalMediaWidget(editor.editing.view.document.selection)
  ) {
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
     * The default alt text.
     *
     * @observable
     *
     * @member {string} #defaultAltText
     */
    this.set('defaultAltText', undefined);

    /**
     * The default alt text view.
     *
     * @type {module:ui/template~Template}
     */
    this.defaultAltTextView = this._createDefaultAltTextView();

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
        class: ['ck', 'ck-media-alternative-text-form', 'ck-vertical-form'],
        tabindex: '-1',
      },

      children: [
        this.defaultAltTextView,
        this.labeledInput,
        this.saveButtonView,
        this.cancelButtonView,
      ],
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

    labeledInput.label = Drupal.t('Alternative text override');

    return labeledInput;
  }

  /**
   * Creates a default alt text view.
   *
   * @return {module:ui/template~Template}
   *   A template for default alt text view.
   * @private
   */
  _createDefaultAltTextView() {
    const bind = delegated_uifrom_dll_reference_CKEditor5.Template.bind(this, this);
    return new delegated_uifrom_dll_reference_CKEditor5.Template({
      tag: 'div',
      attributes: {
        class: [
          'ck-media-alternative-text-form__default-alt-text',
          bind.if('defaultAltText', 'ck-hidden', (value) => !value),
        ],
      },
      children: [
        {
          tag: 'strong',
          attributes: {
            class: 'ck-media-alternative-text-form__default-alt-text-label',
          },
          children: [Drupal.t('Default alternative text:')],
        },
        ' ',
        {
          tag: 'span',
          attributes: {
            class: 'ck-media-alternative-text-form__default-alt-text-value',
          },
          children: [
            {
              text: [bind.to('defaultAltText')],
            },
          ],
        },
      ],
    });
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
        label: Drupal.t('Override media image alternative text'),
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

    // Reposition the balloon or hide the form if a media widget is no longer
    // selected.
    this.listenTo(editor.ui, 'update', () => {
      if (!getClosestSelectedDrupalMediaWidget(viewDocument.selection)) {
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
    const metadataRepository = editor.plugins.get(
      'DrupalMediaMetadataRepository',
    );
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

    this._form.defaultAltText = '';
    const modelElement = editor.model.document.selection.getSelectedElement();

    // Make sure that each time the panel shows up, the default alt text remains
    // in sync with the value from the metadata repository.
    if (isDrupalMedia(modelElement)) {
      metadataRepository
        .getMetadata(modelElement)
        .then((metadata) => {
          this._form.defaultAltText = metadata.imageSourceMetadata
            ? metadata.imageSourceMetadata.alt
            : '';
        })
        .catch((e) => {
          // There isn't any UI indication for errors because this should be
          // always called after the Drupal Media has been upcast, which would
          // already display an error in the UI.
          // @see module:drupalMedia/mediaimagetextalternative/mediaimagetextalternativeediting~MediaImageTextAlternativeEditing
          console.warn(e.toString());
        });
    }

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
// cSpell:words conversionutils datafilter eventinfo downcastdispatcher generalhtmlsupport



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

        preserveElementAttributes(viewMediaElement, 'htmlAttributes');

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
 * Model to view converter for the Drupal Media wrapper attributes.
 *
 * @param {module:utils/eventinfo~EventInfo} evt
 *   An object containing information about the fired event.
 * @param {Object} data
 *   Additional information about the change.
 * @param {module:engine/conversion/downcastdispatcher~DowncastDispatcher} conversionApi
 *   Conversion interface to be used by the callback.
 */
function modelToDataAttributeConverter(evt, data, conversionApi) {
  if (!conversionApi.consumable.consume(data.item, evt.name)) {
    return;
  }

  const viewElement = conversionApi.mapper.toViewElement(data.item);

  setViewAttributes(conversionApi.writer, data.attributeNewValue, viewElement);
}

/**
 * Model to editing view attribute converter.
 *
 * @return {function}
 *   A function that adds an event listener to downcastDispatcher.
 */
function modelToEditingViewAttributeConverter() {
  return (dispatcher) => {
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

    // Render arbitrary attributes on the CKEditor 5 widget wrapper until
    // arbitrary attributes are included as part of the server rendered preview.
    // @see https://www.drupal.org/project/drupal/issues/3231337
    dispatcher.on(
      'attribute:htmlAttributes:drupalMedia',
      modelToDataAttributeConverter,
      { priority: 'low' },
    );
  };
}

/**
 * Model to data view attribute converter.
 *
 * @return {function}
 *   function that adds an event listener to downcastDispatcher.
 */
function modelToDataViewAttributeConverter() {
  return (dispatcher) => {
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

    dispatcher.on(
      'attribute:htmlAttributes:drupalMedia',
      modelToDataAttributeConverter,
      { priority: 'low' },
    );
  };
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
  constructor(editor) {
    super(editor);

    // This plugin is only needed if General HTML Support plugin is loaded.
    if (!editor.plugins.has('GeneralHtmlSupport')) {
      return;
    }
    // This plugin works only if `DataFilter` and `DataSchema` plugins are
    // loaded. These plugins are dependencies of `GeneralHtmlSupport` meaning
    // that these should be available always when `GeneralHtmlSupport` is
    // enabled.
    if (
      !editor.plugins.has('DataFilter') ||
      !editor.plugins.has('DataSchema')
    ) {
      console.error(
        'DataFilter and DataSchema plugins are required for Drupal Media to integrate with General HTML Support plugin.',
      );
    }

    const { schema } = editor.model;
    const { conversion } = editor;
    const dataFilter = this.editor.plugins.get('DataFilter');
    const dataSchema = this.editor.plugins.get('DataSchema');

    // This needs to be initialized in ::constructor() to ensure this runs
    // before the General HTML Support has been initialized.
    // @see module:html-support/generalhtmlsupport~GeneralHtmlSupport
    dataSchema.registerBlockElement({
      model: 'drupalMedia',
      view: 'drupal-media',
    });

    dataFilter.on('register:drupal-media', (evt, definition) => {
      if (definition.model !== 'drupalMedia') {
        return;
      }

      schema.extend('drupalMedia', {
        allowAttributes: ['htmlLinkAttributes', 'htmlAttributes'],
      });

      conversion
        .for('upcast')
        .add(viewToModelDrupalMediaAttributeConverter(dataFilter));
      conversion
        .for('editingDowncast')
        .add(modelToEditingViewAttributeConverter());
      conversion.for('dataDowncast').add(modelToDataViewAttributeConverter());

      evt.stop();
    });
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

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalelementstyle/utils.js
/**
 * A simple helper function that returns the command group name.
 *
 * @example
 *    groupName = 'viewMode' -> commandGroupName = 'drupalViewMode'
 *
 * @param {string} groupName The name of the group (ex. 'align', 'viewMode').
 * @return {string} Command group name.
 */
function getCommandGroupNameFromGroup(groupName) {
  return 'drupal'.concat(groupName[0].toUpperCase() + groupName.substring(1));
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalelementstyle/drupalelementstylecommand.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words documentselection */



/**
 * @module drupalMedia/drupalelementstyle/drupalelementstylecommand
 */

/**
 * Checks the schema if any of the drupalElementStyles with the given attribute name exists.
 *
 * @param {module:engine/model/element~Element|null} selectedElement
 *   The selected element.
 * @param {module:engine/model/schema~Schema} schema
 *   The model schema.
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} styles
 *   All available Drupal Element Styles.
 *
 * @return {boolean}
 *   Does the schema contain the attribute?
 */
function schemaContainsAttribute(selectedElement, schema, styles) {
  // eslint-disable-next-line no-restricted-syntax
  for (const group of Object.keys(styles)) {
    const groupName = group[0].toUpperCase() + group.substring(1);
    if (
      schema.checkAttribute(selectedElement, `drupalElementStyle${groupName}`)
    ) {
      return true;
    }
  }
  return false;
}

/**
 * Gets closest element that has any drupalElementStyle attribute in schema.
 *
 * @param {module:engine/model/documentselection~DocumentSelection} selection
 *   The current document selection.
 * @param {module:engine/model/schema~Schema} schema
 *   The model schema.
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} styles
 *   All available Drupal Element Styles.
 *
 * @return {null|module:engine/model/element~Element}
 *   The closest element that supports element styles.
 */
function getClosestElementWithElementStyleAttribute(selection, schema, styles) {
  const selectedElement = selection.getSelectedElement();
  if (
    selectedElement &&
    schemaContainsAttribute(selectedElement, schema, styles)
  ) {
    return selectedElement;
  }

  let parent = selection.getFirstPosition().parent;

  while (parent) {
    if (
      parent.is('element') &&
      schema.checkAttribute(parent, 'drupalElementStyle')
    ) {
      return parent;
    }

    parent = parent.parent;
  }
  return null;
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
    Object.keys(styles).forEach((group) => {
      this._styles[group] = new Map(
        styles[group].map((style) => {
          return [style.name, style];
        }),
      );
    });
  }

  /**
   * @inheritDoc
   */
  refresh() {
    const { editor } = this;
    const element = getClosestElementWithElementStyleAttribute(
      editor.model.document.selection,
      editor.model.schema,
      this.styles,
    );

    this.isEnabled = !!element;

    // The element needs to be checked against list of possible attributes then
    // update the value to include all drupalElementStyles selected for the element.
    if (this.isEnabled && this.containsAttribute(element)) {
      this.value = this.getGroupAndAttribute(element);
    } else {
      this.value = false;
      // If value is falsy, check if there is a default style to apply to the
      // element.
      if (!this.value) {
        // @todo need to add support for default styles.
        // eslint-disable-next-line no-restricted-syntax
        // for (const [name, style] of this._styles.entries()) {
        //   if (style.isDefault) {
        //     const appliesToCurrentElement = style.modelElements.find(
        //       (modelElement) => element.is('element', modelElement),
        //     );
        //     if (appliesToCurrentElement) {
        //       this.value = name;
        //       break;
        //     }
        //   }
        // }
      }
    }
  }

  /**
   * Checks if an element has a drupalElementStyle attribute.
   *
   * @param {module:engine/model/element~Element|null} element
   *   The element.
   *
   * @return {boolean}
   *   Does the element have a drupalElementStyle attribute?
   */
  containsAttribute(element) {
    // eslint-disable-next-line no-restricted-syntax
    for (const group of Object.keys(this.styles)) {
      const groupName = group[0].toUpperCase() + group.substring(1);
      if (element.hasAttribute(`drupalElementStyle${groupName}`)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Gets the group(s) and attribute(s) of the element.
   *
   * @example {drupalAlign: 'alignLeft', drupalViewMode: 'full'}
   *
   * @param {module:engine/model/element~Element|null} element
   *   The element.
   *
   * @return {Object}
   * The group(s) and attribute(s) in the form of an object.
   */
  getGroupAndAttribute(element) {
    const groupAttr = {};
    Object.keys(this.styles).forEach((group) => {
      const groupName = group[0].toUpperCase() + group.substring(1);
      const commandGroupName = getCommandGroupNameFromGroup(group);
      if (element.hasAttribute(`drupalElementStyle${groupName}`)) {
        groupAttr[`${commandGroupName}`] = element.getAttribute(
          `drupalElementStyle${groupName}`,
        );
      }
    });
    return groupAttr;
  }

  /**
   * Executes the command and applies the style to the selected model element.
   *
   * @example
   *    editor.execute('drupalElementStyle', { value: {drupalAlign: 'alignLeft' }, groupName: 'align' });
   *
   * @param {Object} options
   *   The command options.
   * @param {string} options.value
   *   The name of the style as configured in the Drupal Element style
   *   configuration.
   * @param {string} options.groupName
   *   The group name of the drupalElementStyle.
   */
  execute(options = {}) {
    const { editor } = this;
    const { model } = editor;
    const groupName = Object.values(options)[1];
    const groupNameCap = groupName[0].toUpperCase() + groupName.substring(1);
    model.change((writer) => {
      const modelGroupName = Object.keys(options.value)[0];
      const requestedStyle = options.value;
      const element = getClosestElementWithElementStyleAttribute(
        model.document.selection,
        model.schema,
        this.styles,
      );
      if (
        !requestedStyle ||
        this._styles[groupName].get(requestedStyle[modelGroupName]).isDefault
      ) {
        // Remove value from the object.
        writer.removeAttribute(`drupalElementStyle${groupNameCap}`, element);
      } else {
        // Extend the object with new value.
        writer.setAttribute(
          `drupalElementStyle${groupNameCap}`,
          requestedStyle[modelGroupName],
          element,
        );
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
  styles.forEach((style) => {
    if (style.name === name) {
      return style;
    }
  });
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
      if (newStyle.attributeName === 'class') {
        viewWriter.addClass(newStyle.attributeValue, viewElement);
      } else if (!newStyle.isDefault) {
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
      !conversionApi.schema.checkAttribute(
        modelElement,
        `drupalElementStyle${groupName}`,
      )
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
            `drupalElementStyle${groupName}`,
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
              `drupalElementStyle${groupName}`,
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
 *              - name: 'full view mode'
 *                title: 'Full view mode'
 *                attributeName: 'data-view-mode'
 *                attributeValue: 'full'
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
    const { editor } = this;

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
    Object.keys(stylesConfig).forEach((group) => {
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
          if (
            (!style.isDefault && !style.attributeName) ||
            !style.attributeValue
          ) {
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
    });
    this.normalizedStyles = stylesConfig;

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
    const { editor } = this;
    const { schema } = editor.model;

    const groupNamesArr = Object.keys(this.normalizedStyles);

    for (let i = 0; i < groupNamesArr.length; i++) {
      const group = groupNamesArr[i];
      // Capitalize first letter to append in camelCase properly.
      const groupName = group[0].toUpperCase() + group.substring(1);

      const modelToViewConverter = modelToViewStyleAttribute(
        this.normalizedStyles[group],
      );
      const viewToModelConverter = viewToModelStyleAttribute(
        this.normalizedStyles[group],
        groupName,
      );

      editor.editing.downcastDispatcher.on(
        `attribute:drupalElementStyle${groupName}`,
        modelToViewConverter,
      );
      editor.data.downcastDispatcher.on(
        `attribute:drupalElementStyle${groupName}`,
        modelToViewConverter,
      );

      // Allow drupalElementStyle${groupName} on all model elements that have associated
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
        schema.extend(modelElement, {
          allowAttributes: `drupalElementStyle${groupName}`,
        });
      });
      // View to model converter that runs on all elements.
      editor.data.upcastDispatcher.on(
        'element',
        viewToModelConverter,
        // This needs to be set as low priority to ensure this runs always after
        // the element has been converted to a model element.
        { priority: 'low' },
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
 * @param {string} group
 *   The group of the component.
 * @return {string}
 *   The UI component name.
 *
 * @see module:ui/componentfactory~ComponentFactory
 */
function getUIComponentName(name, group) {
  return `drupalElementStyle:${group}:${name}`;
}

/**
 * Toggles the visibility of the correct view mode buttons depending on the selection's bundle type.
 *
 * @param {module:core/editor/editor~Editor} editor
 *   The editor instance.
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
 *   A list of defined styles.
 * @param {string} style
 *   The style to check be checked against the bundle specific styles.
 * @param {<module:ui/dropdown/utils~ListDropdownItemDefinition>} definition
 *   Dropdown item definition.
 *
 */
function toggleButtonVisibility(editor, definedStyles, style, definition) {
  const { selection } = editor.model.document;
  const modelElement = selection
    ? selection.getSelectedElement()
    : selection.getFirstPosition.findAncestor('drupalElementStyle');
  const bundleType = modelElement.getAttribute('drupalMediaBundle');

  if (!bundleType) {
    return;
  }

  const filteredDefinedStyles = definedStyles.filter(function (item) {
    return item.modelAttributes.drupalMediaBundle.includes(bundleType);
  });

  if (!filteredDefinedStyles.includes(style)) {
    // Hide button if view mode is not available for the bundle that the modelElement is.
    definition.model.set({ class: 'ck-hidden' });
  } else {
    // Un-hide button here after changing selection to a bundle that should have the view mode button visible.
    definition.model.set({ class: '' });
  }
}

/**
 * Upcast `drupalMediaIsImage` from Drupal Media metadata.
 *
 * @param {module:engine/model/node~Node} modelElement
 *   The `drupalMedia` model element.
 * @param {module:core/editor/editor~Editor} editor
 *   The editor instance.
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
 *   A list of defined styles.
 * @param {string} style
 *   The style to check be checked against the bundle specific styles.
 * @param {<module:ui/dropdown/utils~ListDropdownItemDefinition>} definition
 *   Dropdown item definition.
 *
 * @see module:drupalMedia/drupalmediametadatarepository~DrupalMediaMetadataRepository
 *
 * @private
 */
function upcastDrupalMediaBundle(
  modelElement,
  editor,
  definedStyles,
  style,
  definition,
) {
  const metadataRepository = editor.plugins.get(
    'DrupalMediaMetadataRepository',
  );
  // Get all metadata for drupalMedia elements to set value for
  // drupalMediaBundle attribute. When other plugins start using the
  // metadata, this functionality will be handled more generically.
  metadataRepository
    .getMetadata(modelElement)
    .then((metadata) => {
      if (!modelElement) {
        // Nothing to do if model element has been removed before
        // promise was resolved.
        return;
      }
      // Enqueue a model change in `transparent` batch to make it
      // invisible to the undo/redo functionality.
      editor.model.enqueueChange('transparent', (writer) => {
        writer.setAttribute(
          'drupalMediaBundle',
          metadata.bundleType,
          modelElement,
        );
      });
    })
    .catch((e) => {
      if (!modelElement) {
        // Nothing to do if model element has been removed before
        // promise was resolved.
        return;
      }
      console.warn(e.toString());
      editor.model.enqueueChange('transparent', (writer) => {
        writer.setAttribute('drupalMediaBundle', METADATA_ERROR, modelElement);
      });
    });
  toggleButtonVisibility(editor, definedStyles, style, definition);
}

/**
 * A helper function that parses the resize options and returns list item definitions ready for use in the dropdown.
 *
 * @private
 * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
 *   A list of defined styles.
 * @param {module:drupalMedia/drupalelementstyle/drupalelementstylecommand} command The drupalElementStyle command.
 * @param {string} groupName The name of the group (ex. 'align', 'viewMode').
 * @return {Iterable.<module:ui/dropdown/utils~ListDropdownItemDefinition>} Dropdown item definitions.
 */
function getDropdownListItemDefinitions(
  definedStyles,
  command,
  groupName,
  editor,
) {
  const itemDefinitions = new delegated_utilsfrom_dll_reference_CKEditor5.Collection();
  const commandGroup = getCommandGroupNameFromGroup(groupName);
  definedStyles.forEach((style) => {
    const definition = {
      type: 'button',
      model: new delegated_uifrom_dll_reference_CKEditor5.Model({
        commandName: 'drupalElementStyle',
        commandGroup,
        groupName,
        commandValue: style.name,
        label: style.title,
        withText: true,
        class: '',
      }),
    };
    itemDefinitions.add(definition);

    // Handles inserted content's list dropdown button's visibility.
    editor.model.on('insertContent', (eventInfo, [modelElement]) => {
      if (!isDrupalMedia(modelElement)) {
        return;
      }
      // Need to upcast DrupalMediaBundle to model so it can be used to show
      // correct buttons based on bundle. Calls toggle function inside below method.
      upcastDrupalMediaBundle(
        modelElement,
        editor,
        definedStyles,
        style,
        definition,
      );
    });

    // Handles selecting another element's list dropdown button's visibility.
    // We need to listen to editor UI changes instead of selection because
    // visibility of the styles can be impacted by either selection or
    // changes to the model.
    editor.ui.on('update', () => {
      const modelElement = editor.model.document.selection.getSelectedElement();
      if (!isDrupalMedia(modelElement)) {
        return;
      }
      toggleButtonVisibility(editor, definedStyles, style, definition);
    });
  });
  return itemDefinitions;
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
    const definedStyles = plugins.get(
      'DrupalElementStyleEditing',
    ).normalizedStyles;

    Object.keys(definedStyles).forEach((group) => {
      definedStyles[group].forEach((style) => {
        this._createButton(style, group);
      });
    });

    /**
     * A Drupal Element Style dropdown definition.
     *
     * @example
     *    config:
     *       drupalMedia:
     *         toolbar:
     *           - name: 'drupalMedia:alignment'
     *             display: 'toolbar'
     *             title: 'Custom title for the dropdown'
     *             items:
     *               - 'drupalElementStyle:align:alignLeft'
     *               - 'drupalElementStyle:align:alignCenter'
     *               - 'drupalElementStyle:align:alignRight'
     *             defaultItem: 'drupalElementStyle:align:alignCenter'
     *
     * @typedef {Object} Drupal.CKEditor5~drupalElementStyleDropdownDefinition
     *
     * @prop {string} name
     *   The name of the dropdown used for identifying the dropdown.
     * @prop {string} display
     *   The type of the dropdown used.
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

    definedDropdowns.forEach((dropdownConfig) => {
      const groupName = dropdownConfig.name.split(':')[1];
      switch (dropdownConfig.display) {
        case 'toolbar':
          this._createDropdown(dropdownConfig, definedStyles[groupName]);
          break;
        case 'list':
          this._createListDropdown(dropdownConfig, definedStyles[groupName]);
          break;
        default:
          throw new Error('Toolbar display type must be specified.');
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
    const factory = this.editor.ui.componentFactory;

    factory.add(dropdownConfig.name, (locale) => {
      let defaultButton;

      const { defaultItem, items, title } = dropdownConfig;
      const buttonViews = items
        .filter((itemName) => {
          const groupName = itemName.split(':')[1];
          return definedStyles.find(
            ({ name }) => getUIComponentName(name, groupName) === itemName,
          );
        })
        .map((buttonName) => {
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
      // which modifies the styles to indicate that the split button default
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
   * @param {string} group
   *   The name of the group (ex. 'align', 'viewMode').
   *
   * @see module:ui/componentfactory~ComponentFactory
   *
   * @private
   */
  _createButton(buttonConfig, group) {
    const buttonName = buttonConfig.name;

    this.editor.ui.componentFactory.add(
      getUIComponentName(buttonName, group),
      (locale) => {
        const command = this.editor.commands.get('drupalElementStyle');
        const view = new delegated_uifrom_dll_reference_CKEditor5.ButtonView(locale);

        view.set({
          label: buttonConfig.title,
          icon: buttonConfig.icon,
          tooltip: true,
          isToggleable: true,
        });

        view.bind('isEnabled').to(command, 'isEnabled');
        view.bind('isOn').to(command, 'value', (value) => value === buttonName);
        view.on('execute', this._executeCommand.bind(this, buttonName, group));

        return view;
      },
    );
  }

  /**
   * A helper function that creates a list dropdown component for the plugin containing all the style options defined in
   * the editor configuration.
   *
   * @private
   * @param {Drupal.CKEditor5~drupalElementStyleDropdownDefinition} dropdownConfig
   *   The dropdown configuration.
   * @param {Drupal.CKEditor5~DrupalElementStyle[]} definedStyles
   *   A list of defined styles.
   */
  _createListDropdown(dropdownConfig, definedStyles) {
    const factory = this.editor.ui.componentFactory;
    factory.add(dropdownConfig.name, (locale) => {
      let defaultButton;

      const { defaultItem, items, title } = dropdownConfig;
      console.log('dropdownconfig: ', dropdownConfig);
      console.log('definedstyles: ', definedStyles);

      const groupName = dropdownConfig.name.split(':')[1];
      const buttonViews = items
        .filter((itemName) => {
          return definedStyles.find(
            ({ name }) => getUIComponentName(name, groupName) === itemName,
          );
        })
        .map((buttonName) => {
          console.log('buttonName', buttonName);
          const button = factory.create(buttonName);

          if (buttonName === defaultItem) {
            console.log('buttonName in condition:', buttonName);
            console.log('defaultItem in condition:', defaultItem);
            defaultButton = button;
          }

          return button;
        });

      if (items.length !== buttonViews.length) {
        utils.warnInvalidStyle({ dropdown: dropdownConfig });
      }

      const dropdownView = (0,delegated_uifrom_dll_reference_CKEditor5.createDropdown)(locale, delegated_uifrom_dll_reference_CKEditor5.DropdownButtonView);
      const dropdownButtonView = dropdownView.buttonView;

      // If user does not have default enabled as a view mode button, make it the first option
      // from the dropdown.
      if (!defaultButton) {
        defaultButton = buttonViews[0];
      }
      dropdownButtonView.set({
        label: getDropdownButtonTitle(title, defaultButton.label),
        class: null,
        tooltip: Drupal.t('Select view mode'),
        withText: true,
      });

      const command = this.editor.commands.get('drupalElementStyle');
      const commandGroupName = getCommandGroupNameFromGroup(groupName);

      // If style is selected, use the label of the selected style as the
      // default label of the split button.
      dropdownButtonView.bind('label').to(command, 'value', (commandValue) => {
        if (commandValue && commandValue[commandGroupName]) {
          // @todo Use the style title instead of the machine name.
          return commandValue[commandGroupName];
        }
        return dropdownConfig.defaultText;
      });

      dropdownView.bind('isOn').to(command);
      dropdownView.bind('isEnabled').to(this);

      (0,delegated_uifrom_dll_reference_CKEditor5.addListToDropdown)(
        dropdownView,
        getDropdownListItemDefinitions(
          definedStyles,
          command,
          groupName,
          this.editor,
        ),
      );
      // Execute command when an item from the dropdown is selected.
      this.listenTo(dropdownView, 'execute', (evt) => {
        const obj = {};
        const key = evt.source.commandGroup;
        obj[key] = evt.source.commandValue;
        this.editor.execute(evt.source.commandName, {
          value: obj,
          groupName: evt.source.groupName,
        });
        this.editor.editing.view.focus();
      });
      return dropdownView;
    });
  }

  /**
   * Executes the Drupal Element Style command.
   *
   * @param {string} name
   *   The name of the style that should be applied.
   * @param {string} groupName
   *   The name of the group (ex. 'align', 'viewMode').
   *
   * @see module:drupalMedia/drupalelementstyle/drupalelementstylecommand~DrupalElementStyleCommand
   *
   * @private
   */
  _executeCommand(name, groupName) {
    const key = getCommandGroupNameFromGroup(groupName);
    const obj = {};
    obj[key] = name;
    this.editor.execute('drupalElementStyle', {
      value: obj,
      groupName,
    });
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

// EXTERNAL MODULE: delegated ./engine.js from dll-reference CKEditor5.dll
var delegated_enginefrom_dll_reference_CKEditor5 = __webpack_require__(492);
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediacaption/utils.js
/* eslint-disable import/prefer-default-export */


/**
 * Returns the Media caption model element for a model selection.
 *
 * @param {module:engine/model/selection~Selection} selection
 *   The current selection.
 * @returns {module:engine/model/element~Element|null}
 *   The Drupal Media caption element for a model selection. Returns null if the
 *   selection has no Drupal Media caption element ancestor.
 */
function getMediaCaptionFromModelSelection(selection) {
  const captionElement = selection.getFirstPosition().findAncestor('caption');

  if (!captionElement) {
    return null;
  }

  if (isDrupalMedia(captionElement.parent)) {
    return captionElement;
  }

  return null;
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediacaption/drupalmediacaptioncommand.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words imagecaption */




/**
 * Gets the caption model element from the media model selection.
 *
 * @param {module:engine/model/element~Element} drupalMediaModelElement
 *   The model element from which caption should be retrieved.
 * @returns {module:engine/model/element~Element|null}
 *   The caption element or `null` if the selection has no child caption
 *   element.
 */
function getCaptionFromDrupalMediaModelElement(drupalMediaModelElement) {
  // eslint-disable-next-line no-restricted-syntax
  for (const node of drupalMediaModelElement.getChildren()) {
    if (!!node && node.is('element', 'caption')) {
      return node;
    }
  }

  return null;
}

/**
 * The toggle Drupal Media caption command.
 *
 * This command either adds or removes the caption of a selected drupalMedia
 * element.
 *
 * This is inspired by the CKEditor 5 image caption plugin.
 *
 * @see module:image/imagecaption~ImageCaption
 *
 * @extends module:core/command~Command
 *
 * @internal
 */
class ToggleDrupalMediaCaptionCommand extends delegated_corefrom_dll_reference_CKEditor5.Command {
  /**
   * @inheritDoc
   */
  refresh() {
    const selection = this.editor.model.document.selection;
    const selectedElement = selection.getSelectedElement();

    // When selectedElement is falsy, it is potentially due to multiple elements
    // being selected, such as elements that descend from `<drupalMedia>`.
    if (!selectedElement) {
      // Command should be enabled if `<drupalMedia>` element is part of the
      // selection.
      this.isEnabled = !!getClosestSelectedDrupalMediaElement(selection);
      // Check if the selection descends from a `<drupalMedia>` element that
      // also includes a `<caption>`.
      this.value = !!getMediaCaptionFromModelSelection(selection);

      return;
    }

    // If single element is selected, check if it's a `<drupalMedia>` element.
    this.isEnabled = isDrupalMedia(selectedElement);

    if (!this.isEnabled) {
      this.value = false;
    } else {
      // Command value is set based on whether the selected `<drupalMedia>`
      // element has a `<caption>` as a child element.
      this.value = !!getCaptionFromDrupalMediaModelElement(selectedElement);
    }
  }

  /**
   * Executes the command.
   *
   * @example
   *   editor.execute('toggleMediaCaption');
   *
   * @param {Object} [options]
   *   Options for the executed command.
   * @param {String} [options.focusCaptionOnShow]
   *   When true and the caption shows up, the selection will be moved into it
   *    When true: If a caption is present, the selection will be moved to that
   *    caption immediately.
   *
   * @fires execute
   */
  execute(options = {}) {
    const { focusCaptionOnShow } = options;
    this.editor.model.change((writer) => {
      if (this.value) {
        this._hideDrupalMediaCaption(writer);
      } else {
        this._showDrupalMediaCaption(writer, focusCaptionOnShow);
      }
    });
  }

  /**
   * Shows the caption of a selected drupalMedia element.
   *
   * This also attempts to restore the caption content from the
   * `DrupalMediaEditing` caption registry. If the `focusCaptionOnShow` option
   * is true, the selection is immediately moved to the caption.
   *
   * @param {module:engine/model/writer~Writer} writer
   *   The model writer.
   * @param {bool} focusCaptionOnShow
   *   Flag indicating whether the caption should be focused.
   */
  _showDrupalMediaCaption(writer, focusCaptionOnShow) {
    const model = this.editor.model;
    const selection = model.document.selection;
    const mediaCaptionEditing = this.editor.plugins.get(
      'DrupalMediaCaptionEditing',
    );
    const selectedMedia = getClosestSelectedDrupalMediaElement(selection);
    const savedCaption = mediaCaptionEditing._getSavedCaption(selectedMedia);

    // Try restoring the caption from the DrupalMediaCaptionEditing plugin storage.
    const newCaptionElement = savedCaption || writer.createElement('caption');

    writer.append(newCaptionElement, selectedMedia);

    if (focusCaptionOnShow) {
      writer.setSelection(newCaptionElement, 'in');
    }
  }

  /**
   * Hides the caption of a selected drupalMedia element.
   *
   * The content of the caption is stored in the `DrupalMediaCaptionEditing`
   * caption registry to make this a reversible action.
   *
   * @param {module:engine/model/writer~Writer} writer
   *   The model writer.
   */
  _hideDrupalMediaCaption(writer) {
    const editor = this.editor;
    const selection = editor.model.document.selection;
    const mediaCaptionEditing = editor.plugins.get('DrupalMediaCaptionEditing');
    let selectedElement = selection.getSelectedElement();
    let captionElement;

    if (selectedElement) {
      captionElement = getCaptionFromDrupalMediaModelElement(selectedElement);
    } else {
      captionElement = getMediaCaptionFromModelSelection(selection);
      selectedElement = getClosestSelectedDrupalMediaElement(selection);
    }

    // Store the caption content so it can be restored quickly if the user
    // changes their mind.
    mediaCaptionEditing._saveCaption(selectedElement, captionElement);
    writer.setSelection(selectedElement, 'on');
    writer.remove(captionElement);
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediacaption/drupalmediacaptionediting.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words insertdrupalmedia JSONified drupalmediacaptioncommand downcasted */






/**
 * A view to model converter for Drupal Media caption.
 *
 * This upcasts the `data-caption` attribute from `<drupal-media>` elements into
 * a `<caption>` model element. This is converted into a model element instead of
 * a model attribute in order to leverage CKEditor 5 built-in editing.
 *
 * @param {module:core/editor/editor~Editor} editor
 *   Editor on which this converter will be used.
 * @return {function}
 *   A function that attaches converter to the dispatcher.
 */
function viewToModelCaption(editor) {
  const converter = (evt, data, conversionApi) => {
    const { viewItem } = data;
    const { writer, consumable } = conversionApi;
    if (
      !data.modelRange ||
      !consumable.consume(viewItem, { attributes: ['data-caption'] })
    ) {
      return;
    }

    const caption = writer.createElement('caption');
    const drupalMedia = data.modelRange.start.nodeAfter;

    // Parse HTML from data-caption attribute and upcast it to model fragment.
    const viewFragment = editor.data.processor.toView(
      viewItem.getAttribute('data-caption'),
    );
    const modelFragment = writer.createDocumentFragment();

    // Consumable must know about those newly parsed view elements.
    conversionApi.consumable.constructor.createFrom(
      viewFragment,
      conversionApi.consumable,
    );
    conversionApi.convertChildren(viewFragment, modelFragment);

    // Insert caption model nodes into the caption.
    // eslint-disable-next-line no-restricted-syntax
    for (const child of Array.from(modelFragment.getChildren())) {
      writer.append(child, caption);
    }

    // Insert the caption element into drupalMedia, as a last child.
    writer.append(caption, drupalMedia);
  };

  return (dispatcher) => {
    dispatcher.on('element:drupal-media', converter, { priority: 'low' });
  };
}

/**
 * Gets mapper function for repositioning the `<figcaption>` element.
 *
 * @param {module:engine/view/view~View} editingView
 *   The editing view.
 * @return {function}
 *   A mapper callback that moves `<figcaption>` element after the Drupal Media
 *   preview.
 */
function mapModelPositionToView(editingView) {
  return (evt, data) => {
    const modelPosition = data.modelPosition;
    const parent = modelPosition.parent;

    if (!isDrupalMedia(parent)) {
      return;
    }

    const viewElement = data.mapper.toViewElement(parent);
    data.viewPosition = editingView.createPositionAt(
      viewElement,
      modelPosition.offset + 1,
    );
  };
}

/**
 * A model to view converter for Drupal Media caption.
 *
 * This downcasts the `<caption>` model element into `data-caption` attribute in
 * the view.
 *
 * @param {module:core/editor/editor~Editor} editor
 *   Editor on which this converter will be used.
 * @return {function}
 *   A function that attaches converter to the dispatcher.
 */
function modelCaptionToCaptionAttribute(editor) {
  return (dispatcher) => {
    dispatcher.on('insert:caption', (evt, data, conversionApi) => {
      const { consumable, writer, mapper } = conversionApi;

      if (
        !isDrupalMedia(data.item.parent) ||
        !consumable.consume(data.item, 'insert')
      ) {
        return;
      }

      const range = editor.model.createRangeIn(data.item);
      const viewDocumentFragment = writer.createDocumentFragment();

      // Bind caption model element to the detached view document fragment so
      // all content of the caption will be downcasted into that document
      // fragment.
      mapper.bindElements(data.item, viewDocumentFragment);

      // eslint-disable-next-line no-restricted-syntax
      for (const { item } of Array.from(range)) {
        const itemData = {
          item,
          range: editor.model.createRangeOn(item),
        };

        // The following lines are extracted from
        // DowncastDispatcher._convertInsertWithAttributes().
        const eventName = `insert:${item.name || '$text'}`;

        editor.data.downcastDispatcher.fire(eventName, itemData, conversionApi);

        // eslint-disable-next-line no-restricted-syntax
        for (const key of item.getAttributeKeys()) {
          Object.assign(itemData, {
            attributeKey: key,
            attributeOldValue: null,
            attributeNewValue: itemData.item.getAttribute(key),
          });

          editor.data.downcastDispatcher.fire(
            `attribute:${key}`,
            itemData,
            conversionApi,
          );
        }
      }

      // Unbind all the view elements that were downcasted to the document
      // fragment.
      // eslint-disable-next-line no-restricted-syntax
      for (const child of writer
        .createRangeIn(viewDocumentFragment)
        .getItems()) {
        mapper.unbindViewElement(child);
      }

      mapper.unbindViewElement(viewDocumentFragment);

      // Stringify view document fragment to HTML string.
      const captionText = editor.data.processor.toData(viewDocumentFragment);

      if (captionText) {
        const imageViewElement = mapper.toViewElement(data.item.parent);

        writer.setAttribute('data-caption', captionText, imageViewElement);
      }
    });
  };
}

/**
 * The Drupal Media caption editing plugin.
 *
 * @extends module:core/plugin~Plugin
 *
 * @internal
 */
class DrupalMediaCaptionEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritDoc
   */
  static get requires() {
    return [];
  }

  /**
   * @inheritDoc
   */
  static get pluginName() {
    return 'DrupalMediaCaptionEditing';
  }

  /**
   * @inheritDoc
   */
  constructor(editor) {
    super(editor);

    /**
     * A map of saved Drupal Media captions and related model elements.
     *
     * @member {WeakMap.<module:engine/model/element~Element,Object>}
     *
     * @see _saveCaption
     */
    this._savedCaptionsMap = new WeakMap();
  }

  /**
   * @inheritDoc
   */
  init() {
    const editor = this.editor;
    const schema = editor.model.schema;

    // Schema configuration.
    if (!schema.isRegistered('caption')) {
      schema.register('caption', {
        allowIn: 'drupalMedia',
        allowContentOf: '$block',
        isLimit: true,
      });
    } else {
      schema.extend('caption', {
        allowIn: 'drupalMedia',
      });
    }

    editor.commands.add(
      'toggleMediaCaption',
      new ToggleDrupalMediaCaptionCommand(editor),
    );

    this._setupConversion();
  }

  /**
   * Initializes upcasting and downcasting Drupal Media captions.
   */
  _setupConversion() {
    const editor = this.editor;
    const view = editor.editing.view;

    // View -> model converter for the data pipeline.
    editor.conversion.for('upcast').add(viewToModelCaption(editor));

    // Model -> Editing View converter for the data pipeline.
    editor.conversion.for('editingDowncast').elementToElement({
      model: 'caption',
      view: (modelElement, { writer }) => {
        if (!isDrupalMedia(modelElement.parent)) {
          return null;
        }

        const figcaptionElement = writer.createEditableElement('figcaption');

        (0,delegated_enginefrom_dll_reference_CKEditor5.enablePlaceholder)({
          view,
          element: figcaptionElement,
          text: Drupal.t('Enter media caption'),
          keepOnFocus: true,
        });

        return (0,delegated_widgetfrom_dll_reference_CKEditor5.toWidgetEditable)(figcaptionElement, writer);
      },
    });
    // The `<caption>` element inside the Drupal Media wrapper is by default
    // placed before the preview. This rearranges the elements so that
    // `<caption>` is rendered after the preview.
    editor.editing.mapper.on(
      'modelToViewPosition',
      mapModelPositionToView(view),
    );

    // Model -> Data converter for the data pipeline.
    editor.conversion
      .for('dataDowncast')
      .add(modelCaptionToCaptionAttribute(editor));
  }

  /**
   * Returns the saved caption of a Drupal Media model element.
   *
   * @param {module:engine/model/element~Element} drupalMediaModelElement
   *   The model element the caption should be returned for.
   * @return {module:engine/model/element~Element|null}
   *   The model caption element or `null` if there is none.
   */
  _getSavedCaption(drupalMediaModelElement) {
    const jsonObject = this._savedCaptionsMap.get(drupalMediaModelElement);

    return jsonObject ? delegated_enginefrom_dll_reference_CKEditor5.Element.fromJSON(jsonObject) : null;
  }

  /**
   * Saves Drupal Media element caption to allow restoring it in the future.
   *
   * A caption is saved every time it gets hidden and/or the type of an Drupal
   * Media changes. The user should be able to restore it on demand.
   *
   * @param {module:engine/model/element~Element} drupalMediaModelElement
   *   The model element the caption is saved for.
   * @param {module:engine/model/element~Element} caption
   *   The caption model element to be saved.
   *
   * @see _getSavedCaption
   * @see module:engine/model/element~Element#toJSON
   */
  _saveCaption(drupalMediaModelElement, caption) {
    this._savedCaptionsMap.set(drupalMediaModelElement, caption.toJSON());
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediacaption/drupalmediacaptionui.js
/* eslint-disable import/no-extraneous-dependencies */




/**
 * The caption media UI plugin.
 *
 * @internal
 */
class DrupalMediaCaptionUI extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalMediaCaptionUI';
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    const editingView = editor.editing.view;
    editor.ui.componentFactory.add('toggleDrupalMediaCaption', (locale) => {
      const button = new delegated_uifrom_dll_reference_CKEditor5.ButtonView(locale);
      const captionCommand = editor.commands.get('toggleMediaCaption');
      button.set({
        label: Drupal.t('Caption media'),
        icon: delegated_corefrom_dll_reference_CKEditor5.icons.caption,
        tooltip: true,
        isToggleable: true,
      });

      // Bind button isOn and isEnabled properties to the command.
      button.bind('isOn', 'isEnabled').to(captionCommand, 'value', 'isEnabled');

      button
        .bind('label')
        .to(captionCommand, 'value', (value) =>
          value
            ? Drupal.t('Toggle caption off')
            : Drupal.t('Toggle caption on'),
        );

      this.listenTo(button, 'execute', () => {
        editor.execute('toggleMediaCaption', { focusCaptionOnShow: true });

        // If a caption is present, highlight it and scroll to the selection.
        const modelCaptionElement = getMediaCaptionFromModelSelection(
          editor.model.document.selection,
        );
        if (modelCaptionElement) {
          const figcaptionElement =
            editor.editing.mapper.toViewElement(modelCaptionElement);

          editingView.scrollToTheSelection();

          editingView.change((writer) => {
            writer.addClass(
              'drupal-media__caption_highlighted',
              figcaptionElement,
            );
          });
        }
      });

      return button;
    });
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/drupalmediacaption.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalmediacaption drupalmediacaptionediting drupalmediacaptionui */




/**
 * @internal
 */
class DrupalMediaCaption extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  static get requires() {
    return [DrupalMediaCaptionEditing, DrupalMediaCaptionUI];
  }

  static get pluginName() {
    return 'DrupalMediaCaption';
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalMedia/src/index.js
/* eslint-disable import/no-extraneous-dependencies */
// cspell:ignore mediaimagetextalternative drupalmediacaption



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
  DrupalMediaCaption: DrupalMediaCaption,
  DrupalElementStyle: DrupalElementStyle,
});

}();
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});