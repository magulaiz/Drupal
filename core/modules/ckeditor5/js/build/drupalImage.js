/*! cspell:disable */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CKEditor5"] = factory();
	else
		root["CKEditor5"] = root["CKEditor5"] || {}, root["CKEditor5"]["drupalImage"] = factory();
})(self, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 704:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/core.js");

/***/ }),

/***/ 448:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/upload.js");

/***/ }),

/***/ 209:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/utils.js");

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

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalImage/src/drupalimageediting.js
/* eslint-disable import/no-extraneous-dependencies */
// cSpell:words conversionutils downcasted linkimageediting



function createImageViewElement(writer) {
  return writer.createEmptyElement('img');
}

// A simple helper method to detect number strings.
function isNumberString(value) {
  const parsedValue = parseFloat(value);

  return !Number.isNaN(parsedValue) && value === String(parsedValue);
}

function modelEntityUuidToDataAttribute() {
  function converter(evt, data, conversionApi) {
    const { item } = data;
    const { consumable, writer } = conversionApi;

    if (!consumable.consume(item, evt.name)) {
      return;
    }

    const viewElement = conversionApi.mapper.toViewElement(item);
    const imageInFigure = Array.from(viewElement.getChildren()).find(
      (child) => child.name === 'img',
    );

    writer.setAttribute(
      'data-entity-uuid',
      data.attributeNewValue,
      imageInFigure || viewElement,
    );
  }

  return (dispatcher) => {
    dispatcher.on('attribute:dataEntityUuid', converter);
  };
}

