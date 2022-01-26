/*! cspell:disable */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CKEditor5"] = factory();
	else
		root["CKEditor5"] = root["CKEditor5"] || {}, root["CKEditor5"]["drupalHtmlEngine"] = factory();
})(self, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 704:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

module.exports = (__webpack_require__(79))("./src/core.js");

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
;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalHtmlEngine/src/drupalhtmlbuilder.js
// cSpell:words apos

/**
 * HTML builder that converts document fragments into strings.
 *
 * @internal
 */
class DrupalHtmlBuilder {
  /**
   * Constructs a new object.
   */
  constructor() {
    this.chunks = [];
    // @see https://html.spec.whatwg.org/multipage/syntax.html#elements-2
    this.selfClosingTags = [
      'area',
      'base',
      'br',
      'col',
      'embed',
      'hr',
      'img',
      'input',
      'link',
      'meta',
      'param',
      'source',
      'track',
      'wbr',
    ];
  }

  /**
   * Returns the current HTML string built from document fragments.
   *
   * @return {string}
   *   The HTML string built from document fragments.
   */
  build() {
    return this.chunks.join('');
  }

  /**
   * Converts document fragment into HTML string and appends to the value.
   *
   * @param {DocumentFragment} node
   *   A document fragment to be appended to the value.
   */
  appendNode(node) {
    if (node.nodeType === Node.TEXT_NODE) {
      this._appendText(node);
    } else if (node.nodeType === Node.ELEMENT_NODE) {
      this._appendElement(node);
    } else if (node.nodeType === Node.DOCUMENT_FRAGMENT_NODE) {
      this._appendChildren(node);
    }
  }

  /**
   * Appends element node to the value.
   *
   * @param {DocumentFragment} node
   *   A document fragment to be appended to the value.
   *
   * @private
   */
  _appendElement(node) {
    const nodeName = node.nodeName.toLowerCase();

    this._append('<');
    this._append(nodeName);
    this._appendAttributes(node);
    this._append('>');
    if (!this.selfClosingTags.includes(nodeName)) {
      this._appendChildren(node);
      this._append('</');
      this._append(nodeName);
      this._append('>');
    }
  }

  /**
   * Appends child nodes to the value.
   *
   * @param {DocumentFragment} node
   *  A document fragment to be appended to the value.
   *
   * @private
   */
  _appendChildren(node) {
    Object.keys(node.childNodes).forEach((child) => {
      this.appendNode(node.childNodes[child]);
    });
  }

  /**
   * Appends attributes to the value.
   *
   * @param {DocumentFragment} node
   *  A document fragment to be appended to the value.
   *
   * @private
   */
  _appendAttributes(node) {
    Object.keys(node.attributes).forEach((attr) => {
      this._append(' ');
      this._append(node.attributes[attr].name);
      this._append('="');
      this._append(
        this.constructor._escapeAttribute(node.attributes[attr].value),
      );
      this._append('"');
    });
  }

  /**
   * Appends text to the value.
   *
   * @param {DocumentFragment} node
   *  A document fragment to be appended to the value.
   *
   * @private
   */
  _appendText(node) {
    // Text node doesn't have innerHTML property and textContent doesn't encode
    // entities. That's why the text is repacked into another node and extracted
    // using innerHTML.
    const doc = document.implementation.createHTMLDocument('');
    const container = doc.createElement('p');
    container.textContent = node.textContent;

    this._append(container.innerHTML);
  }

  /**
   * Appends string to the value.
   *
   * @param {string} str
   *  A string to be appended to the value.
   *
   * @private
   */
  _append(str) {
    this.chunks.push(str);
  }

  /**
   * Escapes attribute value for compatibility with Drupal's XSS filtering.
   *
   * Drupal's XSS filtering cannot handle entities inside element attribute
   * values. The XSS filtering was written based on W3C XML recommendations
   * which constituted that the ampersand character (&) and the angle
   * brackets (< and >) must not appear in their literal form in attribute
   * values. This differs from the HTML living standard which permits angle
   * brackets.
   *
   * @param {string} text
   *  A string to be escaped.
   *
   * @see https://www.w3.org/TR/2008/REC-xml-20081126/#NT-AttValue
   * @see https://html.spec.whatwg.org/multipage/parsing.html#attribute-value-(single-quoted)-state
   * @see https://www.drupal.org/project/drupal/issues/3227831
   *
   * @private
   */
  static _escapeAttribute(text) {
    return text
      .replace(/&/g, '&amp;')
      .replace(/'/g, '&apos;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\r\n/g, '&#13;')
      .replace(/[\r\n]/g, '&#13;');
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalHtmlEngine/src/drupalhtmlwriter.js
// cSpell:words drupalhtmlbuilder dataprocessor basichtmlwriter htmlwriter


/**
 * Custom HTML writer. It creates HTML by traversing DOM nodes.
 *
 * It differs to BasicHtmlWriter in the way it encodes entities in element
 * attributes.
 *
 * @see module:engine/dataprocessor/basichtmlwriter~BasicHtmlWriter
 * @implements {module:engine/dataprocessor/htmlwriter~HtmlWriter}
 *
 * @see https://www.drupal.org/project/drupal/issues/3227831
 *
 * @internal
 */
class DrupalHtmlWriter {
  /**
   * Returns an HTML string created from the document fragment.
   *
   * @param {DocumentFragment} fragment
   * @return {String}
   */
  // eslint-disable-next-line class-methods-use-this
  getHtml(fragment) {
    const builder = new DrupalHtmlBuilder();
    builder.appendNode(fragment);

    return builder.build();
  }
}

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalHtmlEngine/src/drupalhtmlengine.js
/* eslint-disable import/no-extraneous-dependencies */
// cSpell:words drupalhtmlwriter



/**
 * A plugin that overrides the CKEditor HTML writer.
 *
 * Override the CKEditor 5 HTML writer to escape ampersand characters (&) and
 * the angle brackets (< and >). This is required because
 * \Drupal\Component\Utility\Xss::filter fails to parse element attributes with
 * unescaped entities in value.
 *
 * @see https://www.drupal.org/project/drupal/issues/3227831
 * @see DrupalHtmlBuilder._escapeAttribute
 *
 * @internal
 */
class DrupalHtmlEngine extends delegated_corefrom_dll_reference_CKEditor5.Plugin {
  /**
   * @inheritdoc
   */
  init() {
    this.editor.data.processor.htmlWriter = new DrupalHtmlWriter();
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalHtmlEngine';
  }
}

/* harmony default export */ var drupalhtmlengine = (DrupalHtmlEngine);

;// CONCATENATED MODULE: ./modules/ckeditor5/js/ckeditor5_plugins/drupalHtmlEngine/src/index.js
// cspell:ignore drupalengine drupalhtmlengine


/**
 * @internal
 */
/* harmony default export */ var src = ({
  DrupalHtmlEngine: drupalhtmlengine,
});

}();
__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});