// Downcast `caption` model to `data-caption` attribute with its content
// downcasted to plain HTML. This is needed because CKEditor 5 uses <caption>
// element internally in various places, which differs from Drupal which uses
// an attribute. For now to support that we have to manually repeat work done in
// the DowncastDispatcher's private methods.
function viewCaptionToCaptionAttribute(editor) {
  return (dispatcher) => {
    dispatcher.on(
      'insert:caption',
      (evt, data, conversionApi) => {
        const { consumable, writer, mapper } = conversionApi;
        const imageUtils = editor.plugins.get('ImageUtils');

        if (
          !imageUtils.isImage(data.item.parent) ||
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

          editor.data.downcastDispatcher.fire(
            eventName,
            itemData,
            conversionApi,
          );

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
      },
      // Override default caption converter.
      { priority: 'high' },
    );
  };
}

function modelEntityTypeToDataAttribute() {
  function converter(evt, data, conversionApi) {
    const { item } = data;
    const { consumable, writer } = conversionApi;

    if (!consumable.consume(item, evt.name)) {
      return;
    }

    const viewElement = conversionApi.mapper.toViewElement(item);
    const imageInFigure = Array.from(viewElement.getChildren()).find(
      (child) => child.name === 'img',
    );

    writer.setAttribute(
      'data-entity-type',
      data.attributeNewValue,
      imageInFigure || viewElement,
    );
  }

  return (dispatcher) => {
    dispatcher.on('attribute:dataEntityType', converter);
  };
}

function modelImageStyleToDataAttribute() {
  function converter(evt, data, conversionApi) {
    const { item } = data;
    const { consumable, writer } = conversionApi;

    const mapping = {
      alignLeft: 'left',
      alignRight: 'right',
      alignCenter: 'center',
      alignBlockRight: 'right',
      alignBlockLeft: 'left',
    };

    // Consume only for the values that can be converted into data-align.
    if (
      !mapping[data.attributeNewValue] ||
      !consumable.consume(item, evt.name)
    ) {
      return;
    }

    const viewElement = conversionApi.mapper.toViewElement(item);
    const imageInFigure = Array.from(viewElement.getChildren()).find(
      (child) => child.name === 'img',
    );

    writer.setAttribute(
      'data-align',
      mapping[data.attributeNewValue],
      imageInFigure || viewElement,
    );
  }

  return (dispatcher) => {
    dispatcher.on('attribute:imageStyle', converter, { priority: 'high' });
  };
}

function modelImageWidthToAttribute() {
  function converter(evt, data, conversionApi) {
    const { item } = data;
    const { consumable, writer } = conversionApi;

    if (!consumable.consume(item, evt.name)) {
      return;
    }

    const viewElement = conversionApi.mapper.toViewElement(item);
    const imageInFigure = Array.from(viewElement.getChildren()).find(
      (child) => child.name === 'img',
    );

    writer.setAttribute(
      'width',
      data.attributeNewValue.replace('px', ''),
      imageInFigure || viewElement,
    );
  }

  return (dispatcher) => {
    dispatcher.on('attribute:width:imageInline', converter, {
      priority: 'high',
    });
    dispatcher.on('attribute:width:imageBlock', converter, {
      priority: 'high',
    });
  };
}

function modelImageHeightToAttribute() {
  function converter(evt, data, conversionApi) {
    const { item } = data;
    const { consumable, writer } = conversionApi;

    if (!consumable.consume(item, evt.name)) {
      return;
    }

    const viewElement = conversionApi.mapper.toViewElement(item);
    const imageInFigure = Array.from(viewElement.getChildren()).find(
      (child) => child.name === 'img',
    );

    writer.setAttribute(
      'height',
      data.attributeNewValue.replace('px', ''),
      imageInFigure || viewElement,
    );
  }

  return (dispatcher) => {
    dispatcher.on('attribute:height:imageInline', converter, {
      priority: 'high',
    });
    dispatcher.on('attribute:height:imageBlock', converter, {
      priority: 'high',
    });
  };
}

function viewImageToModelImage(editor) {
  function converter(evt, data, conversionApi) {
    const { viewItem } = data;
    const { writer, consumable, safeInsert, updateConversionResult, schema } =
      conversionApi;
    const attributesToConsume = [];

    let image;

    // Not only check if a given `img` view element has been consumed, but also
    // verify it has `src` attribute present.
    if (!consumable.test(viewItem, { name: true, attributes: 'src' })) {
      return;
    }

    // Create image that's allowed in the given context.
    if (schema.checkChild(data.modelCursor, 'imageInline')) {
      image = writer.createElement('imageInline', {
        src: viewItem.getAttribute('src'),
      });
    } else {
      image = writer.createElement('imageBlock', {
        src: viewItem.getAttribute('src'),
      });
    }

    if (
      editor.plugins.has('ImageStyleEditing') &&
      consumable.test(viewItem, { name: true, attributes: 'data-align' })
    ) {
      // https://ckeditor.com/docs/ckeditor5/latest/api/module_image_imagestyle_utils.html#constant-defaultStyles
      const dataToPresentationMapBlock = {
        left: 'alignBlockLeft',
        center: 'alignCenter',
        right: 'alignBlockRight',
      };
      const dataToPresentationMapInline = {
        left: 'alignLeft',
        right: 'alignRight',
      };

      const dataAlign = viewItem.getAttribute('data-align');
      const alignment = image.is('element', 'imageBlock')
        ? dataToPresentationMapBlock[dataAlign]
        : dataToPresentationMapInline[dataAlign];

      writer.setAttribute('imageStyle', alignment, image);

      // Make sure the attribute can be consumed after successful `safeInsert`
      // operation.
      attributesToConsume.push('data-align');
    }

    // Check if the view element has still unconsumed `data-caption` attribute.
    // Also, we can add caption only to block image.
    if (
      image.is('element', 'imageBlock') &&
      consumable.test(viewItem, { name: true, attributes: 'data-caption' })
    ) {
      // Create `caption` model element. Thanks to that element the rest of the
      // `ckeditor5-plugin` converters can recognize this image as a block image
      // with a caption.
      const caption = writer.createElement('caption');

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

      // Insert the caption element into image, as a last child.
      writer.append(caption, image);

      // Make sure the attribute can be consumed after successful `safeInsert`
      // operation.
      attributesToConsume.push('data-caption');
    }

    if (
      consumable.test(viewItem, { name: true, attributes: 'data-entity-uuid' })
    ) {
      writer.setAttribute(
        'dataEntityUuid',
        viewItem.getAttribute('data-entity-uuid'),
        image,
      );
      attributesToConsume.push('data-entity-uuid');
    }

    if (
      consumable.test(viewItem, { name: true, attributes: 'data-entity-type' })
    ) {
      writer.setAttribute(
        'dataEntityType',
        viewItem.getAttribute('data-entity-type'),
        image,
      );
      attributesToConsume.push('data-entity-type');
    }

    // Try to place the image in the allowed position.
    if (!safeInsert(image, data.modelCursor)) {
      return;
    }

    // Mark given element as consumed. Now other converters will not process it
    // anymore.
    consumable.consume(viewItem, {
      name: true,
      attributes: attributesToConsume,
    });

    // Make sure `modelRange` and `modelCursor` is up to date after inserting
    // new nodes into the model.
    updateConversionResult(image, data);
  }

  return (dispatcher) => {
    dispatcher.on('element:img', converter, { priority: 'high' });
  };
}

// Modified alternative implementation of linkimageediting.js' downcastImageLink.
function downcastBlockImageLink() {
  function converter(evt, data, conversionApi) {
    if (!conversionApi.consumable.consume(data.item, evt.name)) {
      return;
    }

    // The image will be already converted - so it will be present in the view.
    const image = conversionApi.mapper.toViewElement(data.item);
    const writer = conversionApi.writer;

    // 1. Create an empty link element.
    const linkElement = writer.createContainerElement('a', {
      href: data.attributeNewValue,
    });
    // 2. Insert link before the associated image.
    writer.insert(writer.createPositionBefore(image), linkElement);
    // 3. Move the image into the link.
    writer.move(
      writer.createRangeOn(image),
      writer.createPositionAt(linkElement, 0),
    );

    // Modified alternative implementation of GHS' addBlockImageLinkAttributeConversion().
    // This is happening here as well to avoid a race condition with the link
    // element not yet existing.
    if (
      conversionApi.consumable.consume(
        data.item,
        'attribute:htmlLinkAttributes:imageBlock',
      )
    ) {
      setViewAttributes(
        conversionApi.writer,
        data.item.getAttribute('htmlLinkAttributes'),
        linkElement,
      );
    }
  }

  return (dispatcher) => {
    dispatcher.on('attribute:linkHref:imageBlock', converter, {
      priority: 'high',
    });
  };
}

/**
 * @internal
 */
class DrupalImageEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  static get requires() {
    return ['ImageUtils'];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImageEditing';
  }

  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    const { conversion } = editor;
    const { schema } = editor.model;

    if (schema.isRegistered('imageInline')) {
      schema.extend('imageInline', {
        allowAttributes: [
          'dataEntityUuid',
          'dataEntityType',
          'width',
          'height',
        ],
      });
    }

    if (schema.isRegistered('imageBlock')) {
      schema.extend('imageBlock', {
        allowAttributes: [
          'dataEntityUuid',
          'dataEntityType',
          'width',
          'height',
        ],
      });
    }

    // Conversion.
    conversion
      .for('upcast')
      .add(viewImageToModelImage(editor))
      .attributeToAttribute({
        view: {
          name: 'img',
          key: 'width',
        },
        model: {
          key: 'width',
          value: (viewElement) => {
            if (isNumberString(viewElement.getAttribute('width'))) {
              return `${viewElement.getAttribute('width')}px`;
            }
            return `${viewElement.getAttribute('width')}`;
          },
        },
      })
      .attributeToAttribute({
        view: {
          name: 'img',
          key: 'height',
        },
        model: {
          key: 'height',
          value: (viewElement) => {
            if (isNumberString(viewElement.getAttribute('height'))) {
              return `${viewElement.getAttribute('height')}px`;
            }
            return `${viewElement.getAttribute('height')}`;
          },
        },
      });

    conversion
      .for('downcast')
      .add(modelEntityUuidToDataAttribute())
      .add(modelEntityTypeToDataAttribute());

    conversion
      .for('dataDowncast')
      .add(viewCaptionToCaptionAttribute(editor))
      .elementToElement({
        model: 'imageBlock',
        view: (modelElement, { writer }) =>
          createImageViewElement(writer, 'imageBlock'),
        converterPriority: 'high',
      })
      .elementToElement({
        model: 'imageInline',
        view: (modelElement, { writer }) =>
          createImageViewElement(writer, 'imageInline'),
        converterPriority: 'high',
      })
      .add(modelImageStyleToDataAttribute())
      .add(modelImageWidthToAttribute())
      .add(modelImageHeightToAttribute())
      .add(downcastBlockImageLink());
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalImage/src/drupalimage.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalimageediting */




/**
 * @internal
 */
class DrupalImage extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalImageEditing];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImage';
  }
}

/* harmony default export */ var drupalimage = (DrupalImage);

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalImage/src/imageupload/drupalimageuploadediting.js
/* eslint-disable import/no-extraneous-dependencies */


/**
 * @internal
 */
class DrupalImageUploadEditing extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  init() {
    const { editor } = this;
    const imageUploadEditing = editor.plugins.get('ImageUploadEditing');
    imageUploadEditing.on('uploadComplete', (evt, { data, imageElement }) => {
      editor.model.change((writer) => {
        writer.setAttribute(
          'dataEntityUuid',
          data.dataEntityUuid,
          imageElement,
        );
        writer.setAttribute(
          'dataEntityType',
          data.dataEntityType,
          imageElement,
        );
      });
    });
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImageUploadEditing';
  }
}

// EXTERNAL MODULE: delegated ./upload.js from dll-reference CKEditor5.dll
var delegated_uploadfrom_dll_reference_CKEditor5 = __webpack_require__(448);
// EXTERNAL MODULE: delegated ./utils.js from dll-reference CKEditor5.dll
var delegated_utilsfrom_dll_reference_CKEditor5 = __webpack_require__(209);
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalImage/src/imageupload/drupalimageuploadadapter.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words simpleuploadadapter filerepository */

/**
 * Upload adapter. Copied from @ckeditor5/ckeditor5-upload/src/adapters/simpleuploadadapter
 *
 * @internal
 * @implements {module:upload/filerepository~UploadAdapter}
 */
class DrupalImageUploadAdapter {
  /**
   * Creates a new adapter instance.
   *
   * @param {module:upload/filerepository~FileLoader} loader
   *   The file loader.
   * @param {module:upload/adapters/simpleuploadadapter~SimpleUploadConfig} options
   *   The upload options.
   */
  constructor(loader, options) {
    /**
     * FileLoader instance to use during the upload.
     *
     * @member {module:upload/filerepository~FileLoader} #loader
     */
    this.loader = loader;

    /**
     * The configuration of the adapter.
     *
     * @member {module:upload/adapters/simpleuploadadapter~SimpleUploadConfig} #options
     */
    this.options = options;
  }

  /**
   * Starts the upload process.
   *
   * @see module:upload/filerepository~UploadAdapter#upload
   * @return {Promise}
   *   Promise that the upload will be processed.
   */
  upload() {
    return this.loader.file.then(
      (file) =>
        new Promise((resolve, reject) => {
          this._initRequest();
          this._initListeners(resolve, reject, file);
          this._sendRequest(file);
        }),
    );
  }

  /**
   * Aborts the upload process.
   *
   * @see module:upload/filerepository~UploadAdapter#abort
   */
  abort() {
    if (this.xhr) {
      this.xhr.abort();
    }
  }

  /**
   * Initializes the `XMLHttpRequest` object using the URL specified as
   *
   * {@link module:upload/adapters/simpleuploadadapter~SimpleUploadConfig#uploadUrl `simpleUpload.uploadUrl`} in the editor's
   * configuration.
   */
  _initRequest() {
    this.xhr = new XMLHttpRequest();

    this.xhr.open('POST', this.options.uploadUrl, true);
    this.xhr.responseType = 'json';
  }

  /**
   * Initializes XMLHttpRequest listeners
   *
   * @private
   * @param {Function} resolve Callback function to be called when the request is successful.
   * @param {Function} reject Callback function to be called when the request cannot be completed.
   * @param {File} file Native File object.
   */
  _initListeners(resolve, reject, file) {
    const xhr = this.xhr;
    const loader = this.loader;
    const genericErrorText = `Couldn't upload file: ${file.name}.`;

    xhr.addEventListener('error', () => reject(genericErrorText));
    xhr.addEventListener('abort', () => reject());
    xhr.addEventListener('load', () => {
      const response = xhr.response;

      if (!response || response.error) {
        return reject(
          response && response.error && response.error.message
            ? response.error.message
            : genericErrorText,
        );
      }

      resolve({
        urls: { default: response.url },
        dataEntityUuid: response.uuid ? response.uuid : '',
        dataEntityType: response.entity_type ? response.entity_type : '',
      });
    });

    // Upload progress when it is supported.
    if (xhr.upload) {
      xhr.upload.addEventListener('progress', (evt) => {
        if (evt.lengthComputable) {
          loader.uploadTotal = evt.total;
          loader.uploaded = evt.loaded;
        }
      });
    }
  }

  /**
   * Prepares the data and sends the request.
   *
   * @param {File} file
   *   File instance to be uploaded.
   */
  _sendRequest(file) {
    // Set headers if specified.
    const headers = this.options.headers || {};

    // Use the withCredentials flag if specified.
    const withCredentials = this.options.withCredentials || false;

    Object.keys(headers).forEach((headerName) => {
      this.xhr.setRequestHeader(headerName, headers[headerName]);
    });

    this.xhr.withCredentials = withCredentials;

    // Prepare the form data.
    const data = new FormData();

    data.append('upload', file);

    // Send the request.
    this.xhr.send(data);
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalImage/src/imageupload/drupalfilerepository.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words uploadurl drupalimageuploadadapter  */






/**
 * @internal
 */
class DrupalFileRepository extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [delegated_uploadfrom_dll_reference_CKEditor5.FileRepository];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalFileRepository';
  }

  /**
   * @inheritdoc
   */
  init() {
    const options = this.editor.config.get('drupalImageUpload');

    if (!options) {
      return;
    }

    if (!options.uploadUrl) {
      (0,delegated_utilsfrom_dll_reference_CKEditor5.logWarning)('simple-upload-adapter-missing-uploadurl');

      return;
    }

    this.editor.plugins.get(delegated_uploadfrom_dll_reference_CKEditor5.FileRepository).createUploadAdapter = (loader) => {
      return new DrupalImageUploadAdapter(loader, options);
    };
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalImage/src/imageupload/drupalimageupload.js
/* eslint-disable import/no-extraneous-dependencies */
/* cspell:words drupalimageuploadediting drupalfilerepository */





/**
 * Integrates the CKEditor image upload with the Drupal.
 *
 * @internal
 */
class DrupalImageUpload extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalFileRepository, DrupalImageUploadEditing];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalImageUpload';
  }
}

/* harmony default export */ var drupalimageupload = (DrupalImageUpload);

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalImage/src/index.js
// cspell:ignore imageupload imageresize drupalimage drupalimageupload drupalimageresize




/**
 * @internal
 */
/* harmony default export */ var src = ({
  DrupalImage: drupalimage,
  DrupalImageUpload: drupalimageupload,
});

}();
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});