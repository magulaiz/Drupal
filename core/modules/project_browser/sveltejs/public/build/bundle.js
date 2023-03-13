/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./modules/project_browser/sveltejs/src/constants.js":
/*!***********************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/constants.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "ACTIVELY_MAINTAINED_ID": () => (/* binding */ ACTIVELY_MAINTAINED_ID),
/* harmony export */   "ALLOW_UI_INSTALL": () => (/* binding */ ALLOW_UI_INSTALL),
/* harmony export */   "ALL_VALUES_ID": () => (/* binding */ ALL_VALUES_ID),
/* harmony export */   "COVERED_ID": () => (/* binding */ COVERED_ID),
/* harmony export */   "CURRENT_SOURCES_KEYS": () => (/* binding */ CURRENT_SOURCES_KEYS),
/* harmony export */   "DARK_COLOR_SCHEME": () => (/* binding */ DARK_COLOR_SCHEME),
/* harmony export */   "DEFAULT_SOURCE_ID": () => (/* binding */ DEFAULT_SOURCE_ID),
/* harmony export */   "DEVELOPMENT_OPTIONS": () => (/* binding */ DEVELOPMENT_OPTIONS),
/* harmony export */   "FULL_MODULE_PATH": () => (/* binding */ FULL_MODULE_PATH),
/* harmony export */   "MAINTENANCE_OPTIONS": () => (/* binding */ MAINTENANCE_OPTIONS),
/* harmony export */   "MODULE_STATUS": () => (/* binding */ MODULE_STATUS),
/* harmony export */   "ORIGIN_URL": () => (/* binding */ ORIGIN_URL),
/* harmony export */   "PM_VALIDATION_ERROR": () => (/* binding */ PM_VALIDATION_ERROR),
/* harmony export */   "SECURITY_OPTIONS": () => (/* binding */ SECURITY_OPTIONS),
/* harmony export */   "SORT_OPTIONS": () => (/* binding */ SORT_OPTIONS)
/* harmony export */ });
const MAINTENANCE_OPTIONS =
  drupalSettings.project_browser.maintenance_options;
const SECURITY_OPTIONS =
  drupalSettings.project_browser.security_options;
const DEVELOPMENT_OPTIONS =
  drupalSettings.project_browser.development_options;
const SORT_OPTIONS =
  drupalSettings.project_browser.sort_options;
const ACTIVELY_MAINTAINED_ID =
  drupalSettings.project_browser.special_ids.maintenance_status.id;
const COVERED_ID =
  drupalSettings.project_browser.special_ids.security_coverage.id;
const ALL_VALUES_ID =
  drupalSettings.project_browser.special_ids.all_values;
const DEFAULT_SOURCE_ID =
  drupalSettings.project_browser.default_plugin_id;
const CURRENT_SOURCES_KEYS =
  drupalSettings.project_browser.current_sources_keys;
const ORIGIN_URL =
  drupalSettings.project_browser.origin_url;
const MODULE_STATUS =
  drupalSettings.project_browser.modules;
const FULL_MODULE_PATH =
  `${ORIGIN_URL}/${drupalSettings.project_browser.module_path}`;
const ALLOW_UI_INSTALL =
  drupalSettings.project_browser.ui_install;
const DARK_COLOR_SCHEME = matchMedia('(forced-colors: active)').matches && matchMedia('(prefers-color-scheme: dark)').matches;
const PM_VALIDATION_ERROR =
  drupalSettings.project_browser.pm_validation;


/***/ }),

/***/ "./modules/project_browser/sveltejs/src/popup.js":
/*!*******************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/popup.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "copyCommand": () => (/* binding */ copyCommand),
/* harmony export */   "getCommandsPopupMessage": () => (/* binding */ getCommandsPopupMessage),
/* harmony export */   "openPopup": () => (/* binding */ openPopup)
/* harmony export */ });
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./constants */ "./modules/project_browser/sveltejs/src/constants.js");


const copyCommand = (cmd, project) =>  {
  const copiedCommand = document.getElementById(
    cmd === 'Download'
      ? `${project.project_machine_name}-download-command`
      : `${project.project_machine_name}-install-command`,
  );
  copiedCommand.select();
  // For mobile devices.
  copiedCommand.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(copiedCommand.value);
  const copyReceipt = document.getElementById(
    cmd === 'Download'
      ? `${project.project_machine_name}-copied-download`
      : `${project.project_machine_name}-copied-install`,
  );
  copyReceipt.style.opacity = '1';
  setTimeout(() => {
    copyReceipt.style.transition = 'opacity 0.3s';
    copyReceipt.style.opacity = '0';
  }, 1000);
}

const getCommandsPopupMessage = (project) =>  {
  const download = Drupal.t('Download');
  const composerText = Drupal.t(
    'The !use_composer_open recommended way to download any Drupal module!close is with !get_composer_open Composer!close.</a>',
    {
      '!close': '</a>',
      '!use_composer_open':
        '<a href="https://www.drupal.org/docs/develop/using-composer/using-composer-to-install-drupal-and-manage-dependencies#managing-contributed" target="_blank" rel="noreferrer">',
      '!get_composer_open':
        '<a href="https://getcomposer.org/" target="_blank">',
    },
  );
  const composerExistsText = Drupal.t(
    "If you already manage your Drupal application dependencies with Composer, run the following from the command line in your application's Composer root directory",
  );
  const infoText = Drupal.t(
    'This will download the module to your codebase.',
  );
  const composerDontWorkText = Drupal.t(
    "Didn't work? !learn_open Learn how to troubleshoot Composer!close",
    {
      '!learn_open':
        '<a href="https://getcomposer.org/doc/articles/troubleshooting.md" target="_blank" rel="noreferrer">',
      '!close': '</a>',
    },
  );
  const downloadModuleText = Drupal.t(
    'If you cannot use Composer, you may !dl_manually_open download the module manually through your browser!close',
    {
      '!dl_manually_open':
        '<a href="https://www.drupal.org/docs/user_guide/en/extend-module-install.html#s-using-the-administrative-interface" target="_blank" rel="noreferrer">',
      '!close': '</a>',
    },
  );
  const install = Drupal.t('Install');
  const installText = Drupal.t(
    'To use the module you must next install it. Visit the !module_page_open modules page!close to install the module using your web browser!close',
    {
      '!module_page_open': `<a href="${_constants__WEBPACK_IMPORTED_MODULE_0__.ORIGIN_URL}/admin/modules#module-${project.project_machine_name}" target="_blank" rel="noreferrer">`,
      '!close': '</a>',
    },
  );
  const drushText = Drupal.t(
    'Alternatively, you can use !drush_openDrush!close to install it via the command line',
    {
      '!drush_open':
        '<a href="https://www.drush.org/latest/" target="_blank">',
      '!close': '</a>',
    },
  );
  const copied = Drupal.t('Copied!');
  const downloadCopyButton = navigator.clipboard ? `<button id="download-btn"><img src="${_constants__WEBPACK_IMPORTED_MODULE_0__.FULL_MODULE_PATH}/images/copy-icon.svg" alt="${Drupal.t('Copy the download command')}"/></button>
                <div id="${project.project_machine_name}-copied-download" class="copied-download">${copied}</div>` : '';
  const installCopyButton = navigator.clipboard ? `<button id="install-btn"><img src="${_constants__WEBPACK_IMPORTED_MODULE_0__.FULL_MODULE_PATH}/images/copy-icon.svg" alt="${Drupal.t('Copy the install command')}"/></button>
                <div id="${project.project_machine_name}-copied-install" class="copied-install">${copied}</div>` : '';

  const div = document.createElement('div');
  div.classList.add('window');
  div.innerHTML = `<h3>1. ${download}</h3>
              <p>${composerText}</p>
              <p>${composerExistsText}:</p>
              <div id="download-cmd">
                <input id="${project.project_machine_name}-download-command" value="composer require ${project.composer_namespace}" readonly/>
                ${downloadCopyButton}
              </div>
              <p>${infoText}</p>
              <p>${composerDontWorkText}.</p>
              <p>${downloadModuleText}.</p>
              <h3>2. ${install}</h3>
              <p>${installText}.</p>
              <p>${drushText}:</p>
              <div id="install-cmd">
                <input id="${project.project_machine_name}-install-command" value="drush pm:install ${project.project_machine_name}" readonly/>
                ${installCopyButton}
              </div>`;
  if (navigator.clipboard) {
    div.querySelector('#download-btn').addEventListener('click', () => {
      copyCommand('Download', project);
    });
    div.querySelector('#install-btn').addEventListener('click', () => {
      copyCommand('Install', project);
    });
  }
  return div;
}

const openPopup = (getMessage, project) =>  {
  const message =
    typeof getMessage === 'function' ? getMessage() : getMessage;
  const popupModal = Drupal.dialog(message, {
    title: project.title,
    dialogClass: 'project-browser-popup',
    width: '50rem',
  });
  popupModal.showModal();
}


/***/ }),

/***/ "./modules/project_browser/sveltejs/src/stores.js":
/*!********************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/stores.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "activeTab": () => (/* binding */ activeTab),
/* harmony export */   "categoryCheckedTrack": () => (/* binding */ categoryCheckedTrack),
/* harmony export */   "filters": () => (/* binding */ filters),
/* harmony export */   "filtersVocabularies": () => (/* binding */ filtersVocabularies),
/* harmony export */   "focusedElement": () => (/* binding */ focusedElement),
/* harmony export */   "isFirstLoad": () => (/* binding */ isFirstLoad),
/* harmony export */   "moduleCategoryFilter": () => (/* binding */ moduleCategoryFilter),
/* harmony export */   "moduleCategoryVocabularies": () => (/* binding */ moduleCategoryVocabularies),
/* harmony export */   "page": () => (/* binding */ page),
/* harmony export */   "pageSize": () => (/* binding */ pageSize),
/* harmony export */   "preferredView": () => (/* binding */ preferredView),
/* harmony export */   "rowsCount": () => (/* binding */ rowsCount),
/* harmony export */   "searchString": () => (/* binding */ searchString),
/* harmony export */   "sort": () => (/* binding */ sort),
/* harmony export */   "sortCriteria": () => (/* binding */ sortCriteria)
/* harmony export */ });
/* harmony import */ var svelte_store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/store */ "./node_modules/svelte/store/index.mjs");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants */ "./modules/project_browser/sveltejs/src/constants.js");




// Store for applied advanced filters.
const storedFilters = JSON.parse(sessionStorage.getItem('advancedFilter')) || {
  developmentStatus: '',
  maintenanceStatus: '',
  securityCoverage: ''
};
const filters = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedFilters);
filters.subscribe((val) => sessionStorage.setItem('advancedFilter', JSON.stringify(val)));

const rowsCount = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(0);

const filtersVocabularies = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)({
  developmentStatus: JSON.parse(localStorage.getItem('pb.developmentStatus')) || [],
  maintenanceStatus: JSON.parse(localStorage.getItem('pb.maintenanceStatus')) || [],
  securityCoverage: JSON.parse(localStorage.getItem('pb.securityCoverage')) || []
});

// Store for applied category filters.
const storedModuleCategoryFilter = JSON.parse(sessionStorage.getItem('categoryFilter')) || [];
const moduleCategoryFilter = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedModuleCategoryFilter);
moduleCategoryFilter.subscribe((val) => sessionStorage.setItem('categoryFilter', JSON.stringify(val)));

// Store for module category vocabularies.
const storedModuleCategoryVocabularies = JSON.parse(localStorage.getItem('moduleCategoryVocabularies')) || {};
const moduleCategoryVocabularies = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedModuleCategoryVocabularies);
moduleCategoryVocabularies.subscribe((val) => localStorage.setItem('moduleCategoryVocabularies', JSON.stringify(val)));

// Store used to check if the page has loaded once already.
const storedIsFirstLoad = JSON.parse(sessionStorage.getItem('isFirstLoad')) === false ? JSON.parse(sessionStorage.getItem('isFirstLoad')) : true;
const isFirstLoad = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedIsFirstLoad);
isFirstLoad.subscribe((val) => sessionStorage.setItem('isFirstLoad', JSON.stringify(val)));

// Store the page the user is on.
const storedPage = JSON.parse(sessionStorage.getItem('page')) || 0;
const page = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedPage);
page.subscribe((val) => sessionStorage.setItem('page', JSON.stringify(val)));

// Store the selected tab.
const storedActiveTab = JSON.parse(sessionStorage.getItem('activeTab')) || _constants__WEBPACK_IMPORTED_MODULE_1__.DEFAULT_SOURCE_ID;
const activeTab = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedActiveTab);
activeTab.subscribe((val) => sessionStorage.setItem('activeTab', JSON.stringify(val)));

// Store the current sort selected.
const storedSort = JSON.parse(sessionStorage.getItem('sort')) || _constants__WEBPACK_IMPORTED_MODULE_1__.SORT_OPTIONS[storedActiveTab][0].id;
const sort = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedSort);
sort.subscribe((val) => sessionStorage.setItem('sort', JSON.stringify(val)));

// Store tab-wise checked categories.
const storedCategoryCheckedTrack = JSON.parse(sessionStorage.getItem('categoryCheckedTrack')) || {};
const categoryCheckedTrack = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedCategoryCheckedTrack);
categoryCheckedTrack.subscribe((val) => sessionStorage.setItem('categoryCheckedTrack', JSON.stringify(val)));

// Store the element that was last focused.
const storedFocus = JSON.parse(sessionStorage.getItem('focusedElement')) || '';
const focusedElement = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedFocus);
focusedElement.subscribe((val) => sessionStorage.setItem('focusedElement', JSON.stringify(val)));

// Store the search string.
const storedSearchString = JSON.parse(sessionStorage.getItem('searchString')) || '';
const searchString = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedSearchString);
searchString.subscribe((val) => sessionStorage.setItem('searchString', JSON.stringify(val)));

// Store for sort criteria.
const storedSortCriteria = JSON.parse(sessionStorage.getItem('sortCriteria')) || _constants__WEBPACK_IMPORTED_MODULE_1__.SORT_OPTIONS[storedActiveTab];
const sortCriteria = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedSortCriteria);
sortCriteria.subscribe((val) => sessionStorage.setItem('sortCriteria', JSON.stringify(val)));

// Store the selected toggle view.
const storedPreferredView = JSON.parse(sessionStorage.getItem('preferredView')) || 'Grid';
const preferredView = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedPreferredView);
preferredView.subscribe((val) => sessionStorage.setItem('preferredView', JSON.stringify(val)));

// Store the selected page size.
const storedPageSize = JSON.parse(sessionStorage.getItem('pageSize')) || 12;
const pageSize = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(storedPageSize);
pageSize.subscribe((val) => sessionStorage.setItem('pageSize', JSON.stringify(val)));


/***/ }),

/***/ "./modules/project_browser/sveltejs/src/util.js":
/*!******************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/util.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "normalizeOptions": () => (/* binding */ normalizeOptions),
/* harmony export */   "shallowCompare": () => (/* binding */ shallowCompare)
/* harmony export */ });
const normalizeOptions = (value) => {
  const newValue = {};
  const isArray = Array.isArray(value);
  if (isArray) {
    Object.values(value).forEach((item) => {
      newValue[item.id] = item.name;
    });
  }
  else {
    Object.entries(value).forEach(([id, name]) => {
      newValue[id] = name;
    });
  }

  return newValue;
};

const shallowCompare = (obj1, obj2) =>
  Object.keys(obj1).length === Object.keys(obj2).length &&
  Object.keys(obj1).every(key =>
    obj2.hasOwnProperty(key) && obj1[key] === obj2[key]
);


/***/ }),

/***/ "./node_modules/svelte-hmr/runtime/hot-api.js":
/*!****************************************************!*\
  !*** ./node_modules/svelte-hmr/runtime/hot-api.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "makeApplyHmr": () => (/* binding */ makeApplyHmr)
/* harmony export */ });
/* harmony import */ var _proxy_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./proxy.js */ "./node_modules/svelte-hmr/runtime/proxy.js");
/* eslint-env browser */



const logPrefix = '[HMR:Svelte]'

// eslint-disable-next-line no-console
const log = (...args) => console.log(logPrefix, ...args)

const domReload = () => {
  // eslint-disable-next-line no-undef
  const win = typeof window !== 'undefined' && window
  if (win && win.location && win.location.reload) {
    log('Reload')
    win.location.reload()
  } else {
    log('Full reload required')
  }
}

const replaceCss = (previousId, newId) => {
  if (typeof document === 'undefined') return false
  if (!previousId) return false
  if (!newId) return false
  // svelte-xxx-style => svelte-xxx
  const previousClass = previousId.slice(0, -6)
  const newClass = newId.slice(0, -6)
  // eslint-disable-next-line no-undef
  document.querySelectorAll('.' + previousClass).forEach(el => {
    el.classList.remove(previousClass)
    el.classList.add(newClass)
  })
  return true
}

const removeStylesheet = cssId => {
  if (cssId == null) return
  if (typeof document === 'undefined') return
  // eslint-disable-next-line no-undef
  const el = document.getElementById(cssId)
  if (el) el.remove()
  return
}

const defaultArgs = {
  reload: domReload,
}

const makeApplyHmr = transformArgs => args => {
  const allArgs = transformArgs({ ...defaultArgs, ...args })
  return applyHmr(allArgs)
}

let needsReload = false

function applyHmr(args) {
  const {
    id,
    cssId,
    nonCssHash,
    reload = domReload,
    // normalized hot API (must conform to rollup-plugin-hot)
    hot,
    hotOptions,
    Component,
    acceptable, // some types of components are impossible to HMR correctly
    preserveLocalState,
    ProxyAdapter,
    emitCss,
  } = args

  const existing = hot.data && hot.data.record

  const canAccept = acceptable && (!existing || existing.current.canAccept)

  const r =
    existing ||
    (0,_proxy_js__WEBPACK_IMPORTED_MODULE_0__.createProxy)({
      Adapter: ProxyAdapter,
      id,
      Component,
      hotOptions,
      canAccept,
      preserveLocalState,
    })

  const cssOnly =
    hotOptions.injectCss &&
    existing &&
    nonCssHash &&
    existing.current.nonCssHash === nonCssHash

  r.update({
    Component,
    hotOptions,
    canAccept,
    nonCssHash,
    cssId,
    previousCssId: r.current.cssId,
    cssOnly,
    preserveLocalState,
  })

  hot.dispose(data => {
    // handle previous fatal errors
    if (needsReload || (0,_proxy_js__WEBPACK_IMPORTED_MODULE_0__.hasFatalError)()) {
      if (hotOptions && hotOptions.noReload) {
        log('Full reload required')
      } else {
        reload()
      }
    }

    // 2020-09-21 Snowpack master doesn't pass data as arg to dispose handler
    data = data || hot.data

    data.record = r

    if (!emitCss && cssId && r.current.cssId !== cssId) {
      if (hotOptions.cssEjectDelay) {
        setTimeout(() => removeStylesheet(cssId), hotOptions.cssEjectDelay)
      } else {
        removeStylesheet(cssId)
      }
    }
  })

  if (canAccept) {
    hot.accept(async arg => {
      const { bubbled } = arg || {}

      // NOTE Snowpack registers accept handlers only once, so we can NOT rely
      // on the surrounding scope variables -- they're not the last version!
      const { cssId: newCssId, previousCssId } = r.current
      const cssChanged = newCssId !== previousCssId
      // ensure old style sheet has been removed by now
      if (!emitCss && cssChanged) removeStylesheet(previousCssId)
      // guard: css only change
      if (
        // NOTE bubbled is provided only by rollup-plugin-hot, and we
        // can't safely assume a CSS only change without it... this means we
        // can't support CSS only injection with Nollup or Webpack currently
        bubbled === false && // WARNING check false, not falsy!
        r.current.cssOnly &&
        (!cssChanged || replaceCss(previousCssId, newCssId))
      ) {
        return
      }

      const success = await r.reload()

      if ((0,_proxy_js__WEBPACK_IMPORTED_MODULE_0__.hasFatalError)() || (!success && !hotOptions.optimistic)) {
        needsReload = true
      }
    })
  }

  // well, endgame... we won't be able to render next updates, even successful,
  // if we don't have proxies in svelte's tree
  //
  // since we won't return the proxy and the app will expect a svelte component,
  // it's gonna crash... so it's best to report the real cause
  //
  // full reload required
  //
  const proxyOk = r && r.proxy
  if (!proxyOk) {
    throw new Error(`Failed to create HMR proxy for Svelte component ${id}`)
  }

  return r.proxy
}


/***/ }),

/***/ "./node_modules/svelte-hmr/runtime/index.js":
/*!**************************************************!*\
  !*** ./node_modules/svelte-hmr/runtime/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "makeApplyHmr": () => (/* reexport safe */ _hot_api_js__WEBPACK_IMPORTED_MODULE_0__.makeApplyHmr)
/* harmony export */ });
/* harmony import */ var _hot_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./hot-api.js */ "./node_modules/svelte-hmr/runtime/hot-api.js");



/***/ }),

/***/ "./node_modules/svelte-hmr/runtime/overlay.js":
/*!****************************************************!*\
  !*** ./node_modules/svelte-hmr/runtime/overlay.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* eslint-env browser */

const removeElement = el => el && el.parentNode && el.parentNode.removeChild(el)

const ErrorOverlay = () => {
  let errors = []
  let compileError = null

  const errorsTitle = 'Failed to init component'
  const compileErrorTitle = 'Failed to compile'

  const style = {
    section: `
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      padding: 32px;
      background: rgba(0, 0, 0, .85);
      font-family: Menlo, Consolas, monospace;
      font-size: large;
      color: rgb(232, 232, 232);
      overflow: auto;
      z-index: 2147483647;
    `,
    h1: `
      margin-top: 0;
      color: #E36049;
      font-size: large;
      font-weight: normal;
    `,
    h2: `
      margin: 32px 0 0;
      font-size: large;
      font-weight: normal;
    `,
    pre: ``,
  }

  const createOverlay = () => {
    const h1 = document.createElement('h1')
    h1.style = style.h1
    const section = document.createElement('section')
    section.appendChild(h1)
    section.style = style.section
    const body = document.createElement('div')
    section.appendChild(body)
    return { h1, el: section, body }
  }

  const setTitle = title => {
    overlay.h1.textContent = title
  }

  const show = () => {
    const { el } = overlay
    if (!el.parentNode) {
      const target = document.body
      target.appendChild(overlay.el)
    }
  }

  const hide = () => {
    const { el } = overlay
    if (el.parentNode) {
      overlay.el.remove()
    }
  }

  const update = () => {
    if (compileError) {
      overlay.body.innerHTML = ''
      setTitle(compileErrorTitle)
      const errorEl = renderError(compileError)
      overlay.body.appendChild(errorEl)
      show()
    } else if (errors.length > 0) {
      overlay.body.innerHTML = ''
      setTitle(errorsTitle)
      errors.forEach(({ title, message }) => {
        const errorEl = renderError(message, title)
        overlay.body.appendChild(errorEl)
      })
      show()
    } else {
      hide()
    }
  }

  const renderError = (message, title) => {
    const div = document.createElement('div')
    if (title) {
      const h2 = document.createElement('h2')
      h2.textContent = title
      h2.style = style.h2
      div.appendChild(h2)
    }
    const pre = document.createElement('pre')
    pre.textContent = message
    div.appendChild(pre)
    return div
  }

  const addError = (error, title) => {
    const message = (error && error.stack) || error
    errors.push({ title, message })
    update()
  }

  const clearErrors = () => {
    errors.forEach(({ element }) => {
      removeElement(element)
    })
    errors = []
    update()
  }

  const setCompileError = message => {
    compileError = message
    update()
  }

  const overlay = createOverlay()

  return {
    addError,
    clearErrors,
    setCompileError,
  }
}

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ErrorOverlay);


/***/ }),

/***/ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js":
/*!**************************************************************!*\
  !*** ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "adapter": () => (/* binding */ adapter),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _overlay_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./overlay.js */ "./node_modules/svelte-hmr/runtime/overlay.js");
/* global window, document */

// NOTE from 3.38.3 (or so), insert was carrying the hydration logic, that must
// be used because DOM elements are reused more (and so insertion points are not
// necessarily added in order); then in 3.40 the logic was moved to
// insert_hydration, which is the one we must use for HMR
const svelteInsert = svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_hydration || svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert
if (!svelteInsert) {
  throw new Error(
    'failed to find insert_hydration and insert in svelte/internal'
  )
}



const removeElement = el => el && el.parentNode && el.parentNode.removeChild(el)

const adapter = class ProxyAdapterDom {
  constructor(instance) {
    this.instance = instance
    this.insertionPoint = null

    this.afterMount = this.afterMount.bind(this)
    this.rerender = this.rerender.bind(this)

    this._noOverlay = !!instance.hotOptions.noOverlay
  }

  // NOTE overlay is only created before being actually shown to help test
  // runner (it won't have to account for error overlay when running assertions
  // about the contents of the rendered page)
  static getErrorOverlay(noCreate = false) {
    if (!noCreate && !this.errorOverlay) {
      this.errorOverlay = (0,_overlay_js__WEBPACK_IMPORTED_MODULE_1__["default"])()
    }
    return this.errorOverlay
  }

  // TODO this is probably unused now: remove in next breaking release
  static renderCompileError(message) {
    const noCreate = !message
    const overlay = this.getErrorOverlay(noCreate)
    if (!overlay) return
    overlay.setCompileError(message)
  }

  dispose() {
    // Component is being destroyed, detaching is not optional in Svelte3's
    // component API, so we can dispose of the insertion point in every case.
    if (this.insertionPoint) {
      removeElement(this.insertionPoint)
      this.insertionPoint = null
    }
    this.clearError()
  }

  // NOTE afterMount CAN be called multiple times (e.g. keyed list)
  afterMount(target, anchor) {
    const {
      instance: { debugName },
    } = this
    if (!this.insertionPoint) {
      this.insertionPoint = document.createComment(debugName)
    }
    svelteInsert(target, this.insertionPoint, anchor)
  }

  rerender() {
    this.clearError()
    const {
      instance: { refreshComponent },
      insertionPoint,
    } = this
    if (!insertionPoint) {
      throw new Error('Cannot rerender: missing insertion point')
    }
    refreshComponent(insertionPoint.parentNode, insertionPoint)
  }

  renderError(err) {
    if (this._noOverlay) return
    const {
      instance: { debugName },
    } = this
    const title = debugName || err.moduleName || 'Error'
    this.constructor.getErrorOverlay().addError(err, title)
  }

  clearError() {
    if (this._noOverlay) return
    const overlay = this.constructor.getErrorOverlay(true)
    if (!overlay) return
    overlay.clearErrors()
  }
}

// TODO this is probably unused now: remove in next breaking release
if (typeof window !== 'undefined') {
  window.__SVELTE_HMR_ADAPTER = adapter
}

// mitigate situation with Snowpack remote source pulling latest of runtime,
// but using previous version of the Node code transform in the plugin
// see: https://github.com/rixo/svelte-hmr/issues/27
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (adapter);


/***/ }),

/***/ "./node_modules/svelte-hmr/runtime/proxy.js":
/*!**************************************************!*\
  !*** ./node_modules/svelte-hmr/runtime/proxy.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "createProxy": () => (/* binding */ createProxy),
/* harmony export */   "hasFatalError": () => (/* binding */ hasFatalError)
/* harmony export */ });
/* harmony import */ var _svelte_hooks_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./svelte-hooks.js */ "./node_modules/svelte-hmr/runtime/svelte-hooks.js");
/* eslint-env browser */
/**
 * The HMR proxy is a component-like object whose task is to sit in the
 * component tree in place of the proxied component, and rerender each
 * successive versions of said component.
 */



const handledMethods = ['constructor', '$destroy']
const forwardedMethods = ['$set', '$on']

const logError = (msg, err) => {
  // eslint-disable-next-line no-console
  console.error('[HMR][Svelte]', msg)
  if (err) {
    // NOTE avoid too much wrapping around user errors
    // eslint-disable-next-line no-console
    console.error(err)
  }
}

const posixify = file => file.replace(/[/\\]/g, '/')

const getBaseName = id =>
  id
    .split('/')
    .pop()
    .split('.')
    .slice(0, -1)
    .join('.')

const capitalize = str => str[0].toUpperCase() + str.slice(1)

const getFriendlyName = id => capitalize(getBaseName(posixify(id)))

const getDebugName = id => `<${getFriendlyName(id)}>`

const relayCalls = (getTarget, names, dest = {}) => {
  for (const key of names) {
    dest[key] = function(...args) {
      const target = getTarget()
      if (!target) {
        return
      }
      return target[key] && target[key].call(this, ...args)
    }
  }
  return dest
}

const isInternal = key => key !== '$$' && key.slice(0, 2) === '$$'

// This is intented as a somewhat generic / prospective fix to the situation
// that arised with the introduction of $$set in Svelte 3.24.1 -- trying to
// avoid giving full knowledge (like its name) of this implementation detail
// to the proxy. The $$set method can be present or not on the component, and
// its presence impacts the behaviour (but with HMR it will be tested if it is
// present _on the proxy_). So the idea here is to expose exactly the same $$
// props as the current version of the component and, for those that are
// functions, proxy the calls to the current component.
const relayInternalMethods = (proxy, cmp) => {
  // delete any previously added $$ prop
  Object.keys(proxy)
    .filter(isInternal)
    .forEach(key => {
      delete proxy[key]
    })
  // guard: no component
  if (!cmp) return
  // proxy current $$ props to the actual component
  Object.keys(cmp)
    .filter(isInternal)
    .forEach(key => {
      Object.defineProperty(proxy, key, {
        configurable: true,
        get() {
          const value = cmp[key]
          if (typeof value !== 'function') return value
          return (
            value &&
            function(...args) {
              return value.apply(this, args)
            }
          )
        },
      })
    })
}

// proxy custom methods
const copyComponentProperties = (proxy, cmp, previous) => {
  if (previous) {
    previous.forEach(prop => {
      delete proxy[prop]
    })
  }

  const props = Object.getOwnPropertyNames(Object.getPrototypeOf(cmp))
  const wrappedProps = props.filter(prop => {
    if (!handledMethods.includes(prop) && !forwardedMethods.includes(prop)) {
      Object.defineProperty(proxy, prop, {
        configurable: true,
        get() {
          return cmp[prop]
        },
        set(value) {
          // we're changing it on the real component first to see what it
          // gives... if it throws an error, we want to throw the same error in
          // order to most closely follow non-hmr behaviour.
          cmp[prop] = value
        },
      })
      return true
    }
  })

  return wrappedProps
}

// everything in the constructor!
//
// so we don't polute the component class with new members
//
class ProxyComponent {
  constructor(
    {
      Adapter,
      id,
      debugName,
      current, // { Component, hotOptions: { preserveLocalState, ... } }
      register,
    },
    options // { target, anchor, ... }
  ) {
    let cmp
    let disposed = false
    let lastError = null

    const setComponent = _cmp => {
      cmp = _cmp
      relayInternalMethods(this, cmp)
    }

    const getComponent = () => cmp

    const destroyComponent = () => {
      // destroyComponent is tolerant (don't crash on no cmp) because it
      // is possible that reload/rerender is called after a previous
      // createComponent has failed (hence we have a proxy, but no cmp)
      if (cmp) {
        cmp.$destroy()
        setComponent(null)
      }
    }

    const refreshComponent = (target, anchor, conservativeDestroy) => {
      if (lastError) {
        lastError = null
        adapter.rerender()
      } else {
        try {
          const replaceOptions = {
            target,
            anchor,
            preserveLocalState: current.preserveLocalState,
          }
          if (conservativeDestroy) {
            replaceOptions.conservativeDestroy = true
          }
          cmp.$replace(current.Component, replaceOptions)
        } catch (err) {
          setError(err, target, anchor)
          if (
            !current.hotOptions.optimistic ||
            // non acceptable components (that is components that have to defer
            // to their parent for rerender -- e.g. accessors, named exports)
            // are most tricky, and they havent been considered when most of the
            // code has been written... as a result, they are especially tricky
            // to deal with, it's better to consider any error with them to be
            // fatal to avoid odities
            !current.canAccept ||
            (err && err.hmrFatal)
          ) {
            throw err
          } else {
            // const errString = String((err && err.stack) || err)
            logError(`Error during component init: ${debugName}`, err)
          }
        }
      }
    }

    const setError = err => {
      lastError = err
      adapter.renderError(err)
    }

    const instance = {
      hotOptions: current.hotOptions,
      proxy: this,
      id,
      debugName,
      refreshComponent,
    }

    const adapter = new Adapter(instance)

    const { afterMount, rerender } = adapter

    // $destroy is not called when a child component is disposed, so we
    // need to hook from fragment.
    const onDestroy = () => {
      // NOTE do NOT call $destroy on the cmp from here; the cmp is already
      //   dead, this would not work
      if (!disposed) {
        disposed = true
        adapter.dispose()
        unregister()
      }
    }

    // ---- register proxy instance ----

    const unregister = register(rerender)

    // ---- augmented methods ----

    this.$destroy = () => {
      destroyComponent()
      onDestroy()
    }

    // ---- forwarded methods ----

    relayCalls(getComponent, forwardedMethods, this)

    // ---- create & mount target component instance ---

    try {
      let lastProperties
      ;(0,_svelte_hooks_js__WEBPACK_IMPORTED_MODULE_0__.createProxiedComponent)(current.Component, options, {
        allowLiveBinding: current.hotOptions.allowLiveBinding,
        onDestroy,
        onMount: afterMount,
        onInstance: comp => {
          setComponent(comp)
          // WARNING the proxy MUST use the same $$ object as its component
          // instance, because a lot of wiring happens during component
          // initialisation... lots of references to $$ and $$.fragment have
          // already been distributed around when the component constructor
          // returns, before we have a chance to wrap them (and so we can't
          // wrap them no more, because existing references would become
          // invalid)
          this.$$ = comp.$$
          lastProperties = copyComponentProperties(this, comp, lastProperties)
        },
      })
    } catch (err) {
      const { target, anchor } = options
      setError(err, target, anchor)
      throw err
    }
  }
}

const syncStatics = (component, proxy, previousKeys) => {
  // remove previously copied keys
  if (previousKeys) {
    for (const key of previousKeys) {
      delete proxy[key]
    }
  }

  // forward static properties and methods
  const keys = []
  for (const key in component) {
    keys.push(key)
    proxy[key] = component[key]
  }

  return keys
}

const globalListeners = {}

const onGlobal = (event, fn) => {
  event = event.toLowerCase()
  if (!globalListeners[event]) globalListeners[event] = []
  globalListeners[event].push(fn)
}

const fireGlobal = (event, ...args) => {
  const listeners = globalListeners[event]
  if (!listeners) return
  for (const fn of listeners) {
    fn(...args)
  }
}

const fireBeforeUpdate = () => fireGlobal('beforeupdate')

const fireAfterUpdate = () => fireGlobal('afterupdate')

if (typeof window !== 'undefined') {
  window.__SVELTE_HMR = {
    on: onGlobal,
  }
  window.dispatchEvent(new CustomEvent('svelte-hmr:ready'))
}

let fatalError = false

const hasFatalError = () => fatalError

/**
 * Creates a HMR proxy and its associated `reload` function that pushes a new
 * version to all existing instances of the component.
 */
function createProxy({
  Adapter,
  id,
  Component,
  hotOptions,
  canAccept,
  preserveLocalState,
}) {
  const debugName = getDebugName(id)
  const instances = []

  // current object will be updated, proxy instances will keep a ref
  const current = {
    Component,
    hotOptions,
    canAccept,
    preserveLocalState,
  }

  const name = `Proxy${debugName}`

  // this trick gives the dynamic name Proxy<MyComponent> to the concrete
  // proxy class... unfortunately, this doesn't shows in dev tools, but
  // it stills allow to inspect cmp.constructor.name to confirm an instance
  // is a proxy
  const proxy = {
    [name]: class extends ProxyComponent {
      constructor(options) {
        try {
          super(
            {
              Adapter,
              id,
              debugName,
              current,
              register: rerender => {
                instances.push(rerender)
                const unregister = () => {
                  const i = instances.indexOf(rerender)
                  instances.splice(i, 1)
                }
                return unregister
              },
            },
            options
          )
        } catch (err) {
          // If we fail to create a proxy instance, any instance, that means
          // that we won't be able to fix this instance when it is updated.
          // Recovering to normal state will be impossible. HMR's dead.
          //
          // Fatal error will trigger a full reload on next update (reloading
          // right now is kinda pointless since buggy code still exists).
          //
          // NOTE Only report first error to avoid too much polution -- following
          // errors are probably caused by the first one, or they will show up
          // in turn when the first one is fixed ¯\_(ツ)_/¯
          //
          if (!fatalError) {
            fatalError = true
            logError(
              `Unrecoverable HMR error in ${debugName}: ` +
                `next update will trigger a full reload`
            )
          }
          throw err
        }
      }
    },
  }[name]

  // initialize static members
  let previousStatics = syncStatics(current.Component, proxy)

  const update = newState => Object.assign(current, newState)

  // reload all existing instances of this component
  const reload = () => {
    fireBeforeUpdate()

    // copy statics before doing anything because a static prop/method
    // could be used somewhere in the create/render call
    previousStatics = syncStatics(current.Component, proxy, previousStatics)

    const errors = []

    instances.forEach(rerender => {
      try {
        rerender()
      } catch (err) {
        logError(`Failed to rerender ${debugName}`, err)
        errors.push(err)
      }
    })

    if (errors.length > 0) {
      return false
    }

    fireAfterUpdate()

    return true
  }

  const hasFatalError = () => fatalError

  return { id, proxy, update, reload, hasFatalError, current }
}


/***/ }),

/***/ "./node_modules/svelte-hmr/runtime/svelte-hooks.js":
/*!*********************************************************!*\
  !*** ./node_modules/svelte-hmr/runtime/svelte-hooks.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "createProxiedComponent": () => (/* binding */ createProxiedComponent)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/**
 * Emulates forthcoming HMR hooks in Svelte.
 *
 * All references to private component state ($$) are now isolated in this
 * module.
 */


const captureState = cmp => {
  // sanity check: propper behaviour here is to crash noisily so that
  // user knows that they're looking at something broken
  if (!cmp) {
    throw new Error('Missing component')
  }
  if (!cmp.$$) {
    throw new Error('Invalid component')
  }

  const {
    $$: { callbacks, bound, ctx, props },
  } = cmp

  const state = cmp.$capture_state()

  // capturing current value of props (or we'll recreate the component with the
  // initial prop values, that may have changed -- and would not be reflected in
  // options.props)
  const hmr_props_values = {}
  Object.keys(cmp.$$.props).forEach(prop => {
    hmr_props_values[prop] = ctx[props[prop]]
  })

  return {
    ctx,
    props,
    callbacks,
    bound,
    state,
    hmr_props_values,
  }
}

// remapping all existing bindings (including hmr_future_foo ones) to the
// new version's props indexes, and refresh them with the new value from
// context
const restoreBound = (cmp, restore) => {
  // reverse prop:ctxIndex in $$.props to ctxIndex:prop
  //
  // ctxIndex can be either a regular index in $$.ctx or a hmr_future_ prop
  //
  const propsByIndex = {}
  for (const [name, i] of Object.entries(restore.props)) {
    propsByIndex[i] = name
  }

  // NOTE $$.bound cannot change in the HMR lifetime of a component, because
  //      if bindings changes, that means the parent component has changed,
  //      which means the child (current) component will be wholly recreated
  for (const [oldIndex, updateBinding] of Object.entries(restore.bound)) {
    // can be either regular prop, or future_hmr_ prop
    const propName = propsByIndex[oldIndex]

    // this should never happen if remembering of future props is enabled...
    // in any case, there's nothing we can do about it if we have lost prop
    // name knowledge at this point
    if (propName == null) continue

    // NOTE $$.props[propName] also propagates knowledge of a possible
    //      future prop to the new $$.props (via $$.props being a Proxy)
    const newIndex = cmp.$$.props[propName]
    cmp.$$.bound[newIndex] = updateBinding

    // NOTE if the prop doesn't exist or doesn't exist anymore in the new
    //      version of the component, clearing the binding is the expected
    //      behaviour (since that's what would happen in non HMR code)
    const newValue = cmp.$$.ctx[newIndex]
    updateBinding(newValue)
  }
}

// restoreState
//
// It is too late to restore context at this point because component instance
// function has already been called (and so context has already been read).
// Instead, we rely on setting current_component to the same value it has when
// the component was first rendered -- which fix support for context, and is
// also generally more respectful of normal operation.
//
const restoreState = (cmp, restore) => {
  if (!restore) return

  if (restore.callbacks) {
    cmp.$$.callbacks = restore.callbacks
  }

  if (restore.bound) {
    restoreBound(cmp, restore)
  }

  // props, props.$$slots are restored at component creation (works
  // better -- well, at all actually)
}

const get_current_component_safe = () => {
  // NOTE relying on dynamic bindings (current_component) makes us dependent on
  // bundler config (and apparently it does not work in demo-svelte-nollup)
  try {
    // unfortunately, unlike current_component, get_current_component() can
    // crash in the normal path (when there is really no parent)
    return (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_current_component)()
  } catch (err) {
    // ... so we need to consider that this error means that there is no parent
    //
    // that makes us tightly coupled to the error message but, at least, we
    // won't mute an unexpected error, which is quite a horrible thing to do
    if (err.message === 'Function called outside component initialization') {
      // who knows...
      return svelte_internal__WEBPACK_IMPORTED_MODULE_0__.current_component
    } else {
      throw err
    }
  }
}

const createProxiedComponent = (
  Component,
  initialOptions,
  { allowLiveBinding, onInstance, onMount, onDestroy }
) => {
  let cmp
  let options = initialOptions

  const isCurrent = _cmp => cmp === _cmp

  const assignOptions = (target, anchor, restore, preserveLocalState) => {
    const props = Object.assign({}, options.props)

    // Filtering props to avoid "unexpected prop" warning
    // NOTE this is based on props present in initial options, but it should
    //      always works, because props that are passed from the parent can't
    //      change without a code change to the parent itself -- hence, the
    //      child component will be fully recreated, and initial options should
    //      always represent props that are currnetly passed by the parent
    if (options.props && restore.hmr_props_values) {
      for (const prop of Object.keys(options.props)) {
        if (restore.hmr_props_values.hasOwnProperty(prop)) {
          props[prop] = restore.hmr_props_values[prop]
        }
      }
    }

    if (preserveLocalState && restore.state) {
      if (Array.isArray(preserveLocalState)) {
        // form ['a', 'b'] => preserve only 'a' and 'b'
        props.$$inject = {}
        for (const key of preserveLocalState) {
          props.$$inject[key] = restore.state[key]
        }
      } else {
        props.$$inject = restore.state
      }
    } else {
      delete props.$$inject
    }
    options = Object.assign({}, initialOptions, {
      target,
      anchor,
      props,
      hydrate: false,
    })
  }

  // Preserving knowledge of "future props" -- very hackish version (maybe
  // there should be an option to opt out of this)
  //
  // The use case is bind:something where something doesn't exist yet in the
  // target component, but comes to exist later, after a HMR update.
  //
  // If Svelte can't map a prop in the current version of the component, it
  // will just completely discard it:
  // https://github.com/sveltejs/svelte/blob/1632bca34e4803d6b0e0b0abd652ab5968181860/src/runtime/internal/Component.ts#L46
  //
  const rememberFutureProps = cmp => {
    if (typeof Proxy === 'undefined') return

    cmp.$$.props = new Proxy(cmp.$$.props, {
      get(target, name) {
        if (target[name] === undefined) {
          target[name] = 'hmr_future_' + name
        }
        return target[name]
      },
      set(target, name, value) {
        target[name] = value
      },
    })
  }

  const instrument = targetCmp => {
    const createComponent = (Component, restore, previousCmp) => {
      ;(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_current_component)(parentComponent || previousCmp)
      const comp = new Component(options)
      // NOTE must be instrumented before restoreState, because restoring
      // bindings relies on hacked $$.props
      instrument(comp)
      restoreState(comp, restore)
      return comp
    }

    rememberFutureProps(targetCmp)

    targetCmp.$$.on_hmr = []

    // `conservative: true` means we want to be sure that the new component has
    // actually been successfuly created before destroying the old instance.
    // This could be useful for preventing runtime errors in component init to
    // bring down the whole HMR. Unfortunately the implementation bellow is
    // broken (FIXME), but that remains an interesting target for when HMR hooks
    // will actually land in Svelte itself.
    //
    // The goal would be to render an error inplace in case of error, to avoid
    // losing the navigation stack (especially annoying in native, that is not
    // based on URL navigation, so we lose the current page on each error).
    //
    targetCmp.$replace = (
      Component,
      {
        target = options.target,
        anchor = options.anchor,
        preserveLocalState,
        conservative = false,
      }
    ) => {
      const restore = captureState(targetCmp)
      assignOptions(
        target || options.target,
        anchor,
        restore,
        preserveLocalState
      )

      const callbacks = cmp ? cmp.$$.on_hmr : []

      const afterCallbacks = callbacks.map(fn => fn(cmp)).filter(Boolean)

      const previous = cmp
      if (conservative) {
        try {
          const next = createComponent(Component, restore, previous)
          // prevents on_destroy from firing on non-final cmp instance
          cmp = null
          previous.$destroy()
          cmp = next
        } catch (err) {
          cmp = previous
          throw err
        }
      } else {
        // prevents on_destroy from firing on non-final cmp instance
        cmp = null
        if (previous) {
          // previous can be null if last constructor has crashed
          previous.$destroy()
        }
        cmp = createComponent(Component, restore, cmp)
      }

      cmp.$$.hmr_cmp = cmp

      for (const fn of afterCallbacks) {
        fn(cmp)
      }

      cmp.$$.on_hmr = callbacks

      return cmp
    }

    // NOTE onMount must provide target & anchor (for us to be able to determinate
    // 			actual DOM insertion point)
    //
    // 			And also, to support keyed list, it needs to be called each time the
    // 			component is moved (same as $$.fragment.m)
    if (onMount) {
      const m = targetCmp.$$.fragment.m
      targetCmp.$$.fragment.m = (...args) => {
        const result = m(...args)
        onMount(...args)
        return result
      }
    }

    // NOTE onDestroy must be called even if the call doesn't pass through the
    //      component's $destroy method (that we can hook onto by ourselves, since
    //      it's public API) -- this happens a lot in svelte's internals, that
    //      manipulates cmp.$$.fragment directly, often binding to fragment.d,
    //      for example
    if (onDestroy) {
      targetCmp.$$.on_destroy.push(() => {
        if (isCurrent(targetCmp)) {
          onDestroy()
        }
      })
    }

    if (onInstance) {
      onInstance(targetCmp)
    }

    // Svelte 3 creates and mount components from their constructor if
    // options.target is present.
    //
    // This means that at this point, the component's `fragment.c` and,
    // most notably, `fragment.m` will already have been called _from inside
    // createComponent_. That is: before we have a chance to hook on it.
    //
    // Proxy's constructor
    //   -> createComponent
    //     -> component constructor
    //       -> component.$$.fragment.c(...) (or l, if hydrate:true)
    //       -> component.$$.fragment.m(...)
    //
    //   -> you are here <-
    //
    if (onMount) {
      const { target, anchor } = options
      if (target) {
        onMount(target, anchor)
      }
    }
  }

  const parentComponent = allowLiveBinding
    ? svelte_internal__WEBPACK_IMPORTED_MODULE_0__.current_component
    : get_current_component_safe()

  cmp = new Component(options)
  cmp.$$.hmr_cmp = cmp

  instrument(cmp)

  return cmp
}


/***/ }),

/***/ "./modules/project_browser/sveltejs/src/App.svelte":
/*!*********************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/App.svelte ***!
  \*********************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _ProjectBrowser_svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ProjectBrowser.svelte */ "./modules/project_browser/sveltejs/src/ProjectBrowser.svelte");
/* harmony import */ var _ModulePage_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ModulePage.svelte */ "./modules/project_browser/sveltejs/src/ModulePage.svelte");
/* harmony import */ var _Loading_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Loading.svelte */ "./modules/project_browser/sveltejs/src/Loading.svelte");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/App.svelte generated by Svelte v3.56.0 */


const { Object: Object_1 } = svelte_internal__WEBPACK_IMPORTED_MODULE_0__.globals;





const file = "modules/project_browser/sveltejs/src/App.svelte";

// (47:0) {:else}
function create_else_block(ctx) {
	let await_block_anchor;
	let promise;
	let current;

	let info = {
		ctx,
		current: null,
		token: null,
		hasCatch: false,
		pending: create_pending_block,
		then: create_then_block,
		catch: create_catch_block,
		value: 2,
		blocks: [,,,]
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.handle_promise)(promise = /*load*/ ctx[4](`${_constants__WEBPACK_IMPORTED_MODULE_5__.ORIGIN_URL}/drupal-org-proxy/project?machine_name=${/*moduleName*/ ctx[3]}`), info);

	const block = {
		c: function create() {
			await_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
			info.block.c();
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, await_block_anchor, anchor);
			info.block.m(target, info.anchor = anchor);
			info.mount = () => await_block_anchor.parentNode;
			info.anchor = await_block_anchor;
			current = true;
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_await_block_branch)(info, ctx, dirty);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(info.block);
			current = true;
		},
		o: function outro(local) {
			for (let i = 0; i < 3; i += 1) {
				const block = info.blocks[i];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(block);
			}

			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(await_block_anchor);
			info.block.d(detaching);
			info.token = null;
			info = null;
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block.name,
		type: "else",
		source: "(47:0) {:else}",
		ctx
	});

	return block;
}

// (45:0) {#if !moduleName}
function create_if_block(ctx) {
	let projectbrowser;
	let current;
	projectbrowser = new _ProjectBrowser_svelte__WEBPACK_IMPORTED_MODULE_1__["default"]({ $$inline: true });

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectbrowser.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectbrowser, target, anchor);
			current = true;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectbrowser.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectbrowser.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectbrowser, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(45:0) {#if !moduleName}",
		ctx
	});

	return block;
}

// (1:0) <script>   import ProjectBrowser from './ProjectBrowser.svelte';   import ModulePage from './ModulePage.svelte';   import Loading from './Loading.svelte';   import { searchString, activeTab }
function create_catch_block(ctx) {
	const block = {
		c: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		m: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_catch_block.name,
		type: "catch",
		source: "(1:0) <script>   import ProjectBrowser from './ProjectBrowser.svelte';   import ModulePage from './ModulePage.svelte';   import Loading from './Loading.svelte';   import { searchString, activeTab }",
		ctx
	});

	return block;
}

// (52:2) {:then project}
function create_then_block(ctx) {
	let current_block_type_index;
	let if_block;
	let if_block_anchor;
	let current;
	const if_block_creators = [create_if_block_2, create_else_block_1];
	const if_blocks = [];

	function select_block_type_1(ctx, dirty) {
		if (/*projectExists*/ ctx[1]) return 0;
		return 1;
	}

	current_block_type_index = select_block_type_1(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

	const block = {
		c: function create() {
			if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			if_blocks[current_block_type_index].m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			let previous_block_index = current_block_type_index;
			current_block_type_index = select_block_type_1(ctx, dirty);

			if (current_block_type_index === previous_block_index) {
				if_blocks[current_block_type_index].p(ctx, dirty);
			} else {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_blocks[previous_block_index], 1, 1, () => {
					if_blocks[previous_block_index] = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
				if_block = if_blocks[current_block_type_index];

				if (!if_block) {
					if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
					if_block.c();
				} else {
					if_block.p(ctx, dirty);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
				if_block.m(if_block_anchor.parentNode, if_block_anchor);
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if_blocks[current_block_type_index].d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_then_block.name,
		type: "then",
		source: "(52:2) {:then project}",
		ctx
	});

	return block;
}

// (55:4) {:else}
function create_else_block_1(ctx) {
	let projectbrowser;
	let current;
	projectbrowser = new _ProjectBrowser_svelte__WEBPACK_IMPORTED_MODULE_1__["default"]({ $$inline: true });

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectbrowser.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectbrowser, target, anchor);
			current = true;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectbrowser.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectbrowser.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectbrowser, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block_1.name,
		type: "else",
		source: "(55:4) {:else}",
		ctx
	});

	return block;
}

// (53:4) {#if projectExists}
function create_if_block_2(ctx) {
	let modulepage;
	let current;

	modulepage = new _ModulePage_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: { project: /*project*/ ctx[2] },
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(modulepage.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(modulepage, target, anchor);
			current = true;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(modulepage.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(modulepage.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(modulepage, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(53:4) {#if projectExists}",
		ctx
	});

	return block;
}

// (48:84)      {#if loading}
function create_pending_block(ctx) {
	let if_block_anchor;
	let current;
	let if_block = /*loading*/ ctx[0] && create_if_block_1(ctx);

	const block = {
		c: function create() {
			if (if_block) if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			if (if_block) if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			if (/*loading*/ ctx[0]) {
				if (if_block) {
					if (dirty & /*loading*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					}
				} else {
					if_block = create_if_block_1(ctx);
					if_block.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			} else if (if_block) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block, 1, 1, () => {
					if_block = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (if_block) if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_pending_block.name,
		type: "pending",
		source: "(48:84)      {#if loading}",
		ctx
	});

	return block;
}

// (49:4) {#if loading}
function create_if_block_1(ctx) {
	let loading_1;
	let current;
	loading_1 = new _Loading_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({ $$inline: true });

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(loading_1.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(loading_1, target, anchor);
			current = true;
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(loading_1.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(loading_1.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(loading_1, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(49:4) {#if loading}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let current_block_type_index;
	let if_block;
	let if_block_anchor;
	let current;
	const if_block_creators = [create_if_block, create_else_block];
	const if_blocks = [];

	function select_block_type(ctx, dirty) {
		if (!/*moduleName*/ ctx[3]) return 0;
		return 1;
	}

	current_block_type_index = select_block_type(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

	const block = {
		c: function create() {
			if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			if_blocks[current_block_type_index].m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			if_block.p(ctx, dirty);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if_blocks[current_block_type_index].d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let $searchString;
	let $activeTab;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_4__.searchString, 'searchString');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_4__.searchString, $$value => $$invalidate(6, $searchString = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_4__.activeTab, 'activeTab');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_4__.activeTab, $$value => $$invalidate(7, $activeTab = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('App', slots, []);
	const matches = window.location.pathname.match(/\/admin\/modules\/browse\/([^/]+)/);
	const moduleName = matches ? matches[1] : null;
	let loading = true;
	let data;
	let project = [];
	let projectExists = false;

	async function load(url) {
		$$invalidate(0, loading = true);
		const res = await fetch(url);

		if (res.ok) {
			data = await res.json();

			Object.entries(data).forEach(item => {
				const [source, result] = item;

				if (result.totalResults !== 0) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_4__.activeTab, $activeTab = source, $activeTab);
					$$invalidate(2, [project] = result.list, project);
					$$invalidate(1, projectExists = true);
				}
			});
		}

		$$invalidate(0, loading = false);

		if (!projectExists) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_4__.searchString, $searchString = moduleName, $searchString);
		}

		return project;
	}

	// Removes initial loader if it exists.
	const initialLoader = document.getElementById('initial-loader');

	if (initialLoader) {
		initialLoader.remove();
	}

	const writable_props = [];

	Object_1.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<App> was created with unknown prop '${key}'`);
	});

	$$self.$capture_state = () => ({
		ProjectBrowser: _ProjectBrowser_svelte__WEBPACK_IMPORTED_MODULE_1__["default"],
		ModulePage: _ModulePage_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		Loading: _Loading_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		searchString: _stores__WEBPACK_IMPORTED_MODULE_4__.searchString,
		activeTab: _stores__WEBPACK_IMPORTED_MODULE_4__.activeTab,
		ORIGIN_URL: _constants__WEBPACK_IMPORTED_MODULE_5__.ORIGIN_URL,
		matches,
		moduleName,
		loading,
		data,
		project,
		projectExists,
		load,
		initialLoader,
		$searchString,
		$activeTab
	});

	$$self.$inject_state = $$props => {
		if ('loading' in $$props) $$invalidate(0, loading = $$props.loading);
		if ('data' in $$props) data = $$props.data;
		if ('project' in $$props) $$invalidate(2, project = $$props.project);
		if ('projectExists' in $$props) $$invalidate(1, projectExists = $$props.projectExists);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [loading, projectExists, project, moduleName, load];
}

class App extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, {});

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "App",
			options,
			id: create_fragment.name
		});
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (App);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Filter.svelte":
/*!************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Filter.svelte ***!
  \************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _MediaQuery_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./MediaQuery.svelte */ "./modules/project_browser/sveltejs/src/MediaQuery.svelte");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./util */ "./modules/project_browser/sveltejs/src/util.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Filter.svelte generated by Svelte v3.56.0 */









const file = "modules/project_browser/sveltejs/src/Filter.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-16bt2gj", ".filter__summary.pb-16bt2gj.pb-16bt2gj{padding:0;cursor:pointer}[open].pb-16bt2gj .filter__summary.pb-16bt2gj{padding-bottom:0.95rem}.filter__heading.pb-16bt2gj.pb-16bt2gj{margin-top:0;display:inline;font-size:1rem;padding:0 0.5rem}.filter__checkbox-label.pb-16bt2gj.pb-16bt2gj{display:block;padding:5px 0}.filter__checkbox.pb-16bt2gj.pb-16bt2gj{margin-right:10px;margin-left:10px}.filter__fieldset.pb-16bt2gj.pb-16bt2gj{border:none}.filter__checkbox-label.pb-16bt2gj.pb-16bt2gj:hover,.filter__checkbox.pb-16bt2gj.pb-16bt2gj:hover{cursor:pointer}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiRmlsdGVyLnN2ZWx0ZSIsInNvdXJjZXMiOlsiRmlsdGVyLnN2ZWx0ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8c2NyaXB0PlxuICBpbXBvcnQgeyBjcmVhdGVFdmVudERpc3BhdGNoZXIsIGdldENvbnRleHQsIG9uTW91bnQgfSBmcm9tICdzdmVsdGUnO1xuICBpbXBvcnQge1xuICAgIG1vZHVsZUNhdGVnb3J5RmlsdGVyLFxuICAgIG1vZHVsZUNhdGVnb3J5Vm9jYWJ1bGFyaWVzLFxuICAgIGFjdGl2ZVRhYixcbiAgfSBmcm9tICcuL3N0b3Jlcyc7XG4gIGltcG9ydCBNZWRpYVF1ZXJ5IGZyb20gJy4vTWVkaWFRdWVyeS5zdmVsdGUnO1xuICBpbXBvcnQgeyBub3JtYWxpemVPcHRpb25zLCBzaGFsbG93Q29tcGFyZSB9IGZyb20gJy4vdXRpbCc7XG4gIGltcG9ydCB7IE9SSUdJTl9VUkwgfSBmcm9tICcuL2NvbnN0YW50cyc7XG5cbiAgY29uc3QgeyBEcnVwYWwgfSA9IHdpbmRvdztcbiAgY29uc3QgZGlzcGF0Y2ggPSBjcmVhdGVFdmVudERpc3BhdGNoZXIoKTtcbiAgY29uc3Qgc3RhdGVDb250ZXh0ID0gZ2V0Q29udGV4dCgnc3RhdGUnKTtcblxuICBhc3luYyBmdW5jdGlvbiBvblNlbGVjdENhdGVnb3J5KGV2ZW50KSB7XG4gICAgY29uc3Qgc3RhdGUgPSBzdGF0ZUNvbnRleHQuZ2V0U3RhdGUoKTtcbiAgICBjb25zdCBkZXRhaWwgPSB7XG4gICAgICBvcmlnaW5hbEV2ZW50OiBldmVudCxcbiAgICAgIGNhdGVnb3J5OiAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIsXG4gICAgICBwYWdlOiBzdGF0ZS5wYWdlLFxuICAgICAgcGFnZUluZGV4OiBzdGF0ZS5wYWdlSW5kZXgsXG4gICAgICBwYWdlU2l6ZTogc3RhdGUucGFnZVNpemUsXG4gICAgICByb3dzOiBzdGF0ZS5maWx0ZXJlZFJvd3MsXG4gICAgfTtcbiAgICBkaXNwYXRjaCgnc2VsZWN0Q2F0ZWdvcnknLCBkZXRhaWwpO1xuICAgIHN0YXRlQ29udGV4dC5zZXRQYWdlKDAsIDApO1xuICAgIHN0YXRlQ29udGV4dC5zZXRSb3dzKGRldGFpbC5yb3dzKTtcbiAgfVxuXG4gIGFzeW5jIGZ1bmN0aW9uIGZldGNoQWxsQ2F0ZWdvcmllcygpIHtcbiAgICBjb25zdCByZXNwb25zZSA9IGF3YWl0IGZldGNoKGAke09SSUdJTl9VUkx9L2RydXBhbC1vcmctcHJveHkvY2F0ZWdvcmllc2ApO1xuICAgIGlmIChyZXNwb25zZS5vaykge1xuICAgICAgcmV0dXJuIHJlc3BvbnNlLmpzb24oKTtcbiAgICB9XG4gICAgcmV0dXJuIFtdO1xuICB9XG5cbiAgY29uc3QgYXBpTW9kdWxlQ2F0ZWdvcnkgPSBmZXRjaEFsbENhdGVnb3JpZXMoKTtcbiAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIGltcG9ydC9uby1tdXRhYmxlLWV4cG9ydHMsaW1wb3J0L3ByZWZlci1kZWZhdWx0LWV4cG9ydFxuICBleHBvcnQgYXN5bmMgZnVuY3Rpb24gc2V0TW9kdWxlQ2F0ZWdvcnlWb2NhYnVsYXJ5KCkge1xuICAgIGFwaU1vZHVsZUNhdGVnb3J5LnRoZW4oKHZhbHVlKSA9PiB7XG4gICAgICBjb25zdCBub3JtYWxpemVkVmFsdWUgPSBub3JtYWxpemVPcHRpb25zKHZhbHVlWyRhY3RpdmVUYWJdKTtcbiAgICAgIGNvbnN0IHN0b3JlZFZhbHVlID0gJG1vZHVsZUNhdGVnb3J5Vm9jYWJ1bGFyaWVzO1xuICAgICAgaWYgKFxuICAgICAgICBzdG9yZWRWYWx1ZSA9PT0gbnVsbCB8fFxuICAgICAgICAhc2hhbGxvd0NvbXBhcmUobm9ybWFsaXplZFZhbHVlLCBzdG9yZWRWYWx1ZSlcbiAgICAgICkge1xuICAgICAgICBtb2R1bGVDYXRlZ29yeVZvY2FidWxhcmllcy5zZXQobm9ybWFsaXplZFZhbHVlKTtcbiAgICAgIH1cbiAgICB9KTtcbiAgfVxuICBvbk1vdW50KGFzeW5jICgpID0+IHtcbiAgICBhd2FpdCBzZXRNb2R1bGVDYXRlZ29yeVZvY2FidWxhcnkoKTtcbiAgfSk7XG48L3NjcmlwdD5cblxuPE1lZGlhUXVlcnkgcXVlcnk9XCIobWluLXdpZHRoOiA5MDFweClcIiBsZXQ6bWF0Y2hlcz5cbiAgPGZvcm0gY2xhc3M9XCJmaWx0ZXJcIj5cbiAgICA8c2VjdGlvbiBhcmlhLWxhYmVsPXtEcnVwYWwudCgnRmlsdGVyIGNhdGVnb3JpZXMnKX0+XG4gICAgICA8ZGV0YWlscyBjbGFzcz1cImZpbHRlcl9fY2F0ZWdvcmllc1wiIG9wZW49e21hdGNoZXN9PlxuICAgICAgICA8c3VtbWFyeSBjbGFzcz1cImZpbHRlcl9fc3VtbWFyeVwiIGhpZGRlbj17bWF0Y2hlc30+XG4gICAgICAgICAgPGgyIGNsYXNzPVwiZmlsdGVyX19oZWFkaW5nXCI+e0RydXBhbC50KCdGaWx0ZXIgQ2F0ZWdvcmllcycpfTwvaDI+XG4gICAgICAgIDwvc3VtbWFyeT5cbiAgICAgICAgPGZpZWxkc2V0IGNsYXNzPVwiZmlsdGVyX19maWVsZHNldFwiPlxuICAgICAgICAgIDxoMiBjbGFzczp2aXN1YWxseS1oaWRkZW49eyFtYXRjaGVzfT5cbiAgICAgICAgICAgIHtEcnVwYWwudCgnRmlsdGVyIENhdGVnb3JpZXMnKX1cbiAgICAgICAgICA8L2gyPlxuICAgICAgICAgIHsjYXdhaXQgYXBpTW9kdWxlQ2F0ZWdvcnkgdGhlbiBjYXRlZ29yeUxpc3R9XG4gICAgICAgICAgICB7I2VhY2ggY2F0ZWdvcnlMaXN0WyRhY3RpdmVUYWJdIGFzIGR0fVxuICAgICAgICAgICAgICA8bGFiZWwgY2xhc3M9XCJmaWx0ZXJfX2NoZWNrYm94LWxhYmVsXCI+XG4gICAgICAgICAgICAgICAgPGlucHV0XG4gICAgICAgICAgICAgICAgICB0eXBlPVwiY2hlY2tib3hcIlxuICAgICAgICAgICAgICAgICAgaWQ9e2R0LmlkfVxuICAgICAgICAgICAgICAgICAgY2xhc3M9XCJmaWx0ZXJfX2NoZWNrYm94XCJcbiAgICAgICAgICAgICAgICAgIGJpbmQ6Z3JvdXA9eyRtb2R1bGVDYXRlZ29yeUZpbHRlcn1cbiAgICAgICAgICAgICAgICAgIG9uOmNoYW5nZT17b25TZWxlY3RDYXRlZ29yeX1cbiAgICAgICAgICAgICAgICAgIHZhbHVlPXtkdC5pZH1cbiAgICAgICAgICAgICAgICAvPntkdC5uYW1lfTwvbGFiZWxcbiAgICAgICAgICAgICAgPlxuICAgICAgICAgICAgey9lYWNofVxuICAgICAgICAgIHsvYXdhaXR9XG4gICAgICAgIDwvZmllbGRzZXQ+XG4gICAgICA8L2RldGFpbHM+XG4gICAgPC9zZWN0aW9uPlxuICA8L2Zvcm0+XG48L01lZGlhUXVlcnk+XG5cbjxzdHlsZT5cbiAgLmZpbHRlcl9fc3VtbWFyeSB7XG4gICAgcGFkZGluZzogMDtcbiAgICBjdXJzb3I6IHBvaW50ZXI7XG4gIH1cbiAgW29wZW5dIC5maWx0ZXJfX3N1bW1hcnkge1xuICAgIHBhZGRpbmctYm90dG9tOiAwLjk1cmVtO1xuICB9XG4gIC5maWx0ZXJfX2hlYWRpbmcge1xuICAgIG1hcmdpbi10b3A6IDA7XG4gICAgZGlzcGxheTogaW5saW5lO1xuICAgIGZvbnQtc2l6ZTogMXJlbTtcbiAgICBwYWRkaW5nOiAwIDAuNXJlbTtcbiAgfVxuICAuZmlsdGVyX19jaGVja2JveC1sYWJlbCB7XG4gICAgZGlzcGxheTogYmxvY2s7XG4gICAgcGFkZGluZzogNXB4IDA7XG4gIH1cbiAgLmZpbHRlcl9fY2hlY2tib3gge1xuICAgIG1hcmdpbi1yaWdodDogMTBweDtcbiAgICBtYXJnaW4tbGVmdDogMTBweDtcbiAgfVxuICAuZmlsdGVyX19maWVsZHNldCB7XG4gICAgYm9yZGVyOiBub25lO1xuICB9XG4gIC5maWx0ZXJfX2NoZWNrYm94LWxhYmVsOmhvdmVyLFxuICAuZmlsdGVyX19jaGVja2JveDpob3ZlciB7XG4gICAgY3Vyc29yOiBwb2ludGVyO1xuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQXlGRSxzQ0FBaUIsQ0FDZixPQUFPLENBQUUsQ0FBQyxDQUNWLE1BQU0sQ0FBRSxPQUNWLENBQ0EsQ0FBQyxJQUFJLFlBQUMsQ0FBQywyQkFBaUIsQ0FDdEIsY0FBYyxDQUFFLE9BQ2xCLENBQ0Esc0NBQWlCLENBQ2YsVUFBVSxDQUFFLENBQUMsQ0FDYixPQUFPLENBQUUsTUFBTSxDQUNmLFNBQVMsQ0FBRSxJQUFJLENBQ2YsT0FBTyxDQUFFLENBQUMsQ0FBQyxNQUNiLENBQ0EsNkNBQXdCLENBQ3RCLE9BQU8sQ0FBRSxLQUFLLENBQ2QsT0FBTyxDQUFFLEdBQUcsQ0FBQyxDQUNmLENBQ0EsdUNBQWtCLENBQ2hCLFlBQVksQ0FBRSxJQUFJLENBQ2xCLFdBQVcsQ0FBRSxJQUNmLENBQ0EsdUNBQWtCLENBQ2hCLE1BQU0sQ0FBRSxJQUNWLENBQ0EsNkNBQXVCLE1BQU0sQ0FDN0IsdUNBQWlCLE1BQU8sQ0FDdEIsTUFBTSxDQUFFLE9BQ1YifQ== */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[14] = list[i];
	return child_ctx;
}

// (1:0) <script>   import { createEventDispatcher, getContext, onMount }
function create_catch_block(ctx) {
	const block = { c: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop, m: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop, p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop, d: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop };

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_catch_block.name,
		type: "catch",
		source: "(1:0) <script>   import { createEventDispatcher, getContext, onMount }",
		ctx
	});

	return block;
}

// (69:54)              {#each categoryList[$activeTab] as dt}
function create_then_block(ctx) {
	let each_1_anchor;
	let each_value = /*categoryList*/ ctx[13][/*$activeTab*/ ctx[0]];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const block = {
		c: function create() {
			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			each_1_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(target, anchor);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, each_1_anchor, anchor);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*apiModuleCategory, $activeTab, $moduleCategoryFilter, onSelectCategory*/ 27) {
				each_value = /*categoryList*/ ctx[13][/*$activeTab*/ ctx[0]];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						each_blocks[i].m(each_1_anchor.parentNode, each_1_anchor);
					}
				}

				for (; i < each_blocks.length; i += 1) {
					each_blocks[i].d(1);
				}

				each_blocks.length = each_value.length;
			}
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(each_1_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_then_block.name,
		type: "then",
		source: "(69:54)              {#each categoryList[$activeTab] as dt}",
		ctx
	});

	return block;
}

// (70:12) {#each categoryList[$activeTab] as dt}
function create_each_block(ctx) {
	let label;
	let input;
	let input_id_value;
	let input_value_value;
	let value_has_changed = false;
	let t_value = /*dt*/ ctx[14].name + "";
	let t;
	let binding_group;
	let mounted;
	let dispose;
	binding_group = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init_binding_group)(/*$$binding_groups*/ ctx[7][0]);

	const block = {
		c: function create() {
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			input = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("input");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "type", "checkbox");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "id", input_id_value = /*dt*/ ctx[14].id);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "class", "filter__checkbox pb-16bt2gj");
			input.__value = input_value_value = /*dt*/ ctx[14].id;
			input.value = input.__value;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(input, file, 71, 16, 2364);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "class", "filter__checkbox-label pb-16bt2gj");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 70, 14, 2309);
			binding_group.p(input);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, label, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(label, input);
			input.checked = ~(/*$moduleCategoryFilter*/ ctx[1] || []).indexOf(input.__value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(label, t);

			if (!mounted) {
				dispose = [
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(input, "change", /*input_change_handler*/ ctx[6]),
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(input, "change", /*onSelectCategory*/ ctx[3], false, false, false, false)
				];

				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			if (dirty & /*$activeTab*/ 1 && input_id_value !== (input_id_value = /*dt*/ ctx[14].id)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "id", input_id_value);
			}

			if (dirty & /*$activeTab*/ 1 && input_value_value !== (input_value_value = /*dt*/ ctx[14].id)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prop_dev)(input, "__value", input_value_value);
				input.value = input.__value;
				value_has_changed = true;
			}

			if (value_has_changed || dirty & /*$moduleCategoryFilter, $activeTab*/ 3) {
				input.checked = ~(/*$moduleCategoryFilter*/ ctx[1] || []).indexOf(input.__value);
			}

			if (dirty & /*$activeTab*/ 1 && t_value !== (t_value = /*dt*/ ctx[14].name + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(label);
			binding_group.r();
			mounted = false;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.run_all)(dispose);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(70:12) {#each categoryList[$activeTab] as dt}",
		ctx
	});

	return block;
}

// (1:0) <script>   import { createEventDispatcher, getContext, onMount }
function create_pending_block(ctx) {
	const block = { c: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop, m: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop, p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop, d: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop };

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_pending_block.name,
		type: "pending",
		source: "(1:0) <script>   import { createEventDispatcher, getContext, onMount }",
		ctx
	});

	return block;
}

// (58:0) <MediaQuery query="(min-width: 901px)" let:matches>
function create_default_slot(ctx) {
	let form;
	let section;
	let details;
	let summary;
	let h20;
	let summary_hidden_value;
	let t1;
	let fieldset;
	let h21;
	let t3;
	let promise;
	let details_open_value;
	let section_aria_label_value;

	let info = {
		ctx,
		current: null,
		token: null,
		hasCatch: false,
		pending: create_pending_block,
		then: create_then_block,
		catch: create_catch_block,
		value: 13
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.handle_promise)(promise = /*apiModuleCategory*/ ctx[4], info);

	const block = {
		c: function create() {
			form = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("form");
			section = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("section");
			details = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("details");
			summary = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("summary");
			h20 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h2");
			h20.textContent = `${/*Drupal*/ ctx[2].t('Filter Categories')}`;
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			fieldset = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("fieldset");
			h21 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h2");
			h21.textContent = `${/*Drupal*/ ctx[2].t('Filter Categories')}`;
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			info.block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(h20, "class", "filter__heading pb-16bt2gj");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(h20, file, 62, 10, 1953);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(summary, "class", "filter__summary pb-16bt2gj");
			summary.hidden = summary_hidden_value = /*matches*/ ctx[12];
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(summary, file, 61, 8, 1892);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(h21, "visually-hidden", !/*matches*/ ctx[12]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(h21, file, 65, 10, 2091);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(fieldset, "class", "filter__fieldset pb-16bt2gj");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(fieldset, file, 64, 8, 2045);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(details, "class", "filter__categories pb-16bt2gj");
			details.open = details_open_value = /*matches*/ ctx[12];
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(details, file, 60, 6, 1832);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(section, "aria-label", section_aria_label_value = /*Drupal*/ ctx[2].t('Filter categories'));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(section, file, 59, 4, 1773);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(form, "class", "filter");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(form, file, 58, 2, 1747);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, form, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(form, section);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(section, details);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(details, summary);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(summary, h20);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(details, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(details, fieldset);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(fieldset, h21);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(fieldset, t3);
			info.block.m(fieldset, info.anchor = null);
			info.mount = () => fieldset;
			info.anchor = null;
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;

			if (dirty & /*matches*/ 4096 && summary_hidden_value !== (summary_hidden_value = /*matches*/ ctx[12])) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prop_dev)(summary, "hidden", summary_hidden_value);
			}

			if (dirty & /*matches*/ 4096) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(h21, "visually-hidden", !/*matches*/ ctx[12]);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_await_block_branch)(info, ctx, dirty);

			if (dirty & /*matches*/ 4096 && details_open_value !== (details_open_value = /*matches*/ ctx[12])) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prop_dev)(details, "open", details_open_value);
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(form);
			info.block.d();
			info.token = null;
			info = null;
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_default_slot.name,
		type: "slot",
		source: "(58:0) <MediaQuery query=\\\"(min-width: 901px)\\\" let:matches>",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let mediaquery;
	let current;

	mediaquery = new _MediaQuery_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: {
				query: "(min-width: 901px)",
				$$slots: {
					default: [
						create_default_slot,
						({ matches }) => ({ 12: matches }),
						({ matches }) => matches ? 4096 : 0
					]
				},
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(mediaquery.$$.fragment);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(mediaquery, target, anchor);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			const mediaquery_changes = {};

			if (dirty & /*$$scope, matches, $activeTab, $moduleCategoryFilter*/ 135171) {
				mediaquery_changes.$$scope = { dirty, ctx };
			}

			mediaquery.$set(mediaquery_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(mediaquery.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(mediaquery.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(mediaquery, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let $moduleCategoryVocabularies;
	let $activeTab;
	let $moduleCategoryFilter;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryVocabularies, 'moduleCategoryVocabularies');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryVocabularies, $$value => $$invalidate(8, $moduleCategoryVocabularies = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_2__.activeTab, 'activeTab');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_2__.activeTab, $$value => $$invalidate(0, $activeTab = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryFilter, 'moduleCategoryFilter');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryFilter, $$value => $$invalidate(1, $moduleCategoryFilter = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('Filter', slots, []);
	const { Drupal } = window;
	const dispatch = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher)();
	const stateContext = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.getContext)('state');

	async function onSelectCategory(event) {
		const state = stateContext.getState();

		const detail = {
			originalEvent: event,
			category: $moduleCategoryFilter,
			page: state.page,
			pageIndex: state.pageIndex,
			pageSize: state.pageSize,
			rows: state.filteredRows
		};

		dispatch('selectCategory', detail);
		stateContext.setPage(0, 0);
		stateContext.setRows(detail.rows);
	}

	async function fetchAllCategories() {
		const response = await fetch(`${_constants__WEBPACK_IMPORTED_MODULE_5__.ORIGIN_URL}/drupal-org-proxy/categories`);

		if (response.ok) {
			return response.json();
		}

		return [];
	}

	const apiModuleCategory = fetchAllCategories();

	async function setModuleCategoryVocabulary() {
		apiModuleCategory.then(value => {
			const normalizedValue = (0,_util__WEBPACK_IMPORTED_MODULE_4__.normalizeOptions)(value[$activeTab]);
			const storedValue = $moduleCategoryVocabularies;

			if (storedValue === null || !(0,_util__WEBPACK_IMPORTED_MODULE_4__.shallowCompare)(normalizedValue, storedValue)) {
				_stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryVocabularies.set(normalizedValue);
			}
		});
	}

	(0,svelte__WEBPACK_IMPORTED_MODULE_1__.onMount)(async () => {
		await setModuleCategoryVocabulary();
	});

	const writable_props = [];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Filter> was created with unknown prop '${key}'`);
	});

	const $$binding_groups = [[]];

	function input_change_handler() {
		$moduleCategoryFilter = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_binding_group_value)($$binding_groups[0], this.__value, this.checked);
		_stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryFilter.set($moduleCategoryFilter);
	}

	$$self.$capture_state = () => ({
		createEventDispatcher: svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher,
		getContext: svelte__WEBPACK_IMPORTED_MODULE_1__.getContext,
		onMount: svelte__WEBPACK_IMPORTED_MODULE_1__.onMount,
		moduleCategoryFilter: _stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryFilter,
		moduleCategoryVocabularies: _stores__WEBPACK_IMPORTED_MODULE_2__.moduleCategoryVocabularies,
		activeTab: _stores__WEBPACK_IMPORTED_MODULE_2__.activeTab,
		MediaQuery: _MediaQuery_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		normalizeOptions: _util__WEBPACK_IMPORTED_MODULE_4__.normalizeOptions,
		shallowCompare: _util__WEBPACK_IMPORTED_MODULE_4__.shallowCompare,
		ORIGIN_URL: _constants__WEBPACK_IMPORTED_MODULE_5__.ORIGIN_URL,
		Drupal,
		dispatch,
		stateContext,
		onSelectCategory,
		fetchAllCategories,
		apiModuleCategory,
		setModuleCategoryVocabulary,
		$moduleCategoryVocabularies,
		$activeTab,
		$moduleCategoryFilter
	});

	return [
		$activeTab,
		$moduleCategoryFilter,
		Drupal,
		onSelectCategory,
		apiModuleCategory,
		setModuleCategoryVocabulary,
		input_change_handler,
		$$binding_groups
	];
}

class Filter extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { setModuleCategoryVocabulary: 5 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "Filter",
			options,
			id: create_fragment.name
		});
	}

	get setModuleCategoryVocabulary() {
		return this.$$.ctx[5];
	}

	set setModuleCategoryVocabulary(value) {
		throw new Error("<Filter>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Filter);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/ImageCarousel.svelte":
/*!*******************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/ImageCarousel.svelte ***!
  \*******************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Project_Image_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Project/Image.svelte */ "./modules/project_browser/sveltejs/src/Project/Image.svelte");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/ImageCarousel.svelte generated by Svelte v3.56.0 */




const file = "modules/project_browser/sveltejs/src/ImageCarousel.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1e9hp2c", ".image-carousel__carousel.pb-1e9hp2c.pb-1e9hp2c{display:flex;align-items:center;width:100%;height:400px}.image-carousel__slider-image{min-width:650px;min-height:400px;margin:10px}.image-carousel__slide-btn--right.pb-1e9hp2c.pb-1e9hp2c{transform:rotate(180deg)}.image-carousel__slide-btn.pb-1e9hp2c.pb-1e9hp2c{margin:0 10px;cursor:pointer}.image-carousel__slide-btn.pb-1e9hp2c.pb-1e9hp2c:disabled{opacity:0.5;cursor:inherit}.image-carousel__slide-btn.pb-1e9hp2c>img.pb-1e9hp2c{background:transparent;border:none;width:50px;height:59px}@media only screen and (max-width: 600px){.image-carousel__slider-image{min-width:60%;min-height:60%}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiSW1hZ2VDYXJvdXNlbC5zdmVsdGUiLCJzb3VyY2VzIjpbIkltYWdlQ2Fyb3VzZWwuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIGltcG9ydCB7IEZVTExfTU9EVUxFX1BBVEggfSBmcm9tICcuL2NvbnN0YW50cyc7XG5cbiAgaW1wb3J0IEltYWdlIGZyb20gJy4vUHJvamVjdC9JbWFnZS5zdmVsdGUnO1xuXG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBpbXBvcnQvbm8tbXV0YWJsZS1leHBvcnRzLGltcG9ydC9wcmVmZXItZGVmYXVsdC1leHBvcnRcbiAgZXhwb3J0IGxldCBzb3VyY2VzO1xuXG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG4gIGxldCBpbmRleCA9IDA7XG5cbiAgY29uc3QgbWlzc2luZ0FsdFRleHQgPSAoKSA9PiAhIXNvdXJjZXMuZmlsdGVyKChzcmMpID0+ICFzcmMuYWx0KS5sZW5ndGg7XG5cbiAgLyoqXG4gICAqIFByb3BzIGZvciBhIHNsaWRlIG5leHQvcHJldmlvdXMgYnV0dG9uLlxuICAgKlxuICAgKiBAcGFyYW0ge3N0cmluZ30gZGlyXG4gICAqICAgVGhlIGRpcmVjdGlvbiBvZiB0aGUgYnV0dG9uLlxuICAgKiBAcmV0dXJuIHt7ZGlzYWJsZWQ6IGJvb2xlYW4sIGNsYXNzOiBzdHJpbmd9fVxuICAgKiAgIFRoZSBzbGlkZSBwcm9wcy5cbiAgICovXG4gIGNvbnN0IGJ1dHRvblByb3BzID0gKGRpcikgPT4gKHtcbiAgICBjbGFzczogYGltYWdlLWNhcm91c2VsX19zbGlkZS1idG4gaW1hZ2UtY2Fyb3VzZWxfX3NsaWRlLWJ0bi0tJHtkaXJ9YCxcbiAgICBkaXNhYmxlZDogZGlyID09PSAncmlnaHQnID8gaW5kZXggPT09IHNvdXJjZXMubGVuZ3RoIC0gMSA6IGluZGV4ID09PSAwLFxuICB9KTtcblxuICAvKipcbiAgICogUHJvcHMgZm9yIGEgc2xpZGUgbmV4dC9wcmV2aW91cyBidXR0b24gaW1hZ2UuXG4gICAqXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBkaXJcbiAgICogICBUaGUgZGlyZWN0aW9uIG9mIHRoZSBidXR0b25cbiAgICogQHJldHVybiB7e3NyYzogc3RyaW5nLCBhbHQ6ICp9fVxuICAgKiAgIFRoZSBzbGlkZSBidXR0b24gUHJvcHNcbiAgICovXG4gIGNvbnN0IGltZ1Byb3BzID0gKGRpcikgPT4gKHtcbiAgICBzcmM6IGAke0ZVTExfTU9EVUxFX1BBVEh9L2ltYWdlcy9zbGlkZS1pY29uLnN2Z2AsXG4gICAgYWx0OiBkaXIgPT09ICdyaWdodCcgPyBEcnVwYWwudCgnU2xpZGUgcmlnaHQnKSA6IERydXBhbC50KCdTbGlkZSBsZWZ0JyksXG4gIH0pO1xuPC9zY3JpcHQ+XG5cbjwhLS0gc3ZlbHRlLWlnbm9yZSBhMTF5LW1pc3NpbmctYXR0cmlidXRlIC0tPlxuPGRpdiBjbGFzcz1cImltYWdlLWNhcm91c2VsX19jYXJvdXNlbFwiIGFyaWEtaGlkZGVuPXttaXNzaW5nQWx0VGV4dCgpfT5cbiAgeyNpZiBzb3VyY2VzLmxlbmd0aH1cbiAgICA8YnV0dG9uXG4gICAgICBvbjpjbGljaz17KCkgPT4ge1xuICAgICAgICBpbmRleCA9IChpbmRleCArIHNvdXJjZXMubGVuZ3RoIC0gMSkgJSBzb3VyY2VzLmxlbmd0aDtcbiAgICAgIH19XG4gICAgICB7Li4uYnV0dG9uUHJvcHMoJ2xlZnQnKX0+PGltZyB7Li4uaW1nUHJvcHMoJ2xlZnQnKX0gLz48L2J1dHRvblxuICAgID5cbiAgey9pZn1cbiAgPEltYWdlIHtzb3VyY2VzfSB7aW5kZXh9IGNsYXNzPVwiaW1hZ2UtY2Fyb3VzZWxfX3NsaWRlci1pbWFnZVwiIC8+XG4gIHsjaWYgc291cmNlcy5sZW5ndGh9XG4gICAgPGJ1dHRvblxuICAgICAgb246Y2xpY2s9eygpID0+IHtcbiAgICAgICAgaW5kZXggPSAoaW5kZXggKyAxKSAlIHNvdXJjZXMubGVuZ3RoO1xuICAgICAgfX1cbiAgICAgIHsuLi5idXR0b25Qcm9wcygncmlnaHQnKX0+PGltZyB7Li4uaW1nUHJvcHMoJ3JpZ2h0Jyl9IC8+PC9idXR0b25cbiAgICA+XG4gIHsvaWZ9XG48L2Rpdj5cblxuPHN0eWxlPlxuICAuaW1hZ2UtY2Fyb3VzZWxfX2Nhcm91c2VsIHtcbiAgICBkaXNwbGF5OiBmbGV4O1xuICAgIGFsaWduLWl0ZW1zOiBjZW50ZXI7XG4gICAgd2lkdGg6IDEwMCU7XG4gICAgaGVpZ2h0OiA0MDBweDtcbiAgfVxuICA6Z2xvYmFsKC5pbWFnZS1jYXJvdXNlbF9fc2xpZGVyLWltYWdlKSB7XG4gICAgbWluLXdpZHRoOiA2NTBweDtcbiAgICBtaW4taGVpZ2h0OiA0MDBweDtcbiAgICBtYXJnaW46IDEwcHg7XG4gIH1cblxuICAuaW1hZ2UtY2Fyb3VzZWxfX3NsaWRlLWJ0bi0tcmlnaHQge1xuICAgIHRyYW5zZm9ybTogcm90YXRlKDE4MGRlZyk7XG4gIH1cbiAgLmltYWdlLWNhcm91c2VsX19zbGlkZS1idG4ge1xuICAgIG1hcmdpbjogMCAxMHB4O1xuICAgIGN1cnNvcjogcG9pbnRlcjtcbiAgfVxuICAuaW1hZ2UtY2Fyb3VzZWxfX3NsaWRlLWJ0bjpkaXNhYmxlZCB7XG4gICAgb3BhY2l0eTogMC41O1xuICAgIGN1cnNvcjogaW5oZXJpdDtcbiAgfVxuICAuaW1hZ2UtY2Fyb3VzZWxfX3NsaWRlLWJ0biA+IGltZyB7XG4gICAgYmFja2dyb3VuZDogdHJhbnNwYXJlbnQ7XG4gICAgYm9yZGVyOiBub25lO1xuICAgIHdpZHRoOiA1MHB4O1xuICAgIGhlaWdodDogNTlweDtcbiAgfVxuICBAbWVkaWEgb25seSBzY3JlZW4gYW5kIChtYXgtd2lkdGg6IDYwMHB4KSB7XG4gICAgOmdsb2JhbCguaW1hZ2UtY2Fyb3VzZWxfX3NsaWRlci1pbWFnZSkge1xuICAgICAgbWluLXdpZHRoOiA2MCU7XG4gICAgICBtaW4taGVpZ2h0OiA2MCU7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQThERSwrQ0FBMEIsQ0FDeEIsT0FBTyxDQUFFLElBQUksQ0FDYixXQUFXLENBQUUsTUFBTSxDQUNuQixLQUFLLENBQUUsSUFBSSxDQUNYLE1BQU0sQ0FBRSxLQUNWLENBQ1EsNkJBQStCLENBQ3JDLFNBQVMsQ0FBRSxLQUFLLENBQ2hCLFVBQVUsQ0FBRSxLQUFLLENBQ2pCLE1BQU0sQ0FBRSxJQUNWLENBRUEsdURBQWtDLENBQ2hDLFNBQVMsQ0FBRSxPQUFPLE1BQU0sQ0FDMUIsQ0FDQSxnREFBMkIsQ0FDekIsTUFBTSxDQUFFLENBQUMsQ0FBQyxJQUFJLENBQ2QsTUFBTSxDQUFFLE9BQ1YsQ0FDQSxnREFBMEIsU0FBVSxDQUNsQyxPQUFPLENBQUUsR0FBRyxDQUNaLE1BQU0sQ0FBRSxPQUNWLENBQ0EscUNBQTBCLENBQUcsY0FBSSxDQUMvQixVQUFVLENBQUUsV0FBVyxDQUN2QixNQUFNLENBQUUsSUFBSSxDQUNaLEtBQUssQ0FBRSxJQUFJLENBQ1gsTUFBTSxDQUFFLElBQ1YsQ0FDQSxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLFlBQVksS0FBSyxDQUFFLENBQ2hDLDZCQUErQixDQUNyQyxTQUFTLENBQUUsR0FBRyxDQUNkLFVBQVUsQ0FBRSxHQUNkLENBQ0YifQ== */");
}

// (43:2) {#if sources.length}
function create_if_block_1(ctx) {
	let button;
	let img;
	let mounted;
	let dispose;
	let img_levels = [/*imgProps*/ ctx[4]('left')];
	let img_data = {};

	for (let i = 0; i < img_levels.length; i += 1) {
		img_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(img_data, img_levels[i]);
	}

	let button_levels = [/*buttonProps*/ ctx[3]('left')];
	let button_data = {};

	for (let i = 0; i < button_levels.length; i += 1) {
		button_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(button_data, button_levels[i]);
	}

	const block = {
		c: function create() {
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(img, img_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(img, "pb-1e9hp2c", true);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 47, 31, 1378);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(button, button_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "pb-1e9hp2c", true);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 43, 4, 1243);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, button, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, img);
			if (button.autofocus) button.focus();

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button, "click", /*click_handler*/ ctx[5], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(img, "pb-1e9hp2c", true);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "pb-1e9hp2c", true);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(button);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(43:2) {#if sources.length}",
		ctx
	});

	return block;
}

// (52:2) {#if sources.length}
function create_if_block(ctx) {
	let button;
	let img;
	let mounted;
	let dispose;
	let img_levels = [/*imgProps*/ ctx[4]('right')];
	let img_data = {};

	for (let i = 0; i < img_levels.length; i += 1) {
		img_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(img_data, img_levels[i]);
	}

	let button_levels = [/*buttonProps*/ ctx[3]('right')];
	let button_data = {};

	for (let i = 0; i < button_levels.length; i += 1) {
		button_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(button_data, button_levels[i]);
	}

	const block = {
		c: function create() {
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(img, img_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(img, "pb-1e9hp2c", true);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 56, 32, 1643);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(button, button_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "pb-1e9hp2c", true);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 52, 4, 1524);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, button, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, img);
			if (button.autofocus) button.focus();

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button, "click", /*click_handler_1*/ ctx[6], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(img, "pb-1e9hp2c", true);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "pb-1e9hp2c", true);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(button);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(52:2) {#if sources.length}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let div;
	let t0;
	let image;
	let t1;
	let div_aria_hidden_value;
	let current;
	let if_block0 = /*sources*/ ctx[0].length && create_if_block_1(ctx);

	image = new _Project_Image_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				sources: /*sources*/ ctx[0],
				index: /*index*/ ctx[1],
				class: "image-carousel__slider-image"
			},
			$$inline: true
		});

	let if_block1 = /*sources*/ ctx[0].length && create_if_block(ctx);

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if (if_block0) if_block0.c();
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(image.$$.fragment);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block1) if_block1.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "image-carousel__carousel pb-1e9hp2c");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "aria-hidden", div_aria_hidden_value = /*missingAltText*/ ctx[2]());
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 41, 0, 1146);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			if (if_block0) if_block0.m(div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(image, div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t1);
			if (if_block1) if_block1.m(div, null);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			if (/*sources*/ ctx[0].length) {
				if (if_block0) {
					if_block0.p(ctx, dirty);
				} else {
					if_block0 = create_if_block_1(ctx);
					if_block0.c();
					if_block0.m(div, t0);
				}
			} else if (if_block0) {
				if_block0.d(1);
				if_block0 = null;
			}

			const image_changes = {};
			if (dirty & /*sources*/ 1) image_changes.sources = /*sources*/ ctx[0];
			if (dirty & /*index*/ 2) image_changes.index = /*index*/ ctx[1];
			image.$set(image_changes);

			if (/*sources*/ ctx[0].length) {
				if (if_block1) {
					if_block1.p(ctx, dirty);
				} else {
					if_block1 = create_if_block(ctx);
					if_block1.c();
					if_block1.m(div, null);
				}
			} else if (if_block1) {
				if_block1.d(1);
				if_block1 = null;
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(image.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(image.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			if (if_block0) if_block0.d();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(image);
			if (if_block1) if_block1.d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ImageCarousel', slots, []);
	let { sources } = $$props;
	const { Drupal } = window;
	let index = 0;
	const missingAltText = () => !!sources.filter(src => !src.alt).length;

	/**
 * Props for a slide next/previous button.
 *
 * @param {string} dir
 *   The direction of the button.
 * @return {{disabled: boolean, class: string}}
 *   The slide props.
 */
	const buttonProps = dir => ({
		class: `image-carousel__slide-btn image-carousel__slide-btn--${dir}`,
		disabled: dir === 'right'
		? index === sources.length - 1
		: index === 0
	});

	/**
 * Props for a slide next/previous button image.
 *
 * @param {string} dir
 *   The direction of the button
 * @return {{src: string, alt: *}}
 *   The slide button Props
 */
	const imgProps = dir => ({
		src: `${_constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH}/images/slide-icon.svg`,
		alt: dir === 'right'
		? Drupal.t('Slide right')
		: Drupal.t('Slide left')
	});

	$$self.$$.on_mount.push(function () {
		if (sources === undefined && !('sources' in $$props || $$self.$$.bound[$$self.$$.props['sources']])) {
			console.warn("<ImageCarousel> was created without expected prop 'sources'");
		}
	});

	const writable_props = ['sources'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ImageCarousel> was created with unknown prop '${key}'`);
	});

	const click_handler = () => {
		$$invalidate(1, index = (index + sources.length - 1) % sources.length);
	};

	const click_handler_1 = () => {
		$$invalidate(1, index = (index + 1) % sources.length);
	};

	$$self.$$set = $$props => {
		if ('sources' in $$props) $$invalidate(0, sources = $$props.sources);
	};

	$$self.$capture_state = () => ({
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH,
		Image: _Project_Image_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		sources,
		Drupal,
		index,
		missingAltText,
		buttonProps,
		imgProps
	});

	$$self.$inject_state = $$props => {
		if ('sources' in $$props) $$invalidate(0, sources = $$props.sources);
		if ('index' in $$props) $$invalidate(1, index = $$props.index);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [
		sources,
		index,
		missingAltText,
		buttonProps,
		imgProps,
		click_handler,
		click_handler_1
	];
}

class ImageCarousel extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { sources: 0 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ImageCarousel",
			options,
			id: create_fragment.name
		});
	}

	get sources() {
		throw new Error("<ImageCarousel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set sources(value) {
		throw new Error("<ImageCarousel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ImageCarousel);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Loading.svelte":
/*!*************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Loading.svelte ***!
  \*************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Loading.svelte generated by Svelte v3.56.0 */


const file = "modules/project_browser/sveltejs/src/Loading.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1v2uogq", ".loading__ajax-progress.pb-1v2uogq{display:inline-block}.absolute.pb-1v2uogq{position:absolute !important}.ajax-progress__throbber.pb-1v2uogq{box-sizing:border-box;width:1.125rem;height:1.125rem;animation:pb-1v2uogq-pb-throbber 0.75s linear infinite;border:2px solid #003cc5;border-right:2px dotted transparent;border-radius:50%}.ajax-progress--fullscreen.pb-1v2uogq{position:fixed;z-index:1000;top:50%;left:50%;box-sizing:border-box;width:3.5rem;height:3.5rem;margin:-1.75rem;border:1px solid rgba(216, 217, 224, 0.8);border-radius:3.5rem;background:#fff;box-shadow:0 0.25rem 0.625rem rgba(34, 35, 48, 0.1)}.ajax-progress__throbber--fullscreen.pb-1v2uogq{position:absolute;top:50%;left:50%;width:1.75rem;height:1.75rem;margin:-0.875rem;content:'';border:3px solid #003cc5;border-right:3px dotted transparent}@keyframes pb-1v2uogq-pb-throbber{0%{transform:rotateZ(0)}100%{transform:rotateZ(360deg)}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiTG9hZGluZy5zdmVsdGUiLCJzb3VyY2VzIjpbIkxvYWRpbmcuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBpbXBvcnQvcHJlZmVyLWRlZmF1bHQtZXhwb3J0XG4gIGV4cG9ydCBsZXQgcG9zaXRpb25BYnNvbHV0ZSA9IGZhbHNlO1xuPC9zY3JpcHQ+XG5cbjxkaXZcbiAgY2xhc3M9XCJsb2FkaW5nX19hamF4LXByb2dyZXNzIGFqYXgtcHJvZ3Jlc3MtLWZ1bGxzY3JlZW5cIlxuICBjbGFzczphYnNvbHV0ZT17cG9zaXRpb25BYnNvbHV0ZX1cbj5cbiAgPGRpdiBjbGFzcz1cImFqYXgtcHJvZ3Jlc3NfX3Rocm9iYmVyIGFqYXgtcHJvZ3Jlc3NfX3Rocm9iYmVyLS1mdWxsc2NyZWVuXCI+XG4gICAgJm5ic3A7XG4gIDwvZGl2PlxuPC9kaXY+XG5cbjxzdHlsZT5cbiAgLmxvYWRpbmdfX2FqYXgtcHJvZ3Jlc3Mge1xuICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcbiAgfVxuXG4gIC5hYnNvbHV0ZSB7XG4gICAgcG9zaXRpb246IGFic29sdXRlICFpbXBvcnRhbnQ7XG4gIH1cblxuICAuYWpheC1wcm9ncmVzc19fdGhyb2JiZXIge1xuICAgIGJveC1zaXppbmc6IGJvcmRlci1ib3g7XG4gICAgd2lkdGg6IDEuMTI1cmVtO1xuICAgIGhlaWdodDogMS4xMjVyZW07XG4gICAgYW5pbWF0aW9uOiBwYi10aHJvYmJlciAwLjc1cyBsaW5lYXIgaW5maW5pdGU7XG4gICAgYm9yZGVyOiAycHggc29saWQgIzAwM2NjNTtcbiAgICBib3JkZXItcmlnaHQ6IDJweCBkb3R0ZWQgdHJhbnNwYXJlbnQ7XG4gICAgYm9yZGVyLXJhZGl1czogNTAlO1xuICB9XG5cbiAgLmFqYXgtcHJvZ3Jlc3MtLWZ1bGxzY3JlZW4ge1xuICAgIHBvc2l0aW9uOiBmaXhlZDtcbiAgICB6LWluZGV4OiAxMDAwO1xuICAgIHRvcDogNTAlO1xuICAgIGxlZnQ6IDUwJTtcbiAgICBib3gtc2l6aW5nOiBib3JkZXItYm94O1xuICAgIHdpZHRoOiAzLjVyZW07IC8qIDU2cHggKi9cbiAgICBoZWlnaHQ6IDMuNXJlbTtcbiAgICBtYXJnaW46IC0xLjc1cmVtO1xuICAgIGJvcmRlcjogMXB4IHNvbGlkIHJnYmEoMjE2LCAyMTcsIDIyNCwgMC44KTtcbiAgICBib3JkZXItcmFkaXVzOiAzLjVyZW07XG4gICAgYmFja2dyb3VuZDogI2ZmZjtcbiAgICBib3gtc2hhZG93OiAwIDAuMjVyZW0gMC42MjVyZW0gcmdiYSgzNCwgMzUsIDQ4LCAwLjEpO1xuICB9XG5cbiAgLmFqYXgtcHJvZ3Jlc3NfX3Rocm9iYmVyLS1mdWxsc2NyZWVuIHtcbiAgICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gICAgdG9wOiA1MCU7XG4gICAgbGVmdDogNTAlO1xuICAgIHdpZHRoOiAxLjc1cmVtOyAvKiAyOHB4ICovXG4gICAgaGVpZ2h0OiAxLjc1cmVtOyAvKiAyOHB4ICovXG4gICAgbWFyZ2luOiAtMC44NzVyZW07XG4gICAgY29udGVudDogJyc7XG4gICAgYm9yZGVyOiAzcHggc29saWQgIzAwM2NjNTtcbiAgICBib3JkZXItcmlnaHQ6IDNweCBkb3R0ZWQgdHJhbnNwYXJlbnQ7XG4gIH1cblxuICBAa2V5ZnJhbWVzIHBiLXRocm9iYmVyIHtcbiAgICAwJSB7XG4gICAgICB0cmFuc2Zvcm06IHJvdGF0ZVooMCk7XG4gICAgfVxuICAgIDEwMCUge1xuICAgICAgdHJhbnNmb3JtOiByb3RhdGVaKDM2MGRlZyk7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQWVFLGtDQUF3QixDQUN0QixPQUFPLENBQUUsWUFDWCxDQUVBLG9CQUFVLENBQ1IsUUFBUSxDQUFFLFFBQVEsQ0FBQyxVQUNyQixDQUVBLG1DQUF5QixDQUN2QixVQUFVLENBQUUsVUFBVSxDQUN0QixLQUFLLENBQUUsUUFBUSxDQUNmLE1BQU0sQ0FBRSxRQUFRLENBQ2hCLFNBQVMsQ0FBRSxzQkFBVyxDQUFDLEtBQUssQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUM1QyxNQUFNLENBQUUsR0FBRyxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQ3pCLFlBQVksQ0FBRSxHQUFHLENBQUMsTUFBTSxDQUFDLFdBQVcsQ0FDcEMsYUFBYSxDQUFFLEdBQ2pCLENBRUEscUNBQTJCLENBQ3pCLFFBQVEsQ0FBRSxLQUFLLENBQ2YsT0FBTyxDQUFFLElBQUksQ0FDYixHQUFHLENBQUUsR0FBRyxDQUNSLElBQUksQ0FBRSxHQUFHLENBQ1QsVUFBVSxDQUFFLFVBQVUsQ0FDdEIsS0FBSyxDQUFFLE1BQU0sQ0FDYixNQUFNLENBQUUsTUFBTSxDQUNkLE1BQU0sQ0FBRSxRQUFRLENBQ2hCLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBRyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQzFDLGFBQWEsQ0FBRSxNQUFNLENBQ3JCLFVBQVUsQ0FBRSxJQUFJLENBQ2hCLFVBQVUsQ0FBRSxDQUFDLENBQUMsT0FBTyxDQUFDLFFBQVEsQ0FBQyxLQUFLLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEVBQUUsQ0FBQyxDQUFDLEdBQUcsQ0FDckQsQ0FFQSwrQ0FBcUMsQ0FDbkMsUUFBUSxDQUFFLFFBQVEsQ0FDbEIsR0FBRyxDQUFFLEdBQUcsQ0FDUixJQUFJLENBQUUsR0FBRyxDQUNULEtBQUssQ0FBRSxPQUFPLENBQ2QsTUFBTSxDQUFFLE9BQU8sQ0FDZixNQUFNLENBQUUsU0FBUyxDQUNqQixPQUFPLENBQUUsRUFBRSxDQUNYLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FDekIsWUFBWSxDQUFFLEdBQUcsQ0FBQyxNQUFNLENBQUMsV0FDM0IsQ0FFQSxXQUFXLHNCQUFZLENBQ3JCLEVBQUcsQ0FDRCxTQUFTLENBQUUsUUFBUSxDQUFDLENBQ3RCLENBQ0EsSUFBSyxDQUNILFNBQVMsQ0FBRSxRQUFRLE1BQU0sQ0FDM0IsQ0FDRiJ9 */");
}

function create_fragment(ctx) {
	let div1;
	let div0;

	const block = {
		c: function create() {
			div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div0.textContent = " ";
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "class", "ajax-progress__throbber ajax-progress__throbber--fullscreen pb-1v2uogq");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div0, file, 9, 2, 222);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "class", "loading__ajax-progress ajax-progress--fullscreen pb-1v2uogq");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(div1, "absolute", /*positionAbsolute*/ ctx[0]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div1, file, 5, 0, 118);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div1, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, div0);
		},
		p: function update(ctx, [dirty]) {
			if (dirty & /*positionAbsolute*/ 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(div1, "absolute", /*positionAbsolute*/ ctx[0]);
			}
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div1);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('Loading', slots, []);
	let { positionAbsolute = false } = $$props;
	const writable_props = ['positionAbsolute'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Loading> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('positionAbsolute' in $$props) $$invalidate(0, positionAbsolute = $$props.positionAbsolute);
	};

	$$self.$capture_state = () => ({ positionAbsolute });

	$$self.$inject_state = $$props => {
		if ('positionAbsolute' in $$props) $$invalidate(0, positionAbsolute = $$props.positionAbsolute);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [positionAbsolute];
}

class Loading extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { positionAbsolute: 0 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "Loading",
			options,
			id: create_fragment.name
		});
	}

	get positionAbsolute() {
		throw new Error("<Loading>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set positionAbsolute(value) {
		throw new Error("<Loading>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Loading);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/MediaQuery.svelte":
/*!****************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/MediaQuery.svelte ***!
  \****************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/MediaQuery.svelte generated by Svelte v3.56.0 */



const file = "modules/project_browser/sveltejs/src/MediaQuery.svelte";
const get_default_slot_changes = dirty => ({ matches: dirty & /*matches*/ 1 });
const get_default_slot_context = ctx => ({ matches: /*matches*/ ctx[0] });

function create_fragment(ctx) {
	let current;
	const default_slot_template = /*#slots*/ ctx[4].default;
	const default_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(default_slot_template, ctx, /*$$scope*/ ctx[3], get_default_slot_context);

	const block = {
		c: function create() {
			if (default_slot) default_slot.c();
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			if (default_slot) {
				default_slot.m(target, anchor);
			}

			current = true;
		},
		p: function update(ctx, [dirty]) {
			if (default_slot) {
				if (default_slot.p && (!current || dirty & /*$$scope, matches*/ 9)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						default_slot,
						default_slot_template,
						ctx,
						/*$$scope*/ ctx[3],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[3])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(default_slot_template, /*$$scope*/ ctx[3], dirty, get_default_slot_changes),
						get_default_slot_context
					);
				}
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(default_slot, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(default_slot, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (default_slot) default_slot.d(detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('MediaQuery', slots, ['default']);
	let { query } = $$props;
	let mql;
	let mqlListener;
	let wasMounted = false;
	let matches = false;

	// eslint-disable-next-line no-shadow
	function addNewListener(query) {
		mql = window.matchMedia(query);

		mqlListener = v => {
			$$invalidate(0, matches = v.matches);
		};

		mql.addEventListener('change', mqlListener);
		$$invalidate(0, matches = mql.matches);
	}

	function removeActiveListener() {
		if (mql && mqlListener) {
			mql.removeListener(mqlListener);
		}
	}

	(0,svelte__WEBPACK_IMPORTED_MODULE_1__.onMount)(() => {
		$$invalidate(2, wasMounted = true);

		return () => {
			removeActiveListener();
		};
	});

	$$self.$$.on_mount.push(function () {
		if (query === undefined && !('query' in $$props || $$self.$$.bound[$$self.$$.props['query']])) {
			console.warn("<MediaQuery> was created without expected prop 'query'");
		}
	});

	const writable_props = ['query'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<MediaQuery> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('query' in $$props) $$invalidate(1, query = $$props.query);
		if ('$$scope' in $$props) $$invalidate(3, $$scope = $$props.$$scope);
	};

	$$self.$capture_state = () => ({
		onMount: svelte__WEBPACK_IMPORTED_MODULE_1__.onMount,
		query,
		mql,
		mqlListener,
		wasMounted,
		matches,
		addNewListener,
		removeActiveListener
	});

	$$self.$inject_state = $$props => {
		if ('query' in $$props) $$invalidate(1, query = $$props.query);
		if ('mql' in $$props) mql = $$props.mql;
		if ('mqlListener' in $$props) mqlListener = $$props.mqlListener;
		if ('wasMounted' in $$props) $$invalidate(2, wasMounted = $$props.wasMounted);
		if ('matches' in $$props) $$invalidate(0, matches = $$props.matches);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	$$self.$$.update = () => {
		if ($$self.$$.dirty & /*wasMounted, query*/ 6) {
			$: {
				if (wasMounted) {
					removeActiveListener();
					addNewListener(query);
				}
			}
		}
	};

	return [matches, query, wasMounted, $$scope, slots];
}

class MediaQuery extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { query: 1 });

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "MediaQuery",
			options,
			id: create_fragment.name
		});
	}

	get query() {
		throw new Error("<MediaQuery>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set query(value) {
		throw new Error("<MediaQuery>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (MediaQuery);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/ModulePage.svelte":
/*!****************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/ModulePage.svelte ***!
  \****************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _Project_ActionButton_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Project/ActionButton.svelte */ "./modules/project_browser/sveltejs/src/Project/ActionButton.svelte");
/* harmony import */ var _Project_Image_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Project/Image.svelte */ "./modules/project_browser/sveltejs/src/Project/Image.svelte");
/* harmony import */ var _ImageCarousel_svelte__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ImageCarousel.svelte */ "./modules/project_browser/sveltejs/src/ImageCarousel.svelte");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./Project/ProjectIcon.svelte */ "./modules/project_browser/sveltejs/src/Project/ProjectIcon.svelte");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/ModulePage.svelte generated by Svelte v3.56.0 */









const file = "modules/project_browser/sveltejs/src/ModulePage.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-26jj6j", ".module-page__wrapper.pb-26jj6j{display:flex;text-align:start}.module-page__sidebar.pb-26jj6j{flex:1;padding:40px;display:flex;flex-direction:column}.module-page__main.pb-26jj6j{flex:4;padding:40px;display:flex;flex-direction:column;align-items:flex-start}.module-page__action-button-wrapper.pb-26jj6j{margin:20px}.module-page__divider.pb-26jj6j{border:1px solid #000000}.module-page__categories-label.pb-26jj6j{font-weight:bold}.module-page__module-details-grid.pb-26jj6j{display:grid;grid-template-columns:0.5fr 2fr;line-height:20px;margin-top:10px}.module-page__module-details-grid.pb-26jj6j>*:nth-child(odd){margin:10% 20% 0 40%}.module-page__module-details-grid.pb-26jj6j>*:nth-child(even){margin:5px 15px}.module-page__category-list.pb-26jj6j{margin:0.25em 0 0.25em 0;padding:0;display:inline-block;width:100%;height:20px;cursor:pointer}.module-page__category-list-item.pb-26jj6j{list-style:none;display:inline-block;margin-top:5px;margin-bottom:2px;margin-inline-start:7px;padding:2px 9px;border-radius:25px;background-color:#e5e5e5;font-size:0.9em;font-weight:600;color:#4f4f4f}.module-page__project-logo{min-height:200px}.module-page__carousel-wrapper.pb-26jj6j{margin:20px 0}.module-page--back-to-browsing.pb-26jj6j{text-decoration:none}@media only screen and (max-width: 600px){.module-page__wrapper.pb-26jj6j{flex-direction:column}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiTW9kdWxlUGFnZS5zdmVsdGUiLCJzb3VyY2VzIjpbIk1vZHVsZVBhZ2Uuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIGltcG9ydCB7IG9uTW91bnQgfSBmcm9tICdzdmVsdGUnO1xuICBpbXBvcnQgQWN0aW9uQnV0dG9uIGZyb20gJy4vUHJvamVjdC9BY3Rpb25CdXR0b24uc3ZlbHRlJztcbiAgaW1wb3J0IEltYWdlIGZyb20gJy4vUHJvamVjdC9JbWFnZS5zdmVsdGUnO1xuICBpbXBvcnQgSW1hZ2VDYXJvdXNlbCBmcm9tICcuL0ltYWdlQ2Fyb3VzZWwuc3ZlbHRlJztcbiAgaW1wb3J0IHsgT1JJR0lOX1VSTCB9IGZyb20gJy4vY29uc3RhbnRzJztcbiAgaW1wb3J0IHsgbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIsIHBhZ2UgfSBmcm9tICcuL3N0b3Jlcyc7XG4gIGltcG9ydCBQcm9qZWN0SWNvbiBmcm9tICcuL1Byb2plY3QvUHJvamVjdEljb24uc3ZlbHRlJztcblxuICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgaW1wb3J0L25vLW11dGFibGUtZXhwb3J0cyxpbXBvcnQvcHJlZmVyLWRlZmF1bHQtZXhwb3J0XG4gIGV4cG9ydCBsZXQgcHJvamVjdDtcbiAgY29uc3QgeyBEcnVwYWwgfSA9IHdpbmRvdztcblxuICBmdW5jdGlvbiBmaWx0ZXJCeUNhdGVnb3J5KGlkKSB7XG4gICAgJG1vZHVsZUNhdGVnb3J5RmlsdGVyID0gW2lkXTtcbiAgICAkcGFnZSA9IDA7XG4gICAgd2luZG93LmxvY2F0aW9uLmhyZWYgPSBgJHtPUklHSU5fVVJMfS9hZG1pbi9tb2R1bGVzL2Jyb3dzZWA7XG4gIH1cblxuICBvbk1vdW50KCgpID0+IHtcbiAgICBjb25zdCBhbmNob3JzID0gZG9jdW1lbnRcbiAgICAgIC5nZXRFbGVtZW50QnlJZCgnZGVzY3JpcHRpb24td3JhcHBlcicpXG4gICAgICAuZ2V0RWxlbWVudHNCeVRhZ05hbWUoJ2EnKTtcbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IGFuY2hvcnMubGVuZ3RoOyBpKyspIHtcbiAgICAgIGFuY2hvcnNbaV0uc2V0QXR0cmlidXRlKCd0YXJnZXQnLCAnX2JsYW5rJyk7XG4gICAgfVxuICB9KTtcbjwvc2NyaXB0PlxuXG48YVxuICBjbGFzcz1cIm1vZHVsZS1wYWdlLS1iYWNrLXRvLWJyb3dzaW5nIGFjdGlvbi1saW5rXCJcbiAgaHJlZj1cIntPUklHSU5fVVJMfS9hZG1pbi9tb2R1bGVzL2Jyb3dzZVwiXG4+XG4gIDxzcGFuIGFyaWEtaGlkZGVuPVwidHJ1ZVwiPiYjOTAwMSYjeEEwPC9zcGFuPlxuICB7RHJ1cGFsLnQoJ0JhY2sgdG8gQnJvd3NpbmcnKX1cbjwvYT5cblxuPGRpdiBjbGFzcz1cIm1vZHVsZS1wYWdlX193cmFwcGVyXCI+XG4gIDxkaXYgY2xhc3M9XCJtb2R1bGUtcGFnZV9fc2lkZWJhclwiPlxuICAgIDxJbWFnZSBzb3VyY2VzPXtwcm9qZWN0LmxvZ299IGNsYXNzPVwibW9kdWxlLXBhZ2VfX3Byb2plY3QtbG9nb1wiIC8+XG4gICAgPGRpdiBjbGFzcz1cIm1vZHVsZS1wYWdlX19hY3Rpb24tYnV0dG9uLXdyYXBwZXJcIj5cbiAgICAgIDxBY3Rpb25CdXR0b24ge3Byb2plY3R9IC8+XG4gICAgPC9kaXY+XG4gICAgPGRpdiBjbGFzcz1cIm1vZHVsZS1wYWdlX19kaXZpZGVyXCI+Jm5ic3A7PC9kaXY+XG4gICAgPGg0PntEcnVwYWwudCgnRGV0YWlscycpfTwvaDQ+XG4gICAgPGRpdiBjbGFzcz1cIm1vZHVsZS1wYWdlX19wcm9qZWN0LWRhdGFcIj5cbiAgICAgIHsjaWYgcHJvamVjdC5tb2R1bGVfY2F0ZWdvcmllcy5sZW5ndGh9XG4gICAgICAgIDxwIGNsYXNzPVwibW9kdWxlLXBhZ2VfX2NhdGVnb3JpZXMtbGFiZWxcIiBpZD1cImNhdGVnb3JpZXNcIj5cbiAgICAgICAgICB7RHJ1cGFsLnQoJ0NhdGVnb3JpZXM6Jyl9XG4gICAgICAgIDwvcD5cbiAgICAgICAgPHVsIGNsYXNzPVwibW9kdWxlLXBhZ2VfX2NhdGVnb3J5LWxpc3RcIiBhcmlhLWxhYmVsbGVkYnk9XCJjYXRlZ29yaWVzXCI+XG4gICAgICAgICAgeyNlYWNoIHByb2plY3QubW9kdWxlX2NhdGVnb3JpZXMgfHwgW10gYXMgY2F0ZWdvcnl9XG4gICAgICAgICAgICA8IS0tIHN2ZWx0ZS1pZ25vcmUgYTExeS1jbGljay1ldmVudHMtaGF2ZS1rZXktZXZlbnRzIC0tPlxuICAgICAgICAgICAgPGxpXG4gICAgICAgICAgICAgIG9uOmNsaWNrPXsoKSA9PiBmaWx0ZXJCeUNhdGVnb3J5KGNhdGVnb3J5LmlkKX1cbiAgICAgICAgICAgICAgY2xhc3M9XCJtb2R1bGUtcGFnZV9fY2F0ZWdvcnktbGlzdC1pdGVtXCJcbiAgICAgICAgICAgID5cbiAgICAgICAgICAgICAge2NhdGVnb3J5Lm5hbWV9XG4gICAgICAgICAgICA8L2xpPlxuICAgICAgICAgIHsvZWFjaH1cbiAgICAgICAgPC91bD5cbiAgICAgIHsvaWZ9XG4gICAgICA8ZGl2IGNsYXNzPVwibW9kdWxlLXBhZ2VfX21vZHVsZS1kZXRhaWxzLWdyaWRcIj5cbiAgICAgICAgeyNpZiBwcm9qZWN0LmlzX2NvbXBhdGlibGV9XG4gICAgICAgICAgPFByb2plY3RJY29uXG4gICAgICAgICAgICB0eXBlPVwiY29tcGF0aWJsZVwiXG4gICAgICAgICAgICB2YXJpYW50PVwibW9kdWxlLWRldGFpbHNcIlxuICAgICAgICAgICAgY2xhc3Nlcz1cIm1vZHVsZS1wYWdlX19tb2R1bGUtZGV0YWlscy1ncmlkX19pY29uXCJcbiAgICAgICAgICAvPlxuICAgICAgICAgIDxwIGNsYXNzPVwibW9kdWxlLXBhZ2VfX21vZHVsZS1kZXRhaWxzLWdyaWRfX2Rlc2NyaXB0aW9uXCI+XG4gICAgICAgICAgICB7RHJ1cGFsLnQoJ0NvbXBhdGlibGUgd2l0aCB5b3VyIERydXBhbCBpbnN0YWxsYXRpb24nKX1cbiAgICAgICAgICA8L3A+XG4gICAgICAgIHsvaWZ9XG4gICAgICAgIHsjaWYgcHJvamVjdC5wcm9qZWN0X3VzYWdlX3RvdGFsICE9PSAtMX1cbiAgICAgICAgICA8UHJvamVjdEljb25cbiAgICAgICAgICAgIHR5cGU9XCJ1c2FnZVwiXG4gICAgICAgICAgICB2YXJpYW50PVwibW9kdWxlLWRldGFpbHNcIlxuICAgICAgICAgICAgY2xhc3Nlcz1cIm1vZHVsZS1wYWdlX19tb2R1bGUtZGV0YWlscy1ncmlkX19pY29uXCJcbiAgICAgICAgICAvPlxuICAgICAgICAgIDxwIGNsYXNzPVwibW9kdWxlLXBhZ2VfX21vZHVsZS1kZXRhaWxzLWdyaWRfX2Rlc2NyaXB0aW9uXCI+XG4gICAgICAgICAgICB7cHJvamVjdC5wcm9qZWN0X3VzYWdlX3RvdGFsXG4gICAgICAgICAgICAgIC50b1N0cmluZygpXG4gICAgICAgICAgICAgIC5yZXBsYWNlKC9cXEIoPz0oXFxkezN9KSsoPyFcXGQpKS9nLCAnLCcpfXtEcnVwYWwudChcbiAgICAgICAgICAgICAgJyBzaXRlcyByZXBvcnQgdXNpbmcgdGhpcyBtb2R1bGUnLFxuICAgICAgICAgICAgKX1cbiAgICAgICAgICA8L3A+XG4gICAgICAgIHsvaWZ9XG4gICAgICAgIHsjaWYgcHJvamVjdC5pc19jb3ZlcmVkfVxuICAgICAgICAgIDxQcm9qZWN0SWNvblxuICAgICAgICAgICAgdHlwZT1cInN0YXR1c1wiXG4gICAgICAgICAgICB2YXJpYW50PVwibW9kdWxlLWRldGFpbHNcIlxuICAgICAgICAgICAgY2xhc3Nlcz1cIm1vZHVsZS1wYWdlX19tb2R1bGUtZGV0YWlscy1ncmlkX19pY29uXCJcbiAgICAgICAgICAvPlxuICAgICAgICAgIDxwIGNsYXNzPVwibW9kdWxlLXBhZ2VfX21vZHVsZS1kZXRhaWxzLWdyaWRfX2Rlc2NyaXB0aW9uXCI+XG4gICAgICAgICAgICB7RHJ1cGFsLnQoXG4gICAgICAgICAgICAgICdTdGFibGUgcmVsZWFzZXMgZm9yIHRoaXMgcHJvamVjdCBhcmUgY292ZXJlZCBieSB0aGUgc2VjdXJpdHkgYWR2aXNvcnkgcG9saWN5JyxcbiAgICAgICAgICAgICl9XG4gICAgICAgICAgPC9wPlxuICAgICAgICB7L2lmfVxuICAgICAgPC9kaXY+XG4gICAgPC9kaXY+XG4gIDwvZGl2PlxuICA8ZGl2IGNsYXNzPVwibW9kdWxlLXBhZ2VfX21haW5cIj5cbiAgICA8aDIgY2xhc3M9XCJtb2R1bGUtcGFnZV9faDJcIj57cHJvamVjdC50aXRsZX08L2gyPlxuICAgIDxwIGNsYXNzPVwibW9kdWxlLXBhZ2VfX2F1dGhvclwiPlxuICAgICAge0RydXBhbC50KCdCeSAnKX17cHJvamVjdC5hdXRob3IubmFtZX1cbiAgICA8L3A+XG4gICAgeyNpZiBwcm9qZWN0LnByb2plY3RfaW1hZ2VzLmxlbmd0aH1cbiAgICAgIDxkaXYgY2xhc3M9XCJtb2R1bGUtcGFnZV9fY2Fyb3VzZWwtd3JhcHBlclwiPlxuICAgICAgICA8SW1hZ2VDYXJvdXNlbCBzb3VyY2VzPXtwcm9qZWN0LnByb2plY3RfaW1hZ2VzfSAvPlxuICAgICAgPC9kaXY+XG4gICAgey9pZn1cbiAgICA8ZGl2IGNsYXNzPVwibW9kdWxlLXBhZ2VfX3Byb2plY3QtZGVzY3JpcHRpb25cIiBpZD1cImRlc2NyaXB0aW9uLXdyYXBwZXJcIj5cbiAgICAgIHtAaHRtbCBwcm9qZWN0LmJvZHkudmFsdWV9XG4gICAgPC9kaXY+XG4gIDwvZGl2PlxuPC9kaXY+XG5cbjxzdHlsZT5cbiAgLm1vZHVsZS1wYWdlX193cmFwcGVyIHtcbiAgICBkaXNwbGF5OiBmbGV4O1xuICAgIHRleHQtYWxpZ246IHN0YXJ0O1xuICB9XG4gIC5tb2R1bGUtcGFnZV9fc2lkZWJhciB7XG4gICAgZmxleDogMTtcbiAgICBwYWRkaW5nOiA0MHB4O1xuICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgfVxuICAubW9kdWxlLXBhZ2VfX21haW4ge1xuICAgIGZsZXg6IDQ7XG4gICAgcGFkZGluZzogNDBweDtcbiAgICBkaXNwbGF5OiBmbGV4O1xuICAgIGZsZXgtZGlyZWN0aW9uOiBjb2x1bW47XG4gICAgYWxpZ24taXRlbXM6IGZsZXgtc3RhcnQ7XG4gIH1cbiAgLm1vZHVsZS1wYWdlX19hY3Rpb24tYnV0dG9uLXdyYXBwZXIge1xuICAgIG1hcmdpbjogMjBweDtcbiAgfVxuICAubW9kdWxlLXBhZ2VfX2RpdmlkZXIge1xuICAgIGJvcmRlcjogMXB4IHNvbGlkICMwMDAwMDA7XG4gIH1cbiAgLm1vZHVsZS1wYWdlX19jYXRlZ29yaWVzLWxhYmVsIHtcbiAgICBmb250LXdlaWdodDogYm9sZDtcbiAgfVxuICAubW9kdWxlLXBhZ2VfX21vZHVsZS1kZXRhaWxzLWdyaWQge1xuICAgIGRpc3BsYXk6IGdyaWQ7XG4gICAgZ3JpZC10ZW1wbGF0ZS1jb2x1bW5zOiAwLjVmciAyZnI7XG4gICAgbGluZS1oZWlnaHQ6IDIwcHg7XG4gICAgbWFyZ2luLXRvcDogMTBweDtcbiAgfVxuXG4gIC5tb2R1bGUtcGFnZV9fbW9kdWxlLWRldGFpbHMtZ3JpZCA+IDpnbG9iYWwoKjpudGgtY2hpbGQob2RkKSkge1xuICAgIG1hcmdpbjogMTAlIDIwJSAwIDQwJTtcbiAgfVxuXG4gIC5tb2R1bGUtcGFnZV9fbW9kdWxlLWRldGFpbHMtZ3JpZCA+IDpnbG9iYWwoKjpudGgtY2hpbGQoZXZlbikpIHtcbiAgICBtYXJnaW46IDVweCAxNXB4O1xuICB9XG4gIC5tb2R1bGUtcGFnZV9fY2F0ZWdvcnktbGlzdCB7XG4gICAgbWFyZ2luOiAwLjI1ZW0gMCAwLjI1ZW0gMDtcbiAgICBwYWRkaW5nOiAwO1xuICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcbiAgICB3aWR0aDogMTAwJTtcbiAgICBoZWlnaHQ6IDIwcHg7XG4gICAgY3Vyc29yOiBwb2ludGVyO1xuICB9XG4gIC5tb2R1bGUtcGFnZV9fY2F0ZWdvcnktbGlzdC1pdGVtIHtcbiAgICBsaXN0LXN0eWxlOiBub25lO1xuICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcbiAgICBtYXJnaW4tdG9wOiA1cHg7XG4gICAgbWFyZ2luLWJvdHRvbTogMnB4O1xuICAgIG1hcmdpbi1pbmxpbmUtc3RhcnQ6IDdweDtcbiAgICBwYWRkaW5nOiAycHggOXB4O1xuICAgIGJvcmRlci1yYWRpdXM6IDI1cHg7XG4gICAgYmFja2dyb3VuZC1jb2xvcjogI2U1ZTVlNTtcbiAgICBmb250LXNpemU6IDAuOWVtO1xuICAgIGZvbnQtd2VpZ2h0OiA2MDA7XG4gICAgY29sb3I6ICM0ZjRmNGY7XG4gIH1cbiAgOmdsb2JhbCgubW9kdWxlLXBhZ2VfX3Byb2plY3QtbG9nbykge1xuICAgIG1pbi1oZWlnaHQ6IDIwMHB4O1xuICB9XG4gIC5tb2R1bGUtcGFnZV9fY2Fyb3VzZWwtd3JhcHBlciB7XG4gICAgbWFyZ2luOiAyMHB4IDA7XG4gIH1cbiAgLm1vZHVsZS1wYWdlLS1iYWNrLXRvLWJyb3dzaW5nIHtcbiAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7XG4gIH1cbiAgQG1lZGlhIG9ubHkgc2NyZWVuIGFuZCAobWF4LXdpZHRoOiA2MDBweCkge1xuICAgIC5tb2R1bGUtcGFnZV9fd3JhcHBlciB7XG4gICAgICBmbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICAgIH1cbiAgfVxuPC9zdHlsZT5cbiJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUF1SEUsK0JBQXNCLENBQ3BCLE9BQU8sQ0FBRSxJQUFJLENBQ2IsVUFBVSxDQUFFLEtBQ2QsQ0FDQSwrQkFBc0IsQ0FDcEIsSUFBSSxDQUFFLENBQUMsQ0FDUCxPQUFPLENBQUUsSUFBSSxDQUNiLE9BQU8sQ0FBRSxJQUFJLENBQ2IsY0FBYyxDQUFFLE1BQ2xCLENBQ0EsNEJBQW1CLENBQ2pCLElBQUksQ0FBRSxDQUFDLENBQ1AsT0FBTyxDQUFFLElBQUksQ0FDYixPQUFPLENBQUUsSUFBSSxDQUNiLGNBQWMsQ0FBRSxNQUFNLENBQ3RCLFdBQVcsQ0FBRSxVQUNmLENBQ0EsNkNBQW9DLENBQ2xDLE1BQU0sQ0FBRSxJQUNWLENBQ0EsK0JBQXNCLENBQ3BCLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FBSyxDQUFDLE9BQ3BCLENBQ0Esd0NBQStCLENBQzdCLFdBQVcsQ0FBRSxJQUNmLENBQ0EsMkNBQWtDLENBQ2hDLE9BQU8sQ0FBRSxJQUFJLENBQ2IscUJBQXFCLENBQUUsS0FBSyxDQUFDLEdBQUcsQ0FDaEMsV0FBVyxDQUFFLElBQUksQ0FDakIsVUFBVSxDQUFFLElBQ2QsQ0FFQSwyQ0FBaUMsQ0FBVyxnQkFBa0IsQ0FDNUQsTUFBTSxDQUFFLEdBQUcsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUFDLEdBQ3BCLENBRUEsMkNBQWlDLENBQVcsaUJBQW1CLENBQzdELE1BQU0sQ0FBRSxHQUFHLENBQUMsSUFDZCxDQUNBLHFDQUE0QixDQUMxQixNQUFNLENBQUUsTUFBTSxDQUFDLENBQUMsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUN6QixPQUFPLENBQUUsQ0FBQyxDQUNWLE9BQU8sQ0FBRSxZQUFZLENBQ3JCLEtBQUssQ0FBRSxJQUFJLENBQ1gsTUFBTSxDQUFFLElBQUksQ0FDWixNQUFNLENBQUUsT0FDVixDQUNBLDBDQUFpQyxDQUMvQixVQUFVLENBQUUsSUFBSSxDQUNoQixPQUFPLENBQUUsWUFBWSxDQUNyQixVQUFVLENBQUUsR0FBRyxDQUNmLGFBQWEsQ0FBRSxHQUFHLENBQ2xCLG1CQUFtQixDQUFFLEdBQUcsQ0FDeEIsT0FBTyxDQUFFLEdBQUcsQ0FBQyxHQUFHLENBQ2hCLGFBQWEsQ0FBRSxJQUFJLENBQ25CLGdCQUFnQixDQUFFLE9BQU8sQ0FDekIsU0FBUyxDQUFFLEtBQUssQ0FDaEIsV0FBVyxDQUFFLEdBQUcsQ0FDaEIsS0FBSyxDQUFFLE9BQ1QsQ0FDUSwwQkFBNEIsQ0FDbEMsVUFBVSxDQUFFLEtBQ2QsQ0FDQSx3Q0FBK0IsQ0FDN0IsTUFBTSxDQUFFLElBQUksQ0FBQyxDQUNmLENBQ0Esd0NBQStCLENBQzdCLGVBQWUsQ0FBRSxJQUNuQixDQUNBLE9BQU8sSUFBSSxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsWUFBWSxLQUFLLENBQUUsQ0FDeEMsK0JBQXNCLENBQ3BCLGNBQWMsQ0FBRSxNQUNsQixDQUNGIn0= */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[6] = list[i];
	return child_ctx;
}

// (47:6) {#if project.module_categories.length}
function create_if_block_4(ctx) {
	let p;
	let t1;
	let ul;
	let each_value = /*project*/ ctx[0].module_categories || [];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const block = {
		c: function create() {
			p = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			p.textContent = `${/*Drupal*/ ctx[1].t('Categories:')}`;
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			ul = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("ul");

			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p, "class", "module-page__categories-label pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p, "id", "categories");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p, file, 47, 8, 1510);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(ul, "class", "module-page__category-list pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(ul, "aria-labelledby", "categories");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(ul, file, 50, 8, 1625);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, p, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t1, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, ul, anchor);

			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(ul, null);
				}
			}
		},
		p: function update(ctx, dirty) {
			if (dirty & /*filterByCategory, project*/ 5) {
				each_value = /*project*/ ctx[0].module_categories || [];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						each_blocks[i].m(ul, null);
					}
				}

				for (; i < each_blocks.length; i += 1) {
					each_blocks[i].d(1);
				}

				each_blocks.length = each_value.length;
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(p);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t1);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(ul);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_4.name,
		type: "if",
		source: "(47:6) {#if project.module_categories.length}",
		ctx
	});

	return block;
}

// (52:10) {#each project.module_categories || [] as category}
function create_each_block(ctx) {
	let li;
	let t0_value = /*category*/ ctx[6].name + "";
	let t0;
	let t1;
	let mounted;
	let dispose;

	function click_handler() {
		return /*click_handler*/ ctx[3](/*category*/ ctx[6]);
	}

	const block = {
		c: function create() {
			li = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("li");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", "module-page__category-list-item pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(li, file, 53, 12, 1837);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, li, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, t1);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(li, "click", click_handler, false, false, false, false);
				mounted = true;
			}
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;
			if (dirty & /*project*/ 1 && t0_value !== (t0_value = /*category*/ ctx[6].name + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, t0_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(li);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(52:10) {#each project.module_categories || [] as category}",
		ctx
	});

	return block;
}

// (64:8) {#if project.is_compatible}
function create_if_block_3(ctx) {
	let projecticon;
	let t0;
	let p;
	let current;

	projecticon = new _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_7__["default"]({
			props: {
				type: "compatible",
				variant: "module-details",
				classes: "module-page__module-details-grid__icon"
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projecticon.$$.fragment);
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			p = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			p.textContent = `${/*Drupal*/ ctx[1].t('Compatible with your Drupal installation')}`;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p, "class", "module-page__module-details-grid__description");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p, file, 69, 10, 2325);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projecticon, target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t0, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, p, anchor);
			current = true;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projecticon.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projecticon.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projecticon, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t0);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(p);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_3.name,
		type: "if",
		source: "(64:8) {#if project.is_compatible}",
		ctx
	});

	return block;
}

// (74:8) {#if project.project_usage_total !== -1}
function create_if_block_2(ctx) {
	let projecticon;
	let t0;
	let p;
	let t1_value = /*project*/ ctx[0].project_usage_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + "";
	let t1;
	let t2_value = /*Drupal*/ ctx[1].t(' sites report using this module') + "";
	let t2;
	let current;

	projecticon = new _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_7__["default"]({
			props: {
				type: "usage",
				variant: "module-details",
				classes: "module-page__module-details-grid__icon"
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projecticon.$$.fragment);
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			p = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t1_value);
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t2_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p, "class", "module-page__module-details-grid__description");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p, file, 79, 10, 2697);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projecticon, target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t0, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, p, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p, t2);
			current = true;
		},
		p: function update(ctx, dirty) {
			if ((!current || dirty & /*project*/ 1) && t1_value !== (t1_value = /*project*/ ctx[0].project_usage_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',') + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t1, t1_value);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projecticon.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projecticon.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projecticon, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t0);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(p);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(74:8) {#if project.project_usage_total !== -1}",
		ctx
	});

	return block;
}

// (88:8) {#if project.is_covered}
function create_if_block_1(ctx) {
	let projecticon;
	let t0;
	let p;
	let current;

	projecticon = new _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_7__["default"]({
			props: {
				type: "status",
				variant: "module-details",
				classes: "module-page__module-details-grid__icon"
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projecticon.$$.fragment);
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			p = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			p.textContent = `${/*Drupal*/ ctx[1].t('Stable releases for this project are covered by the security advisory policy')}`;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p, "class", "module-page__module-details-grid__description");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p, file, 93, 10, 3182);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projecticon, target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t0, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, p, anchor);
			current = true;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projecticon.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projecticon.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projecticon, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t0);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(p);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(88:8) {#if project.is_covered}",
		ctx
	});

	return block;
}

// (108:4) {#if project.project_images.length}
function create_if_block(ctx) {
	let div;
	let imagecarousel;
	let current;

	imagecarousel = new _ImageCarousel_svelte__WEBPACK_IMPORTED_MODULE_4__["default"]({
			props: {
				sources: /*project*/ ctx[0].project_images
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(imagecarousel.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "module-page__carousel-wrapper pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 108, 6, 3657);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(imagecarousel, div, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			const imagecarousel_changes = {};
			if (dirty & /*project*/ 1) imagecarousel_changes.sources = /*project*/ ctx[0].project_images;
			imagecarousel.$set(imagecarousel_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(imagecarousel.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(imagecarousel.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(imagecarousel);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(108:4) {#if project.project_images.length}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let a;
	let span;
	let t1;
	let t2_value = /*Drupal*/ ctx[1].t('Back to Browsing') + "";
	let t2;
	let a_href_value;
	let t3;
	let div7;
	let div4;
	let image;
	let t4;
	let div0;
	let actionbutton;
	let t5;
	let div1;
	let t7;
	let h4;
	let t9;
	let div3;
	let t10;
	let div2;
	let t11;
	let t12;
	let t13;
	let div6;
	let h2;
	let t14_value = /*project*/ ctx[0].title + "";
	let t14;
	let t15;
	let p;
	let t16_value = /*Drupal*/ ctx[1].t('By ') + "";
	let t16;
	let t17_value = /*project*/ ctx[0].author.name + "";
	let t17;
	let t18;
	let t19;
	let div5;
	let raw_value = /*project*/ ctx[0].body.value + "";
	let current;

	image = new _Project_Image_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: {
				sources: /*project*/ ctx[0].logo,
				class: "module-page__project-logo"
			},
			$$inline: true
		});

	actionbutton = new _Project_ActionButton_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: { project: /*project*/ ctx[0] },
			$$inline: true
		});

	let if_block0 = /*project*/ ctx[0].module_categories.length && create_if_block_4(ctx);
	let if_block1 = /*project*/ ctx[0].is_compatible && create_if_block_3(ctx);
	let if_block2 = /*project*/ ctx[0].project_usage_total !== -1 && create_if_block_2(ctx);
	let if_block3 = /*project*/ ctx[0].is_covered && create_if_block_1(ctx);
	let if_block4 = /*project*/ ctx[0].project_images.length && create_if_block(ctx);

	const block = {
		c: function create() {
			a = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("a");
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			span.textContent = "〈 ";
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t2_value);
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div7 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(image.$$.fragment);
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(actionbutton.$$.fragment);
			t5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div1.textContent = " ";
			t7 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			h4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h4");
			h4.textContent = `${/*Drupal*/ ctx[1].t('Details')}`;
			t9 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if (if_block0) if_block0.c();
			t10 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if (if_block1) if_block1.c();
			t11 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block2) if_block2.c();
			t12 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block3) if_block3.c();
			t13 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div6 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			h2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h2");
			t14 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t14_value);
			t15 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			p = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			t16 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t16_value);
			t17 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t17_value);
			t18 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block4) if_block4.c();
			t19 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "aria-hidden", "true");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 33, 2, 1004);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "class", "module-page--back-to-browsing action-link pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "href", a_href_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_5__.ORIGIN_URL + "/admin/modules/browse"));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(a, file, 29, 0, 902);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "class", "module-page__action-button-wrapper pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div0, file, 40, 4, 1234);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "class", "module-page__divider pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div1, file, 43, 4, 1331);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(h4, file, 44, 4, 1382);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div2, "class", "module-page__module-details-grid pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div2, file, 62, 6, 2068);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div3, "class", "module-page__project-data");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div3, file, 45, 4, 1417);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div4, "class", "module-page__sidebar pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div4, file, 38, 2, 1124);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(h2, "class", "module-page__h2");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(h2, file, 103, 4, 3472);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p, "class", "module-page__author");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p, file, 104, 4, 3525);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div5, "class", "module-page__project-description");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div5, "id", "description-wrapper");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div5, file, 112, 4, 3787);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div6, "class", "module-page__main pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div6, file, 102, 2, 3436);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div7, "class", "module-page__wrapper pb-26jj6j");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div7, file, 37, 0, 1087);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, a, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(a, span);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(a, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(a, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t3, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div7, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div7, div4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(image, div4, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, div0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(actionbutton, div0, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t5);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, div1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t7);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, h4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t9);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, div3);
			if (if_block0) if_block0.m(div3, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, t10);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, div2);
			if (if_block1) if_block1.m(div2, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t11);
			if (if_block2) if_block2.m(div2, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t12);
			if (if_block3) if_block3.m(div2, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div7, t13);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div7, div6);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div6, h2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(h2, t14);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div6, t15);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div6, p);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p, t16);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p, t17);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div6, t18);
			if (if_block4) if_block4.m(div6, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div6, t19);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div6, div5);
			div5.innerHTML = raw_value;
			current = true;
		},
		p: function update(ctx, [dirty]) {
			const image_changes = {};
			if (dirty & /*project*/ 1) image_changes.sources = /*project*/ ctx[0].logo;
			image.$set(image_changes);
			const actionbutton_changes = {};
			if (dirty & /*project*/ 1) actionbutton_changes.project = /*project*/ ctx[0];
			actionbutton.$set(actionbutton_changes);

			if (/*project*/ ctx[0].module_categories.length) {
				if (if_block0) {
					if_block0.p(ctx, dirty);
				} else {
					if_block0 = create_if_block_4(ctx);
					if_block0.c();
					if_block0.m(div3, t10);
				}
			} else if (if_block0) {
				if_block0.d(1);
				if_block0 = null;
			}

			if (/*project*/ ctx[0].is_compatible) {
				if (if_block1) {
					if_block1.p(ctx, dirty);

					if (dirty & /*project*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block1, 1);
					}
				} else {
					if_block1 = create_if_block_3(ctx);
					if_block1.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block1, 1);
					if_block1.m(div2, t11);
				}
			} else if (if_block1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block1, 1, 1, () => {
					if_block1 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (/*project*/ ctx[0].project_usage_total !== -1) {
				if (if_block2) {
					if_block2.p(ctx, dirty);

					if (dirty & /*project*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block2, 1);
					}
				} else {
					if_block2 = create_if_block_2(ctx);
					if_block2.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block2, 1);
					if_block2.m(div2, t12);
				}
			} else if (if_block2) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block2, 1, 1, () => {
					if_block2 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (/*project*/ ctx[0].is_covered) {
				if (if_block3) {
					if_block3.p(ctx, dirty);

					if (dirty & /*project*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3, 1);
					}
				} else {
					if_block3 = create_if_block_1(ctx);
					if_block3.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3, 1);
					if_block3.m(div2, null);
				}
			} else if (if_block3) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block3, 1, 1, () => {
					if_block3 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if ((!current || dirty & /*project*/ 1) && t14_value !== (t14_value = /*project*/ ctx[0].title + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t14, t14_value);
			if ((!current || dirty & /*project*/ 1) && t17_value !== (t17_value = /*project*/ ctx[0].author.name + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t17, t17_value);

			if (/*project*/ ctx[0].project_images.length) {
				if (if_block4) {
					if_block4.p(ctx, dirty);

					if (dirty & /*project*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block4, 1);
					}
				} else {
					if_block4 = create_if_block(ctx);
					if_block4.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block4, 1);
					if_block4.m(div6, t19);
				}
			} else if (if_block4) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block4, 1, 1, () => {
					if_block4 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if ((!current || dirty & /*project*/ 1) && raw_value !== (raw_value = /*project*/ ctx[0].body.value + "")) div5.innerHTML = raw_value;;
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(image.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(actionbutton.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block4);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(image.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(actionbutton.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block4);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(a);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t3);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div7);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(image);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(actionbutton);
			if (if_block0) if_block0.d();
			if (if_block1) if_block1.d();
			if (if_block2) if_block2.d();
			if (if_block3) if_block3.d();
			if (if_block4) if_block4.d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let $page;
	let $moduleCategoryFilter;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.page, 'page');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.page, $$value => $$invalidate(4, $page = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter, 'moduleCategoryFilter');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter, $$value => $$invalidate(5, $moduleCategoryFilter = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ModulePage', slots, []);
	let { project } = $$props;
	const { Drupal } = window;

	function filterByCategory(id) {
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter, $moduleCategoryFilter = [id], $moduleCategoryFilter);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.page, $page = 0, $page);
		window.location.href = `${_constants__WEBPACK_IMPORTED_MODULE_5__.ORIGIN_URL}/admin/modules/browse`;
	}

	(0,svelte__WEBPACK_IMPORTED_MODULE_1__.onMount)(() => {
		const anchors = document.getElementById('description-wrapper').getElementsByTagName('a');

		for (let i = 0; i < anchors.length; i++) {
			anchors[i].setAttribute('target', '_blank');
		}
	});

	$$self.$$.on_mount.push(function () {
		if (project === undefined && !('project' in $$props || $$self.$$.bound[$$self.$$.props['project']])) {
			console.warn("<ModulePage> was created without expected prop 'project'");
		}
	});

	const writable_props = ['project'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ModulePage> was created with unknown prop '${key}'`);
	});

	const click_handler = category => filterByCategory(category.id);

	$$self.$$set = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
	};

	$$self.$capture_state = () => ({
		onMount: svelte__WEBPACK_IMPORTED_MODULE_1__.onMount,
		ActionButton: _Project_ActionButton_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		Image: _Project_Image_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		ImageCarousel: _ImageCarousel_svelte__WEBPACK_IMPORTED_MODULE_4__["default"],
		ORIGIN_URL: _constants__WEBPACK_IMPORTED_MODULE_5__.ORIGIN_URL,
		moduleCategoryFilter: _stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter,
		page: _stores__WEBPACK_IMPORTED_MODULE_6__.page,
		ProjectIcon: _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_7__["default"],
		project,
		Drupal,
		filterByCategory,
		$page,
		$moduleCategoryFilter
	});

	$$self.$inject_state = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [project, Drupal, filterByCategory, click_handler];
}

class ModulePage extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { project: 0 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ModulePage",
			options,
			id: create_fragment.name
		});
	}

	get project() {
		throw new Error("<ModulePage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set project(value) {
		throw new Error("<ModulePage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ModulePage);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/PagerItem.svelte":
/*!***************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/PagerItem.svelte ***!
  \***************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/PagerItem.svelte generated by Svelte v3.56.0 */



const file = "modules/project_browser/sveltejs/src/PagerItem.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-12h33vt", ".pager__link--forward.pb-12h33vt::after{margin-inline-start:0.5rem}.pager__link--backward.pb-12h33vt::before{margin-inline-end:0.5rem}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUGFnZXJJdGVtLnN2ZWx0ZSIsInNvdXJjZXMiOlsiUGFnZXJJdGVtLnN2ZWx0ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8c2NyaXB0PlxuICBpbXBvcnQgeyBjcmVhdGVFdmVudERpc3BhdGNoZXIsIGdldENvbnRleHQgfSBmcm9tICdzdmVsdGUnO1xuXG4gIGNvbnN0IGRpc3BhdGNoID0gY3JlYXRlRXZlbnREaXNwYXRjaGVyKCk7XG4gIGNvbnN0IHN0YXRlQ29udGV4dCA9IGdldENvbnRleHQoJ3N0YXRlJyk7XG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG5cbiAgZXhwb3J0IGxldCBpdGVtVHlwZXMgPSBbXTtcbiAgZXhwb3J0IGxldCBsaW5rVHlwZXMgPSBbXTtcbiAgZXhwb3J0IGxldCBsYWJlbCA9ICcnO1xuICBleHBvcnQgbGV0IHRvUGFnZSA9IDA7XG4gIGV4cG9ydCBsZXQgYXJpYUxhYmVsID0gbnVsbDtcbiAgZXhwb3J0IGxldCBpc0N1cnJlbnQgPSBmYWxzZTtcblxuICBmdW5jdGlvbiBvbkNoYW5nZShldmVudCwgc2VsZWN0ZWRQYWdlKSB7XG4gICAgY29uc3Qgc3RhdGUgPSBzdGF0ZUNvbnRleHQuZ2V0U3RhdGUoKTtcbiAgICBjb25zdCBkZXRhaWwgPSB7XG4gICAgICBvcmlnaW5hbEV2ZW50OiBldmVudCxcbiAgICAgIHBhZ2U6IHNlbGVjdGVkUGFnZSxcbiAgICAgIHBhZ2VJbmRleDogMCxcbiAgICAgIHBhZ2VTaXplOiBzdGF0ZS5wYWdlU2l6ZSxcbiAgICB9O1xuICAgIGRpc3BhdGNoKCdwYWdlQ2hhbmdlJywgZGV0YWlsKTtcbiAgICBpZiAoZGV0YWlsLnByZXZlbnREZWZhdWx0ICE9PSB0cnVlKSB7XG4gICAgICBzdGF0ZUNvbnRleHQuc2V0UGFnZShkZXRhaWwucGFnZSwgZGV0YWlsLnBhZ2VJbmRleCk7XG4gICAgfVxuICB9XG48L3NjcmlwdD5cblxuPGxpXG4gIGNsYXNzPXtgcGFnZXJfX2l0ZW0gJHtpdGVtVHlwZXNcbiAgICAubWFwKChpdGVtKSA9PiBgcGFnZXJfX2l0ZW0tLSR7aXRlbX1gKVxuICAgIC5qb2luKCcgJyl9YH1cbiAgY2xhc3M6cGFnZXJfX2l0ZW0tLWFjdGl2ZT17aXNDdXJyZW50fVxuPlxuICA8YVxuICAgIGhyZWY9eycjJ31cbiAgICBjbGFzcz17YHBhZ2VyX19saW5rICR7bGlua1R5cGVzXG4gICAgICAubWFwKChpdGVtKSA9PiBgcGFnZXJfX2xpbmstLSR7aXRlbX1gKVxuICAgICAgLmpvaW4oJyAnKX1gfVxuICAgIGNsYXNzOmlzLWFjdGl2ZT17aXNDdXJyZW50fVxuICAgIGFyaWEtbGFiZWw9e2FyaWFMYWJlbCB8fCBEcnVwYWwudCgnQGxvY2F0aW9uIHBhZ2UnLCB7ICdAbG9jYXRpb24nOiBsYWJlbCB9KX1cbiAgICBvbjpjbGljaz17KGUpID0+IG9uQ2hhbmdlKGUsIHRvUGFnZSl9XG4gICAgYXJpYS1jdXJyZW50PXtpc0N1cnJlbnQgPyAncGFnZScgOiBudWxsfVxuICA+XG4gICAge2xhYmVsfVxuICA8L2E+XG48L2xpPlxuXG48c3R5bGU+XG4gIC5wYWdlcl9fbGluay0tZm9yd2FyZDo6YWZ0ZXIge1xuICAgIG1hcmdpbi1pbmxpbmUtc3RhcnQ6IDAuNXJlbTtcbiAgfVxuICAucGFnZXJfX2xpbmstLWJhY2t3YXJkOjpiZWZvcmUge1xuICAgIG1hcmdpbi1pbmxpbmUtZW5kOiAwLjVyZW07XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBa0RFLGdDQUFxQixPQUFRLENBQzNCLG1CQUFtQixDQUFFLE1BQ3ZCLENBQ0EsaUNBQXNCLFFBQVMsQ0FDN0IsaUJBQWlCLENBQUUsTUFDckIifQ== */");
}

function create_fragment(ctx) {
	let li;
	let a;
	let t;
	let a_href_value;
	let a_class_value;
	let a_aria_label_value;
	let a_aria_current_value;
	let li_class_value;
	let mounted;
	let dispose;

	const block = {
		c: function create() {
			li = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("li");
			a = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("a");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(/*label*/ ctx[2]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "href", a_href_value = '#');
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "class", a_class_value = "" + ((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.null_to_empty)(`pager__link ${/*linkTypes*/ ctx[1].map(func).join(' ')}`) + " pb-12h33vt"));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "aria-label", a_aria_label_value = /*ariaLabel*/ ctx[4] || /*Drupal*/ ctx[6].t('@location page', { '@location': /*label*/ ctx[2] }));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "aria-current", a_aria_current_value = /*isCurrent*/ ctx[5] ? 'page' : null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(a, "is-active", /*isCurrent*/ ctx[5]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(a, file, 35, 2, 883);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", li_class_value = "" + ((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.null_to_empty)(`pager__item ${/*itemTypes*/ ctx[0].map(func_1).join(' ')}`) + " pb-12h33vt"));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(li, "pager__item--active", /*isCurrent*/ ctx[5]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(li, file, 29, 0, 740);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, li, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, a);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(a, t);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(a, "click", /*click_handler*/ ctx[8], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, [dirty]) {
			if (dirty & /*label*/ 4) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, /*label*/ ctx[2]);

			if (dirty & /*linkTypes*/ 2 && a_class_value !== (a_class_value = "" + ((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.null_to_empty)(`pager__link ${/*linkTypes*/ ctx[1].map(func).join(' ')}`) + " pb-12h33vt"))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "class", a_class_value);
			}

			if (dirty & /*ariaLabel, label*/ 20 && a_aria_label_value !== (a_aria_label_value = /*ariaLabel*/ ctx[4] || /*Drupal*/ ctx[6].t('@location page', { '@location': /*label*/ ctx[2] }))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "aria-label", a_aria_label_value);
			}

			if (dirty & /*isCurrent*/ 32 && a_aria_current_value !== (a_aria_current_value = /*isCurrent*/ ctx[5] ? 'page' : null)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "aria-current", a_aria_current_value);
			}

			if (dirty & /*linkTypes, isCurrent*/ 34) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(a, "is-active", /*isCurrent*/ ctx[5]);
			}

			if (dirty & /*itemTypes*/ 1 && li_class_value !== (li_class_value = "" + ((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.null_to_empty)(`pager__item ${/*itemTypes*/ ctx[0].map(func_1).join(' ')}`) + " pb-12h33vt"))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", li_class_value);
			}

			if (dirty & /*itemTypes, isCurrent*/ 33) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(li, "pager__item--active", /*isCurrent*/ ctx[5]);
			}
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(li);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

const func = item => `pager__link--${item}`;
const func_1 = item => `pager__item--${item}`;

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('PagerItem', slots, []);
	const dispatch = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher)();
	const stateContext = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.getContext)('state');
	const { Drupal } = window;
	let { itemTypes = [] } = $$props;
	let { linkTypes = [] } = $$props;
	let { label = '' } = $$props;
	let { toPage = 0 } = $$props;
	let { ariaLabel = null } = $$props;
	let { isCurrent = false } = $$props;

	function onChange(event, selectedPage) {
		const state = stateContext.getState();

		const detail = {
			originalEvent: event,
			page: selectedPage,
			pageIndex: 0,
			pageSize: state.pageSize
		};

		dispatch('pageChange', detail);

		if (detail.preventDefault !== true) {
			stateContext.setPage(detail.page, detail.pageIndex);
		}
	}

	const writable_props = ['itemTypes', 'linkTypes', 'label', 'toPage', 'ariaLabel', 'isCurrent'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<PagerItem> was created with unknown prop '${key}'`);
	});

	const click_handler = e => onChange(e, toPage);

	$$self.$$set = $$props => {
		if ('itemTypes' in $$props) $$invalidate(0, itemTypes = $$props.itemTypes);
		if ('linkTypes' in $$props) $$invalidate(1, linkTypes = $$props.linkTypes);
		if ('label' in $$props) $$invalidate(2, label = $$props.label);
		if ('toPage' in $$props) $$invalidate(3, toPage = $$props.toPage);
		if ('ariaLabel' in $$props) $$invalidate(4, ariaLabel = $$props.ariaLabel);
		if ('isCurrent' in $$props) $$invalidate(5, isCurrent = $$props.isCurrent);
	};

	$$self.$capture_state = () => ({
		createEventDispatcher: svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher,
		getContext: svelte__WEBPACK_IMPORTED_MODULE_1__.getContext,
		dispatch,
		stateContext,
		Drupal,
		itemTypes,
		linkTypes,
		label,
		toPage,
		ariaLabel,
		isCurrent,
		onChange
	});

	$$self.$inject_state = $$props => {
		if ('itemTypes' in $$props) $$invalidate(0, itemTypes = $$props.itemTypes);
		if ('linkTypes' in $$props) $$invalidate(1, linkTypes = $$props.linkTypes);
		if ('label' in $$props) $$invalidate(2, label = $$props.label);
		if ('toPage' in $$props) $$invalidate(3, toPage = $$props.toPage);
		if ('ariaLabel' in $$props) $$invalidate(4, ariaLabel = $$props.ariaLabel);
		if ('isCurrent' in $$props) $$invalidate(5, isCurrent = $$props.isCurrent);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [
		itemTypes,
		linkTypes,
		label,
		toPage,
		ariaLabel,
		isCurrent,
		Drupal,
		onChange,
		click_handler
	];
}

class PagerItem extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(
			this,
			options,
			instance,
			create_fragment,
			svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal,
			{
				itemTypes: 0,
				linkTypes: 1,
				label: 2,
				toPage: 3,
				ariaLabel: 4,
				isCurrent: 5
			},
			add_css
		);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "PagerItem",
			options,
			id: create_fragment.name
		});
	}

	get itemTypes() {
		throw new Error("<PagerItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set itemTypes(value) {
		throw new Error("<PagerItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get linkTypes() {
		throw new Error("<PagerItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set linkTypes(value) {
		throw new Error("<PagerItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get label() {
		throw new Error("<PagerItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set label(value) {
		throw new Error("<PagerItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get toPage() {
		throw new Error("<PagerItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set toPage(value) {
		throw new Error("<PagerItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get ariaLabel() {
		throw new Error("<PagerItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set ariaLabel(value) {
		throw new Error("<PagerItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get isCurrent() {
		throw new Error("<PagerItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set isCurrent(value) {
		throw new Error("<PagerItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PagerItem);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Pagination.svelte":
/*!****************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Pagination.svelte ***!
  \****************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _PagerItem_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./PagerItem.svelte */ "./modules/project_browser/sveltejs/src/PagerItem.svelte");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Pagination.svelte generated by Svelte v3.56.0 */





const file = "modules/project_browser/sveltejs/src/Pagination.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-tvylpu", ".pagination__pager.pb-tvylpu{display:flex;align-items:center;justify-content:center}.pagination__num-projects.pb-tvylpu{margin:0 10px}@media only screen and (max-width: 32rem){.pagination__pager.pb-tvylpu{flex-direction:column}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUGFnaW5hdGlvbi5zdmVsdGUiLCJzb3VyY2VzIjpbIlBhZ2luYXRpb24uc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIGltcG9ydCB7IGNyZWF0ZUV2ZW50RGlzcGF0Y2hlciB9IGZyb20gJ3N2ZWx0ZSc7XG4gIGltcG9ydCBQYWdlckl0ZW0gZnJvbSAnLi9QYWdlckl0ZW0uc3ZlbHRlJztcbiAgaW1wb3J0IHsgcGFnZVNpemUgfSBmcm9tICcuL3N0b3Jlcyc7XG5cbiAgY29uc3QgZGlzcGF0Y2ggPSBjcmVhdGVFdmVudERpc3BhdGNoZXIoKTtcblxuICBmdW5jdGlvbiBwYWdlU2l6ZUNoYW5nZSgpIHtcbiAgICBkaXNwYXRjaCgncGFnZVNpemVDaGFuZ2UnKTtcbiAgfVxuXG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG5cbiAgZXhwb3J0IGxldCBidXR0b25zID0gWy00LCAtMywgLTIsIC0xLCAwLCAxLCAyLCAzLCA0XTtcbiAgZXhwb3J0IGxldCBjb3VudDtcbiAgZXhwb3J0IGxldCBwYWdlID0gMDtcblxuICBleHBvcnQgbGV0IGxhYmVscyA9IHtcbiAgICBmaXJzdDogRHJ1cGFsLnQoJ0ZpcnN0JyksXG4gICAgbGFzdDogRHJ1cGFsLnQoJ0xhc3QnKSxcbiAgICBuZXh0OiBEcnVwYWwudCgnTmV4dCcpLFxuICAgIHByZXZpb3VzOiBEcnVwYWwudCgnUHJldmlvdXMnKSxcbiAgfTtcblxuICAkOiBwYWdlQ291bnQgPSBNYXRoLmNlaWwoY291bnQgLyAkcGFnZVNpemUpIC0gMTtcbiAgY29uc3Qgb3B0aW9ucyA9IFtcbiAgICB7IGlkOiAxMiwgdmFsdWU6IDEyIH0sXG4gICAgeyBpZDogMjQsIHZhbHVlOiAyNCB9LFxuICAgIHsgaWQ6IDM2LCB2YWx1ZTogMzYgfSxcbiAgICB7IGlkOiA0OCwgdmFsdWU6IDQ4IH0sXG4gIF07XG48L3NjcmlwdD5cblxuPCEtLSBzdmVsdGUtaWdub3JlIGExMXktbm8tcmVkdW5kYW50LXJvbGVzIC0tPlxueyNpZiBwYWdlQ291bnQgPiAwfVxuICA8bmF2XG4gICAgY2xhc3M9XCJwYWdpbmF0aW9uX19wYWdlclwiXG4gICAgYXJpYS1sYWJlbD17RHJ1cGFsLnQoJ1Byb2plY3QgQnJvd3NlciBQYWdpbmF0aW9uJyl9XG4gICAgcm9sZT1cIm5hdmlnYXRpb25cIlxuICA+XG4gICAgPGxhYmVsIGZvcj1cIm51bS1wcm9qZWN0c1wiPlxuICAgICAge0RydXBhbC50KCdTaG93IHByb2plY3RzJyl9XG4gICAgPC9sYWJlbD5cbiAgICA8c2VsZWN0XG4gICAgICBjbGFzcz1cInBhZ2luYXRpb25fX251bS1wcm9qZWN0c1wiXG4gICAgICBpZD1cIm51bS1wcm9qZWN0c1wiXG4gICAgICBuYW1lPVwibnVtLXByb2plY3RzXCJcbiAgICAgIGJpbmQ6dmFsdWU9eyRwYWdlU2l6ZX1cbiAgICAgIG9uOmNoYW5nZT17KCkgPT4ge1xuICAgICAgICBwYWdlU2l6ZUNoYW5nZSgpO1xuICAgICAgfX1cbiAgICA+XG4gICAgICB7I2VhY2ggb3B0aW9ucyBhcyBvcHRpb259XG4gICAgICAgIDxvcHRpb24gdmFsdWU9e29wdGlvbi5pZH0+e29wdGlvbi52YWx1ZX08L29wdGlvbj5cbiAgICAgIHsvZWFjaH1cbiAgICA8L3NlbGVjdD5cbiAgICA8dWwgY2xhc3M9XCJwYWdpbmF0aW9uX19wYWdlci1pdGVtcyBwYWdlcl9faXRlbXMganMtcGFnZXJfX2l0ZW1zXCI+XG4gICAgICB7I2lmIHBhZ2UgIT09IDB9XG4gICAgICAgIDxQYWdlckl0ZW1cbiAgICAgICAgICBvbjpwYWdlQ2hhbmdlXG4gICAgICAgICAgaXRlbVR5cGVzPXtbJ2FjdGlvbicsICdmaXJzdCddfVxuICAgICAgICAgIGxpbmtUeXBlcz17WydhY3Rpb24tbGluaycsICdiYWNrd2FyZCddfVxuICAgICAgICAgIGxhYmVsPXtsYWJlbHMuZmlyc3R9XG4gICAgICAgICAgdG9QYWdlPXswfVxuICAgICAgICAvPlxuICAgICAgICA8UGFnZXJJdGVtXG4gICAgICAgICAgb246cGFnZUNoYW5nZVxuICAgICAgICAgIGl0ZW1UeXBlcz17WydhY3Rpb24nLCAncHJldmlvdXMnXX1cbiAgICAgICAgICBsaW5rVHlwZXM9e1snYWN0aW9uLWxpbmsnLCAnYmFja3dhcmQnXX1cbiAgICAgICAgICBsYWJlbD17bGFiZWxzLnByZXZpb3VzfVxuICAgICAgICAgIHRvUGFnZT17cGFnZSAtIDF9XG4gICAgICAgIC8+XG4gICAgICB7L2lmfVxuICAgICAgeyNpZiBwYWdlID49IDV9XG4gICAgICAgIDxsaSBjbGFzcz1cInBhZ2VyX19pdGVtIHBhZ2VyX19pdGVtLS1lbGxpcHNpc1wiIHJvbGU9XCJwcmVzZW50YXRpb25cIj5cbiAgICAgICAgICAmaGVsbGlwO1xuICAgICAgICA8L2xpPlxuICAgICAgey9pZn1cbiAgICAgIHsjZWFjaCBidXR0b25zIGFzIGJ1dHRvbn1cbiAgICAgICAgeyNpZiBwYWdlICsgYnV0dG9uID49IDAgJiYgcGFnZSArIGJ1dHRvbiA8PSBwYWdlQ291bnR9XG4gICAgICAgICAgPFBhZ2VySXRlbVxuICAgICAgICAgICAgb246cGFnZUNoYW5nZVxuICAgICAgICAgICAgaXRlbVR5cGVzPXtbJ251bWJlciddfVxuICAgICAgICAgICAgaXNDdXJyZW50PXtidXR0b24gPT09IDAgPyAncGFnZScgOiBudWxsfVxuICAgICAgICAgICAgbGFiZWw9e3BhZ2UgKyBidXR0b24gKyAxfVxuICAgICAgICAgICAgdG9QYWdlPXtwYWdlICsgYnV0dG9ufVxuICAgICAgICAgICAgYXJpYUxhYmVsPXtEcnVwYWwudCgnUGFnZSBAcGFnZV9udW1iZXInLCB7XG4gICAgICAgICAgICAgICdAcGFnZV9udW1iZXInOiBwYWdlICsgYnV0dG9uICsgMSxcbiAgICAgICAgICAgIH0pfVxuICAgICAgICAgIC8+XG4gICAgICAgIHsvaWZ9XG4gICAgICB7L2VhY2h9XG4gICAgICB7I2lmIHBhZ2UgKyA1IDw9IHBhZ2VDb3VudH1cbiAgICAgICAgPGxpIGNsYXNzPVwicGFnZXJfX2l0ZW0gcGFnZXJfX2l0ZW0tLWVsbGlwc2lzXCIgcm9sZT1cInByZXNlbnRhdGlvblwiPlxuICAgICAgICAgICZoZWxsaXA7XG4gICAgICAgIDwvbGk+XG4gICAgICB7L2lmfVxuICAgICAgeyNpZiBwYWdlICE9PSBwYWdlQ291bnR9XG4gICAgICAgIDxQYWdlckl0ZW1cbiAgICAgICAgICBvbjpwYWdlQ2hhbmdlXG4gICAgICAgICAgaXRlbVR5cGVzPXtbJ2FjdGlvbicsICduZXh0J119XG4gICAgICAgICAgbGlua1R5cGVzPXtbJ2FjdGlvbi1saW5rJywgJ2ZvcndhcmQnXX1cbiAgICAgICAgICBsYWJlbD17bGFiZWxzLm5leHR9XG4gICAgICAgICAgdG9QYWdlPXtwYWdlICsgMX1cbiAgICAgICAgLz5cbiAgICAgICAgPFBhZ2VySXRlbVxuICAgICAgICAgIG9uOnBhZ2VDaGFuZ2VcbiAgICAgICAgICBpdGVtVHlwZXM9e1snYWN0aW9uJywgJ2xhc3QnXX1cbiAgICAgICAgICBsaW5rVHlwZXM9e1snYWN0aW9uLWxpbmsnLCAnZm9yd2FyZCddfVxuICAgICAgICAgIGxhYmVsPXtsYWJlbHMubGFzdH1cbiAgICAgICAgICB0b1BhZ2U9e3BhZ2VDb3VudH1cbiAgICAgICAgLz5cbiAgICAgIHsvaWZ9XG4gICAgPC91bD5cbiAgPC9uYXY+XG57L2lmfVxuXG48c3R5bGU+XG4gIC5wYWdpbmF0aW9uX19wYWdlciB7XG4gICAgZGlzcGxheTogZmxleDtcbiAgICBhbGlnbi1pdGVtczogY2VudGVyO1xuICAgIGp1c3RpZnktY29udGVudDogY2VudGVyO1xuICB9XG4gIC5wYWdpbmF0aW9uX19udW0tcHJvamVjdHMge1xuICAgIG1hcmdpbjogMCAxMHB4O1xuICB9XG4gIEBtZWRpYSBvbmx5IHNjcmVlbiBhbmQgKG1heC13aWR0aDogMzJyZW0pIHtcbiAgICAucGFnaW5hdGlvbl9fcGFnZXIge1xuICAgICAgZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgICB9XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBc0hFLDRCQUFtQixDQUNqQixPQUFPLENBQUUsSUFBSSxDQUNiLFdBQVcsQ0FBRSxNQUFNLENBQ25CLGVBQWUsQ0FBRSxNQUNuQixDQUNBLG1DQUEwQixDQUN4QixNQUFNLENBQUUsQ0FBQyxDQUFDLElBQ1osQ0FDQSxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLFlBQVksS0FBSyxDQUFFLENBQ3hDLDRCQUFtQixDQUNqQixjQUFjLENBQUUsTUFDbEIsQ0FDRiJ9 */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[17] = list[i];
	return child_ctx;
}

function get_each_context_1(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[20] = list[i];
	return child_ctx;
}

// (35:0) {#if pageCount > 0}
function create_if_block(ctx) {
	let nav;
	let label;
	let t1;
	let select;
	let t2;
	let ul;
	let t3;
	let t4;
	let t5;
	let t6;
	let nav_aria_label_value;
	let current;
	let mounted;
	let dispose;
	let each_value_1 = /*options*/ ctx[7];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value_1);
	let each_blocks_1 = [];

	for (let i = 0; i < each_value_1.length; i += 1) {
		each_blocks_1[i] = create_each_block_1(get_each_context_1(ctx, each_value_1, i));
	}

	let if_block0 = /*page*/ ctx[1] !== 0 && create_if_block_5(ctx);
	let if_block1 = /*page*/ ctx[1] >= 5 && create_if_block_4(ctx);
	let each_value = /*buttons*/ ctx[0];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const out = i => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks[i], 1, 1, () => {
		each_blocks[i] = null;
	});

	let if_block2 = /*page*/ ctx[1] + 5 <= /*pageCount*/ ctx[4] && create_if_block_2(ctx);
	let if_block3 = /*page*/ ctx[1] !== /*pageCount*/ ctx[4] && create_if_block_1(ctx);

	const block = {
		c: function create() {
			nav = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("nav");
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			label.textContent = `${/*Drupal*/ ctx[6].t('Show projects')}`;
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			select = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("select");

			for (let i = 0; i < each_blocks_1.length; i += 1) {
				each_blocks_1[i].c();
			}

			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			ul = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("ul");
			if (if_block0) if_block0.c();
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block1) if_block1.c();
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();

			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			t5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block2) if_block2.c();
			t6 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block3) if_block3.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", "num-projects");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 40, 4, 924);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(select, "class", "pagination__num-projects pb-tvylpu");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(select, "id", "num-projects");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(select, "name", "num-projects");
			if (/*$pageSize*/ ctx[3] === void 0) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_render_callback)(() => /*select_change_handler*/ ctx[9].call(select));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(select, file, 43, 4, 1002);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(ul, "class", "pagination__pager-items pager__items js-pager__items");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(ul, file, 56, 4, 1316);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(nav, "class", "pagination__pager pb-tvylpu");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(nav, "aria-label", nav_aria_label_value = /*Drupal*/ ctx[6].t('Project Browser Pagination'));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(nav, "role", "navigation");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(nav, file, 35, 2, 803);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, nav, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(nav, label);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(nav, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(nav, select);

			for (let i = 0; i < each_blocks_1.length; i += 1) {
				if (each_blocks_1[i]) {
					each_blocks_1[i].m(select, null);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.select_option)(select, /*$pageSize*/ ctx[3]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(nav, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(nav, ul);
			if (if_block0) if_block0.m(ul, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(ul, t3);
			if (if_block1) if_block1.m(ul, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(ul, t4);

			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(ul, null);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(ul, t5);
			if (if_block2) if_block2.m(ul, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(ul, t6);
			if (if_block3) if_block3.m(ul, null);
			current = true;

			if (!mounted) {
				dispose = [
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(select, "change", /*select_change_handler*/ ctx[9]),
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(select, "change", /*change_handler*/ ctx[10], false, false, false, false)
				];

				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			if (dirty & /*options*/ 128) {
				each_value_1 = /*options*/ ctx[7];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value_1);
				let i;

				for (i = 0; i < each_value_1.length; i += 1) {
					const child_ctx = get_each_context_1(ctx, each_value_1, i);

					if (each_blocks_1[i]) {
						each_blocks_1[i].p(child_ctx, dirty);
					} else {
						each_blocks_1[i] = create_each_block_1(child_ctx);
						each_blocks_1[i].c();
						each_blocks_1[i].m(select, null);
					}
				}

				for (; i < each_blocks_1.length; i += 1) {
					each_blocks_1[i].d(1);
				}

				each_blocks_1.length = each_value_1.length;
			}

			if (dirty & /*$pageSize, options*/ 136) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.select_option)(select, /*$pageSize*/ ctx[3]);
			}

			if (/*page*/ ctx[1] !== 0) {
				if (if_block0) {
					if_block0.p(ctx, dirty);

					if (dirty & /*page*/ 2) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block0, 1);
					}
				} else {
					if_block0 = create_if_block_5(ctx);
					if_block0.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block0, 1);
					if_block0.m(ul, t3);
				}
			} else if (if_block0) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block0, 1, 1, () => {
					if_block0 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (/*page*/ ctx[1] >= 5) {
				if (if_block1) {
					
				} else {
					if_block1 = create_if_block_4(ctx);
					if_block1.c();
					if_block1.m(ul, t4);
				}
			} else if (if_block1) {
				if_block1.d(1);
				if_block1 = null;
			}

			if (dirty & /*buttons, page, Drupal, pageCount*/ 83) {
				each_value = /*buttons*/ ctx[0];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i], 1);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i], 1);
						each_blocks[i].m(ul, t5);
					}
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				for (i = each_value.length; i < each_blocks.length; i += 1) {
					out(i);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (/*page*/ ctx[1] + 5 <= /*pageCount*/ ctx[4]) {
				if (if_block2) {
					
				} else {
					if_block2 = create_if_block_2(ctx);
					if_block2.c();
					if_block2.m(ul, t6);
				}
			} else if (if_block2) {
				if_block2.d(1);
				if_block2 = null;
			}

			if (/*page*/ ctx[1] !== /*pageCount*/ ctx[4]) {
				if (if_block3) {
					if_block3.p(ctx, dirty);

					if (dirty & /*page, pageCount*/ 18) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3, 1);
					}
				} else {
					if_block3 = create_if_block_1(ctx);
					if_block3.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3, 1);
					if_block3.m(ul, null);
				}
			} else if (if_block3) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block3, 1, 1, () => {
					if_block3 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block0);

			for (let i = 0; i < each_value.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i]);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block0);
			each_blocks = each_blocks.filter(Boolean);

			for (let i = 0; i < each_blocks.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks[i]);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block3);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(nav);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks_1, detaching);
			if (if_block0) if_block0.d();
			if (if_block1) if_block1.d();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
			if (if_block2) if_block2.d();
			if (if_block3) if_block3.d();
			mounted = false;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.run_all)(dispose);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(35:0) {#if pageCount > 0}",
		ctx
	});

	return block;
}

// (53:6) {#each options as option}
function create_each_block_1(ctx) {
	let option;
	let t_value = /*option*/ ctx[20].value + "";
	let t;
	let option_value_value;

	const block = {
		c: function create() {
			option = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("option");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			option.__value = option_value_value = /*option*/ ctx[20].id;
			option.value = option.__value;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(option, file, 53, 8, 1234);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, option, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(option, t);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(option);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block_1.name,
		type: "each",
		source: "(53:6) {#each options as option}",
		ctx
	});

	return block;
}

// (58:6) {#if page !== 0}
function create_if_block_5(ctx) {
	let pageritem0;
	let t;
	let pageritem1;
	let current;

	pageritem0 = new _PagerItem_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				itemTypes: ['action', 'first'],
				linkTypes: ['action-link', 'backward'],
				label: /*labels*/ ctx[2].first,
				toPage: 0
			},
			$$inline: true
		});

	pageritem0.$on("pageChange", /*pageChange_handler*/ ctx[11]);

	pageritem1 = new _PagerItem_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				itemTypes: ['action', 'previous'],
				linkTypes: ['action-link', 'backward'],
				label: /*labels*/ ctx[2].previous,
				toPage: /*page*/ ctx[1] - 1
			},
			$$inline: true
		});

	pageritem1.$on("pageChange", /*pageChange_handler_1*/ ctx[12]);

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(pageritem0.$$.fragment);
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(pageritem1.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(pageritem0, target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(pageritem1, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const pageritem0_changes = {};
			if (dirty & /*labels*/ 4) pageritem0_changes.label = /*labels*/ ctx[2].first;
			pageritem0.$set(pageritem0_changes);
			const pageritem1_changes = {};
			if (dirty & /*labels*/ 4) pageritem1_changes.label = /*labels*/ ctx[2].previous;
			if (dirty & /*page*/ 2) pageritem1_changes.toPage = /*page*/ ctx[1] - 1;
			pageritem1.$set(pageritem1_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(pageritem0.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(pageritem1.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(pageritem0.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(pageritem1.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(pageritem0, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(pageritem1, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_5.name,
		type: "if",
		source: "(58:6) {#if page !== 0}",
		ctx
	});

	return block;
}

// (74:6) {#if page >= 5}
function create_if_block_4(ctx) {
	let li;

	const block = {
		c: function create() {
			li = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("li");
			li.textContent = "…";
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", "pager__item pager__item--ellipsis");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "role", "presentation");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(li, file, 74, 8, 1856);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, li, anchor);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(li);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_4.name,
		type: "if",
		source: "(74:6) {#if page >= 5}",
		ctx
	});

	return block;
}

// (80:8) {#if page + button >= 0 && page + button <= pageCount}
function create_if_block_3(ctx) {
	let pageritem;
	let current;

	pageritem = new _PagerItem_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				itemTypes: ['number'],
				isCurrent: /*button*/ ctx[17] === 0 ? 'page' : null,
				label: /*page*/ ctx[1] + /*button*/ ctx[17] + 1,
				toPage: /*page*/ ctx[1] + /*button*/ ctx[17],
				ariaLabel: /*Drupal*/ ctx[6].t('Page @page_number', {
					'@page_number': /*page*/ ctx[1] + /*button*/ ctx[17] + 1
				})
			},
			$$inline: true
		});

	pageritem.$on("pageChange", /*pageChange_handler_2*/ ctx[13]);

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(pageritem.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(pageritem, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const pageritem_changes = {};
			if (dirty & /*buttons*/ 1) pageritem_changes.isCurrent = /*button*/ ctx[17] === 0 ? 'page' : null;
			if (dirty & /*page, buttons*/ 3) pageritem_changes.label = /*page*/ ctx[1] + /*button*/ ctx[17] + 1;
			if (dirty & /*page, buttons*/ 3) pageritem_changes.toPage = /*page*/ ctx[1] + /*button*/ ctx[17];

			if (dirty & /*page, buttons*/ 3) pageritem_changes.ariaLabel = /*Drupal*/ ctx[6].t('Page @page_number', {
				'@page_number': /*page*/ ctx[1] + /*button*/ ctx[17] + 1
			});

			pageritem.$set(pageritem_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(pageritem.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(pageritem.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(pageritem, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_3.name,
		type: "if",
		source: "(80:8) {#if page + button >= 0 && page + button <= pageCount}",
		ctx
	});

	return block;
}

// (79:6) {#each buttons as button}
function create_each_block(ctx) {
	let if_block_anchor;
	let current;
	let if_block = /*page*/ ctx[1] + /*button*/ ctx[17] >= 0 && /*page*/ ctx[1] + /*button*/ ctx[17] <= /*pageCount*/ ctx[4] && create_if_block_3(ctx);

	const block = {
		c: function create() {
			if (if_block) if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			if (if_block) if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			if (/*page*/ ctx[1] + /*button*/ ctx[17] >= 0 && /*page*/ ctx[1] + /*button*/ ctx[17] <= /*pageCount*/ ctx[4]) {
				if (if_block) {
					if_block.p(ctx, dirty);

					if (dirty & /*page, buttons, pageCount*/ 19) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					}
				} else {
					if_block = create_if_block_3(ctx);
					if_block.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			} else if (if_block) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block, 1, 1, () => {
					if_block = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (if_block) if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(79:6) {#each buttons as button}",
		ctx
	});

	return block;
}

// (93:6) {#if page + 5 <= pageCount}
function create_if_block_2(ctx) {
	let li;

	const block = {
		c: function create() {
			li = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("li");
			li.textContent = "…";
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", "pager__item pager__item--ellipsis");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "role", "presentation");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(li, file, 93, 8, 2474);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, li, anchor);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(li);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(93:6) {#if page + 5 <= pageCount}",
		ctx
	});

	return block;
}

// (98:6) {#if page !== pageCount}
function create_if_block_1(ctx) {
	let pageritem0;
	let t;
	let pageritem1;
	let current;

	pageritem0 = new _PagerItem_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				itemTypes: ['action', 'next'],
				linkTypes: ['action-link', 'forward'],
				label: /*labels*/ ctx[2].next,
				toPage: /*page*/ ctx[1] + 1
			},
			$$inline: true
		});

	pageritem0.$on("pageChange", /*pageChange_handler_3*/ ctx[14]);

	pageritem1 = new _PagerItem_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				itemTypes: ['action', 'last'],
				linkTypes: ['action-link', 'forward'],
				label: /*labels*/ ctx[2].last,
				toPage: /*pageCount*/ ctx[4]
			},
			$$inline: true
		});

	pageritem1.$on("pageChange", /*pageChange_handler_4*/ ctx[15]);

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(pageritem0.$$.fragment);
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(pageritem1.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(pageritem0, target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(pageritem1, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const pageritem0_changes = {};
			if (dirty & /*labels*/ 4) pageritem0_changes.label = /*labels*/ ctx[2].next;
			if (dirty & /*page*/ 2) pageritem0_changes.toPage = /*page*/ ctx[1] + 1;
			pageritem0.$set(pageritem0_changes);
			const pageritem1_changes = {};
			if (dirty & /*labels*/ 4) pageritem1_changes.label = /*labels*/ ctx[2].last;
			if (dirty & /*pageCount*/ 16) pageritem1_changes.toPage = /*pageCount*/ ctx[4];
			pageritem1.$set(pageritem1_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(pageritem0.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(pageritem1.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(pageritem0.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(pageritem1.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(pageritem0, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(pageritem1, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(98:6) {#if page !== pageCount}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let if_block_anchor;
	let current;
	let if_block = /*pageCount*/ ctx[4] > 0 && create_if_block(ctx);

	const block = {
		c: function create() {
			if (if_block) if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			if (if_block) if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			if (/*pageCount*/ ctx[4] > 0) {
				if (if_block) {
					if_block.p(ctx, dirty);

					if (dirty & /*pageCount*/ 16) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					}
				} else {
					if_block = create_if_block(ctx);
					if_block.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			} else if (if_block) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block, 1, 1, () => {
					if_block = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (if_block) if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let pageCount;
	let $pageSize;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_3__.pageSize, 'pageSize');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_3__.pageSize, $$value => $$invalidate(3, $pageSize = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('Pagination', slots, []);
	const dispatch = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher)();

	function pageSizeChange() {
		dispatch('pageSizeChange');
	}

	const { Drupal } = window;
	let { buttons = [-4, -3, -2, -1, 0, 1, 2, 3, 4] } = $$props;
	let { count } = $$props;
	let { page = 0 } = $$props;

	let { labels = {
		first: Drupal.t('First'),
		last: Drupal.t('Last'),
		next: Drupal.t('Next'),
		previous: Drupal.t('Previous')
	} } = $$props;

	const options = [
		{ id: 12, value: 12 },
		{ id: 24, value: 24 },
		{ id: 36, value: 36 },
		{ id: 48, value: 48 }
	];

	$$self.$$.on_mount.push(function () {
		if (count === undefined && !('count' in $$props || $$self.$$.bound[$$self.$$.props['count']])) {
			console.warn("<Pagination> was created without expected prop 'count'");
		}
	});

	const writable_props = ['buttons', 'count', 'page', 'labels'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Pagination> was created with unknown prop '${key}'`);
	});

	function select_change_handler() {
		$pageSize = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.select_value)(this);
		_stores__WEBPACK_IMPORTED_MODULE_3__.pageSize.set($pageSize);
		$$invalidate(7, options);
	}

	const change_handler = () => {
		pageSizeChange();
	};

	function pageChange_handler(event) {
		svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bubble.call(this, $$self, event);
	}

	function pageChange_handler_1(event) {
		svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bubble.call(this, $$self, event);
	}

	function pageChange_handler_2(event) {
		svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bubble.call(this, $$self, event);
	}

	function pageChange_handler_3(event) {
		svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bubble.call(this, $$self, event);
	}

	function pageChange_handler_4(event) {
		svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bubble.call(this, $$self, event);
	}

	$$self.$$set = $$props => {
		if ('buttons' in $$props) $$invalidate(0, buttons = $$props.buttons);
		if ('count' in $$props) $$invalidate(8, count = $$props.count);
		if ('page' in $$props) $$invalidate(1, page = $$props.page);
		if ('labels' in $$props) $$invalidate(2, labels = $$props.labels);
	};

	$$self.$capture_state = () => ({
		createEventDispatcher: svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher,
		PagerItem: _PagerItem_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		pageSize: _stores__WEBPACK_IMPORTED_MODULE_3__.pageSize,
		dispatch,
		pageSizeChange,
		Drupal,
		buttons,
		count,
		page,
		labels,
		options,
		pageCount,
		$pageSize
	});

	$$self.$inject_state = $$props => {
		if ('buttons' in $$props) $$invalidate(0, buttons = $$props.buttons);
		if ('count' in $$props) $$invalidate(8, count = $$props.count);
		if ('page' in $$props) $$invalidate(1, page = $$props.page);
		if ('labels' in $$props) $$invalidate(2, labels = $$props.labels);
		if ('pageCount' in $$props) $$invalidate(4, pageCount = $$props.pageCount);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	$$self.$$.update = () => {
		if ($$self.$$.dirty & /*count, $pageSize*/ 264) {
			$: $$invalidate(4, pageCount = Math.ceil(count / $pageSize) - 1);
		}
	};

	return [
		buttons,
		page,
		labels,
		$pageSize,
		pageCount,
		pageSizeChange,
		Drupal,
		options,
		count,
		select_change_handler,
		change_handler,
		pageChange_handler,
		pageChange_handler_1,
		pageChange_handler_2,
		pageChange_handler_3,
		pageChange_handler_4
	];
}

class Pagination extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { buttons: 0, count: 8, page: 1, labels: 2 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "Pagination",
			options,
			id: create_fragment.name
		});
	}

	get buttons() {
		throw new Error("<Pagination>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set buttons(value) {
		throw new Error("<Pagination>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get count() {
		throw new Error("<Pagination>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set count(value) {
		throw new Error("<Pagination>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get page() {
		throw new Error("<Pagination>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set page(value) {
		throw new Error("<Pagination>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get labels() {
		throw new Error("<Pagination>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set labels(value) {
		throw new Error("<Pagination>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Pagination);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/ActionButton.svelte":
/*!**************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/ActionButton.svelte ***!
  \**************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Loading_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../Loading.svelte */ "./modules/project_browser/sveltejs/src/Loading.svelte");
/* harmony import */ var _popup__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../popup */ "./modules/project_browser/sveltejs/src/popup.js");
/* harmony import */ var _AddInstallButton_svelte__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./AddInstallButton.svelte */ "./modules/project_browser/sveltejs/src/Project/AddInstallButton.svelte");
/* harmony import */ var _LoadingEllipsis_svelte__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./LoadingEllipsis.svelte */ "./modules/project_browser/sveltejs/src/Project/LoadingEllipsis.svelte");
/* harmony import */ var _ProjectButtonBase_svelte__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./ProjectButtonBase.svelte */ "./modules/project_browser/sveltejs/src/Project/ProjectButtonBase.svelte");
/* harmony import */ var _ProjectStatusIndicator_svelte__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./ProjectStatusIndicator.svelte */ "./modules/project_browser/sveltejs/src/Project/ProjectStatusIndicator.svelte");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/ActionButton.svelte generated by Svelte v3.56.0 */












const file = "modules/project_browser/sveltejs/src/Project/ActionButton.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1freki0", ".action-button__wrapper.pb-1freki0{padding:0.5em 0;margin-inline-start:auto}.action-button__unicode.pb-1freki0{color:#228572}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiQWN0aW9uQnV0dG9uLnN2ZWx0ZSIsInNvdXJjZXMiOlsiQWN0aW9uQnV0dG9uLnN2ZWx0ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8c2NyaXB0PlxuICBpbXBvcnQgeyBvbk1vdW50IH0gZnJvbSAnc3ZlbHRlJztcbiAgaW1wb3J0IHtcbiAgICBNT0RVTEVfU1RBVFVTLFxuICAgIE9SSUdJTl9VUkwsXG4gICAgQUxMT1dfVUlfSU5TVEFMTCxcbiAgICBQTV9WQUxJREFUSU9OX0VSUk9SLFxuICB9IGZyb20gJy4uL2NvbnN0YW50cyc7XG4gIGltcG9ydCBMb2FkaW5nIGZyb20gJy4uL0xvYWRpbmcuc3ZlbHRlJztcbiAgaW1wb3J0IHsgb3BlblBvcHVwLCBnZXRDb21tYW5kc1BvcHVwTWVzc2FnZSB9IGZyb20gJy4uL3BvcHVwJztcbiAgaW1wb3J0IEFkZEluc3RhbGxCdXR0b24gZnJvbSAnLi9BZGRJbnN0YWxsQnV0dG9uLnN2ZWx0ZSc7XG4gIGltcG9ydCBMb2FkaW5nRWxsaXBzaXMgZnJvbSAnLi9Mb2FkaW5nRWxsaXBzaXMuc3ZlbHRlJztcbiAgaW1wb3J0IFByb2plY3RCdXR0b25CYXNlIGZyb20gJy4vUHJvamVjdEJ1dHRvbkJhc2Uuc3ZlbHRlJztcbiAgaW1wb3J0IFByb2plY3RTdGF0dXNJbmRpY2F0b3IgZnJvbSAnLi9Qcm9qZWN0U3RhdHVzSW5kaWNhdG9yLnN2ZWx0ZSc7XG5cbiAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIGltcG9ydC9uby1tdXRhYmxlLWV4cG9ydHMsaW1wb3J0L3ByZWZlci1kZWZhdWx0LWV4cG9ydFxuICBleHBvcnQgbGV0IHByb2plY3Q7XG4gIGxldCBsb2FkaW5nID0gZmFsc2U7XG4gIGxldCBsb2FkaW5nUGhhc2UgPSAnQWRkaW5nJztcblxuICBjb25zdCB7IGRydXBhbFNldHRpbmdzLCBEcnVwYWwgfSA9IHdpbmRvdztcblxuICAvKipcbiAgICogRGV0ZXJtaW5lIGlzIGEgcHJvamVjdCBpcyBwcmVzZW50IGluIHRoZSBsb2NhbCBEcnVwYWwgY29kZWJhc2UuXG4gICAqXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBwcm9qZWN0TmFtZVxuICAgKiAgICBUaGUgcHJvamVjdCBuYW1lLlxuICAgKiBAcmV0dXJuIHtib29sZWFufVxuICAgKiAgIFRydWUgaWYgdGhlIHByb2plY3QgaXMgcHJlc2VudC5cbiAgICovXG4gIGZ1bmN0aW9uIHByb2plY3RJc0Rvd25sb2FkZWQocHJvamVjdE5hbWUpIHtcbiAgICByZXR1cm4gKFxuICAgICAgdHlwZW9mIGRydXBhbFNldHRpbmdzICE9PSAndW5kZWZpbmVkJyAmJiBwcm9qZWN0TmFtZSBpbiBNT0RVTEVfU1RBVFVTXG4gICAgKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBEZXRlcm1pbmUgaWYgYSBwcm9qZWN0IGlzIGluc3RhbGxlZCBpbiB0aGUgbG9jYWwgRHJ1cGFsIGNvZGViYXNlLlxuICAgKlxuICAgKiBAcGFyYW0ge3N0cmluZ30gcHJvamVjdE5hbWVcbiAgICogICBUaGUgcHJvamVjdCBuYW1lLlxuICAgKiBAcmV0dXJuIHtib29sZWFufVxuICAgKiAgIFRydWUgaWYgdGhlIHByb2plY3QgaXMgaW5zdGFsbGVkLlxuICAgKi9cbiAgZnVuY3Rpb24gcHJvamVjdElzSW5zdGFsbGVkKHByb2plY3ROYW1lKSB7XG4gICAgcmV0dXJuIChcbiAgICAgIHR5cGVvZiBkcnVwYWxTZXR0aW5ncyAhPT0gJ3VuZGVmaW5lZCcgJiZcbiAgICAgIHByb2plY3ROYW1lIGluIE1PRFVMRV9TVEFUVVMgJiZcbiAgICAgIE1PRFVMRV9TVEFUVVNbcHJvamVjdE5hbWVdID09PSAxXG4gICAgKTtcbiAgfVxuXG4gIGxldCBwcm9qZWN0SW5zdGFsbGVkID0gcHJvamVjdElzSW5zdGFsbGVkKHByb2plY3QucHJvamVjdF9tYWNoaW5lX25hbWUpO1xuICBsZXQgcHJvamVjdERvd25sb2FkZWQgPSBwcm9qZWN0SXNEb3dubG9hZGVkKHByb2plY3QucHJvamVjdF9tYWNoaW5lX25hbWUpO1xuXG4gIC8qKlxuICAgKiBDaGVja3MgdGhlIGRvd25sb2FkL2luc3RhbGwgc3RhdHVzIG9mIGEgcHJvamVjdCBhbmQgdXBkYXRlcyB0aGUgVUkuXG4gICAqXG4gICAqIER1cmluZyBhbiBpbnN0YWxsLCB0aGlzIGZ1bmN0aW9uIGlzIHJlcGVhdGVkbHkgY2FsbGVkIHRvIGNoZWNrIHRoZSBzdGF0dXNcbiAgICogb2YgdGhlIGRvd25sb2FkL2luc3RhbGwgb3BlcmF0aW9uLCBhbmQgdGhlIFVJIGlzIHVwZGF0ZWQgd2l0aCB0aGUgc3RhZ2UgdGhlXG4gICAqIHByb2Nlc3MgaXMgY3VycmVudGx5IGluLiBUaGlzIGZ1bmN0aW9uIHN0b3BzIGJlaW5nIGNhbGxlZCB3aGVuIHRoZSBwcm9jZXNzXG4gICAqIHN1Y2Nlc3NmdWxseSBjb21wbGV0ZXMgb3Igc3RvcHMgZHVlIHRvIGFuIGVycm9yLlxuICAgKlxuICAgKiBAcGFyYW0ge2Jvb2xlYW59IGluaXRpYXRlXG4gICAqICAgV2hlbiB0cnVlLCBiZWdpbiB0aGUgaW5zdGFsbCBwcm9jZXNzIGZvciB0aGUgcHJvamVjdC5cbiAgICogQHJldHVybiB7UHJvbWlzZTx2b2lkPn1cbiAgICogICBSZXR1cm4gaXMgbm90IHVzZWQsIGJ1dCBpcyBhIHByb21pc2UgZHVlIHRvIHRoaXMgYmVpbmcgYXN5bmMuXG4gICAqL1xuICBjb25zdCBzaG93U3RhdHVzID0gYXN5bmMgKGluaXRpYXRlID0gZmFsc2UpID0+IHtcbiAgICBjb25zdCB1cmwgPSBgJHtPUklHSU5fVVJMfS9hZG1pbi9tb2R1bGVzL3Byb2plY3RfYnJvd3Nlci9pbnN0YWxsX2luX3Byb2dyZXNzLyR7cHJvamVjdC5wcm9qZWN0X21hY2hpbmVfbmFtZX1gO1xuXG4gICAgLy9cbiAgICAvKipcbiAgICAgKiBHZXRzIHRoZSBjdXJyZW50IHN0YXR1cyBvZiB0aGUgcHJvamVjdCdzIGRvd25sb2FkIG9yIHJlcXVpcmUgcHJvY2Vzcy5cbiAgICAgKlxuICAgICAqIEByZXR1cm4ge1Byb21pc2U8YW55Pn1cbiAgICAgKiAgIFRoZSBKU09OIHN0YXR1cyByZXNwb25zZSwgcGx1cyB0aGUgdGltZXN0YW1wIG9mIHdoZW4gaXQgd2FzIHJldHVybmVkLlxuICAgICAqL1xuICAgIGNvbnN0IHN0YXR1cyA9IGFzeW5jICgpID0+IHtcbiAgICAgIGNvbnN0IHByb2dyZXNzQ2hlY2sgPSBhd2FpdCBmZXRjaCh1cmwpO1xuICAgICAgY29uc3QganNvbiA9IGF3YWl0IHByb2dyZXNzQ2hlY2suanNvbigpO1xuICAgICAgcmV0dXJuIHsgLi4uanNvbiwgdGltZTogbmV3IERhdGUoKS5nZXRUaW1lKCkgfTtcbiAgICB9O1xuICAgIGNvbnN0IGxvYWRpbmdTdGF0dXMgPSBhd2FpdCBzdGF0dXMoKTtcblxuICAgIC8vIFdlIGtlZXAgdHJhY2sgb2YgaG93IG1hbnkgaW50ZXJ2YWxzIGhhdmUgdGFrZW4gcGxhY2UgZHVyaW5nIHRoZSBwcm9ncmVzc1xuICAgIC8vIGNoZWNrIHNvIHdlIGNhbiBhbm5vdW5jZSBwcm9ncmVzcyB0byBldmVyeSA1LTEwIHNlY29uZHMuXG4gICAgbGV0IGludGVydmFscyA9IDA7XG5cbiAgICAvLyBXaGVuIGEgcmVxdWlyZSBiZWdpbnMsIHRoZXJlIG1heSBiZSBhIGRlbGF5IGJlZm9yZSB0aGVcbiAgICAvLyBgaW5zdGFsbF9pbl9wcm9ncmVzc2AgZW5kcG9pbnQgcHJvdmlkZXMgdGhlIGNvcnJlY3Qgc3RhdHVzLiBUaGVcbiAgICAvLyBpbml0aWF0ZUxhZyBpcyBob3cgbWFueSB0aW1lcyB0aGUgaW50ZXJ2YWwgYmVsb3cgd2lsbCBjaGVjayBmb3IgdGhhdFxuICAgIC8vIHN0YXR1cyBiZWZvcmUgYWJvcnRpbmcuXG4gICAgbGV0IGluaXRpYXRlTGFnID0gNDtcblxuICAgIC8vIFRoZSBpbml0aWF0ZSB2YXJpYWJsZSBtZWFucyBhIG5ldyBkb3dubG9hZCBvciBpbnN0YWxsIHdhcyByZXF1ZXN0ZWQgYW5kXG4gICAgLy8gdGhlIGFzc29jaWF0ZSBwcm9jZXNzIHNob3VsZCBiZWdpbi5cbiAgICAvLyBUaGUgbG9hZGluZ1N0YXR1cyBjaGVja3MgYXJlIGZvciB3aGVuIHByb2plY3QgYnJvd3NlciBpcyBsb2FkZWQgYW5kIG9uZVxuICAgIC8vIG9mIHRoZSBsaXN0ZWQgcHJvamVjdHMgaGFzIGEgZG93bmxvYWQgb3IgaW5zdGFsbCBpbiBwcm9ncmVzcywgc28gdGhlIFVJXG4gICAgLy8gY29udmV5cyB0aGlzIGV2ZW4gaWYgdGhlIHByb2Nlc3Mgd2FzIGluaXRhdGVkIGluIGFub3RoZXIgdGFiIG9yIGJ5IGFcbiAgICAvLyBkaWZmZXJlbnQgdXNlci5cbiAgICBpZiAoaW5pdGlhdGUgfHwgKGxvYWRpbmdTdGF0dXMgJiYgbG9hZGluZ1N0YXR1cy5zdGF0dXMgIT09IDApKSB7XG4gICAgICBsb2FkaW5nID0gdHJ1ZTtcbiAgICAgIGxvYWRpbmdQaGFzZSA9IGxvYWRpbmdTdGF0dXMucGhhc2VcbiAgICAgICAgPyBEcnVwYWwudCgnQWRkaW5nOiBAcGhhc2UnLCB7ICdAcGhhc2UnOiBsb2FkaW5nU3RhdHVzLnBoYXNlIH0pXG4gICAgICAgIDogRHJ1cGFsLnQoJ0luc3RhbGxpbmcnKTtcbiAgICAgIGNvbnN0IGludGVydmFsSWQgPSBzZXRJbnRlcnZhbChhc3luYyAoKSA9PiB7XG4gICAgICAgIGNvbnN0IGN1cnJlbnRTdGF0dXMgPSBhd2FpdCBzdGF0dXMoKTtcbiAgICAgICAgY29uc3Qgbm90SW5Qcm9ncmVzcyA9IGN1cnJlbnRTdGF0dXMuc3RhdHVzID09PSAwICYmICFpbml0aWF0ZTtcbiAgICAgICAgLy8gSWYgdGhlIGluaXRpYXRlTGFnIGlzIGF0IDAsIHRoZXJlJ3MgYmVlbiBzdWZmaWNpZW50IHRpbWUgZm9yIHRoZVxuICAgICAgICAvLyBpbnN0YWxsIGNvbnRyb2xsZXIgdG8gcmV0dXJuIGEgdmFsaWQgc3RhdHVzLiBJZiB0aGF0IGhhcyBub3RcbiAgICAgICAgLy8gaGFwcGVuZWQgYnkgdGhlbiwgdGhlcmUgaXMgbGlrZWx5IGFuIHVuZGVybHlpbmcgaXNzdWUgdGhhdCB3b24ndCBiZVxuICAgICAgICAvLyBhZGRyZXNzZWQgYnkgd2FpdGluZyBsb25nZXIuIFdlIGNhdGVnb3JpemUgdGhpcyBhdHRlbXB0IHRvIGRvd25sb2FkIC9cbiAgICAgICAgLy8gaW5zdGFsbCBhcyBcImluaXRpYXRlZCBidXQgbmV2ZXIgc3RhcnRlZFwiIGFuZCBjbGVhciB0aGUgaW50ZXJ2YWwgdGhhdFxuICAgICAgICAvLyBpcyByZXBlYXRlZGx5IGludm9raW5nIHRoaXMgZnVuY3Rpb24uXG4gICAgICAgIGNvbnN0IGluaXRpYXRlZEJ1dE5ldmVyU3RhcnRlZCA9XG4gICAgICAgICAgKGluaXRpYXRlTGFnID09PSAwIHx8ICFjdXJyZW50U3RhdHVzLmhhc093blByb3BlcnR5KCdwaGFzZScpKSAmJlxuICAgICAgICAgIGluaXRpYXRlICYmXG4gICAgICAgICAgIWN1cnJlbnRTdGF0dXMuc3RhdHVzID09PSAwO1xuICAgICAgICBpZiAobm90SW5Qcm9ncmVzcyB8fCBpbml0aWF0ZWRCdXROZXZlclN0YXJ0ZWQgfHwgIWxvYWRpbmcpIHtcbiAgICAgICAgICAvLyBUaGUgcHJvY2VzcyBoYXMgZWl0aGVyIGNvbXBsZXRlZCwgb3IgZW5jb3VudGVyZWQgYSBwcm9ibGVtIHRoYXRcbiAgICAgICAgICAvLyB3b3VsZCBub3QgYmVuZWZpdCBmcm9tIGZ1cnRoZXIgaXRlcmF0aW9ucyBvZiB0aGlzIGZ1bmN0aW9uLiBUaGVcbiAgICAgICAgICAvLyBpbnRlcnZhbCBpcyBjbGVhcmVkIGFuZCB0aGUgVUkgaXMgdXBkYXRlZCB0byBpbmRpY2F0ZSBub3RoaW5nIGlzIGluXG4gICAgICAgICAgLy8gcHJvZ3Jlc3MuXG4gICAgICAgICAgY2xlYXJJbnRlcnZhbChpbnRlcnZhbElkKTtcbiAgICAgICAgICBsb2FkaW5nID0gZmFsc2U7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgLy8gRHVyaW5nIHBhcnRzIG9mIHRoZSBwcm9jZXNzIHdoZXJlIHRoZSBQYWNrYWdlIE1hbmFnZXIgc3RhZ2UgaXMgaW5cbiAgICAgICAgICAvLyB1c2UsIHRoZSBzdGF0dXMgaW5jbHVkZXMgdGhlIHBoYXNlIG9mIHRoZSBwcm9jZXNzIHRha2luZyBwbGFjZS5cbiAgICAgICAgICAvLyBVc2UgdGhpcyB3aGVuIGF2YWlsYWJsZSwgb3RoZXJ3aXNlIHByb3ZpZGUgYSBkZWZhdWx0IG1lc3NhZ2UuXG4gICAgICAgICAgbG9hZGluZ1BoYXNlID0gY3VycmVudFN0YXR1cy5waGFzZSB8fCAnSW4gcHJvZ3Jlc3MnO1xuICAgICAgICB9XG4gICAgICAgIGluaXRpYXRlTGFnIC09IDE7XG4gICAgICAgIGlmIChpbnRlcnZhbHMgJSA0ID09PSAxKSB7XG4gICAgICAgICAgLy8gQ2xlYXIgYW5ub3VuY2UgaW4gdGhlIGludGVydmFsIGltbWVkaWF0ZWx5IGFmdGVyIGEgcmVhZCBzbyBpZlxuICAgICAgICAgIC8vIGFubm91bmNlIGlzIGNhbGxlZCBhZ2FpbiBpdCB3aWxsIGJlIGNvbnZleWVkIHRvIHRoZSBzY3JlZW4gcmVhZGVyXG4gICAgICAgICAgLy8gZXZlbiBpZiB0aGUgcHJvZ3Jlc3MgbWVzc2FnZSBpcyB1bmNoYW5nZWQuXG4gICAgICAgICAgRHJ1cGFsLmFubm91bmNlKCcnKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAoaW50ZXJ2YWxzID09PSAwIHx8IGludGVydmFscyAlIDQgPT09IDApIHtcbiAgICAgICAgICBpZiAoY3VycmVudFN0YXR1cy5waGFzZSkge1xuICAgICAgICAgICAgRHJ1cGFsLmFubm91bmNlKFxuICAgICAgICAgICAgICBEcnVwYWwudChcbiAgICAgICAgICAgICAgICAnQWRkaW5nIG1vZHVsZSBAbW9kdWxlLCBwaGFzZSBAcGhhc2UgaW4gcHJvZ3Jlc3MnLFxuICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICdAbW9kdWxlJzogcHJvamVjdC50aXRsZSxcbiAgICAgICAgICAgICAgICAgICdAcGhhc2UnOiBjdXJyZW50U3RhdHVzLnBoYXNlLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgJ2Fzc2VydGl2ZScsXG4gICAgICAgICAgICAgICksXG4gICAgICAgICAgICApO1xuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBEcnVwYWwuYW5ub3VuY2UoXG4gICAgICAgICAgICAgIERydXBhbC50KFxuICAgICAgICAgICAgICAgICdBZGRpbmcgbW9kdWxlIEBtb2R1bGUsIGluIHByb2dyZXNzJyxcbiAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAnQG1vZHVsZSc6IHByb2plY3QudGl0bGUsXG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAnYXNzZXJ0aXZlJyxcbiAgICAgICAgICAgICAgKSxcbiAgICAgICAgICAgICk7XG4gICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGludGVydmFscyArPSAxO1xuICAgICAgfSwgMTI1MCk7XG4gICAgfVxuICB9O1xuXG4gIG9uTW91bnQoKCkgPT4ge1xuICAgIC8vIElmIHRoZSBtb2R1bGUgaXMgbWlkLWRvd25sb2FkIG9yIG1pZC1pbnN0YWxsIHdoZW4gdGhlIHBhZ2UgbG9hZHMsIHRoZSBVSVxuICAgIC8vIHNob3VsZCByZWZsZWN0IHRoYXQgYnkgYWRkaW5nIGEgcHJvZ3Jlc3Mgc3Bpbm5lciBhbmQgZGlzYWJsaW5nIGFjdGlvbnMuXG4gICAgLy8gVGhlIGFwcCB3aWxsIGNoZWNrIHBlcmlvZGljYWxseSB0byBzZWUgaWYgdGhlIHN0YXR1cyBoYXMgY2hhbmdlZCBhbmRcbiAgICAvLyB1cGRhdGUgdGhlIFVJLlxuICAgIGlmIChBTExPV19VSV9JTlNUQUxMKSB7XG4gICAgICBzaG93U3RhdHVzKCk7XG4gICAgfVxuICB9KTtcbjwvc2NyaXB0PlxuXG48ZGl2IGNsYXNzPVwiYWN0aW9uLWJ1dHRvbl9fd3JhcHBlclwiPlxuICB7I2lmICFwcm9qZWN0LmlzX2NvbXBhdGlibGV9XG4gICAgPFByb2plY3RTdGF0dXNJbmRpY2F0b3Ige3Byb2plY3R9IHN0YXR1c1RleHQ9e0RydXBhbC50KCdOb3QgY29tcGF0aWJsZScpfSAvPlxuICB7OmVsc2UgaWYgcHJvamVjdEluc3RhbGxlZH1cbiAgICA8UHJvamVjdFN0YXR1c0luZGljYXRvciB7cHJvamVjdH0gc3RhdHVzVGV4dD17RHJ1cGFsLnQoJ0luc3RhbGxlZCcpfT5cbiAgICAgIDxzcGFuIGNsYXNzPVwiYWN0aW9uLWJ1dHRvbl9fdW5pY29kZVwiIGFyaWEtaGlkZGVuPVwidHJ1ZVwiPiYjMTAwMDMmI3gyMDwvc3BhblxuICAgICAgPlxuICAgIDwvUHJvamVjdFN0YXR1c0luZGljYXRvcj5cbiAgezplbHNlIGlmIHByb2plY3REb3dubG9hZGVkfVxuICAgIDxzcGFuPlxuICAgICAgeyNpZiBBTExPV19VSV9JTlNUQUxMfVxuICAgICAgICB7I2lmIGxvYWRpbmd9XG4gICAgICAgICAgPExvYWRpbmdFbGxpcHNpcyAvPlxuICAgICAgICAgIDxMb2FkaW5nIHBvc2l0aW9uQWJzb2x1dGU9e3RydWV9IC8+XG4gICAgICAgIHs6ZWxzZX1cbiAgICAgICAgICA8QWRkSW5zdGFsbEJ1dHRvblxuICAgICAgICAgICAge3Byb2plY3R9XG4gICAgICAgICAgICBiaW5kOmxvYWRpbmdcbiAgICAgICAgICAgIGJpbmQ6cHJvamVjdEluc3RhbGxlZFxuICAgICAgICAgICAgYmluZDpwcm9qZWN0RG93bmxvYWRlZFxuICAgICAgICAgICAge3Nob3dTdGF0dXN9XG4gICAgICAgICAgICBhbHJlYWR5QWRkZWQ9e3RydWV9XG4gICAgICAgICAgLz5cbiAgICAgICAgey9pZn1cbiAgICAgIHs6ZWxzZX1cbiAgICAgICAgPGFcbiAgICAgICAgICBocmVmPVwie09SSUdJTl9VUkx9L2FkbWluL21vZHVsZXMjbW9kdWxlLXtwcm9qZWN0LnNlbGVjdG9yX2lkfVwiXG4gICAgICAgICAgdGFyZ2V0PVwiX2JsYW5rXCJcbiAgICAgICAgICByZWw9XCJub3JlZmVycmVyXCJcbiAgICAgICAgICA+PFByb2plY3RCdXR0b25CYXNlPntEcnVwYWwudCgnSW5zdGFsbCcpfTwvUHJvamVjdEJ1dHRvbkJhc2U+PC9hXG4gICAgICAgID5cbiAgICAgIHsvaWZ9XG4gICAgPC9zcGFuPlxuICB7OmVsc2V9XG4gICAgPHNwYW4+XG4gICAgICB7I2lmICFQTV9WQUxJREFUSU9OX0VSUk9SICYmIEFMTE9XX1VJX0lOU1RBTEx9XG4gICAgICAgIHsjaWYgbG9hZGluZ31cbiAgICAgICAgICA8c3BhbiBjbGFzcz1cImxvYWRpbmctZWxsaXBzaXNcIj57bG9hZGluZ1BoYXNlfTwvc3Bhbj5cbiAgICAgICAgICA8TG9hZGluZyBwb3NpdGlvbkFic29sdXRlPXt0cnVlfSAvPlxuICAgICAgICB7OmVsc2V9XG4gICAgICAgICAgPEFkZEluc3RhbGxCdXR0b25cbiAgICAgICAgICAgIHtwcm9qZWN0fVxuICAgICAgICAgICAgYmluZDpsb2FkaW5nXG4gICAgICAgICAgICBiaW5kOnByb2plY3RJbnN0YWxsZWRcbiAgICAgICAgICAgIGJpbmQ6cHJvamVjdERvd25sb2FkZWRcbiAgICAgICAgICAgIHtzaG93U3RhdHVzfVxuICAgICAgICAgIC8+XG4gICAgICAgIHsvaWZ9XG4gICAgICB7OmVsc2V9XG4gICAgICAgIDxQcm9qZWN0QnV0dG9uQmFzZVxuICAgICAgICAgIGNsaWNrPXsoKSA9PiBvcGVuUG9wdXAoZ2V0Q29tbWFuZHNQb3B1cE1lc3NhZ2UocHJvamVjdCksIHByb2plY3QpfVxuICAgICAgICAgID57RHJ1cGFsLnQoJ1ZpZXcgQ29tbWFuZHMnKX1cbiAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpc3VhbGx5LWhpZGRlblwiXG4gICAgICAgICAgICA+e0RydXBhbC50KCcgZm9yICcpfSB7cHJvamVjdC50aXRsZX08L3NwYW5cbiAgICAgICAgICA+XG4gICAgICAgIDwvUHJvamVjdEJ1dHRvbkJhc2U+XG4gICAgICB7L2lmfVxuICAgIDwvc3Bhbj5cbiAgey9pZn1cbjwvZGl2PlxuXG48c3R5bGU+XG4gIC5hY3Rpb24tYnV0dG9uX193cmFwcGVyIHtcbiAgICBwYWRkaW5nOiAwLjVlbSAwO1xuICAgIG1hcmdpbi1pbmxpbmUtc3RhcnQ6IGF1dG87XG4gIH1cblxuICAuYWN0aW9uLWJ1dHRvbl9fdW5pY29kZSB7XG4gICAgY29sb3I6ICMyMjg1NzI7XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBaVBFLGtDQUF3QixDQUN0QixPQUFPLENBQUUsS0FBSyxDQUFDLENBQUMsQ0FDaEIsbUJBQW1CLENBQUUsSUFDdkIsQ0FFQSxrQ0FBd0IsQ0FDdEIsS0FBSyxDQUFFLE9BQ1QifQ== */");
}

// (213:2) {:else}
function create_else_block_2(ctx) {
	let span;
	let current_block_type_index;
	let if_block;
	let current;
	const if_block_creators = [create_if_block_5, create_else_block_4];
	const if_blocks = [];

	function select_block_type_3(ctx, dirty) {
		if (!_constants__WEBPACK_IMPORTED_MODULE_2__.PM_VALIDATION_ERROR && _constants__WEBPACK_IMPORTED_MODULE_2__.ALLOW_UI_INSTALL) return 0;
		return 1;
	}

	current_block_type_index = select_block_type_3(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			if_block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 213, 4, 7841);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			if_blocks[current_block_type_index].m(span, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			if_block.p(ctx, dirty);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
			if_blocks[current_block_type_index].d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block_2.name,
		type: "else",
		source: "(213:2) {:else}",
		ctx
	});

	return block;
}

// (188:30) 
function create_if_block_2(ctx) {
	let span;
	let current_block_type_index;
	let if_block;
	let current;
	const if_block_creators = [create_if_block_3, create_else_block_1];
	const if_blocks = [];

	function select_block_type_1(ctx, dirty) {
		if (_constants__WEBPACK_IMPORTED_MODULE_2__.ALLOW_UI_INSTALL) return 0;
		return 1;
	}

	current_block_type_index = select_block_type_1(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			if_block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 188, 4, 7189);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			if_blocks[current_block_type_index].m(span, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			if_block.p(ctx, dirty);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
			if_blocks[current_block_type_index].d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(188:30) ",
		ctx
	});

	return block;
}

// (183:29) 
function create_if_block_1(ctx) {
	let projectstatusindicator;
	let current;

	projectstatusindicator = new _ProjectStatusIndicator_svelte__WEBPACK_IMPORTED_MODULE_8__["default"]({
			props: {
				project: /*project*/ ctx[0],
				statusText: /*Drupal*/ ctx[5].t('Installed'),
				$$slots: { default: [create_default_slot] },
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectstatusindicator.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectstatusindicator, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const projectstatusindicator_changes = {};
			if (dirty & /*project*/ 1) projectstatusindicator_changes.project = /*project*/ ctx[0];

			if (dirty & /*$$scope*/ 131072) {
				projectstatusindicator_changes.$$scope = { dirty, ctx };
			}

			projectstatusindicator.$set(projectstatusindicator_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectstatusindicator.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectstatusindicator.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectstatusindicator, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(183:29) ",
		ctx
	});

	return block;
}

// (181:2) {#if !project.is_compatible}
function create_if_block(ctx) {
	let projectstatusindicator;
	let current;

	projectstatusindicator = new _ProjectStatusIndicator_svelte__WEBPACK_IMPORTED_MODULE_8__["default"]({
			props: {
				project: /*project*/ ctx[0],
				statusText: /*Drupal*/ ctx[5].t('Not compatible')
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectstatusindicator.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectstatusindicator, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const projectstatusindicator_changes = {};
			if (dirty & /*project*/ 1) projectstatusindicator_changes.project = /*project*/ ctx[0];
			projectstatusindicator.$set(projectstatusindicator_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectstatusindicator.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectstatusindicator.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectstatusindicator, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(181:2) {#if !project.is_compatible}",
		ctx
	});

	return block;
}

// (228:6) {:else}
function create_else_block_4(ctx) {
	let projectbuttonbase;
	let current;

	projectbuttonbase = new _ProjectButtonBase_svelte__WEBPACK_IMPORTED_MODULE_7__["default"]({
			props: {
				click: /*func*/ ctx[13],
				$$slots: { default: [create_default_slot_2] },
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectbuttonbase.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectbuttonbase, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const projectbuttonbase_changes = {};
			if (dirty & /*project*/ 1) projectbuttonbase_changes.click = /*func*/ ctx[13];

			if (dirty & /*$$scope, project*/ 131073) {
				projectbuttonbase_changes.$$scope = { dirty, ctx };
			}

			projectbuttonbase.$set(projectbuttonbase_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectbuttonbase.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectbuttonbase.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectbuttonbase, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block_4.name,
		type: "else",
		source: "(228:6) {:else}",
		ctx
	});

	return block;
}

// (215:6) {#if !PM_VALIDATION_ERROR && ALLOW_UI_INSTALL}
function create_if_block_5(ctx) {
	let current_block_type_index;
	let if_block;
	let if_block_anchor;
	let current;
	const if_block_creators = [create_if_block_6, create_else_block_3];
	const if_blocks = [];

	function select_block_type_4(ctx, dirty) {
		if (/*loading*/ ctx[1]) return 0;
		return 1;
	}

	current_block_type_index = select_block_type_4(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

	const block = {
		c: function create() {
			if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			if_blocks[current_block_type_index].m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			let previous_block_index = current_block_type_index;
			current_block_type_index = select_block_type_4(ctx, dirty);

			if (current_block_type_index === previous_block_index) {
				if_blocks[current_block_type_index].p(ctx, dirty);
			} else {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_blocks[previous_block_index], 1, 1, () => {
					if_blocks[previous_block_index] = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
				if_block = if_blocks[current_block_type_index];

				if (!if_block) {
					if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
					if_block.c();
				} else {
					if_block.p(ctx, dirty);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
				if_block.m(if_block_anchor.parentNode, if_block_anchor);
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if_blocks[current_block_type_index].d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_5.name,
		type: "if",
		source: "(215:6) {#if !PM_VALIDATION_ERROR && ALLOW_UI_INSTALL}",
		ctx
	});

	return block;
}

// (229:8) <ProjectButtonBase           click={() => openPopup(getCommandsPopupMessage(project), project)}           >
function create_default_slot_2(ctx) {
	let t0_value = /*Drupal*/ ctx[5].t('View Commands') + "";
	let t0;
	let t1;
	let span;
	let t2_value = /*Drupal*/ ctx[5].t(' for ') + "";
	let t2;
	let t3;
	let t4_value = /*project*/ ctx[0].title + "";
	let t4;

	const block = {
		c: function create() {
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t2_value);
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t4_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "visually-hidden");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 231, 10, 8411);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t0, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t1, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t4);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*project*/ 1 && t4_value !== (t4_value = /*project*/ ctx[0].title + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t4, t4_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t0);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t1);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_default_slot_2.name,
		type: "slot",
		source: "(229:8) <ProjectButtonBase           click={() => openPopup(getCommandsPopupMessage(project), project)}           >",
		ctx
	});

	return block;
}

// (219:8) {:else}
function create_else_block_3(ctx) {
	let addinstallbutton;
	let updating_loading;
	let updating_projectInstalled;
	let updating_projectDownloaded;
	let current;

	function addinstallbutton_loading_binding_1(value) {
		/*addinstallbutton_loading_binding_1*/ ctx[10](value);
	}

	function addinstallbutton_projectInstalled_binding_1(value) {
		/*addinstallbutton_projectInstalled_binding_1*/ ctx[11](value);
	}

	function addinstallbutton_projectDownloaded_binding_1(value) {
		/*addinstallbutton_projectDownloaded_binding_1*/ ctx[12](value);
	}

	let addinstallbutton_props = {
		project: /*project*/ ctx[0],
		showStatus: /*showStatus*/ ctx[6]
	};

	if (/*loading*/ ctx[1] !== void 0) {
		addinstallbutton_props.loading = /*loading*/ ctx[1];
	}

	if (/*projectInstalled*/ ctx[3] !== void 0) {
		addinstallbutton_props.projectInstalled = /*projectInstalled*/ ctx[3];
	}

	if (/*projectDownloaded*/ ctx[4] !== void 0) {
		addinstallbutton_props.projectDownloaded = /*projectDownloaded*/ ctx[4];
	}

	addinstallbutton = new _AddInstallButton_svelte__WEBPACK_IMPORTED_MODULE_5__["default"]({
			props: addinstallbutton_props,
			$$inline: true
		});

	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(addinstallbutton, 'loading', addinstallbutton_loading_binding_1));
	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(addinstallbutton, 'projectInstalled', addinstallbutton_projectInstalled_binding_1));
	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(addinstallbutton, 'projectDownloaded', addinstallbutton_projectDownloaded_binding_1));

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(addinstallbutton.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(addinstallbutton, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const addinstallbutton_changes = {};
			if (dirty & /*project*/ 1) addinstallbutton_changes.project = /*project*/ ctx[0];

			if (!updating_loading && dirty & /*loading*/ 2) {
				updating_loading = true;
				addinstallbutton_changes.loading = /*loading*/ ctx[1];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_loading = false);
			}

			if (!updating_projectInstalled && dirty & /*projectInstalled*/ 8) {
				updating_projectInstalled = true;
				addinstallbutton_changes.projectInstalled = /*projectInstalled*/ ctx[3];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_projectInstalled = false);
			}

			if (!updating_projectDownloaded && dirty & /*projectDownloaded*/ 16) {
				updating_projectDownloaded = true;
				addinstallbutton_changes.projectDownloaded = /*projectDownloaded*/ ctx[4];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_projectDownloaded = false);
			}

			addinstallbutton.$set(addinstallbutton_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(addinstallbutton.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(addinstallbutton.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(addinstallbutton, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block_3.name,
		type: "else",
		source: "(219:8) {:else}",
		ctx
	});

	return block;
}

// (216:8) {#if loading}
function create_if_block_6(ctx) {
	let span;
	let t0;
	let t1;
	let loading_1;
	let current;

	loading_1 = new _Loading_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: { positionAbsolute: true },
			$$inline: true
		});

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(/*loadingPhase*/ ctx[2]);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(loading_1.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "loading-ellipsis");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 216, 10, 7933);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t1, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(loading_1, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			if (!current || dirty & /*loadingPhase*/ 4) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, /*loadingPhase*/ ctx[2]);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(loading_1.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(loading_1.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(loading_1, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_6.name,
		type: "if",
		source: "(216:8) {#if loading}",
		ctx
	});

	return block;
}

// (204:6) {:else}
function create_else_block_1(ctx) {
	let a;
	let projectbuttonbase;
	let a_href_value;
	let current;

	projectbuttonbase = new _ProjectButtonBase_svelte__WEBPACK_IMPORTED_MODULE_7__["default"]({
			props: {
				$$slots: { default: [create_default_slot_1] },
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			a = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("a");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectbuttonbase.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "href", a_href_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL + "/admin/modules#module-" + /*project*/ ctx[0].selector_id));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "target", "_blank");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "rel", "noreferrer");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(a, file, 204, 8, 7589);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, a, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectbuttonbase, a, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			const projectbuttonbase_changes = {};

			if (dirty & /*$$scope*/ 131072) {
				projectbuttonbase_changes.$$scope = { dirty, ctx };
			}

			projectbuttonbase.$set(projectbuttonbase_changes);

			if (!current || dirty & /*project*/ 1 && a_href_value !== (a_href_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL + "/admin/modules#module-" + /*project*/ ctx[0].selector_id))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "href", a_href_value);
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectbuttonbase.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectbuttonbase.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(a);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectbuttonbase);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block_1.name,
		type: "else",
		source: "(204:6) {:else}",
		ctx
	});

	return block;
}

// (190:6) {#if ALLOW_UI_INSTALL}
function create_if_block_3(ctx) {
	let current_block_type_index;
	let if_block;
	let if_block_anchor;
	let current;
	const if_block_creators = [create_if_block_4, create_else_block];
	const if_blocks = [];

	function select_block_type_2(ctx, dirty) {
		if (/*loading*/ ctx[1]) return 0;
		return 1;
	}

	current_block_type_index = select_block_type_2(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

	const block = {
		c: function create() {
			if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			if_blocks[current_block_type_index].m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			let previous_block_index = current_block_type_index;
			current_block_type_index = select_block_type_2(ctx, dirty);

			if (current_block_type_index === previous_block_index) {
				if_blocks[current_block_type_index].p(ctx, dirty);
			} else {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_blocks[previous_block_index], 1, 1, () => {
					if_blocks[previous_block_index] = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
				if_block = if_blocks[current_block_type_index];

				if (!if_block) {
					if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
					if_block.c();
				} else {
					if_block.p(ctx, dirty);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
				if_block.m(if_block_anchor.parentNode, if_block_anchor);
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if_blocks[current_block_type_index].d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_3.name,
		type: "if",
		source: "(190:6) {#if ALLOW_UI_INSTALL}",
		ctx
	});

	return block;
}

// (209:11) <ProjectButtonBase>
function create_default_slot_1(ctx) {
	let t_value = /*Drupal*/ ctx[5].t('Install') + "";
	let t;

	const block = {
		c: function create() {
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t, anchor);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_default_slot_1.name,
		type: "slot",
		source: "(209:11) <ProjectButtonBase>",
		ctx
	});

	return block;
}

// (194:8) {:else}
function create_else_block(ctx) {
	let addinstallbutton;
	let updating_loading;
	let updating_projectInstalled;
	let updating_projectDownloaded;
	let current;

	function addinstallbutton_loading_binding(value) {
		/*addinstallbutton_loading_binding*/ ctx[7](value);
	}

	function addinstallbutton_projectInstalled_binding(value) {
		/*addinstallbutton_projectInstalled_binding*/ ctx[8](value);
	}

	function addinstallbutton_projectDownloaded_binding(value) {
		/*addinstallbutton_projectDownloaded_binding*/ ctx[9](value);
	}

	let addinstallbutton_props = {
		project: /*project*/ ctx[0],
		showStatus: /*showStatus*/ ctx[6],
		alreadyAdded: true
	};

	if (/*loading*/ ctx[1] !== void 0) {
		addinstallbutton_props.loading = /*loading*/ ctx[1];
	}

	if (/*projectInstalled*/ ctx[3] !== void 0) {
		addinstallbutton_props.projectInstalled = /*projectInstalled*/ ctx[3];
	}

	if (/*projectDownloaded*/ ctx[4] !== void 0) {
		addinstallbutton_props.projectDownloaded = /*projectDownloaded*/ ctx[4];
	}

	addinstallbutton = new _AddInstallButton_svelte__WEBPACK_IMPORTED_MODULE_5__["default"]({
			props: addinstallbutton_props,
			$$inline: true
		});

	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(addinstallbutton, 'loading', addinstallbutton_loading_binding));
	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(addinstallbutton, 'projectInstalled', addinstallbutton_projectInstalled_binding));
	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(addinstallbutton, 'projectDownloaded', addinstallbutton_projectDownloaded_binding));

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(addinstallbutton.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(addinstallbutton, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const addinstallbutton_changes = {};
			if (dirty & /*project*/ 1) addinstallbutton_changes.project = /*project*/ ctx[0];

			if (!updating_loading && dirty & /*loading*/ 2) {
				updating_loading = true;
				addinstallbutton_changes.loading = /*loading*/ ctx[1];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_loading = false);
			}

			if (!updating_projectInstalled && dirty & /*projectInstalled*/ 8) {
				updating_projectInstalled = true;
				addinstallbutton_changes.projectInstalled = /*projectInstalled*/ ctx[3];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_projectInstalled = false);
			}

			if (!updating_projectDownloaded && dirty & /*projectDownloaded*/ 16) {
				updating_projectDownloaded = true;
				addinstallbutton_changes.projectDownloaded = /*projectDownloaded*/ ctx[4];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_projectDownloaded = false);
			}

			addinstallbutton.$set(addinstallbutton_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(addinstallbutton.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(addinstallbutton.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(addinstallbutton, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block.name,
		type: "else",
		source: "(194:8) {:else}",
		ctx
	});

	return block;
}

// (191:8) {#if loading}
function create_if_block_4(ctx) {
	let loadingellipsis;
	let t;
	let loading_1;
	let current;
	loadingellipsis = new _LoadingEllipsis_svelte__WEBPACK_IMPORTED_MODULE_6__["default"]({ $$inline: true });

	loading_1 = new _Loading_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: { positionAbsolute: true },
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(loadingellipsis.$$.fragment);
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(loading_1.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(loadingellipsis, target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(loading_1, target, anchor);
			current = true;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(loadingellipsis.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(loading_1.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(loadingellipsis.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(loading_1.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(loadingellipsis, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(loading_1, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_4.name,
		type: "if",
		source: "(191:8) {#if loading}",
		ctx
	});

	return block;
}

// (184:4) <ProjectStatusIndicator {project} statusText={Drupal.t('Installed')}>
function create_default_slot(ctx) {
	let span;

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			span.textContent = "✓";
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "action-button__unicode pb-1freki0");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "aria-hidden", "true");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 184, 6, 7041);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_default_slot.name,
		type: "slot",
		source: "(184:4) <ProjectStatusIndicator {project} statusText={Drupal.t('Installed')}>",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let div;
	let current_block_type_index;
	let if_block;
	let current;
	const if_block_creators = [create_if_block, create_if_block_1, create_if_block_2, create_else_block_2];
	const if_blocks = [];

	function select_block_type(ctx, dirty) {
		if (!/*project*/ ctx[0].is_compatible) return 0;
		if (/*projectInstalled*/ ctx[3]) return 1;
		if (/*projectDownloaded*/ ctx[4]) return 2;
		return 3;
	}

	current_block_type_index = select_block_type(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if_block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "action-button__wrapper pb-1freki0");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 179, 0, 6782);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			if_blocks[current_block_type_index].m(div, null);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			let previous_block_index = current_block_type_index;
			current_block_type_index = select_block_type(ctx, dirty);

			if (current_block_type_index === previous_block_index) {
				if_blocks[current_block_type_index].p(ctx, dirty);
			} else {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_blocks[previous_block_index], 1, 1, () => {
					if_blocks[previous_block_index] = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
				if_block = if_blocks[current_block_type_index];

				if (!if_block) {
					if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
					if_block.c();
				} else {
					if_block.p(ctx, dirty);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
				if_block.m(div, null);
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			if_blocks[current_block_type_index].d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ActionButton', slots, []);
	let { project } = $$props;
	let loading = false;
	let loadingPhase = 'Adding';
	const { drupalSettings, Drupal } = window;

	/**
 * Determine is a project is present in the local Drupal codebase.
 *
 * @param {string} projectName
 *    The project name.
 * @return {boolean}
 *   True if the project is present.
 */
	function projectIsDownloaded(projectName) {
		return typeof drupalSettings !== 'undefined' && projectName in _constants__WEBPACK_IMPORTED_MODULE_2__.MODULE_STATUS;
	}

	/**
 * Determine if a project is installed in the local Drupal codebase.
 *
 * @param {string} projectName
 *   The project name.
 * @return {boolean}
 *   True if the project is installed.
 */
	function projectIsInstalled(projectName) {
		return typeof drupalSettings !== 'undefined' && projectName in _constants__WEBPACK_IMPORTED_MODULE_2__.MODULE_STATUS && _constants__WEBPACK_IMPORTED_MODULE_2__.MODULE_STATUS[projectName] === 1;
	}

	let projectInstalled = projectIsInstalled(project.project_machine_name);
	let projectDownloaded = projectIsDownloaded(project.project_machine_name);

	/**
 * Checks the download/install status of a project and updates the UI.
 *
 * During an install, this function is repeatedly called to check the status
 * of the download/install operation, and the UI is updated with the stage the
 * process is currently in. This function stops being called when the process
 * successfully completes or stops due to an error.
 *
 * @param {boolean} initiate
 *   When true, begin the install process for the project.
 * @return {Promise<void>}
 *   Return is not used, but is a promise due to this being async.
 */
	const showStatus = async (initiate = false) => {
		const url = `${_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL}/admin/modules/project_browser/install_in_progress/${project.project_machine_name}`;

		//
		/**
 * Gets the current status of the project's download or require process.
 *
 * @return {Promise<any>}
 *   The JSON status response, plus the timestamp of when it was returned.
 */
		const status = async () => {
			const progressCheck = await fetch(url);
			const json = await progressCheck.json();
			return { ...json, time: new Date().getTime() };
		};

		const loadingStatus = await status();

		// We keep track of how many intervals have taken place during the progress
		// check so we can announce progress to every 5-10 seconds.
		let intervals = 0;

		// When a require begins, there may be a delay before the
		// `install_in_progress` endpoint provides the correct status. The
		// initiateLag is how many times the interval below will check for that
		// status before aborting.
		let initiateLag = 4;

		// The initiate variable means a new download or install was requested and
		// the associate process should begin.
		// The loadingStatus checks are for when project browser is loaded and one
		// of the listed projects has a download or install in progress, so the UI
		// conveys this even if the process was initated in another tab or by a
		// different user.
		if (initiate || loadingStatus && loadingStatus.status !== 0) {
			$$invalidate(1, loading = true);

			$$invalidate(2, loadingPhase = loadingStatus.phase
			? Drupal.t('Adding: @phase', { '@phase': loadingStatus.phase })
			: Drupal.t('Installing'));

			const intervalId = setInterval(
				async () => {
					const currentStatus = await status();
					const notInProgress = currentStatus.status === 0 && !initiate;

					// If the initiateLag is at 0, there's been sufficient time for the
					// install controller to return a valid status. If that has not
					// happened by then, there is likely an underlying issue that won't be
					// addressed by waiting longer. We categorize this attempt to download /
					// install as "initiated but never started" and clear the interval that
					// is repeatedly invoking this function.
					const initiatedButNeverStarted = (initiateLag === 0 || !currentStatus.hasOwnProperty('phase')) && initiate && !currentStatus.status === 0;

					if (notInProgress || initiatedButNeverStarted || !loading) {
						// The process has either completed, or encountered a problem that
						// would not benefit from further iterations of this function. The
						// interval is cleared and the UI is updated to indicate nothing is in
						// progress.
						clearInterval(intervalId);

						$$invalidate(1, loading = false);
					} else {
						// During parts of the process where the Package Manager stage is in
						// use, the status includes the phase of the process taking place.
						// Use this when available, otherwise provide a default message.
						$$invalidate(2, loadingPhase = currentStatus.phase || 'In progress');
					}

					initiateLag -= 1;

					if (intervals % 4 === 1) {
						// Clear announce in the interval immediately after a read so if
						// announce is called again it will be conveyed to the screen reader
						// even if the progress message is unchanged.
						Drupal.announce('');
					}

					if (intervals === 0 || intervals % 4 === 0) {
						if (currentStatus.phase) {
							Drupal.announce(Drupal.t(
								'Adding module @module, phase @phase in progress',
								{
									'@module': project.title,
									'@phase': currentStatus.phase
								},
								'assertive'
							));
						} else {
							Drupal.announce(Drupal.t('Adding module @module, in progress', { '@module': project.title }, 'assertive'));
						}
					}

					intervals += 1;
				},
				1250
			);
		}
	};

	(0,svelte__WEBPACK_IMPORTED_MODULE_1__.onMount)(() => {
		// If the module is mid-download or mid-install when the page loads, the UI
		// should reflect that by adding a progress spinner and disabling actions.
		// The app will check periodically to see if the status has changed and
		// update the UI.
		if (_constants__WEBPACK_IMPORTED_MODULE_2__.ALLOW_UI_INSTALL) {
			showStatus();
		}
	});

	$$self.$$.on_mount.push(function () {
		if (project === undefined && !('project' in $$props || $$self.$$.bound[$$self.$$.props['project']])) {
			console.warn("<ActionButton> was created without expected prop 'project'");
		}
	});

	const writable_props = ['project'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ActionButton> was created with unknown prop '${key}'`);
	});

	function addinstallbutton_loading_binding(value) {
		loading = value;
		$$invalidate(1, loading);
	}

	function addinstallbutton_projectInstalled_binding(value) {
		projectInstalled = value;
		$$invalidate(3, projectInstalled);
	}

	function addinstallbutton_projectDownloaded_binding(value) {
		projectDownloaded = value;
		$$invalidate(4, projectDownloaded);
	}

	function addinstallbutton_loading_binding_1(value) {
		loading = value;
		$$invalidate(1, loading);
	}

	function addinstallbutton_projectInstalled_binding_1(value) {
		projectInstalled = value;
		$$invalidate(3, projectInstalled);
	}

	function addinstallbutton_projectDownloaded_binding_1(value) {
		projectDownloaded = value;
		$$invalidate(4, projectDownloaded);
	}

	const func = () => (0,_popup__WEBPACK_IMPORTED_MODULE_4__.openPopup)((0,_popup__WEBPACK_IMPORTED_MODULE_4__.getCommandsPopupMessage)(project), project);

	$$self.$$set = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
	};

	$$self.$capture_state = () => ({
		onMount: svelte__WEBPACK_IMPORTED_MODULE_1__.onMount,
		MODULE_STATUS: _constants__WEBPACK_IMPORTED_MODULE_2__.MODULE_STATUS,
		ORIGIN_URL: _constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL,
		ALLOW_UI_INSTALL: _constants__WEBPACK_IMPORTED_MODULE_2__.ALLOW_UI_INSTALL,
		PM_VALIDATION_ERROR: _constants__WEBPACK_IMPORTED_MODULE_2__.PM_VALIDATION_ERROR,
		Loading: _Loading_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		openPopup: _popup__WEBPACK_IMPORTED_MODULE_4__.openPopup,
		getCommandsPopupMessage: _popup__WEBPACK_IMPORTED_MODULE_4__.getCommandsPopupMessage,
		AddInstallButton: _AddInstallButton_svelte__WEBPACK_IMPORTED_MODULE_5__["default"],
		LoadingEllipsis: _LoadingEllipsis_svelte__WEBPACK_IMPORTED_MODULE_6__["default"],
		ProjectButtonBase: _ProjectButtonBase_svelte__WEBPACK_IMPORTED_MODULE_7__["default"],
		ProjectStatusIndicator: _ProjectStatusIndicator_svelte__WEBPACK_IMPORTED_MODULE_8__["default"],
		project,
		loading,
		loadingPhase,
		drupalSettings,
		Drupal,
		projectIsDownloaded,
		projectIsInstalled,
		projectInstalled,
		projectDownloaded,
		showStatus
	});

	$$self.$inject_state = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
		if ('loading' in $$props) $$invalidate(1, loading = $$props.loading);
		if ('loadingPhase' in $$props) $$invalidate(2, loadingPhase = $$props.loadingPhase);
		if ('projectInstalled' in $$props) $$invalidate(3, projectInstalled = $$props.projectInstalled);
		if ('projectDownloaded' in $$props) $$invalidate(4, projectDownloaded = $$props.projectDownloaded);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [
		project,
		loading,
		loadingPhase,
		projectInstalled,
		projectDownloaded,
		Drupal,
		showStatus,
		addinstallbutton_loading_binding,
		addinstallbutton_projectInstalled_binding,
		addinstallbutton_projectDownloaded_binding,
		addinstallbutton_loading_binding_1,
		addinstallbutton_projectInstalled_binding_1,
		addinstallbutton_projectDownloaded_binding_1,
		func
	];
}

class ActionButton extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { project: 0 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ActionButton",
			options,
			id: create_fragment.name
		});
	}

	get project() {
		throw new Error("<ActionButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set project(value) {
		throw new Error("<ActionButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ActionButton);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/AddInstallButton.svelte":
/*!******************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/AddInstallButton.svelte ***!
  \******************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _popup__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../popup */ "./modules/project_browser/sveltejs/src/popup.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _ProjectButtonBase_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ProjectButtonBase.svelte */ "./modules/project_browser/sveltejs/src/Project/ProjectButtonBase.svelte");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/AddInstallButton.svelte generated by Svelte v3.56.0 */


const { console: console_1 } = svelte_internal__WEBPACK_IMPORTED_MODULE_0__.globals;



const file = "modules/project_browser/sveltejs/src/Project/AddInstallButton.svelte";

// (156:0) <ProjectButtonBase   click={() => {     if (alreadyAdded) {       installModule();     } else {       downloadModule(true);     }   }}   disabled={PM_VALIDATION_ERROR} >
function create_default_slot(ctx) {
	let t0_value = (/*alreadyAdded*/ ctx[1]
	? /*Drupal*/ ctx[2].t('Install')
	: /*Drupal*/ ctx[2].t('Add and Install')) + "";

	let t0;
	let span;
	let t1_value = /*project*/ ctx[0].title + "";
	let t1;

	const block = {
		c: function create() {
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t1_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "visually-hidden");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 165, 68, 5917);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t0, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t1);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*alreadyAdded*/ 2 && t0_value !== (t0_value = (/*alreadyAdded*/ ctx[1]
			? /*Drupal*/ ctx[2].t('Install')
			: /*Drupal*/ ctx[2].t('Add and Install')) + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, t0_value);

			if (dirty & /*project*/ 1 && t1_value !== (t1_value = /*project*/ ctx[0].title + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t1, t1_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t0);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_default_slot.name,
		type: "slot",
		source: "(156:0) <ProjectButtonBase   click={() => {     if (alreadyAdded) {       installModule();     } else {       downloadModule(true);     }   }}   disabled={PM_VALIDATION_ERROR} >",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let projectbuttonbase;
	let current;

	projectbuttonbase = new _ProjectButtonBase_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: {
				click: /*func*/ ctx[9],
				disabled: _constants__WEBPACK_IMPORTED_MODULE_2__.PM_VALIDATION_ERROR,
				$$slots: { default: [create_default_slot] },
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectbuttonbase.$$.fragment);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectbuttonbase, target, anchor);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			const projectbuttonbase_changes = {};
			if (dirty & /*alreadyAdded*/ 2) projectbuttonbase_changes.click = /*func*/ ctx[9];

			if (dirty & /*$$scope, project, alreadyAdded*/ 4099) {
				projectbuttonbase_changes.$$scope = { dirty, ctx };
			}

			projectbuttonbase.$set(projectbuttonbase_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectbuttonbase.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectbuttonbase.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectbuttonbase, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('AddInstallButton', slots, []);
	let { project } = $$props;
	let { loading } = $$props;
	let { projectInstalled } = $$props;
	let { projectDownloaded } = $$props;
	let { showStatus } = $$props;
	let { alreadyAdded = false } = $$props;
	const { Drupal } = window;

	const handleError = async errorResponse => {
		// If an error occurred, set loading to false so the UI no longer reports
		// the download/install as in progress.
		$$invalidate(5, loading = false);

		// The error can take on many shapes, so it should be normalized.
		let err = '';

		if (typeof errorResponse === 'string') {
			err = errorResponse;
		} else {
			err = await errorResponse.text();
		}

		try {
			// See if the error string can be parsed as JSON. If not, the block
			// is exited before the `err` string is overwritten.
			const parsed = JSON.parse(err);

			err = parsed;
		} catch(error) {
			
		} // The catch behavior is established before the try block.

		const errorMessage = err.message || err;

		// The popup function expects an element, so a div containing the error
		// message is created here for it to display in a modal.
		const div = document.createElement('div');

		if (err.unlock_url && err.unlock_url !== '') {
			div.innerHTML += `<p>${errorMessage} <a href="${err.unlock_url}&destination=admin/modules/browse">${Drupal.t('Unlock Install Stage')}</a></p>`;
		} else {
			div.innerHTML += `<p>${errorMessage}</p>`;
		}

		(0,_popup__WEBPACK_IMPORTED_MODULE_1__.openPopup)(div, {
			...project,
			title: `Error while installing ${project.title}`
		});
	};

	/**
 * Installs an already downloaded module.
 */
	async function installModule() {
		$$invalidate(5, loading = true);
		const url = `${_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL}/admin/modules/project_browser/activate-module/${project.project_machine_name}`;
		const installResponse = await fetch(url);

		if (!installResponse.ok) {
			handleError(installResponse);
			$$invalidate(5, loading = false);
			return;
		}

		let responseContent = await installResponse.text();

		try {
			const parsedJson = JSON.parse(responseContent);
			responseContent = parsedJson;
		} catch(err) {
			handleError(installResponse);
		}

		if (responseContent.status === 0) {
			_constants__WEBPACK_IMPORTED_MODULE_2__.MODULE_STATUS[project.project_machine_name] = 1;
			$$invalidate(6, projectInstalled = true);
			$$invalidate(5, loading = false);
		}
	}

	/**
 * Uses package manager to download a module using Composer.
 *
 * @param {boolean} install
 *   If true, the module will be installed after it is downlaoded.
 */
	function downloadModule(install = false) {
		showStatus(true);

		/**
 * Performs the requests necessary to download a module via Package Manager.
 *
 * @return {Promise<void>}
 *   No return, but is technically a Promise because this function is async.
 */
		async function doRequests() {
			$$invalidate(5, loading = true);
			const beginInstallUrl = `${_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL}/admin/modules/project_browser/install-begin/${project.composer_namespace}`;
			const beginInstallResponse = await fetch(beginInstallUrl);

			if (!beginInstallResponse.ok) {
				await handleError(beginInstallResponse);
			} else {
				const beginInstallJson = await beginInstallResponse.json();
				const stageId = beginInstallJson.stage_id;

				// The process of adding a module is separated into four stages, each
				// with their own endpoint. When one stage completes, the next one is
				// requested.
				const installSteps = [
					`${_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL}/admin/modules/project_browser/install-require/${project.composer_namespace}/${stageId}`,
					`${_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL}/admin/modules/project_browser/install-apply/${project.composer_namespace}/${stageId}`,
					`${_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL}/admin/modules/project_browser/install-post_apply/${project.composer_namespace}/${stageId}`,
					`${_constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL}/admin/modules/project_browser/install-destroy/${project.composer_namespace}/${stageId}`
				];

				// eslint-disable-next-line no-restricted-syntax,guard-for-in
				for (const step in installSteps) {
					// eslint-disable-next-line no-await-in-loop
					const stepResponse = await fetch(installSteps[step]);

					if (!stepResponse.ok) {
						// eslint-disable-next-line no-await-in-loop
						const errorMessage = await stepResponse.text();

						// eslint-disable-next-line no-console
						console.warn(`failed request to ${installSteps[step]}: ${errorMessage}`, stepResponse);

						// eslint-disable-next-line no-await-in-loop
						await handleError(errorMessage);

						return;
					}
				}

				// If this line is reached, then every stage of the download process
				// was completed without error and we can consider the module
				// downloaded and the process complete.
				_constants__WEBPACK_IMPORTED_MODULE_2__.MODULE_STATUS[project.project_machine_name] = 0;

				$$invalidate(7, projectDownloaded = true);
				$$invalidate(5, loading = false);

				// If install is true, install the module before conveying the process
				// is complete to the UI.
				if (install === true) {
					installModule();
				}
			}
		}

		// Begin the install process, which is contained in the doRequests()
		// function so it can be async without its parent function having to be.
		doRequests();
	}

	$$self.$$.on_mount.push(function () {
		if (project === undefined && !('project' in $$props || $$self.$$.bound[$$self.$$.props['project']])) {
			console_1.warn("<AddInstallButton> was created without expected prop 'project'");
		}

		if (loading === undefined && !('loading' in $$props || $$self.$$.bound[$$self.$$.props['loading']])) {
			console_1.warn("<AddInstallButton> was created without expected prop 'loading'");
		}

		if (projectInstalled === undefined && !('projectInstalled' in $$props || $$self.$$.bound[$$self.$$.props['projectInstalled']])) {
			console_1.warn("<AddInstallButton> was created without expected prop 'projectInstalled'");
		}

		if (projectDownloaded === undefined && !('projectDownloaded' in $$props || $$self.$$.bound[$$self.$$.props['projectDownloaded']])) {
			console_1.warn("<AddInstallButton> was created without expected prop 'projectDownloaded'");
		}

		if (showStatus === undefined && !('showStatus' in $$props || $$self.$$.bound[$$self.$$.props['showStatus']])) {
			console_1.warn("<AddInstallButton> was created without expected prop 'showStatus'");
		}
	});

	const writable_props = [
		'project',
		'loading',
		'projectInstalled',
		'projectDownloaded',
		'showStatus',
		'alreadyAdded'
	];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console_1.warn(`<AddInstallButton> was created with unknown prop '${key}'`);
	});

	const func = () => {
		if (alreadyAdded) {
			installModule();
		} else {
			downloadModule(true);
		}
	};

	$$self.$$set = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
		if ('loading' in $$props) $$invalidate(5, loading = $$props.loading);
		if ('projectInstalled' in $$props) $$invalidate(6, projectInstalled = $$props.projectInstalled);
		if ('projectDownloaded' in $$props) $$invalidate(7, projectDownloaded = $$props.projectDownloaded);
		if ('showStatus' in $$props) $$invalidate(8, showStatus = $$props.showStatus);
		if ('alreadyAdded' in $$props) $$invalidate(1, alreadyAdded = $$props.alreadyAdded);
	};

	$$self.$capture_state = () => ({
		openPopup: _popup__WEBPACK_IMPORTED_MODULE_1__.openPopup,
		MODULE_STATUS: _constants__WEBPACK_IMPORTED_MODULE_2__.MODULE_STATUS,
		ORIGIN_URL: _constants__WEBPACK_IMPORTED_MODULE_2__.ORIGIN_URL,
		PM_VALIDATION_ERROR: _constants__WEBPACK_IMPORTED_MODULE_2__.PM_VALIDATION_ERROR,
		ProjectButtonBase: _ProjectButtonBase_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		project,
		loading,
		projectInstalled,
		projectDownloaded,
		showStatus,
		alreadyAdded,
		Drupal,
		handleError,
		installModule,
		downloadModule
	});

	$$self.$inject_state = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
		if ('loading' in $$props) $$invalidate(5, loading = $$props.loading);
		if ('projectInstalled' in $$props) $$invalidate(6, projectInstalled = $$props.projectInstalled);
		if ('projectDownloaded' in $$props) $$invalidate(7, projectDownloaded = $$props.projectDownloaded);
		if ('showStatus' in $$props) $$invalidate(8, showStatus = $$props.showStatus);
		if ('alreadyAdded' in $$props) $$invalidate(1, alreadyAdded = $$props.alreadyAdded);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [
		project,
		alreadyAdded,
		Drupal,
		installModule,
		downloadModule,
		loading,
		projectInstalled,
		projectDownloaded,
		showStatus,
		func
	];
}

class AddInstallButton extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, {
			project: 0,
			loading: 5,
			projectInstalled: 6,
			projectDownloaded: 7,
			showStatus: 8,
			alreadyAdded: 1
		});

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "AddInstallButton",
			options,
			id: create_fragment.name
		});
	}

	get project() {
		throw new Error("<AddInstallButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set project(value) {
		throw new Error("<AddInstallButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get loading() {
		throw new Error("<AddInstallButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set loading(value) {
		throw new Error("<AddInstallButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get projectInstalled() {
		throw new Error("<AddInstallButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set projectInstalled(value) {
		throw new Error("<AddInstallButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get projectDownloaded() {
		throw new Error("<AddInstallButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set projectDownloaded(value) {
		throw new Error("<AddInstallButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get showStatus() {
		throw new Error("<AddInstallButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set showStatus(value) {
		throw new Error("<AddInstallButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get alreadyAdded() {
		throw new Error("<AddInstallButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set alreadyAdded(value) {
		throw new Error("<AddInstallButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AddInstallButton);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/Categories.svelte":
/*!************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/Categories.svelte ***!
  \************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/Categories.svelte generated by Svelte v3.56.0 */


const file = "modules/project_browser/sveltejs/src/Project/Categories.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1dogn30", ".categories.pb-1dogn30.pb-1dogn30{clear:both;margin-top:1em;position:center}.categories__list.pb-1dogn30.pb-1dogn30{margin:0.25em 0 0.25em 0;padding:0;display:inline-block;width:100%;height:20px}.categories__category.pb-1dogn30.pb-1dogn30{list-style:none;display:inline-block;margin-top:2px;margin-bottom:2px;padding:2px 9px;border-radius:25px;background-color:#e5e5e5;font-size:0.9em;font-weight:600;color:#4f4f4f}.categories__category.pb-1dogn30.pb-1dogn30:not(:first-child, .categories__category--extra){margin-inline-start:4px}.categories.pb-1dogn30 .categories__category--extra.pb-1dogn30{background-color:transparent;border:1px solid #bbb}.categories__list--grid.pb-1dogn30.pb-1dogn30{text-align:center}@media(forced-colors: active){.categories__category.pb-1dogn30.pb-1dogn30{border:1px solid}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiQ2F0ZWdvcmllcy5zdmVsdGUiLCJzb3VyY2VzIjpbIkNhdGVnb3JpZXMuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBpbXBvcnQvbm8tbXV0YWJsZS1leHBvcnRzLGltcG9ydC9wcmVmZXItZGVmYXVsdC1leHBvcnRcbiAgZXhwb3J0IGxldCBtb2R1bGVDYXRlZ29yaWVzO1xuICBleHBvcnQgbGV0IHRvZ2dsZVZpZXc7XG5cbiAgY29uc3QgZXh0cmFDYXRlZ29yaWVzID0gbW9kdWxlQ2F0ZWdvcmllcy5zcGxpY2UoMyk7XG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG48L3NjcmlwdD5cblxuPGRpdiBjbGFzcz1cImNhdGVnb3JpZXNcIiBkYXRhLWxhYmVsPVwiQ2F0ZWdvcmllc1wiPlxuICB7I2lmIHR5cGVvZiBtb2R1bGVDYXRlZ29yaWVzICE9PSAndW5kZWZpbmVkJyAmJiBtb2R1bGVDYXRlZ29yaWVzLmxlbmd0aH1cbiAgICA8dWxcbiAgICAgIGNsYXNzOmNhdGVnb3JpZXNfX2xpc3QtLWdyaWQ9e3RvZ2dsZVZpZXcgPT09ICdHcmlkJ31cbiAgICAgIGNsYXNzPVwiY2F0ZWdvcmllc19fbGlzdFwiXG4gICAgPlxuICAgICAgeyNlYWNoIG1vZHVsZUNhdGVnb3JpZXMgfHwgW10gYXMgY2F0ZWdvcnl9XG4gICAgICAgIDxsaSBjbGFzcz1cImNhdGVnb3JpZXNfX2NhdGVnb3J5XCI+XG4gICAgICAgICAge2NhdGVnb3J5Lm5hbWV9XG4gICAgICAgIDwvbGk+XG4gICAgICB7L2VhY2h9XG4gICAgICB7I2lmIGV4dHJhQ2F0ZWdvcmllcy5sZW5ndGh9XG4gICAgICAgIDxsaSBjbGFzcz1cImNhdGVnb3JpZXNfX2NhdGVnb3J5IGNhdGVnb3JpZXNfX2NhdGVnb3J5LS1leHRyYVwiPlxuICAgICAgICAgIHtEcnVwYWwudCgnKyBAY291bnQgbW9yZScsIHsgJ0Bjb3VudCc6IGV4dHJhQ2F0ZWdvcmllcy5sZW5ndGggfSl9XG4gICAgICAgIDwvbGk+XG4gICAgICB7L2lmfVxuICAgIDwvdWw+XG4gIHsvaWZ9XG48L2Rpdj5cblxuPHN0eWxlPlxuICAuY2F0ZWdvcmllcyB7XG4gICAgY2xlYXI6IGJvdGg7XG4gICAgbWFyZ2luLXRvcDogMWVtO1xuICAgIHBvc2l0aW9uOiBjZW50ZXI7XG4gIH1cbiAgLmNhdGVnb3JpZXNfX2xpc3Qge1xuICAgIG1hcmdpbjogMC4yNWVtIDAgMC4yNWVtIDA7XG4gICAgcGFkZGluZzogMDtcbiAgICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7XG4gICAgd2lkdGg6IDEwMCU7XG4gICAgaGVpZ2h0OiAyMHB4O1xuICB9XG4gIC5jYXRlZ29yaWVzX19jYXRlZ29yeSB7XG4gICAgbGlzdC1zdHlsZTogbm9uZTtcbiAgICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7XG4gICAgbWFyZ2luLXRvcDogMnB4O1xuICAgIG1hcmdpbi1ib3R0b206IDJweDtcbiAgICBwYWRkaW5nOiAycHggOXB4O1xuICAgIGJvcmRlci1yYWRpdXM6IDI1cHg7XG4gICAgYmFja2dyb3VuZC1jb2xvcjogI2U1ZTVlNTtcbiAgICBmb250LXNpemU6IDAuOWVtO1xuICAgIGZvbnQtd2VpZ2h0OiA2MDA7XG4gICAgY29sb3I6ICM0ZjRmNGY7XG4gIH1cbiAgLmNhdGVnb3JpZXNfX2NhdGVnb3J5Om5vdCg6Zmlyc3QtY2hpbGQsIC5jYXRlZ29yaWVzX19jYXRlZ29yeS0tZXh0cmEpIHtcbiAgICBtYXJnaW4taW5saW5lLXN0YXJ0OiA0cHg7XG4gIH1cbiAgLmNhdGVnb3JpZXMgLmNhdGVnb3JpZXNfX2NhdGVnb3J5LS1leHRyYSB7XG4gICAgYmFja2dyb3VuZC1jb2xvcjogdHJhbnNwYXJlbnQ7XG4gICAgYm9yZGVyOiAxcHggc29saWQgI2JiYjtcbiAgfVxuICAuY2F0ZWdvcmllc19fbGlzdC0tZ3JpZCB7XG4gICAgdGV4dC1hbGlnbjogY2VudGVyO1xuICB9XG4gIEBtZWRpYSAoZm9yY2VkLWNvbG9yczogYWN0aXZlKSB7XG4gICAgLmNhdGVnb3JpZXNfX2NhdGVnb3J5IHtcbiAgICAgIGJvcmRlcjogMXB4IHNvbGlkO1xuICAgIH1cbiAgfVxuPC9zdHlsZT5cbiJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUE4QkUsaUNBQVksQ0FDVixLQUFLLENBQUUsSUFBSSxDQUNYLFVBQVUsQ0FBRSxHQUFHLENBQ2YsUUFBUSxDQUFFLE1BQ1osQ0FDQSx1Q0FBa0IsQ0FDaEIsTUFBTSxDQUFFLE1BQU0sQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FDekIsT0FBTyxDQUFFLENBQUMsQ0FDVixPQUFPLENBQUUsWUFBWSxDQUNyQixLQUFLLENBQUUsSUFBSSxDQUNYLE1BQU0sQ0FBRSxJQUNWLENBQ0EsMkNBQXNCLENBQ3BCLFVBQVUsQ0FBRSxJQUFJLENBQ2hCLE9BQU8sQ0FBRSxZQUFZLENBQ3JCLFVBQVUsQ0FBRSxHQUFHLENBQ2YsYUFBYSxDQUFFLEdBQUcsQ0FDbEIsT0FBTyxDQUFFLEdBQUcsQ0FBQyxHQUFHLENBQ2hCLGFBQWEsQ0FBRSxJQUFJLENBQ25CLGdCQUFnQixDQUFFLE9BQU8sQ0FDekIsU0FBUyxDQUFFLEtBQUssQ0FDaEIsV0FBVyxDQUFFLEdBQUcsQ0FDaEIsS0FBSyxDQUFFLE9BQ1QsQ0FDQSwyQ0FBcUIsS0FBSyxZQUFZLEVBQUUsNEJBQTRCLENBQUUsQ0FDcEUsbUJBQW1CLENBQUUsR0FDdkIsQ0FDQSxzQkFBVyxDQUFDLHVDQUE2QixDQUN2QyxnQkFBZ0IsQ0FBRSxXQUFXLENBQzdCLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FBSyxDQUFDLElBQ3BCLENBQ0EsNkNBQXdCLENBQ3RCLFVBQVUsQ0FBRSxNQUNkLENBQ0EsTUFBTyxnQkFBZ0IsTUFBTSxDQUFFLENBQzdCLDJDQUFzQixDQUNwQixNQUFNLENBQUUsR0FBRyxDQUFDLEtBQ2QsQ0FDRiJ9 */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[4] = list[i];
	return child_ctx;
}

// (11:2) {#if typeof moduleCategories !== 'undefined' && moduleCategories.length}
function create_if_block(ctx) {
	let ul;
	let t;
	let each_value = /*moduleCategories*/ ctx[0] || [];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	let if_block = /*extraCategories*/ ctx[2].length && create_if_block_1(ctx);

	const block = {
		c: function create() {
			ul = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("ul");

			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block) if_block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(ul, "class", "categories__list pb-1dogn30");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(ul, "categories__list--grid", /*toggleView*/ ctx[1] === 'Grid');
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(ul, file, 11, 4, 373);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, ul, anchor);

			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(ul, null);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(ul, t);
			if (if_block) if_block.m(ul, null);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*moduleCategories*/ 1) {
				each_value = /*moduleCategories*/ ctx[0] || [];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						each_blocks[i].m(ul, t);
					}
				}

				for (; i < each_blocks.length; i += 1) {
					each_blocks[i].d(1);
				}

				each_blocks.length = each_value.length;
			}

			if (/*extraCategories*/ ctx[2].length) if_block.p(ctx, dirty);

			if (dirty & /*toggleView*/ 2) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(ul, "categories__list--grid", /*toggleView*/ ctx[1] === 'Grid');
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(ul);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
			if (if_block) if_block.d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(11:2) {#if typeof moduleCategories !== 'undefined' && moduleCategories.length}",
		ctx
	});

	return block;
}

// (16:6) {#each moduleCategories || [] as category}
function create_each_block(ctx) {
	let li;
	let t_value = /*category*/ ctx[4].name + "";
	let t;

	const block = {
		c: function create() {
			li = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("li");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", "categories__category pb-1dogn30");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(li, file, 16, 8, 530);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, li, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, t);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*moduleCategories*/ 1 && t_value !== (t_value = /*category*/ ctx[4].name + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(li);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(16:6) {#each moduleCategories || [] as category}",
		ctx
	});

	return block;
}

// (21:6) {#if extraCategories.length}
function create_if_block_1(ctx) {
	let li;

	const block = {
		c: function create() {
			li = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("li");

			li.textContent = `${/*Drupal*/ ctx[3].t('+ @count more', {
				'@count': /*extraCategories*/ ctx[2].length
			})}`;

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", "categories__category categories__category--extra pb-1dogn30");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(li, file, 21, 8, 661);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, li, anchor);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(li);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(21:6) {#if extraCategories.length}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let div;
	let if_block = typeof /*moduleCategories*/ ctx[0] !== 'undefined' && /*moduleCategories*/ ctx[0].length && create_if_block(ctx);

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if (if_block) if_block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "categories pb-1dogn30");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "data-label", "Categories");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 9, 0, 245);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			if (if_block) if_block.m(div, null);
		},
		p: function update(ctx, [dirty]) {
			if (typeof /*moduleCategories*/ ctx[0] !== 'undefined' && /*moduleCategories*/ ctx[0].length) {
				if (if_block) {
					if_block.p(ctx, dirty);
				} else {
					if_block = create_if_block(ctx);
					if_block.c();
					if_block.m(div, null);
				}
			} else if (if_block) {
				if_block.d(1);
				if_block = null;
			}
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			if (if_block) if_block.d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('Categories', slots, []);
	let { moduleCategories } = $$props;
	let { toggleView } = $$props;
	const extraCategories = moduleCategories.splice(3);
	const { Drupal } = window;

	$$self.$$.on_mount.push(function () {
		if (moduleCategories === undefined && !('moduleCategories' in $$props || $$self.$$.bound[$$self.$$.props['moduleCategories']])) {
			console.warn("<Categories> was created without expected prop 'moduleCategories'");
		}

		if (toggleView === undefined && !('toggleView' in $$props || $$self.$$.bound[$$self.$$.props['toggleView']])) {
			console.warn("<Categories> was created without expected prop 'toggleView'");
		}
	});

	const writable_props = ['moduleCategories', 'toggleView'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Categories> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('moduleCategories' in $$props) $$invalidate(0, moduleCategories = $$props.moduleCategories);
		if ('toggleView' in $$props) $$invalidate(1, toggleView = $$props.toggleView);
	};

	$$self.$capture_state = () => ({
		moduleCategories,
		toggleView,
		extraCategories,
		Drupal
	});

	$$self.$inject_state = $$props => {
		if ('moduleCategories' in $$props) $$invalidate(0, moduleCategories = $$props.moduleCategories);
		if ('toggleView' in $$props) $$invalidate(1, toggleView = $$props.toggleView);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [moduleCategories, toggleView, extraCategories, Drupal];
}

class Categories extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { moduleCategories: 0, toggleView: 1 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "Categories",
			options,
			id: create_fragment.name
		});
	}

	get moduleCategories() {
		throw new Error("<Categories>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set moduleCategories(value) {
		throw new Error("<Categories>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get toggleView() {
		throw new Error("<Categories>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set toggleView(value) {
		throw new Error("<Categories>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Categories);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/Image.svelte":
/*!*******************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/Image.svelte ***!
  \*******************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/Image.svelte generated by Svelte v3.56.0 */


const { Error: Error_1 } = svelte_internal__WEBPACK_IMPORTED_MODULE_0__.globals;

const file = "modules/project_browser/sveltejs/src/Project/Image.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-2sbg4a", ".image-carousel__slider-image, .project__logo{display:block;margin-left:auto;margin-right:auto;width:50%;object-fit:scale-down}@media only screen and (min-width: 600px){.image-carousel__slider-image, .project__logo{display:block;width:auto;border-radius:5px;height:100px;margin-top:20px}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiSW1hZ2Uuc3ZlbHRlIiwic291cmNlcyI6WyJJbWFnZS5zdmVsdGUiXSwic291cmNlc0NvbnRlbnQiOlsiPHNjcmlwdD5cbiAgaW1wb3J0IHsgRlVMTF9NT0RVTEVfUEFUSCB9IGZyb20gJy4uL2NvbnN0YW50cyc7XG5cbiAgZXhwb3J0IGFzeW5jIGZ1bmN0aW9uIGZldGNoRW50aXR5KHVyaSkge1xuICAgIGxldCBkYXRhO1xuICAgIGNvbnN0IHJlc3BvbnNlID0gYXdhaXQgZmV0Y2goYCR7dXJpfS5qc29uYCk7XG4gICAgaWYgKHJlc3BvbnNlLm9rKSB7XG4gICAgICBkYXRhID0gYXdhaXQgcmVzcG9uc2UuanNvbigpO1xuICAgICAgcmV0dXJuIGRhdGE7XG4gICAgfVxuICAgIHRocm93IG5ldyBFcnJvcignQ291bGQgbm90IGxvYWQgZW50aXR5Jyk7XG4gIH1cblxuICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgaW1wb3J0L25vLW11dGFibGUtZXhwb3J0cyxpbXBvcnQvcHJlZmVyLWRlZmF1bHQtZXhwb3J0XG4gIGV4cG9ydCBsZXQgc291cmNlcztcbiAgZXhwb3J0IGxldCBpbmRleCA9IDA7XG5cbiAgY29uc3Qgbm9ybWFsaXplZFNvdXJjZXMgPSBzb3VyY2VzID8gW3NvdXJjZXNdLmZsYXQoKSA6IFtdO1xuICBjb25zdCB7IERydXBhbCB9ID0gd2luZG93O1xuICBjb25zdCBmYWxsYmFja0ltYWdlID0gYCR7RlVMTF9NT0RVTEVfUEFUSH0vaW1hZ2VzL3B1enpsZS1waWVjZS1wbGFjZWhvbGRlci5zdmdgO1xuICBjb25zdCBzaG93RmFsbGJhY2sgPSAoZXYpID0+IHtcbiAgICBldi50YXJnZXQuc3JjID0gZmFsbGJhY2tJbWFnZTtcbiAgfTtcblxuICAvKipcbiAgICogUHJvcHMgZm9yIHRoZSBpbWFnZXMgdXNlZCBpbiB0aGUgY2Fyb3VzZWwuXG4gICAqXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBzcmNcbiAgICogICBUaGUgc291cmNlIGF0dHJpYnV0ZS5cbiAgICogQHBhcmFtIHtzdHJpbmd9IGFsdFxuICAgKiAgIFRoZSBhbHQgYXR0cmlidXRlLCBkZWZhdWx0cyB0byAnUGxhY2Vob2xkZXInIGlmIHVuZGVmaW5lZC5cbiAgICpcbiAgICogQHJldHVybiB7e3NyYywgYWx0OiBzdHJpbmcsIGNsYXNzOiBzdHJpbmd9fVxuICAgKiAgIEFuIG9iamVjdCBvZiBlbGVtZW50IGF0dHJpYnV0ZXNcbiAgICovXG4gIGNvbnN0IGRlZmF1bHRJbWdQcm9wcyA9IChzcmMsIGFsdCkgPT4gKHtcbiAgICBzcmMsXG4gICAgYWx0OiB0eXBlb2YgYWx0ICE9PSAndW5kZWZpbmVkJyA/IGFsdCA6IERydXBhbC50KCdQbGFjZWhvbGRlcicpLFxuICAgIGNsYXNzOiBgJHskJHByb3BzLmNsYXNzfSBgLFxuICB9KTtcbjwvc2NyaXB0PlxuXG48IS0tIHN2ZWx0ZS1pZ25vcmUgYTExeS1taXNzaW5nLWF0dHJpYnV0ZSAtLT5cbnsjaWYgbm9ybWFsaXplZFNvdXJjZXMubGVuZ3RofVxuICB7I2lmIG5vcm1hbGl6ZWRTb3VyY2VzW2luZGV4XS5maWxlLnJlc291cmNlID09PSAnaW1hZ2UnfVxuICAgIDxpbWdcbiAgICAgIHNyYz17bm9ybWFsaXplZFNvdXJjZXNbaW5kZXhdLmZpbGUudXJpfVxuICAgICAgYWx0PVwiXCJcbiAgICAgIG9uOmVycm9yPXtzaG93RmFsbGJhY2t9XG4gICAgICBjbGFzcz17JCRwcm9wcy5jbGFzc31cbiAgICAvPlxuICB7OmVsc2UgaWYgKG5vcm1hbGl6ZWRTb3VyY2VzW2luZGV4XS5maWxlLnJlc291cmNlID0gJ2ZpbGUnKX1cbiAgICA8IS0tIEtlZXBpbmcgdGhpcyBibG9jayBmb3IgY29tcGF0aWJpbGl0eSB3aXRoIHRoZSBtb2NrYXBpLiAtLT5cbiAgICB7I2F3YWl0IGZldGNoRW50aXR5KG5vcm1hbGl6ZWRTb3VyY2VzW2luZGV4XS5maWxlLnVyaSl9XG4gICAgICA8aW1nIHsuLi5kZWZhdWx0SW1nUHJvcHMoZmFsbGJhY2tJbWFnZSl9IC8+XG4gICAgezp0aGVuIGZpbGV9XG4gICAgICA8aW1nIHsuLi5kZWZhdWx0SW1nUHJvcHMoZmlsZS51cmwsICcnKX0gb246ZXJyb3I9e3Nob3dGYWxsYmFja30gYWx0PVwiXCIgLz5cbiAgICB7OmNhdGNoIGVycm9yfVxuICAgICAgPHNwYW4gY2xhc3M9XCJpbWFnZV9lcnJvclwiIHN0eWxlPVwiY29sb3I6IHJlZFwiPntlcnJvci5tZXNzYWdlfTwvc3Bhbj5cbiAgICB7L2F3YWl0fVxuICB7OmVsc2V9XG4gICAgPGltZyB7Li4uZGVmYXVsdEltZ1Byb3BzKGZhbGxiYWNrSW1hZ2UpfSAvPlxuICB7L2lmfVxuezplbHNlfVxuICA8aW1nIHsuLi5kZWZhdWx0SW1nUHJvcHMoZmFsbGJhY2tJbWFnZSl9IC8+XG57L2lmfVxuXG48c3R5bGU+XG4gIDpnbG9iYWwoLmltYWdlLWNhcm91c2VsX19zbGlkZXItaW1hZ2UsIC5wcm9qZWN0X19sb2dvKSB7XG4gICAgZGlzcGxheTogYmxvY2s7XG4gICAgbWFyZ2luLWxlZnQ6IGF1dG87XG4gICAgbWFyZ2luLXJpZ2h0OiBhdXRvO1xuICAgIHdpZHRoOiA1MCU7XG4gICAgb2JqZWN0LWZpdDogc2NhbGUtZG93bjtcbiAgfVxuICAvKiBTbWFsbCBkZXZpY2VzIChwb3J0cmFpdCB0YWJsZXRzIGFuZCBsYXJnZSBwaG9uZXMsIDYwMHB4IGFuZCB1cCkgKi9cbiAgQG1lZGlhIG9ubHkgc2NyZWVuIGFuZCAobWluLXdpZHRoOiA2MDBweCkge1xuICAgIDpnbG9iYWwoLmltYWdlLWNhcm91c2VsX19zbGlkZXItaW1hZ2UsIC5wcm9qZWN0X19sb2dvKSB7XG4gICAgICBkaXNwbGF5OiBibG9jaztcbiAgICAgIHdpZHRoOiBhdXRvO1xuICAgICAgYm9yZGVyLXJhZGl1czogNXB4O1xuICAgICAgaGVpZ2h0OiAxMDBweDtcbiAgICAgIG1hcmdpbi10b3A6IDIwcHg7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQW9FVSw2Q0FBK0MsQ0FDckQsT0FBTyxDQUFFLEtBQUssQ0FDZCxXQUFXLENBQUUsSUFBSSxDQUNqQixZQUFZLENBQUUsSUFBSSxDQUNsQixLQUFLLENBQUUsR0FBRyxDQUNWLFVBQVUsQ0FBRSxVQUNkLENBRUEsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQyxZQUFZLEtBQUssQ0FBRSxDQUNoQyw2Q0FBK0MsQ0FDckQsT0FBTyxDQUFFLEtBQUssQ0FDZCxLQUFLLENBQUUsSUFBSSxDQUNYLGFBQWEsQ0FBRSxHQUFHLENBQ2xCLE1BQU0sQ0FBRSxLQUFLLENBQ2IsVUFBVSxDQUFFLElBQ2QsQ0FDRiJ9 */");
}

// (64:0) {:else}
function create_else_block_1(ctx) {
	let img;
	let img_levels = [/*defaultImgProps*/ ctx[5](/*fallbackImage*/ ctx[3])];
	let img_data = {};

	for (let i = 0; i < img_levels.length; i += 1) {
		img_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(img_data, img_levels[i]);
	}

	const block = {
		c: function create() {
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(img, img_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 64, 2, 1943);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, img, anchor);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(img);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block_1.name,
		type: "else",
		source: "(64:0) {:else}",
		ctx
	});

	return block;
}

// (44:0) {#if normalizedSources.length}
function create_if_block(ctx) {
	let if_block_anchor;

	function select_block_type_1(ctx, dirty) {
		if (/*normalizedSources*/ ctx[2][/*index*/ ctx[1]].file.resource === 'image') return create_if_block_1;
		if (/*normalizedSources*/ ctx[2][/*index*/ ctx[1]].file.resource = 'file') return create_if_block_2;
		return create_else_block;
	}

	let current_block_type = select_block_type_1(ctx, -1);
	let if_block = current_block_type(ctx);

	const block = {
		c: function create() {
			if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
		},
		p: function update(ctx, dirty) {
			if (current_block_type === (current_block_type = select_block_type_1(ctx, dirty)) && if_block) {
				if_block.p(ctx, dirty);
			} else {
				if_block.d(1);
				if_block = current_block_type(ctx);

				if (if_block) {
					if_block.c();
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			}
		},
		d: function destroy(detaching) {
			if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(44:0) {#if normalizedSources.length}",
		ctx
	});

	return block;
}

// (61:2) {:else}
function create_else_block(ctx) {
	let img;
	let img_levels = [/*defaultImgProps*/ ctx[5](/*fallbackImage*/ ctx[3])];
	let img_data = {};

	for (let i = 0; i < img_levels.length; i += 1) {
		img_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(img_data, img_levels[i]);
	}

	const block = {
		c: function create() {
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(img, img_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 61, 4, 1881);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, img, anchor);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(img);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block.name,
		type: "else",
		source: "(61:2) {:else}",
		ctx
	});

	return block;
}

// (52:62) 
function create_if_block_2(ctx) {
	let await_block_anchor;
	let promise;

	let info = {
		ctx,
		current: null,
		token: null,
		hasCatch: true,
		pending: create_pending_block,
		then: create_then_block,
		catch: create_catch_block,
		value: 9,
		error: 10
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.handle_promise)(promise = fetchEntity(/*normalizedSources*/ ctx[2][/*index*/ ctx[1]].file.uri), info);

	const block = {
		c: function create() {
			await_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
			info.block.c();
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, await_block_anchor, anchor);
			info.block.m(target, info.anchor = anchor);
			info.mount = () => await_block_anchor.parentNode;
			info.anchor = await_block_anchor;
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;
			info.ctx = ctx;

			if (dirty & /*index*/ 2 && promise !== (promise = fetchEntity(/*normalizedSources*/ ctx[2][/*index*/ ctx[1]].file.uri)) && (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.handle_promise)(promise, info)) {
				
			} else {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_await_block_branch)(info, ctx, dirty);
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(await_block_anchor);
			info.block.d(detaching);
			info.token = null;
			info = null;
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(52:62) ",
		ctx
	});

	return block;
}

// (45:2) {#if normalizedSources[index].file.resource === 'image'}
function create_if_block_1(ctx) {
	let img;
	let img_src_value;
	let img_class_value;
	let mounted;
	let dispose;

	const block = {
		c: function create() {
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = /*normalizedSources*/ ctx[2][/*index*/ ctx[1]].file.uri)) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "alt", "");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", img_class_value = /*$$props*/ ctx[6].class);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 45, 4, 1294);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, img, anchor);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(img, "error", /*showFallback*/ ctx[4], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			if (dirty & /*index*/ 2 && !(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = /*normalizedSources*/ ctx[2][/*index*/ ctx[1]].file.uri)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			}

			if (dirty & /*$$props*/ 64 && img_class_value !== (img_class_value = /*$$props*/ ctx[6].class)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", img_class_value);
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(img);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(45:2) {#if normalizedSources[index].file.resource === 'image'}",
		ctx
	});

	return block;
}

// (58:4) {:catch error}
function create_catch_block(ctx) {
	let span;
	let t_value = /*error*/ ctx[10].message + "";
	let t;

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "image_error");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_style)(span, "color", "red");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 58, 6, 1786);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*index*/ 2 && t_value !== (t_value = /*error*/ ctx[10].message + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_catch_block.name,
		type: "catch",
		source: "(58:4) {:catch error}",
		ctx
	});

	return block;
}

// (56:4) {:then file}
function create_then_block(ctx) {
	let img;
	let mounted;
	let dispose;
	let img_levels = [/*defaultImgProps*/ ctx[5](/*file*/ ctx[9].url, ''), { alt: "" }];
	let img_data = {};

	for (let i = 0; i < img_levels.length; i += 1) {
		img_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(img_data, img_levels[i]);
	}

	const block = {
		c: function create() {
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(img, img_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 56, 6, 1687);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, img, anchor);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(img, "error", /*showFallback*/ ctx[4], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(img, img_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_spread_update)(img_levels, [
				dirty & /*index*/ 2 && /*defaultImgProps*/ ctx[5](/*file*/ ctx[9].url, ''),
				{ alt: "" }
			]));
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(img);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_then_block.name,
		type: "then",
		source: "(56:4) {:then file}",
		ctx
	});

	return block;
}

// (54:59)        <img {...defaultImgProps(fallbackImage)}
function create_pending_block(ctx) {
	let img;
	let img_levels = [/*defaultImgProps*/ ctx[5](/*fallbackImage*/ ctx[3])];
	let img_data = {};

	for (let i = 0; i < img_levels.length; i += 1) {
		img_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(img_data, img_levels[i]);
	}

	const block = {
		c: function create() {
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(img, img_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 54, 6, 1620);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, img, anchor);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(img);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_pending_block.name,
		type: "pending",
		source: "(54:59)        <img {...defaultImgProps(fallbackImage)}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let if_block_anchor;

	function select_block_type(ctx, dirty) {
		if (/*normalizedSources*/ ctx[2].length) return create_if_block;
		return create_else_block_1;
	}

	let current_block_type = select_block_type(ctx, -1);
	let if_block = current_block_type(ctx);

	const block = {
		c: function create() {
			if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		l: function claim(nodes) {
			throw new Error_1("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
		},
		p: function update(ctx, [dirty]) {
			if_block.p(ctx, dirty);
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

async function fetchEntity(uri) {
	let data;
	const response = await fetch(`${uri}.json`);

	if (response.ok) {
		data = await response.json();
		return data;
	}

	throw new Error('Could not load entity');
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('Image', slots, []);
	let { sources } = $$props;
	let { index = 0 } = $$props;
	const normalizedSources = sources ? [sources].flat() : [];
	const { Drupal } = window;
	const fallbackImage = `${_constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH}/images/puzzle-piece-placeholder.svg`;

	const showFallback = ev => {
		ev.target.src = fallbackImage;
	};

	/**
 * Props for the images used in the carousel.
 *
 * @param {string} src
 *   The source attribute.
 * @param {string} alt
 *   The alt attribute, defaults to 'Placeholder' if undefined.
 *
 * @return {{src, alt: string, class: string}}
 *   An object of element attributes
 */
	const defaultImgProps = (src, alt) => ({
		src,
		alt: typeof alt !== 'undefined'
		? alt
		: Drupal.t('Placeholder'),
		class: `${$$props.class} `
	});

	$$self.$$.on_mount.push(function () {
		if (sources === undefined && !('sources' in $$props || $$self.$$.bound[$$self.$$.props['sources']])) {
			console.warn("<Image> was created without expected prop 'sources'");
		}
	});

	$$self.$$set = $$new_props => {
		$$invalidate(6, $$props = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)({}, $$props), (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.exclude_internal_props)($$new_props)));
		if ('sources' in $$new_props) $$invalidate(7, sources = $$new_props.sources);
		if ('index' in $$new_props) $$invalidate(1, index = $$new_props.index);
	};

	$$self.$capture_state = () => ({
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH,
		fetchEntity,
		sources,
		index,
		normalizedSources,
		Drupal,
		fallbackImage,
		showFallback,
		defaultImgProps
	});

	$$self.$inject_state = $$new_props => {
		$$invalidate(6, $$props = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)({}, $$props), $$new_props));
		if ('sources' in $$props) $$invalidate(7, sources = $$new_props.sources);
		if ('index' in $$props) $$invalidate(1, index = $$new_props.index);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	$$props = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.exclude_internal_props)($$props);

	return [
		fetchEntity,
		index,
		normalizedSources,
		fallbackImage,
		showFallback,
		defaultImgProps,
		$$props,
		sources
	];
}

class Image extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { fetchEntity: 0, sources: 7, index: 1 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "Image",
			options,
			id: create_fragment.name
		});
	}

	get fetchEntity() {
		return fetchEntity;
	}

	set fetchEntity(value) {
		throw new Error_1("<Image>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get sources() {
		throw new Error_1("<Image>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set sources(value) {
		throw new Error_1("<Image>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get index() {
		throw new Error_1("<Image>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set index(value) {
		throw new Error_1("<Image>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Image);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/LoadingEllipsis.svelte":
/*!*****************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/LoadingEllipsis.svelte ***!
  \*****************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/LoadingEllipsis.svelte generated by Svelte v3.56.0 */


const file = "modules/project_browser/sveltejs/src/Project/LoadingEllipsis.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-spe220", ".loading-ellipsis.pb-spe220{position:relative}.loading-ellipsis.pb-spe220:after{position:absolute;overflow:hidden;display:inline-block;vertical-align:bottom;-webkit-animation:pb-spe220-ellipsis steps(4, end) 900ms infinite;animation:pb-spe220-ellipsis steps(4, end) 900ms infinite;content:'\\2026';width:0}@keyframes pb-spe220-ellipsis{to{width:20px}}@-webkit-keyframes pb-spe220-ellipsis{to{width:20px}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiTG9hZGluZ0VsbGlwc2lzLnN2ZWx0ZSIsInNvdXJjZXMiOlsiTG9hZGluZ0VsbGlwc2lzLnN2ZWx0ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8c2NyaXB0PlxuICBjb25zdCB7IERydXBhbCB9ID0gd2luZG93O1xuICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgaW1wb3J0L3ByZWZlci1kZWZhdWx0LWV4cG9ydFxuICBleHBvcnQgbGV0IG1lc3NhZ2UgPSBEcnVwYWwudCgnSW5zdGFsbGluZycpO1xuPC9zY3JpcHQ+XG5cbjxzcGFuIGNsYXNzPVwibG9hZGluZy1lbGxpcHNpc1wiPnttZXNzYWdlfTwvc3Bhbj5cblxuPHN0eWxlPlxuICAubG9hZGluZy1lbGxpcHNpcyB7XG4gICAgcG9zaXRpb246IHJlbGF0aXZlO1xuICB9XG5cbiAgLmxvYWRpbmctZWxsaXBzaXM6YWZ0ZXIge1xuICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTtcbiAgICBvdmVyZmxvdzogaGlkZGVuO1xuICAgIGRpc3BsYXk6IGlubGluZS1ibG9jaztcbiAgICB2ZXJ0aWNhbC1hbGlnbjogYm90dG9tO1xuICAgIC13ZWJraXQtYW5pbWF0aW9uOiBlbGxpcHNpcyBzdGVwcyg0LCBlbmQpIDkwMG1zIGluZmluaXRlO1xuICAgIGFuaW1hdGlvbjogZWxsaXBzaXMgc3RlcHMoNCwgZW5kKSA5MDBtcyBpbmZpbml0ZTtcbiAgICBjb250ZW50OiAnXFwyMDI2JzsgLyogYXNjaWkgY29kZSBmb3IgdGhlIGVsbGlwc2lzIGNoYXJhY3RlciAqL1xuICAgIHdpZHRoOiAwO1xuICB9XG5cbiAgQGtleWZyYW1lcyBlbGxpcHNpcyB7XG4gICAgdG8ge1xuICAgICAgd2lkdGg6IDIwcHg7XG4gICAgfVxuICB9XG5cbiAgQC13ZWJraXQta2V5ZnJhbWVzIGVsbGlwc2lzIHtcbiAgICB0byB7XG4gICAgICB3aWR0aDogMjBweDtcbiAgICB9XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBU0UsMkJBQWtCLENBQ2hCLFFBQVEsQ0FBRSxRQUNaLENBRUEsMkJBQWlCLE1BQU8sQ0FDdEIsUUFBUSxDQUFFLFFBQVEsQ0FDbEIsUUFBUSxDQUFFLE1BQU0sQ0FDaEIsT0FBTyxDQUFFLFlBQVksQ0FDckIsY0FBYyxDQUFFLE1BQU0sQ0FDdEIsaUJBQWlCLENBQUUsa0JBQVEsQ0FBQyxNQUFNLENBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUFDLEtBQUssQ0FBQyxRQUFRLENBQ3hELFNBQVMsQ0FBRSxrQkFBUSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FDaEQsT0FBTyxDQUFFLE9BQU8sQ0FDaEIsS0FBSyxDQUFFLENBQ1QsQ0FFQSxXQUFXLGtCQUFTLENBQ2xCLEVBQUcsQ0FDRCxLQUFLLENBQUUsSUFDVCxDQUNGLENBRUEsbUJBQW1CLGtCQUFTLENBQzFCLEVBQUcsQ0FDRCxLQUFLLENBQUUsSUFDVCxDQUNGIn0= */");
}

function create_fragment(ctx) {
	let span;
	let t;

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(/*message*/ ctx[0]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "loading-ellipsis pb-spe220");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 6, 0, 155);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t);
		},
		p: function update(ctx, [dirty]) {
			if (dirty & /*message*/ 1) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, /*message*/ ctx[0]);
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('LoadingEllipsis', slots, []);
	const { Drupal } = window;
	let { message = Drupal.t('Installing') } = $$props;
	const writable_props = ['message'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<LoadingEllipsis> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('message' in $$props) $$invalidate(0, message = $$props.message);
	};

	$$self.$capture_state = () => ({ Drupal, message });

	$$self.$inject_state = $$props => {
		if ('message' in $$props) $$invalidate(0, message = $$props.message);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [message];
}

class LoadingEllipsis extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { message: 0 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "LoadingEllipsis",
			options,
			id: create_fragment.name
		});
	}

	get message() {
		throw new Error("<LoadingEllipsis>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set message(value) {
		throw new Error("<LoadingEllipsis>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (LoadingEllipsis);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/Project.svelte":
/*!*********************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/Project.svelte ***!
  \*********************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _ActionButton_svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActionButton.svelte */ "./modules/project_browser/sveltejs/src/Project/ActionButton.svelte");
/* harmony import */ var _Image_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Image.svelte */ "./modules/project_browser/sveltejs/src/Project/Image.svelte");
/* harmony import */ var _Categories_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Categories.svelte */ "./modules/project_browser/sveltejs/src/Project/Categories.svelte");
/* harmony import */ var _ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ProjectIcon.svelte */ "./modules/project_browser/sveltejs/src/Project/ProjectIcon.svelte");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/Project.svelte generated by Svelte v3.56.0 */








const file = "modules/project_browser/sveltejs/src/Project/Project.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1w8yugs", "@media only screen and (min-width: 53.75rem){.project--grid.pb-1w8yugs .project__logo.pb-1w8yugs{overflow:hidden;display:flex;align-items:center}.project--list.pb-1w8yugs .project__logo.pb-1w8yugs{margin-top:-20px;display:flex;align-items:center}.project--list.project.pb-1w8yugs.pb-1w8yugs{padding:0 2rem}}@media screen and (max-width: 75rem){.project--grid.project.pb-1w8yugs.pb-1w8yugs{width:100%}}@media only screen and (min-width: 75rem){.project--grid.project.pb-1w8yugs.pb-1w8yugs{width:49%}.project--list.project.pb-1w8yugs.pb-1w8yugs{width:100%;padding:0 2rem}}@media only screen and (min-width: 87.5rem){.project--grid.project.pb-1w8yugs.pb-1w8yugs{width:32%}}.project.pb-1w8yugs.pb-1w8yugs{position:relative}.project--grid.project.pb-1w8yugs.pb-1w8yugs{display:flex;flex-direction:column;margin-bottom:2em;border:1px solid rgba(212, 212, 218, 0.8);border-radius:2px;box-shadow:0 4px 10px rgb(0 0 0 / 10%);margin-inline-end:0.5vw}.project--grid.pb-1w8yugs .project__title.pb-1w8yugs{margin-top:0.25em;text-align:center}.project__link.pb-1w8yugs.pb-1w8yugs{text-decoration:none;color:black}.project__link.pb-1w8yugs.pb-1w8yugs:hover{color:#003ecc;text-decoration:underline}.project--grid.pb-1w8yugs .project__main.pb-1w8yugs{display:flex;background:white;position:relative;padding:1em 1em;flex-direction:column}.project--grid.pb-1w8yugs .project__body.pb-1w8yugs{text-align:center}.project--grid.pb-1w8yugs .project__icons.pb-1w8yugs{display:flex;margin-top:auto;padding:1em 1em;height:3em}.project__body.pb-1w8yugs.pb-1w8yugs{font-size:15px}.project--list.project.pb-1w8yugs.pb-1w8yugs{display:grid;grid-template-columns:20% 1fr;grid-template-rows:1fr;grid-template-areas:'aside main'\n      'aside footer'}.project--list.pb-1w8yugs .project__image.pb-1w8yugs{padding-inline-start:4em}.project--list.pb-1w8yugs .project__icons.pb-1w8yugs{grid-area:footer}.project--list.pb-1w8yugs .project__logo.pb-1w8yugs{grid-area:aside;padding-top:2rem;padding-bottom:2rem;padding-inline:0 2rem}.project--list.pb-1w8yugs .project__main.pb-1w8yugs{grid-area:main}.project--list.pb-1w8yugs .project__title.pb-1w8yugs{padding-top:10px}.project--list.project.pb-1w8yugs.pb-1w8yugs{margin-bottom:2em;background:#ffffff;border:0.5px solid #a4a2a2;box-shadow:0px 4px 4px rgba(0, 0, 0, 0.25)}.project--list.pb-1w8yugs .project__icons.pb-1w8yugs{display:flex;padding-top:1rem;padding-bottom:1rem;padding-inline:0 1em}.project__icons.pb-1w8yugs p{display:inline}.project__icons.warnings.pb-1w8yugs.pb-1w8yugs{display:block}.project__icons.warnings.pb-1w8yugs span.pb-1w8yugs{display:list-item}.project__icons.warnings.pb-1w8yugs img.pb-1w8yugs{display:inline;width:1.2rem;position:relative;bottom:-0.25rem}.warnings.pb-1w8yugs+.action{margin-inline-end:1em;margin-bottom:1em}.project__project-usage-container.pb-1w8yugs.pb-1w8yugs{display:flex;align-items:center;justify-content:center}.project__status-icon.pb-1w8yugs.pb-1w8yugs{width:2.4em;margin-inline-end:0.5em;display:block}.project__install-count.pb-1w8yugs.pb-1w8yugs{margin-inline-start:10px;font-size:13px}.project__active-installs-text.pb-1w8yugs.pb-1w8yugs{font-size:13px}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUHJvamVjdC5zdmVsdGUiLCJzb3VyY2VzIjpbIlByb2plY3Quc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBpbXBvcnQvbm8tbXV0YWJsZS1leHBvcnRzLGltcG9ydC9wcmVmZXItZGVmYXVsdC1leHBvcnRcbiAgZXhwb3J0IGxldCBwcm9qZWN0O1xuICBleHBvcnQgbGV0IHRvZ2dsZVZpZXc7XG4gIGltcG9ydCBBY3Rpb25CdXR0b24gZnJvbSAnLi9BY3Rpb25CdXR0b24uc3ZlbHRlJztcbiAgaW1wb3J0IEltYWdlIGZyb20gJy4vSW1hZ2Uuc3ZlbHRlJztcbiAgaW1wb3J0IENhdGVnb3JpZXMgZnJvbSAnLi9DYXRlZ29yaWVzLnN2ZWx0ZSc7XG4gIGltcG9ydCBQcm9qZWN0SWNvbiBmcm9tICcuL1Byb2plY3RJY29uLnN2ZWx0ZSc7XG4gIGltcG9ydCB7IGZvY3VzZWRFbGVtZW50IH0gZnJvbSAnLi4vc3RvcmVzJztcbiAgaW1wb3J0IHsgRlVMTF9NT0RVTEVfUEFUSCwgT1JJR0lOX1VSTCB9IGZyb20gJy4uL2NvbnN0YW50cyc7XG5cbiAgY29uc3QgeyBEcnVwYWwgfSA9IHdpbmRvdztcbjwvc2NyaXB0PlxuXG48bGkgY2xhc3M9XCJwcm9qZWN0IHByb2plY3QtLXt0b2dnbGVWaWV3LnRvTG93ZXJDYXNlKCl9XCI+XG4gIDxkaXYgY2xhc3M9XCJwcm9qZWN0X19sb2dvXCI+XG4gICAgPEltYWdlIHNvdXJjZXM9e3Byb2plY3QubG9nb30gY2xhc3M9XCJwcm9qZWN0X19sb2dvXCIgLz5cbiAgPC9kaXY+XG4gIDxkaXYgY2xhc3M9XCJwcm9qZWN0X19tYWluXCI+XG4gICAgPGRpdiBjbGFzcz1cInByb2plY3RfX21pZGRsZVwiPlxuICAgICAgPCEtLSBzdmVsdGUtaWdub3JlIGExMXktY2xpY2stZXZlbnRzLWhhdmUta2V5LWV2ZW50cyAtLT5cbiAgICAgIDxoM1xuICAgICAgICBvbjpjbGljaz17KCkgPT4ge1xuICAgICAgICAgICRmb2N1c2VkRWxlbWVudCA9IGAke3Byb2plY3QucHJvamVjdF9tYWNoaW5lX25hbWV9X3RpdGxlYDtcbiAgICAgICAgfX1cbiAgICAgICAgY2xhc3M9XCJwcm9qZWN0X190aXRsZVwiXG4gICAgICA+XG4gICAgICAgIDxhXG4gICAgICAgICAgaWQ9XCJ7cHJvamVjdC5wcm9qZWN0X21hY2hpbmVfbmFtZX1fdGl0bGVcIlxuICAgICAgICAgIGNsYXNzPVwicHJvamVjdF9fbGlua1wiXG4gICAgICAgICAgaHJlZj1cIntPUklHSU5fVVJMfS9hZG1pbi9tb2R1bGVzL2Jyb3dzZS97cHJvamVjdC5wcm9qZWN0X21hY2hpbmVfbmFtZX1cIlxuICAgICAgICAgIHJlbD1cIm5vcmVmZXJyZXJcIj57cHJvamVjdC50aXRsZX08L2FcbiAgICAgICAgPlxuICAgICAgPC9oMz5cbiAgICAgIDxkaXYgY2xhc3M9XCJwcm9qZWN0X19ib2R5XCI+e0BodG1sIHByb2plY3QuYm9keS5zdW1tYXJ5fTwvZGl2PlxuICAgICAgPENhdGVnb3JpZXMge3RvZ2dsZVZpZXd9IG1vZHVsZUNhdGVnb3JpZXM9e3Byb2plY3QubW9kdWxlX2NhdGVnb3JpZXN9IC8+XG4gICAgPC9kaXY+XG4gIDwvZGl2PlxuICA8ZGl2XG4gICAgY2xhc3M9XCJwcm9qZWN0X19pY29uc1wiXG4gICAgY2xhc3M6d2FybmluZ3M9e3Byb2plY3Qud2FybmluZ3MgJiYgcHJvamVjdC53YXJuaW5ncy5sZW5ndGggPiAwfVxuICA+XG4gICAgeyNpZiBwcm9qZWN0LmlzX2NvdmVyZWR9XG4gICAgICA8c3BhbiBjbGFzcz1cInByb2plY3RfX3N0YXR1cy1pY29uXCI+XG4gICAgICAgIDxQcm9qZWN0SWNvbiB0eXBlPVwic3RhdHVzXCIgLz5cbiAgICAgICAgPCEtLSBTaG93IHRoZSBzZWN1cml0eSBwb2xpY3kgZGVzY3JpcHRpb24gaWYgaXQgaXMgYWNjb21wYW5pZWQgYnkgd2FybmluZ3MsXG4gICAgICAgICAgICAgc2luY2UgdGhvc2UgYWxzbyBoYXZlIGRlc2NyaXB0aW9ucy4gIC0tPlxuICAgICAgICB7I2lmIHByb2plY3Qud2FybmluZ3MgJiYgcHJvamVjdC53YXJuaW5ncy5sZW5ndGggPiAwfVxuICAgICAgICAgIDxzbWFsbD57RHJ1cGFsLnQoJ0NvdmVyZWQgYnkgdGhlIHNlY3VyaXR5IGFkdmlzb3J5IHBvbGljeScpfTwvc21hbGw+XG4gICAgICAgIHsvaWZ9XG4gICAgICA8L3NwYW4+XG4gICAgey9pZn1cbiAgICB7I2lmIHRvZ2dsZVZpZXcgPT09ICdHcmlkJyAmJiBwcm9qZWN0LnByb2plY3RfdXNhZ2VfdG90YWwgIT09IC0xfVxuICAgICAgPGRpdiBjbGFzcz1cInByb2plY3RfX2luc3RhbGwtY291bnQtY29udGFpbmVyXCI+XG4gICAgICAgIDxzcGFuIGNsYXNzPVwicHJvamVjdF9faW5zdGFsbC1jb3VudFwiXG4gICAgICAgICAgPntEcnVwYWwudCgnQGNvdW50IGluc3RhbGxzICcsIHtcbiAgICAgICAgICAgICdAY291bnQnOiBwcm9qZWN0LnByb2plY3RfdXNhZ2VfdG90YWwudG9Mb2NhbGVTdHJpbmcoKSxcbiAgICAgICAgICB9KX08L3NwYW5cbiAgICAgICAgPlxuICAgICAgPC9kaXY+XG4gICAgey9pZn1cbiAgICB7I2lmIHByb2plY3Qud2FybmluZ3MgJiYgcHJvamVjdC53YXJuaW5ncy5sZW5ndGggPiAwfVxuICAgICAgeyNlYWNoIHByb2plY3Qud2FybmluZ3MgYXMgd2FybmluZ31cbiAgICAgICAgPHNwYW4gY2xhc3M9XCJwcm9qZWN0X19zdGF0dXMtaWNvblwiPlxuICAgICAgICAgIDxpbWcgc3JjPVwie0ZVTExfTU9EVUxFX1BBVEh9L2ltYWdlcy90cmlhbmdsZS1hbGVydC5zdmdcIiBhbHQ9XCJcIiAvPlxuICAgICAgICAgIDxzbWFsbD57QGh0bWwgd2FybmluZ308L3NtYWxsPlxuICAgICAgICA8L3NwYW4+XG4gICAgICB7L2VhY2h9XG4gICAgey9pZn1cbiAgICB7I2lmIHRvZ2dsZVZpZXcgPT09ICdMaXN0JyAmJiBwcm9qZWN0LnByb2plY3RfdXNhZ2VfdG90YWwgIT09IC0xfVxuICAgICAgPGRpdiBjbGFzcz1cInByb2plY3RfX3Byb2plY3QtdXNhZ2UtY29udGFpbmVyXCI+XG4gICAgICAgIDxkaXYgY2xhc3M9XCJwcm9qZWN0X19pbWFnZVwiPlxuICAgICAgICAgIDxQcm9qZWN0SWNvbiB0eXBlPVwidXNhZ2VcIiB2YXJpYW50PVwicHJvamVjdC1saXN0aW5nXCIgLz5cbiAgICAgICAgPC9kaXY+XG4gICAgICAgIDxkaXYgY2xhc3M9XCJwcm9qZWN0X19hY3RpdmUtaW5zdGFsbHMtdGV4dFwiPlxuICAgICAgICAgIHtwcm9qZWN0LnByb2plY3RfdXNhZ2VfdG90YWwudG9Mb2NhbGVTdHJpbmcoKX0gQWN0aXZlIEluc3RhbGxzXG4gICAgICAgIDwvZGl2PlxuICAgICAgPC9kaXY+XG4gICAgey9pZn1cbiAgICA8IS0tSWYgdGhlcmUgYXJlIG5vIHdhcm5pbmdzLCB0aGVyZSBpcyBzcGFjZSB0byBpbmNsdWRlIHRoZSBhY3Rpb24gYnV0dG9uXG4gICAgICAgIGluIHRoZSBpY29ucyBjb250YWluZXIgLS0+XG4gICAgeyNpZiAhcHJvamVjdC53YXJuaW5ncyB8fCBwcm9qZWN0Lndhcm5pbmdzLmxlbmd0aCA9PT0gMH1cbiAgICAgIDxBY3Rpb25CdXR0b24ge3Byb2plY3R9IC8+XG4gICAgey9pZn1cbiAgPC9kaXY+XG4gIDwhLS1JZiB0aGVyZSBhcmUgd2FybmluZ3MsIHRoZSBhY3Rpb24gYnV0dG9uIG5lZWRzIHRvIGJlIG1vdmVkIG91dCBvZiB0aGVcbiAgICAgIGljb25zIGNvbnRhaW5lciB0byBwcm92aWRlIHNwYWNlIGZvciB0aGUgd2FybmluZyBkZXNjcmlwdGlvbnMuIC0tPlxuICB7I2lmIHByb2plY3Qud2FybmluZ3MgJiYgcHJvamVjdC53YXJuaW5ncy5sZW5ndGggPiAwfVxuICAgIDxBY3Rpb25CdXR0b24ge3Byb2plY3R9IC8+XG4gIHsvaWZ9XG48L2xpPlxuXG48c3R5bGU+XG4gIC8qIFNtYWxsIGRldmljZXMgKHBvcnRyYWl0IHRhYmxldHMgYW5kIGxhcmdlIHBob25lcywgNjAwcHggYW5kIHVwKSAqL1xuICBAbWVkaWEgb25seSBzY3JlZW4gYW5kIChtaW4td2lkdGg6IDUzLjc1cmVtKSB7XG4gICAgLnByb2plY3QtLWdyaWQgLnByb2plY3RfX2xvZ28ge1xuICAgICAgb3ZlcmZsb3c6IGhpZGRlbjtcbiAgICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgICBhbGlnbi1pdGVtczogY2VudGVyO1xuICAgIH1cbiAgICAucHJvamVjdC0tbGlzdCAucHJvamVjdF9fbG9nbyB7XG4gICAgICBtYXJnaW4tdG9wOiAtMjBweDtcbiAgICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgICBhbGlnbi1pdGVtczogY2VudGVyO1xuICAgIH1cbiAgICAucHJvamVjdC0tbGlzdC5wcm9qZWN0IHtcbiAgICAgIHBhZGRpbmc6IDAgMnJlbTtcbiAgICB9XG4gIH1cblxuICAvKiBPbmUgY29sdW1uIGNhcmQgdmlldyAqL1xuICBAbWVkaWEgc2NyZWVuIGFuZCAobWF4LXdpZHRoOiA3NXJlbSkge1xuICAgIC5wcm9qZWN0LS1ncmlkLnByb2plY3Qge1xuICAgICAgd2lkdGg6IDEwMCU7XG4gICAgfVxuICB9XG5cbiAgLyogVHdvIGNvbHVtbiBjYXJkIHZpZXcgKGxhcHRvcHMvZGVza3RvcHMsIDEyMDBweCBhbmQgdXApICovXG4gIEBtZWRpYSBvbmx5IHNjcmVlbiBhbmQgKG1pbi13aWR0aDogNzVyZW0pIHtcbiAgICAucHJvamVjdC0tZ3JpZC5wcm9qZWN0IHtcbiAgICAgIHdpZHRoOiA0OSU7XG4gICAgfVxuICAgIC5wcm9qZWN0LS1saXN0LnByb2plY3Qge1xuICAgICAgd2lkdGg6IDEwMCU7XG4gICAgICBwYWRkaW5nOiAwIDJyZW07XG4gICAgfVxuICB9XG5cbiAgQG1lZGlhIG9ubHkgc2NyZWVuIGFuZCAobWluLXdpZHRoOiA4Ny41cmVtKSB7XG4gICAgLnByb2plY3QtLWdyaWQucHJvamVjdCB7XG4gICAgICB3aWR0aDogMzIlO1xuICAgIH1cbiAgfVxuXG4gIC5wcm9qZWN0IHtcbiAgICBwb3NpdGlvbjogcmVsYXRpdmU7XG4gIH1cbiAgLnByb2plY3QtLWdyaWQucHJvamVjdCB7XG4gICAgZGlzcGxheTogZmxleDtcbiAgICBmbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICAgIG1hcmdpbi1ib3R0b206IDJlbTtcbiAgICBib3JkZXI6IDFweCBzb2xpZCByZ2JhKDIxMiwgMjEyLCAyMTgsIDAuOCk7XG4gICAgYm9yZGVyLXJhZGl1czogMnB4O1xuICAgIGJveC1zaGFkb3c6IDAgNHB4IDEwcHggcmdiKDAgMCAwIC8gMTAlKTtcbiAgICBtYXJnaW4taW5saW5lLWVuZDogMC41dnc7XG4gIH1cblxuICAucHJvamVjdC0tZ3JpZCAucHJvamVjdF9fdGl0bGUge1xuICAgIG1hcmdpbi10b3A6IDAuMjVlbTtcbiAgICB0ZXh0LWFsaWduOiBjZW50ZXI7XG4gIH1cblxuICAucHJvamVjdF9fbGluayB7XG4gICAgdGV4dC1kZWNvcmF0aW9uOiBub25lO1xuICAgIGNvbG9yOiBibGFjaztcbiAgfVxuICAucHJvamVjdF9fbGluazpob3ZlciB7XG4gICAgY29sb3I6ICMwMDNlY2M7XG4gICAgdGV4dC1kZWNvcmF0aW9uOiB1bmRlcmxpbmU7XG4gIH1cblxuICAucHJvamVjdC0tZ3JpZCAucHJvamVjdF9fbWFpbiB7XG4gICAgZGlzcGxheTogZmxleDtcbiAgICBiYWNrZ3JvdW5kOiB3aGl0ZTtcbiAgICBwb3NpdGlvbjogcmVsYXRpdmU7XG4gICAgcGFkZGluZzogMWVtIDFlbTtcbiAgICBmbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICB9XG5cbiAgLnByb2plY3QtLWdyaWQgLnByb2plY3RfX2JvZHkge1xuICAgIHRleHQtYWxpZ246IGNlbnRlcjtcbiAgfVxuXG4gIC5wcm9qZWN0LS1ncmlkIC5wcm9qZWN0X19pY29ucyB7XG4gICAgZGlzcGxheTogZmxleDtcbiAgICBtYXJnaW4tdG9wOiBhdXRvO1xuICAgIHBhZGRpbmc6IDFlbSAxZW07XG4gICAgaGVpZ2h0OiAzZW07XG4gIH1cbiAgLnByb2plY3RfX2JvZHkge1xuICAgIGZvbnQtc2l6ZTogMTVweDtcbiAgfVxuICAucHJvamVjdC0tbGlzdC5wcm9qZWN0IHtcbiAgICBkaXNwbGF5OiBncmlkO1xuICAgIGdyaWQtdGVtcGxhdGUtY29sdW1uczogMjAlIDFmcjtcbiAgICBncmlkLXRlbXBsYXRlLXJvd3M6IDFmcjtcbiAgICBncmlkLXRlbXBsYXRlLWFyZWFzOlxuICAgICAgJ2FzaWRlIG1haW4nXG4gICAgICAnYXNpZGUgZm9vdGVyJztcbiAgfVxuXG4gIC5wcm9qZWN0LS1saXN0IC5wcm9qZWN0X19pbWFnZSB7XG4gICAgcGFkZGluZy1pbmxpbmUtc3RhcnQ6IDRlbTtcbiAgfVxuXG4gIC5wcm9qZWN0LS1saXN0IC5wcm9qZWN0X19pY29ucyB7XG4gICAgZ3JpZC1hcmVhOiBmb290ZXI7XG4gIH1cblxuICAucHJvamVjdC0tbGlzdCAucHJvamVjdF9fbG9nbyB7XG4gICAgZ3JpZC1hcmVhOiBhc2lkZTtcbiAgICBwYWRkaW5nLXRvcDogMnJlbTtcbiAgICBwYWRkaW5nLWJvdHRvbTogMnJlbTtcbiAgICBwYWRkaW5nLWlubGluZTogMCAycmVtO1xuICB9XG4gIC5wcm9qZWN0LS1saXN0IC5wcm9qZWN0X19tYWluIHtcbiAgICBncmlkLWFyZWE6IG1haW47XG4gIH1cbiAgLnByb2plY3QtLWxpc3QgLnByb2plY3RfX3RpdGxlIHtcbiAgICBwYWRkaW5nLXRvcDogMTBweDtcbiAgfVxuICAucHJvamVjdC0tbGlzdC5wcm9qZWN0IHtcbiAgICBtYXJnaW4tYm90dG9tOiAyZW07XG4gICAgYmFja2dyb3VuZDogI2ZmZmZmZjtcbiAgICBib3JkZXI6IDAuNXB4IHNvbGlkICNhNGEyYTI7XG4gICAgYm94LXNoYWRvdzogMHB4IDRweCA0cHggcmdiYSgwLCAwLCAwLCAwLjI1KTtcbiAgfVxuICAucHJvamVjdC0tbGlzdCAucHJvamVjdF9faWNvbnMge1xuICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgcGFkZGluZy10b3A6IDFyZW07XG4gICAgcGFkZGluZy1ib3R0b206IDFyZW07XG4gICAgcGFkZGluZy1pbmxpbmU6IDAgMWVtO1xuICB9XG4gIC5wcm9qZWN0X19pY29ucyA6Z2xvYmFsKHApIHtcbiAgICBkaXNwbGF5OiBpbmxpbmU7XG4gIH1cbiAgLnByb2plY3RfX2ljb25zLndhcm5pbmdzIHtcbiAgICBkaXNwbGF5OiBibG9jaztcbiAgfVxuICAucHJvamVjdF9faWNvbnMud2FybmluZ3Mgc3BhbiB7XG4gICAgZGlzcGxheTogbGlzdC1pdGVtO1xuICB9XG4gIC5wcm9qZWN0X19pY29ucy53YXJuaW5ncyBpbWcge1xuICAgIGRpc3BsYXk6IGlubGluZTtcbiAgICB3aWR0aDogMS4ycmVtO1xuICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgICBib3R0b206IC0wLjI1cmVtO1xuICB9XG4gIC53YXJuaW5ncyArIDpnbG9iYWwoLmFjdGlvbikge1xuICAgIG1hcmdpbi1pbmxpbmUtZW5kOiAxZW07XG4gICAgbWFyZ2luLWJvdHRvbTogMWVtO1xuICB9XG4gIC5wcm9qZWN0X19wcm9qZWN0LXVzYWdlLWNvbnRhaW5lciB7XG4gICAgZGlzcGxheTogZmxleDtcbiAgICBhbGlnbi1pdGVtczogY2VudGVyO1xuICAgIGp1c3RpZnktY29udGVudDogY2VudGVyO1xuICB9XG4gIC5wcm9qZWN0X19zdGF0dXMtaWNvbiB7XG4gICAgd2lkdGg6IDIuNGVtO1xuICAgIG1hcmdpbi1pbmxpbmUtZW5kOiAwLjVlbTtcbiAgICBkaXNwbGF5OiBibG9jaztcbiAgfVxuICAucHJvamVjdF9faW5zdGFsbC1jb3VudCB7XG4gICAgbWFyZ2luLWlubGluZS1zdGFydDogMTBweDtcbiAgICBmb250LXNpemU6IDEzcHg7XG4gIH1cbiAgLnByb2plY3RfX2FjdGl2ZS1pbnN0YWxscy10ZXh0IHtcbiAgICBmb250LXNpemU6IDEzcHg7XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBOEZFLE9BQU8sSUFBSSxDQUFDLE1BQU0sQ0FBQyxHQUFHLENBQUMsWUFBWSxRQUFRLENBQUUsQ0FDM0MseUJBQWMsQ0FBQyx5QkFBZSxDQUM1QixRQUFRLENBQUUsTUFBTSxDQUNoQixPQUFPLENBQUUsSUFBSSxDQUNiLFdBQVcsQ0FBRSxNQUNmLENBQ0EseUJBQWMsQ0FBQyx5QkFBZSxDQUM1QixVQUFVLENBQUUsS0FBSyxDQUNqQixPQUFPLENBQUUsSUFBSSxDQUNiLFdBQVcsQ0FBRSxNQUNmLENBQ0EsY0FBYyw4QkFBUyxDQUNyQixPQUFPLENBQUUsQ0FBQyxDQUFDLElBQ2IsQ0FDRixDQUdBLE9BQU8sTUFBTSxDQUFDLEdBQUcsQ0FBQyxZQUFZLEtBQUssQ0FBRSxDQUNuQyxjQUFjLDhCQUFTLENBQ3JCLEtBQUssQ0FBRSxJQUNULENBQ0YsQ0FHQSxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLFlBQVksS0FBSyxDQUFFLENBQ3hDLGNBQWMsOEJBQVMsQ0FDckIsS0FBSyxDQUFFLEdBQ1QsQ0FDQSxjQUFjLDhCQUFTLENBQ3JCLEtBQUssQ0FBRSxJQUFJLENBQ1gsT0FBTyxDQUFFLENBQUMsQ0FBQyxJQUNiLENBQ0YsQ0FFQSxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLFlBQVksT0FBTyxDQUFFLENBQzFDLGNBQWMsOEJBQVMsQ0FDckIsS0FBSyxDQUFFLEdBQ1QsQ0FDRixDQUVBLDhCQUFTLENBQ1AsUUFBUSxDQUFFLFFBQ1osQ0FDQSxjQUFjLDhCQUFTLENBQ3JCLE9BQU8sQ0FBRSxJQUFJLENBQ2IsY0FBYyxDQUFFLE1BQU0sQ0FDdEIsYUFBYSxDQUFFLEdBQUcsQ0FDbEIsTUFBTSxDQUFFLEdBQUcsQ0FBQyxLQUFLLENBQUMsS0FBSyxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FBQyxHQUFHLENBQUMsQ0FDMUMsYUFBYSxDQUFFLEdBQUcsQ0FDbEIsVUFBVSxDQUFFLENBQUMsQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxDQUN2QyxpQkFBaUIsQ0FBRSxLQUNyQixDQUVBLHlCQUFjLENBQUMsMEJBQWdCLENBQzdCLFVBQVUsQ0FBRSxNQUFNLENBQ2xCLFVBQVUsQ0FBRSxNQUNkLENBRUEsb0NBQWUsQ0FDYixlQUFlLENBQUUsSUFBSSxDQUNyQixLQUFLLENBQUUsS0FDVCxDQUNBLG9DQUFjLE1BQU8sQ0FDbkIsS0FBSyxDQUFFLE9BQU8sQ0FDZCxlQUFlLENBQUUsU0FDbkIsQ0FFQSx5QkFBYyxDQUFDLHlCQUFlLENBQzVCLE9BQU8sQ0FBRSxJQUFJLENBQ2IsVUFBVSxDQUFFLEtBQUssQ0FDakIsUUFBUSxDQUFFLFFBQVEsQ0FDbEIsT0FBTyxDQUFFLEdBQUcsQ0FBQyxHQUFHLENBQ2hCLGNBQWMsQ0FBRSxNQUNsQixDQUVBLHlCQUFjLENBQUMseUJBQWUsQ0FDNUIsVUFBVSxDQUFFLE1BQ2QsQ0FFQSx5QkFBYyxDQUFDLDBCQUFnQixDQUM3QixPQUFPLENBQUUsSUFBSSxDQUNiLFVBQVUsQ0FBRSxJQUFJLENBQ2hCLE9BQU8sQ0FBRSxHQUFHLENBQUMsR0FBRyxDQUNoQixNQUFNLENBQUUsR0FDVixDQUNBLG9DQUFlLENBQ2IsU0FBUyxDQUFFLElBQ2IsQ0FDQSxjQUFjLDhCQUFTLENBQ3JCLE9BQU8sQ0FBRSxJQUFJLENBQ2IscUJBQXFCLENBQUUsR0FBRyxDQUFDLEdBQUcsQ0FDOUIsa0JBQWtCLENBQUUsR0FBRyxDQUN2QixtQkFBbUIsQ0FDakIsWUFBWTtBQUNsQixNQUFNLGNBQ0osQ0FFQSx5QkFBYyxDQUFDLDBCQUFnQixDQUM3QixvQkFBb0IsQ0FBRSxHQUN4QixDQUVBLHlCQUFjLENBQUMsMEJBQWdCLENBQzdCLFNBQVMsQ0FBRSxNQUNiLENBRUEseUJBQWMsQ0FBQyx5QkFBZSxDQUM1QixTQUFTLENBQUUsS0FBSyxDQUNoQixXQUFXLENBQUUsSUFBSSxDQUNqQixjQUFjLENBQUUsSUFBSSxDQUNwQixjQUFjLENBQUUsQ0FBQyxDQUFDLElBQ3BCLENBQ0EseUJBQWMsQ0FBQyx5QkFBZSxDQUM1QixTQUFTLENBQUUsSUFDYixDQUNBLHlCQUFjLENBQUMsMEJBQWdCLENBQzdCLFdBQVcsQ0FBRSxJQUNmLENBQ0EsY0FBYyw4QkFBUyxDQUNyQixhQUFhLENBQUUsR0FBRyxDQUNsQixVQUFVLENBQUUsT0FBTyxDQUNuQixNQUFNLENBQUUsS0FBSyxDQUFDLEtBQUssQ0FBQyxPQUFPLENBQzNCLFVBQVUsQ0FBRSxHQUFHLENBQUMsR0FBRyxDQUFDLEdBQUcsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FDNUMsQ0FDQSx5QkFBYyxDQUFDLDBCQUFnQixDQUM3QixPQUFPLENBQUUsSUFBSSxDQUNiLFdBQVcsQ0FBRSxJQUFJLENBQ2pCLGNBQWMsQ0FBRSxJQUFJLENBQ3BCLGNBQWMsQ0FBRSxDQUFDLENBQUMsR0FDcEIsQ0FDQSwwQkFBZSxDQUFTLENBQUcsQ0FDekIsT0FBTyxDQUFFLE1BQ1gsQ0FDQSxlQUFlLCtCQUFVLENBQ3ZCLE9BQU8sQ0FBRSxLQUNYLENBQ0EsZUFBZSxvQkFBUyxDQUFDLGVBQUssQ0FDNUIsT0FBTyxDQUFFLFNBQ1gsQ0FDQSxlQUFlLG9CQUFTLENBQUMsY0FBSSxDQUMzQixPQUFPLENBQUUsTUFBTSxDQUNmLEtBQUssQ0FBRSxNQUFNLENBQ2IsUUFBUSxDQUFFLFFBQVEsQ0FDbEIsTUFBTSxDQUFFLFFBQ1YsQ0FDQSxvQkFBUyxDQUFXLE9BQVMsQ0FDM0IsaUJBQWlCLENBQUUsR0FBRyxDQUN0QixhQUFhLENBQUUsR0FDakIsQ0FDQSx1REFBa0MsQ0FDaEMsT0FBTyxDQUFFLElBQUksQ0FDYixXQUFXLENBQUUsTUFBTSxDQUNuQixlQUFlLENBQUUsTUFDbkIsQ0FDQSwyQ0FBc0IsQ0FDcEIsS0FBSyxDQUFFLEtBQUssQ0FDWixpQkFBaUIsQ0FBRSxLQUFLLENBQ3hCLE9BQU8sQ0FBRSxLQUNYLENBQ0EsNkNBQXdCLENBQ3RCLG1CQUFtQixDQUFFLElBQUksQ0FDekIsU0FBUyxDQUFFLElBQ2IsQ0FDQSxvREFBK0IsQ0FDN0IsU0FBUyxDQUFFLElBQ2IifQ== */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[5] = list[i];
	return child_ctx;
}

// (43:4) {#if project.is_covered}
function create_if_block_5(ctx) {
	let span;
	let projecticon;
	let t;
	let current;

	projecticon = new _ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_4__["default"]({
			props: { type: "status" },
			$$inline: true
		});

	let if_block = /*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0 && create_if_block_6(ctx);

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projecticon.$$.fragment);
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block) if_block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "project__status-icon pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 43, 6, 1470);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projecticon, span, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t);
			if (if_block) if_block.m(span, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			if (/*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0) {
				if (if_block) {
					if_block.p(ctx, dirty);
				} else {
					if_block = create_if_block_6(ctx);
					if_block.c();
					if_block.m(span, null);
				}
			} else if (if_block) {
				if_block.d(1);
				if_block = null;
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projecticon.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projecticon.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projecticon);
			if (if_block) if_block.d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_5.name,
		type: "if",
		source: "(43:4) {#if project.is_covered}",
		ctx
	});

	return block;
}

// (48:8) {#if project.warnings && project.warnings.length > 0}
function create_if_block_6(ctx) {
	let small;

	const block = {
		c: function create() {
			small = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("small");
			small.textContent = `${/*Drupal*/ ctx[3].t('Covered by the security advisory policy')}`;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(small, file, 48, 10, 1754);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, small, anchor);
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(small);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_6.name,
		type: "if",
		source: "(48:8) {#if project.warnings && project.warnings.length > 0}",
		ctx
	});

	return block;
}

// (53:4) {#if toggleView === 'Grid' && project.project_usage_total !== -1}
function create_if_block_4(ctx) {
	let div;
	let span;

	let t_value = /*Drupal*/ ctx[3].t('@count installs ', {
		'@count': /*project*/ ctx[0].project_usage_total.toLocaleString()
	}) + "";

	let t;

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "project__install-count pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 54, 8, 1992);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "project__install-count-container");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 53, 6, 1937);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, span);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*project*/ 1 && t_value !== (t_value = /*Drupal*/ ctx[3].t('@count installs ', {
				'@count': /*project*/ ctx[0].project_usage_total.toLocaleString()
			}) + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_4.name,
		type: "if",
		source: "(53:4) {#if toggleView === 'Grid' && project.project_usage_total !== -1}",
		ctx
	});

	return block;
}

// (62:4) {#if project.warnings && project.warnings.length > 0}
function create_if_block_3(ctx) {
	let each_1_anchor;
	let each_value = /*project*/ ctx[0].warnings;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const block = {
		c: function create() {
			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			each_1_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(target, anchor);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, each_1_anchor, anchor);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*project, FULL_MODULE_PATH*/ 1) {
				each_value = /*project*/ ctx[0].warnings;
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						each_blocks[i].m(each_1_anchor.parentNode, each_1_anchor);
					}
				}

				for (; i < each_blocks.length; i += 1) {
					each_blocks[i].d(1);
				}

				each_blocks.length = each_value.length;
			}
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(each_1_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_3.name,
		type: "if",
		source: "(62:4) {#if project.warnings && project.warnings.length > 0}",
		ctx
	});

	return block;
}

// (63:6) {#each project.warnings as warning}
function create_each_block(ctx) {
	let span;
	let img;
	let img_src_value;
	let t0;
	let small;
	let raw_value = /*warning*/ ctx[5] + "";
	let t1;

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			small = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("small");
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_6__.FULL_MODULE_PATH + "/images/triangle-alert.svg"))) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "alt", "");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", "pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 64, 10, 2347);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(small, file, 65, 10, 2423);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "project__status-icon pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 63, 8, 2301);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, img);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, small);
			small.innerHTML = raw_value;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t1);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*project*/ 1 && raw_value !== (raw_value = /*warning*/ ctx[5] + "")) small.innerHTML = raw_value;;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(63:6) {#each project.warnings as warning}",
		ctx
	});

	return block;
}

// (70:4) {#if toggleView === 'List' && project.project_usage_total !== -1}
function create_if_block_2(ctx) {
	let div2;
	let div0;
	let projecticon;
	let t0;
	let div1;
	let t1_value = /*project*/ ctx[0].project_usage_total.toLocaleString() + "";
	let t1;
	let t2;
	let current;

	projecticon = new _ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_4__["default"]({
			props: {
				type: "usage",
				variant: "project-listing"
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projecticon.$$.fragment);
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t1_value);
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(" Active Installs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "class", "project__image pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div0, file, 71, 8, 2625);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "class", "project__active-installs-text pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div1, file, 74, 8, 2742);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div2, "class", "project__project-usage-container pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div2, file, 70, 6, 2570);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div2, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, div0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projecticon, div0, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, div1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, t2);
			current = true;
		},
		p: function update(ctx, dirty) {
			if ((!current || dirty & /*project*/ 1) && t1_value !== (t1_value = /*project*/ ctx[0].project_usage_total.toLocaleString() + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t1, t1_value);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projecticon.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projecticon.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projecticon);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(70:4) {#if toggleView === 'List' && project.project_usage_total !== -1}",
		ctx
	});

	return block;
}

// (82:4) {#if !project.warnings || project.warnings.length === 0}
function create_if_block_1(ctx) {
	let actionbutton;
	let current;

	actionbutton = new _ActionButton_svelte__WEBPACK_IMPORTED_MODULE_1__["default"]({
			props: { project: /*project*/ ctx[0] },
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(actionbutton.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(actionbutton, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const actionbutton_changes = {};
			if (dirty & /*project*/ 1) actionbutton_changes.project = /*project*/ ctx[0];
			actionbutton.$set(actionbutton_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(actionbutton.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(actionbutton.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(actionbutton, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(82:4) {#if !project.warnings || project.warnings.length === 0}",
		ctx
	});

	return block;
}

// (88:2) {#if project.warnings && project.warnings.length > 0}
function create_if_block(ctx) {
	let actionbutton;
	let current;

	actionbutton = new _ActionButton_svelte__WEBPACK_IMPORTED_MODULE_1__["default"]({
			props: { project: /*project*/ ctx[0] },
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(actionbutton.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(actionbutton, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const actionbutton_changes = {};
			if (dirty & /*project*/ 1) actionbutton_changes.project = /*project*/ ctx[0];
			actionbutton.$set(actionbutton_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(actionbutton.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(actionbutton.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(actionbutton, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(88:2) {#if project.warnings && project.warnings.length > 0}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let li;
	let div0;
	let image;
	let t0;
	let div3;
	let div2;
	let h3;
	let a;
	let t1_value = /*project*/ ctx[0].title + "";
	let t1;
	let a_id_value;
	let a_href_value;
	let t2;
	let div1;
	let raw_value = /*project*/ ctx[0].body.summary + "";
	let t3;
	let categories;
	let t4;
	let div4;
	let t5;
	let t6;
	let t7;
	let t8;
	let t9;
	let li_class_value;
	let current;
	let mounted;
	let dispose;

	image = new _Image_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				sources: /*project*/ ctx[0].logo,
				class: "project__logo"
			},
			$$inline: true
		});

	categories = new _Categories_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: {
				toggleView: /*toggleView*/ ctx[1],
				moduleCategories: /*project*/ ctx[0].module_categories
			},
			$$inline: true
		});

	let if_block0 = /*project*/ ctx[0].is_covered && create_if_block_5(ctx);
	let if_block1 = /*toggleView*/ ctx[1] === 'Grid' && /*project*/ ctx[0].project_usage_total !== -1 && create_if_block_4(ctx);
	let if_block2 = /*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0 && create_if_block_3(ctx);
	let if_block3 = /*toggleView*/ ctx[1] === 'List' && /*project*/ ctx[0].project_usage_total !== -1 && create_if_block_2(ctx);
	let if_block4 = (!/*project*/ ctx[0].warnings || /*project*/ ctx[0].warnings.length === 0) && create_if_block_1(ctx);
	let if_block5 = /*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0 && create_if_block(ctx);

	const block = {
		c: function create() {
			li = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("li");
			div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(image.$$.fragment);
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			h3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("h3");
			a = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("a");
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t1_value);
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(categories.$$.fragment);
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if (if_block0) if_block0.c();
			t5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block1) if_block1.c();
			t6 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block2) if_block2.c();
			t7 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block3) if_block3.c();
			t8 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block4) if_block4.c();
			t9 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block5) if_block5.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "class", "project__logo pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div0, file, 15, 2, 538);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "id", a_id_value = "" + (/*project*/ ctx[0].project_machine_name + "_title"));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "class", "project__link pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "href", a_href_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_6__.ORIGIN_URL + "/admin/modules/browse/" + /*project*/ ctx[0].project_machine_name));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "rel", "noreferrer");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(a, file, 27, 8, 924);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(h3, "class", "project__title pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(h3, file, 21, 6, 767);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "class", "project__body pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div1, file, 34, 6, 1167);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div2, "class", "project__middle");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div2, file, 19, 4, 668);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div3, "class", "project__main pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div3, file, 18, 2, 636);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div4, "class", "project__icons pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(div4, "warnings", /*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div4, file, 38, 2, 1330);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", li_class_value = "project project--" + /*toggleView*/ ctx[1].toLowerCase() + " pb-1w8yugs");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(li, file, 14, 0, 479);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, li, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, div0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(image, div0, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, div3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, div2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, h3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(h3, a);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(a, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, div1);
			div1.innerHTML = raw_value;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(categories, div2, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, t4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, div4);
			if (if_block0) if_block0.m(div4, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t5);
			if (if_block1) if_block1.m(div4, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t6);
			if (if_block2) if_block2.m(div4, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t7);
			if (if_block3) if_block3.m(div4, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div4, t8);
			if (if_block4) if_block4.m(div4, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(li, t9);
			if (if_block5) if_block5.m(li, null);
			current = true;

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(h3, "click", /*click_handler*/ ctx[4], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, [dirty]) {
			const image_changes = {};
			if (dirty & /*project*/ 1) image_changes.sources = /*project*/ ctx[0].logo;
			image.$set(image_changes);
			if ((!current || dirty & /*project*/ 1) && t1_value !== (t1_value = /*project*/ ctx[0].title + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t1, t1_value);

			if (!current || dirty & /*project*/ 1 && a_id_value !== (a_id_value = "" + (/*project*/ ctx[0].project_machine_name + "_title"))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "id", a_id_value);
			}

			if (!current || dirty & /*project*/ 1 && a_href_value !== (a_href_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_6__.ORIGIN_URL + "/admin/modules/browse/" + /*project*/ ctx[0].project_machine_name))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(a, "href", a_href_value);
			}

			if ((!current || dirty & /*project*/ 1) && raw_value !== (raw_value = /*project*/ ctx[0].body.summary + "")) div1.innerHTML = raw_value;;
			const categories_changes = {};
			if (dirty & /*toggleView*/ 2) categories_changes.toggleView = /*toggleView*/ ctx[1];
			if (dirty & /*project*/ 1) categories_changes.moduleCategories = /*project*/ ctx[0].module_categories;
			categories.$set(categories_changes);

			if (/*project*/ ctx[0].is_covered) {
				if (if_block0) {
					if_block0.p(ctx, dirty);

					if (dirty & /*project*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block0, 1);
					}
				} else {
					if_block0 = create_if_block_5(ctx);
					if_block0.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block0, 1);
					if_block0.m(div4, t5);
				}
			} else if (if_block0) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block0, 1, 1, () => {
					if_block0 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (/*toggleView*/ ctx[1] === 'Grid' && /*project*/ ctx[0].project_usage_total !== -1) {
				if (if_block1) {
					if_block1.p(ctx, dirty);
				} else {
					if_block1 = create_if_block_4(ctx);
					if_block1.c();
					if_block1.m(div4, t6);
				}
			} else if (if_block1) {
				if_block1.d(1);
				if_block1 = null;
			}

			if (/*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0) {
				if (if_block2) {
					if_block2.p(ctx, dirty);
				} else {
					if_block2 = create_if_block_3(ctx);
					if_block2.c();
					if_block2.m(div4, t7);
				}
			} else if (if_block2) {
				if_block2.d(1);
				if_block2 = null;
			}

			if (/*toggleView*/ ctx[1] === 'List' && /*project*/ ctx[0].project_usage_total !== -1) {
				if (if_block3) {
					if_block3.p(ctx, dirty);

					if (dirty & /*toggleView, project*/ 3) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3, 1);
					}
				} else {
					if_block3 = create_if_block_2(ctx);
					if_block3.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3, 1);
					if_block3.m(div4, t8);
				}
			} else if (if_block3) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block3, 1, 1, () => {
					if_block3 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (!/*project*/ ctx[0].warnings || /*project*/ ctx[0].warnings.length === 0) {
				if (if_block4) {
					if_block4.p(ctx, dirty);

					if (dirty & /*project*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block4, 1);
					}
				} else {
					if_block4 = create_if_block_1(ctx);
					if_block4.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block4, 1);
					if_block4.m(div4, null);
				}
			} else if (if_block4) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block4, 1, 1, () => {
					if_block4 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (!current || dirty & /*project*/ 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(div4, "warnings", /*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0);
			}

			if (/*project*/ ctx[0].warnings && /*project*/ ctx[0].warnings.length > 0) {
				if (if_block5) {
					if_block5.p(ctx, dirty);

					if (dirty & /*project*/ 1) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block5, 1);
					}
				} else {
					if_block5 = create_if_block(ctx);
					if_block5.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block5, 1);
					if_block5.m(li, null);
				}
			} else if (if_block5) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block5, 1, 1, () => {
					if_block5 = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (!current || dirty & /*toggleView*/ 2 && li_class_value !== (li_class_value = "project project--" + /*toggleView*/ ctx[1].toLowerCase() + " pb-1w8yugs")) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(li, "class", li_class_value);
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(image.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(categories.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block5);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(image.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(categories.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block5);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(li);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(image);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(categories);
			if (if_block0) if_block0.d();
			if (if_block1) if_block1.d();
			if (if_block2) if_block2.d();
			if (if_block3) if_block3.d();
			if (if_block4) if_block4.d();
			if (if_block5) if_block5.d();
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let $focusedElement;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_5__.focusedElement, 'focusedElement');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_5__.focusedElement, $$value => $$invalidate(2, $focusedElement = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('Project', slots, []);
	let { project } = $$props;
	let { toggleView } = $$props;
	const { Drupal } = window;

	$$self.$$.on_mount.push(function () {
		if (project === undefined && !('project' in $$props || $$self.$$.bound[$$self.$$.props['project']])) {
			console.warn("<Project> was created without expected prop 'project'");
		}

		if (toggleView === undefined && !('toggleView' in $$props || $$self.$$.bound[$$self.$$.props['toggleView']])) {
			console.warn("<Project> was created without expected prop 'toggleView'");
		}
	});

	const writable_props = ['project', 'toggleView'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Project> was created with unknown prop '${key}'`);
	});

	const click_handler = () => {
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_5__.focusedElement, $focusedElement = `${project.project_machine_name}_title`, $focusedElement);
	};

	$$self.$$set = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
		if ('toggleView' in $$props) $$invalidate(1, toggleView = $$props.toggleView);
	};

	$$self.$capture_state = () => ({
		project,
		toggleView,
		ActionButton: _ActionButton_svelte__WEBPACK_IMPORTED_MODULE_1__["default"],
		Image: _Image_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		Categories: _Categories_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		ProjectIcon: _ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_4__["default"],
		focusedElement: _stores__WEBPACK_IMPORTED_MODULE_5__.focusedElement,
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_6__.FULL_MODULE_PATH,
		ORIGIN_URL: _constants__WEBPACK_IMPORTED_MODULE_6__.ORIGIN_URL,
		Drupal,
		$focusedElement
	});

	$$self.$inject_state = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
		if ('toggleView' in $$props) $$invalidate(1, toggleView = $$props.toggleView);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [project, toggleView, $focusedElement, Drupal, click_handler];
}

class Project extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { project: 0, toggleView: 1 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "Project",
			options,
			id: create_fragment.name
		});
	}

	get project() {
		throw new Error("<Project>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set project(value) {
		throw new Error("<Project>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get toggleView() {
		throw new Error("<Project>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set toggleView(value) {
		throw new Error("<Project>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Project);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/ProjectButtonBase.svelte":
/*!*******************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/ProjectButtonBase.svelte ***!
  \*******************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/ProjectButtonBase.svelte generated by Svelte v3.56.0 */


const file = "modules/project_browser/sveltejs/src/Project/ProjectButtonBase.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-syang9", ".project_button--primary.pb-syang9{margin:0 !important;border-width:0 !important;box-shadow:none;color:#fff;height:24px;font-size:12.65px;line-height:19px;display:flex;align-items:center;text-align:center;justify-content:center;white-space:nowrap}@media(forced-colors: active){.project_button--primary.pb-syang9{border:1px solid !important}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUHJvamVjdEJ1dHRvbkJhc2Uuc3ZlbHRlIiwic291cmNlcyI6WyJQcm9qZWN0QnV0dG9uQmFzZS5zdmVsdGUiXSwic291cmNlc0NvbnRlbnQiOlsiPHNjcmlwdD5cbiAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIGltcG9ydC9wcmVmZXItZGVmYXVsdC1leHBvcnRcbiAgZXhwb3J0IGxldCBjbGljayA9ICgpID0+IHt9O1xuPC9zY3JpcHQ+XG5cbjxidXR0b25cbiAgb246Y2xpY2s9e2NsaWNrfVxuICBjbGFzcz1cImJ1dHRvbiBidXR0b24tLXByaW1hcnkgcHJvamVjdF9idXR0b24tLXByaW1hcnlcIlxuICB7Li4uJCRyZXN0UHJvcHN9XG4+XG4gIDxzbG90IC8+XG48L2J1dHRvbj5cblxuPHN0eWxlPlxuICAucHJvamVjdF9idXR0b24tLXByaW1hcnkge1xuICAgIG1hcmdpbjogMCAhaW1wb3J0YW50O1xuICAgIGJvcmRlci13aWR0aDogMCAhaW1wb3J0YW50O1xuICAgIGJveC1zaGFkb3c6IG5vbmU7XG4gICAgY29sb3I6ICNmZmY7XG4gICAgaGVpZ2h0OiAyNHB4O1xuICAgIGZvbnQtc2l6ZTogMTIuNjVweDtcbiAgICBsaW5lLWhlaWdodDogMTlweDtcbiAgICBkaXNwbGF5OiBmbGV4O1xuICAgIGFsaWduLWl0ZW1zOiBjZW50ZXI7XG4gICAgdGV4dC1hbGlnbjogY2VudGVyO1xuICAgIGp1c3RpZnktY29udGVudDogY2VudGVyO1xuICAgIHdoaXRlLXNwYWNlOiBub3dyYXA7XG4gIH1cbiAgQG1lZGlhIChmb3JjZWQtY29sb3JzOiBhY3RpdmUpIHtcbiAgICAucHJvamVjdF9idXR0b24tLXByaW1hcnkge1xuICAgICAgYm9yZGVyOiAxcHggc29saWQgIWltcG9ydGFudDtcbiAgICB9XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBY0Usa0NBQXlCLENBQ3ZCLE1BQU0sQ0FBRSxDQUFDLENBQUMsVUFBVSxDQUNwQixZQUFZLENBQUUsQ0FBQyxDQUFDLFVBQVUsQ0FDMUIsVUFBVSxDQUFFLElBQUksQ0FDaEIsS0FBSyxDQUFFLElBQUksQ0FDWCxNQUFNLENBQUUsSUFBSSxDQUNaLFNBQVMsQ0FBRSxPQUFPLENBQ2xCLFdBQVcsQ0FBRSxJQUFJLENBQ2pCLE9BQU8sQ0FBRSxJQUFJLENBQ2IsV0FBVyxDQUFFLE1BQU0sQ0FDbkIsVUFBVSxDQUFFLE1BQU0sQ0FDbEIsZUFBZSxDQUFFLE1BQU0sQ0FDdkIsV0FBVyxDQUFFLE1BQ2YsQ0FDQSxNQUFPLGdCQUFnQixNQUFNLENBQUUsQ0FDN0Isa0NBQXlCLENBQ3ZCLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FBSyxDQUFDLFVBQ3BCLENBQ0YifQ== */");
}

function create_fragment(ctx) {
	let button;
	let current;
	let mounted;
	let dispose;
	const default_slot_template = /*#slots*/ ctx[3].default;
	const default_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(default_slot_template, ctx, /*$$scope*/ ctx[2], null);

	let button_levels = [
		{
			class: "button button--primary project_button--primary"
		},
		/*$$restProps*/ ctx[1]
	];

	let button_data = {};

	for (let i = 0; i < button_levels.length; i += 1) {
		button_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)(button_data, button_levels[i]);
	}

	const block = {
		c: function create() {
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			if (default_slot) default_slot.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(button, button_data);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "pb-syang9", true);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 5, 0, 110);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, button, anchor);

			if (default_slot) {
				default_slot.m(button, null);
			}

			if (button.autofocus) button.focus();
			current = true;

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(
					button,
					"click",
					function () {
						if ((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.is_function)(/*click*/ ctx[0])) /*click*/ ctx[0].apply(this, arguments);
					},
					false,
					false,
					false,
					false
				);

				mounted = true;
			}
		},
		p: function update(new_ctx, [dirty]) {
			ctx = new_ctx;

			if (default_slot) {
				if (default_slot.p && (!current || dirty & /*$$scope*/ 4)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						default_slot,
						default_slot_template,
						ctx,
						/*$$scope*/ ctx[2],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[2])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(default_slot_template, /*$$scope*/ ctx[2], dirty, null),
						null
					);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_attributes)(button, button_data = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_spread_update)(button_levels, [
				{
					class: "button button--primary project_button--primary"
				},
				dirty & /*$$restProps*/ 2 && /*$$restProps*/ ctx[1]
			]));

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "pb-syang9", true);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(default_slot, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(default_slot, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(button);
			if (default_slot) default_slot.d(detaching);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	const omit_props_names = ["click"];
	let $$restProps = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.compute_rest_props)($$props, omit_props_names);
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ProjectButtonBase', slots, ['default']);

	let { click = () => {
		
	} } = $$props;

	$$self.$$set = $$new_props => {
		$$props = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.assign)({}, $$props), (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.exclude_internal_props)($$new_props));
		$$invalidate(1, $$restProps = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.compute_rest_props)($$props, omit_props_names));
		if ('click' in $$new_props) $$invalidate(0, click = $$new_props.click);
		if ('$$scope' in $$new_props) $$invalidate(2, $$scope = $$new_props.$$scope);
	};

	$$self.$capture_state = () => ({ click });

	$$self.$inject_state = $$new_props => {
		if ('click' in $$props) $$invalidate(0, click = $$new_props.click);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [click, $$restProps, $$scope, slots];
}

class ProjectButtonBase extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { click: 0 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ProjectButtonBase",
			options,
			id: create_fragment.name
		});
	}

	get click() {
		throw new Error("<ProjectButtonBase>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set click(value) {
		throw new Error("<ProjectButtonBase>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ProjectButtonBase);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/ProjectIcon.svelte":
/*!*************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/ProjectIcon.svelte ***!
  \*************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/ProjectIcon.svelte generated by Svelte v3.56.0 */



const file = "modules/project_browser/sveltejs/src/Project/ProjectIcon.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1rte2u7", ".pb-icon--project-listing{width:2.4em}.pb-icon--module-details{width:25px}@media(forced-colors: active) and (prefers-color-scheme: dark){.pb-icon--usage{filter:invert(1)}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUHJvamVjdEljb24uc3ZlbHRlIiwic291cmNlcyI6WyJQcm9qZWN0SWNvbi5zdmVsdGUiXSwic291cmNlc0NvbnRlbnQiOlsiPHNjcmlwdD5cbiAgaW1wb3J0IHsgRlVMTF9NT0RVTEVfUEFUSCwgREFSS19DT0xPUl9TQ0hFTUUgfSBmcm9tICcuLi9jb25zdGFudHMnO1xuXG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBpbXBvcnQvbm8tbXV0YWJsZS1leHBvcnRzLGltcG9ydC9wcmVmZXItZGVmYXVsdC1leHBvcnRcbiAgZXhwb3J0IGxldCB0eXBlID0gJyc7XG4gIGV4cG9ydCBsZXQgdmFyaWFudCA9IGZhbHNlO1xuICBleHBvcnQgbGV0IGNsYXNzZXMgPSBmYWxzZTtcblxuICBjb25zdCB0eXBlVG9JbWcgPSB7XG4gICAgc3RhdHVzOiB7XG4gICAgICBwYXRoOiAnYmx1ZS1zZWN1cml0eS1zaGllbGQtaWNvbicsXG4gICAgICBhbHQ6IERydXBhbC50KCdTZWN1cml0eSBBZHZpc29yeSBDb3ZlcmFnZScpLFxuICAgIH0sXG4gICAgdXNhZ2U6IHtcbiAgICAgIHBhdGg6ICdwcm9qZWN0LXVzYWdlLWljb24nLFxuICAgICAgYWx0OiBEcnVwYWwudCgnUHJvamVjdCBVc2FnZScpLFxuICAgIH0sXG4gICAgY29tcGF0aWJsZToge1xuICAgICAgcGF0aDogJ2NvbXBhdGlibGUtaWNvbicsXG4gICAgICBhbHQ6IERydXBhbC50KCdDb21wYXRpYmxlJyksXG4gICAgfSxcbiAgfTtcbjwvc2NyaXB0PlxuXG48aW1nXG4gIHNyYz1cIntGVUxMX01PRFVMRV9QQVRIfS9pbWFnZXMve3R5cGVUb0ltZ1t0eXBlXS5wYXRofXtEQVJLX0NPTE9SX1NDSEVNRVxuICAgID8gJy0tZGFyay1jb2xvci1zY2hlbWUnXG4gICAgOiAnJ30uc3ZnXCJcbiAgY2xhc3M9e2BwYi1pY29uIHBiLWljb24tLSR7dmFyaWFudH0gcGItaWNvbi0tJHt0eXBlfSAke2NsYXNzZXN9YH1cbiAgYWx0PXt0eXBlVG9JbWdbdHlwZV0ucGF0aC5hbHR9XG4vPlxuXG48c3R5bGU+XG4gIDpnbG9iYWwoLnBiLWljb24tLXByb2plY3QtbGlzdGluZykge1xuICAgIHdpZHRoOiAyLjRlbTtcbiAgfVxuICA6Z2xvYmFsKC5wYi1pY29uLS1tb2R1bGUtZGV0YWlscykge1xuICAgIHdpZHRoOiAyNXB4O1xuICB9XG5cbiAgQG1lZGlhIChmb3JjZWQtY29sb3JzOiBhY3RpdmUpIGFuZCAocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspIHtcbiAgICA6Z2xvYmFsKC5wYi1pY29uLS11c2FnZSkge1xuICAgICAgZmlsdGVyOiBpbnZlcnQoMSk7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQWtDVSx5QkFBMkIsQ0FDakMsS0FBSyxDQUFFLEtBQ1QsQ0FDUSx3QkFBMEIsQ0FDaEMsS0FBSyxDQUFFLElBQ1QsQ0FFQSxNQUFPLGdCQUFnQixNQUFNLENBQUMsQ0FBQyxHQUFHLENBQUMsdUJBQXVCLElBQUksQ0FBRSxDQUN0RCxlQUFpQixDQUN2QixNQUFNLENBQUUsT0FBTyxDQUFDLENBQ2xCLENBQ0YifQ== */");
}

function create_fragment(ctx) {
	let img;
	let img_src_value;
	let img_class_value;
	let img_alt_value;

	const block = {
		c: function create() {
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH + "/images/" + /*typeToImg*/ ctx[3][/*type*/ ctx[0]].path + (_constants__WEBPACK_IMPORTED_MODULE_1__.DARK_COLOR_SCHEME ? '--dark-color-scheme' : '') + ".svg"))) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", img_class_value = `pb-icon pb-icon--${/*variant*/ ctx[1]} pb-icon--${/*type*/ ctx[0]} ${/*classes*/ ctx[2]}`);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "alt", img_alt_value = /*typeToImg*/ ctx[3][/*type*/ ctx[0]].path.alt);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 25, 0, 613);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, img, anchor);
		},
		p: function update(ctx, [dirty]) {
			if (dirty & /*type*/ 1 && !(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH + "/images/" + /*typeToImg*/ ctx[3][/*type*/ ctx[0]].path + (_constants__WEBPACK_IMPORTED_MODULE_1__.DARK_COLOR_SCHEME ? '--dark-color-scheme' : '') + ".svg"))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			}

			if (dirty & /*variant, type, classes*/ 7 && img_class_value !== (img_class_value = `pb-icon pb-icon--${/*variant*/ ctx[1]} pb-icon--${/*type*/ ctx[0]} ${/*classes*/ ctx[2]}`)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", img_class_value);
			}

			if (dirty & /*type*/ 1 && img_alt_value !== (img_alt_value = /*typeToImg*/ ctx[3][/*type*/ ctx[0]].path.alt)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "alt", img_alt_value);
			}
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(img);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ProjectIcon', slots, []);
	const { Drupal } = window;
	let { type = '' } = $$props;
	let { variant = false } = $$props;
	let { classes = false } = $$props;

	const typeToImg = {
		status: {
			path: 'blue-security-shield-icon',
			alt: Drupal.t('Security Advisory Coverage')
		},
		usage: {
			path: 'project-usage-icon',
			alt: Drupal.t('Project Usage')
		},
		compatible: {
			path: 'compatible-icon',
			alt: Drupal.t('Compatible')
		}
	};

	const writable_props = ['type', 'variant', 'classes'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ProjectIcon> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('type' in $$props) $$invalidate(0, type = $$props.type);
		if ('variant' in $$props) $$invalidate(1, variant = $$props.variant);
		if ('classes' in $$props) $$invalidate(2, classes = $$props.classes);
	};

	$$self.$capture_state = () => ({
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH,
		DARK_COLOR_SCHEME: _constants__WEBPACK_IMPORTED_MODULE_1__.DARK_COLOR_SCHEME,
		Drupal,
		type,
		variant,
		classes,
		typeToImg
	});

	$$self.$inject_state = $$props => {
		if ('type' in $$props) $$invalidate(0, type = $$props.type);
		if ('variant' in $$props) $$invalidate(1, variant = $$props.variant);
		if ('classes' in $$props) $$invalidate(2, classes = $$props.classes);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [type, variant, classes, typeToImg];
}

class ProjectIcon extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { type: 0, variant: 1, classes: 2 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ProjectIcon",
			options,
			id: create_fragment.name
		});
	}

	get type() {
		throw new Error("<ProjectIcon>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set type(value) {
		throw new Error("<ProjectIcon>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get variant() {
		throw new Error("<ProjectIcon>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set variant(value) {
		throw new Error("<ProjectIcon>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get classes() {
		throw new Error("<ProjectIcon>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set classes(value) {
		throw new Error("<ProjectIcon>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ProjectIcon);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Project/ProjectStatusIndicator.svelte":
/*!************************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Project/ProjectStatusIndicator.svelte ***!
  \************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Project/ProjectStatusIndicator.svelte generated by Svelte v3.56.0 */


const file = "modules/project_browser/sveltejs/src/Project/ProjectStatusIndicator.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1o0v226", ".project_status-indicator.pb-1o0v226{font-weight:bold;text-decoration:none}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUHJvamVjdFN0YXR1c0luZGljYXRvci5zdmVsdGUiLCJzb3VyY2VzIjpbIlByb2plY3RTdGF0dXNJbmRpY2F0b3Iuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIGV4cG9ydCBsZXQgcHJvamVjdDtcbiAgZXhwb3J0IGxldCBzdGF0dXNUZXh0O1xuXG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG48L3NjcmlwdD5cblxuPHNwYW4gY2xhc3M9XCJwcm9qZWN0X3N0YXR1cy1pbmRpY2F0b3JcIj5cbiAgPHNsb3QgLz5cbiAgPHNwYW4gY2xhc3M9XCJ2aXN1YWxseS1oaWRkZW5cIlxuICAgID57RHJ1cGFsLnQoJ0Btb2R1bGUgaXMnLCB7XG4gICAgICAnQG1vZHVsZSc6IGAke3Byb2plY3QudGl0bGV9YCxcbiAgICB9KX08L3NwYW5cbiAgPlxuICB7c3RhdHVzVGV4dH1cbjwvc3Bhbj5cblxuPHN0eWxlPlxuICAucHJvamVjdF9zdGF0dXMtaW5kaWNhdG9yIHtcbiAgICBmb250LXdlaWdodDogYm9sZDtcbiAgICB0ZXh0LWRlY29yYXRpb246IG5vbmU7XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBa0JFLG9DQUEwQixDQUN4QixXQUFXLENBQUUsSUFBSSxDQUNqQixlQUFlLENBQUUsSUFDbkIifQ== */");
}

function create_fragment(ctx) {
	let span1;
	let t0;
	let span0;
	let t1_value = /*Drupal*/ ctx[2].t('@module is', { '@module': `${/*project*/ ctx[0].title}` }) + "";
	let t1;
	let t2;
	let t3;
	let current;
	const default_slot_template = /*#slots*/ ctx[4].default;
	const default_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(default_slot_template, ctx, /*$$scope*/ ctx[3], null);

	const block = {
		c: function create() {
			span1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			if (default_slot) default_slot.c();
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			span0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t1_value);
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(/*statusText*/ ctx[1]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span0, "class", "visually-hidden");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span0, file, 9, 2, 150);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span1, "class", "project_status-indicator pb-1o0v226");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span1, file, 7, 0, 97);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span1, anchor);

			if (default_slot) {
				default_slot.m(span1, null);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, span0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span0, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, t3);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			if (default_slot) {
				if (default_slot.p && (!current || dirty & /*$$scope*/ 8)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						default_slot,
						default_slot_template,
						ctx,
						/*$$scope*/ ctx[3],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[3])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(default_slot_template, /*$$scope*/ ctx[3], dirty, null),
						null
					);
				}
			}

			if ((!current || dirty & /*project*/ 1) && t1_value !== (t1_value = /*Drupal*/ ctx[2].t('@module is', { '@module': `${/*project*/ ctx[0].title}` }) + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t1, t1_value);
			if (!current || dirty & /*statusText*/ 2) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t3, /*statusText*/ ctx[1]);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(default_slot, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(default_slot, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span1);
			if (default_slot) default_slot.d(detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ProjectStatusIndicator', slots, ['default']);
	let { project } = $$props;
	let { statusText } = $$props;
	const { Drupal } = window;

	$$self.$$.on_mount.push(function () {
		if (project === undefined && !('project' in $$props || $$self.$$.bound[$$self.$$.props['project']])) {
			console.warn("<ProjectStatusIndicator> was created without expected prop 'project'");
		}

		if (statusText === undefined && !('statusText' in $$props || $$self.$$.bound[$$self.$$.props['statusText']])) {
			console.warn("<ProjectStatusIndicator> was created without expected prop 'statusText'");
		}
	});

	const writable_props = ['project', 'statusText'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ProjectStatusIndicator> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
		if ('statusText' in $$props) $$invalidate(1, statusText = $$props.statusText);
		if ('$$scope' in $$props) $$invalidate(3, $$scope = $$props.$$scope);
	};

	$$self.$capture_state = () => ({ project, statusText, Drupal });

	$$self.$inject_state = $$props => {
		if ('project' in $$props) $$invalidate(0, project = $$props.project);
		if ('statusText' in $$props) $$invalidate(1, statusText = $$props.statusText);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [project, statusText, Drupal, $$scope, slots];
}

class ProjectStatusIndicator extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { project: 0, statusText: 1 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ProjectStatusIndicator",
			options,
			id: create_fragment.name
		});
	}

	get project() {
		throw new Error("<ProjectStatusIndicator>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set project(value) {
		throw new Error("<ProjectStatusIndicator>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get statusText() {
		throw new Error("<ProjectStatusIndicator>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set statusText(value) {
		throw new Error("<ProjectStatusIndicator>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ProjectStatusIndicator);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/ProjectBrowser.svelte":
/*!********************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/ProjectBrowser.svelte ***!
  \********************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var svelte_previous__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! svelte-previous */ "./node_modules/svelte-previous/dist/index.es.js");
/* harmony import */ var _ProjectGrid_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ProjectGrid.svelte */ "./modules/project_browser/sveltejs/src/ProjectGrid.svelte");
/* harmony import */ var _Pagination_svelte__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Pagination.svelte */ "./modules/project_browser/sveltejs/src/Pagination.svelte");
/* harmony import */ var _Project_Project_svelte__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./Project/Project.svelte */ "./modules/project_browser/sveltejs/src/Project/Project.svelte");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _MediaQuery_svelte__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./MediaQuery.svelte */ "./modules/project_browser/sveltejs/src/MediaQuery.svelte");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/ProjectBrowser.svelte generated by Svelte v3.56.0 */


const { Object: Object_1 } = svelte_internal__WEBPACK_IMPORTED_MODULE_0__.globals;












const file = "modules/project_browser/sveltejs/src/ProjectBrowser.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1l3u22a", ".project-browser__toggle.pb-1l3u22a.pb-1l3u22a{display:flex;justify-content:space-evenly;align-items:center;margin-bottom:1.5em;font-family:inherit;color:#232429;background-color:#d3d4d9;width:80.41px;height:30px;box-shadow:0 2px 4px rgba(0, 0, 0, 0.25);border:none}.project-browser__list-icon.pb-1l3u22a.pb-1l3u22a,.project-browser__grid-icon.pb-1l3u22a.pb-1l3u22a{pointer-events:none}.project-browser__toggle.pb-1l3u22a.pb-1l3u22a:first-child{margin-inline-start:auto}.project-browser__toggle-buttons.pb-1l3u22a.pb-1l3u22a{display:flex;margin-inline-end:25px}.project-browser__toggle.pb-1l3u22a.pb-1l3u22a:focus{box-shadow:0 0 0 2px #fff, 0 0 0 5px #26a769}.project-browser__toggle.project-browser__list-button.pb-1l3u22a.pb-1l3u22a{margin-inline-end:5px;border-radius:2px 0 0 2px;cursor:pointer}.project-browser__toggle.project-browser__grid-button.pb-1l3u22a.pb-1l3u22a{border-radius:0 2px 2px 0;cursor:pointer;margin-inline-end:5px}.project-browser__selected-tab.pb-1l3u22a.pb-1l3u22a{background-color:#adaeb3}.project-browser__plugin-tabs.pb-1l3u22a.pb-1l3u22a{display:flex}.project-browser__plugin-tab.pb-1l3u22a.pb-1l3u22a{margin-right:5px;margin-left:5px;width:33%;height:auto;min-height:30px;cursor:pointer}.project-browser__plugin-tabs.pb-1l3u22a .project-browser__toggle.pb-1l3u22a{margin-inline-start:0}.project-browser__install-warning.pb-1l3u22a.pb-1l3u22a{border:1px solid red;padding:1em}.project-browser__warning.pb-1l3u22a.pb-1l3u22a{margin:0.5em 0}.project-browser__warning-header.pb-1l3u22a.pb-1l3u22a{color:red}@media(forced-colors: active){.project-browser__toggle.pb-1l3u22a.pb-1l3u22a{border:1px solid}@media(prefers-color-scheme: dark){.project-browser__list-icon.pb-1l3u22a.pb-1l3u22a{filter:invert(1)}.project-browser__grid-icon.pb-1l3u22a.pb-1l3u22a{filter:invert(1)}}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUHJvamVjdEJyb3dzZXIuc3ZlbHRlIiwic291cmNlcyI6WyJQcm9qZWN0QnJvd3Nlci5zdmVsdGUiXSwic291cmNlc0NvbnRlbnQiOlsiPHNjcmlwdD5cbiAgaW1wb3J0IHsgb25Nb3VudCB9IGZyb20gJ3N2ZWx0ZSc7XG4gIGltcG9ydCB7IHdpdGhQcmV2aW91cyB9IGZyb20gJ3N2ZWx0ZS1wcmV2aW91cyc7XG4gIGltcG9ydCBQcm9qZWN0R3JpZCwgeyBTZWFyY2gsIEZpbHRlciB9IGZyb20gJy4vUHJvamVjdEdyaWQuc3ZlbHRlJztcbiAgaW1wb3J0IFBhZ2luYXRpb24gZnJvbSAnLi9QYWdpbmF0aW9uLnN2ZWx0ZSc7XG4gIGltcG9ydCBQcm9qZWN0IGZyb20gJy4vUHJvamVjdC9Qcm9qZWN0LnN2ZWx0ZSc7XG4gIGltcG9ydCB7XG4gICAgZmlsdGVycyxcbiAgICByb3dzQ291bnQsXG4gICAgbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIsXG4gICAgaXNGaXJzdExvYWQsXG4gICAgcGFnZSxcbiAgICBzb3J0LFxuICAgIGZvY3VzZWRFbGVtZW50LFxuICAgIHNlYXJjaFN0cmluZyxcbiAgICBhY3RpdmVUYWIsXG4gICAgY2F0ZWdvcnlDaGVja2VkVHJhY2ssXG4gICAgc29ydENyaXRlcmlhLFxuICAgIHByZWZlcnJlZFZpZXcsXG4gICAgcGFnZVNpemUsXG4gIH0gZnJvbSAnLi9zdG9yZXMnO1xuICBpbXBvcnQgTWVkaWFRdWVyeSBmcm9tICcuL01lZGlhUXVlcnkuc3ZlbHRlJztcbiAgaW1wb3J0IHtcbiAgICBBQ1RJVkVMWV9NQUlOVEFJTkVEX0lELFxuICAgIENPVkVSRURfSUQsXG4gICAgQUxMX1ZBTFVFU19JRCxcbiAgICBERUZBVUxUX1NPVVJDRV9JRCxcbiAgICBDVVJSRU5UX1NPVVJDRVNfS0VZUyxcbiAgICBPUklHSU5fVVJMLFxuICAgIEZVTExfTU9EVUxFX1BBVEgsXG4gICAgU09SVF9PUFRJT05TLFxuICAgIE1PRFVMRV9TVEFUVVMsXG4gICAgQUxMT1dfVUlfSU5TVEFMTCxcbiAgICBQTV9WQUxJREFUSU9OX0VSUk9SLFxuICB9IGZyb20gJy4vY29uc3RhbnRzJztcblxuICBjb25zdCB7IERydXBhbCB9ID0gd2luZG93O1xuXG4gIGxldCBkYXRhO1xuICBsZXQgcm93cyA9IFtdO1xuICBsZXQgc291cmNlcyA9IFtdO1xuICBsZXQgZGF0YUFycmF5ID0gW107XG4gIGNvbnN0IHBhZ2VJbmRleCA9IDA7IC8vIGZpcnN0IHJvd1xuXG4gIGxldCBsb2FkaW5nID0gdHJ1ZTtcbiAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIGltcG9ydC9uby1tdXRhYmxlLWV4cG9ydHMsaW1wb3J0L3ByZWZlci1kZWZhdWx0LWV4cG9ydFxuICBleHBvcnQgbGV0IHNlYXJjaFRleHQ7XG4gIHNlYXJjaFN0cmluZy5zdWJzY3JpYmUoKHZhbHVlKSA9PiB7XG4gICAgc2VhcmNoVGV4dCA9IHZhbHVlO1xuICB9KTtcbiAgbGV0IHRvZ2dsZVZpZXcgPSAnR3JpZCc7XG4gIHByZWZlcnJlZFZpZXcuc3Vic2NyaWJlKCh2YWx1ZSkgPT4ge1xuICAgIHRvZ2dsZVZpZXcgPSB2YWx1ZTtcbiAgfSk7XG4gIGNvbnN0IFtjdXJyZW50UGFnZSwgcHJldmlvdXNQYWdlXSA9IHdpdGhQcmV2aW91cygwKTtcbiAgJDogJGN1cnJlbnRQYWdlID0gJHBhZ2U7XG4gIGxldCBlbGVtZW50ID0gJyc7XG4gIGZvY3VzZWRFbGVtZW50LnN1YnNjcmliZSgodmFsdWUpID0+IHtcbiAgICBlbGVtZW50ID0gdmFsdWU7XG4gIH0pO1xuICBsZXQgZmlsdGVyQ29tcG9uZW50O1xuXG4gIC8qKlxuICAgKiBMb2FkIGRhdGEgZnJvbSBEcnVwYWwub3JnIEFQSS5cbiAgICpcbiAgICogQHBhcmFtIHtudW1iZXJ8c3RyaW5nfSBfcGFnZVxuICAgKiAgIFRoZSBwYWdlIG51bWJlci5cbiAgICpcbiAgICogQHJldHVybiB7UHJvbWlzZTx2b2lkPn1cbiAgICogICBFbXB0eSBwcm9taXNlIHRoYXQgcmVzb2x2ZXMgb24gY29udGVudCBsb2FkLipcbiAgICovXG4gIGFzeW5jIGZ1bmN0aW9uIGxvYWQoX3BhZ2UpIHtcbiAgICBsb2FkaW5nID0gdHJ1ZTtcbiAgICBjb25zdCBzZWFyY2hQYXJhbXMgPSBuZXcgVVJMU2VhcmNoUGFyYW1zKHtcbiAgICAgIHBhZ2U6IF9wYWdlLFxuICAgICAgbGltaXQ6ICRwYWdlU2l6ZSxcbiAgICAgIHNvcnQ6ICRzb3J0LFxuICAgICAgc291cmNlOiAkYWN0aXZlVGFiLFxuICAgIH0pO1xuICAgIGlmIChzZWFyY2hUZXh0KSB7XG4gICAgICBzZWFyY2hQYXJhbXMuc2V0KCdzZWFyY2gnLCBzZWFyY2hUZXh0KTtcbiAgICB9XG4gICAgaWYgKCRtb2R1bGVDYXRlZ29yeUZpbHRlciAmJiAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIubGVuZ3RoKSB7XG4gICAgICBzZWFyY2hQYXJhbXMuc2V0KCdjYXRlZ29yaWVzJywgJG1vZHVsZUNhdGVnb3J5RmlsdGVyKTtcbiAgICB9XG4gICAgaWYgKCRmaWx0ZXJzLmRldmVsb3BtZW50U3RhdHVzICYmICRmaWx0ZXJzLmRldmVsb3BtZW50U3RhdHVzLmxlbmd0aCkge1xuICAgICAgc2VhcmNoUGFyYW1zLnNldCgnZGV2ZWxvcG1lbnRfc3RhdHVzJywgJGZpbHRlcnMuZGV2ZWxvcG1lbnRTdGF0dXMpO1xuICAgIH1cbiAgICBpZiAoJGZpbHRlcnMubWFpbnRlbmFuY2VTdGF0dXMgJiYgJGZpbHRlcnMubWFpbnRlbmFuY2VTdGF0dXMubGVuZ3RoKSB7XG4gICAgICBzZWFyY2hQYXJhbXMuc2V0KCdtYWludGVuYW5jZV9zdGF0dXMnLCAkZmlsdGVycy5tYWludGVuYW5jZVN0YXR1cyk7XG4gICAgfVxuICAgIGlmICgkZmlsdGVycy5zZWN1cml0eUNvdmVyYWdlICYmICRmaWx0ZXJzLnNlY3VyaXR5Q292ZXJhZ2UubGVuZ3RoKSB7XG4gICAgICBzZWFyY2hQYXJhbXMuc2V0KCdzZWN1cml0eV9hZHZpc29yeV9jb3ZlcmFnZScsICRmaWx0ZXJzLnNlY3VyaXR5Q292ZXJhZ2UpO1xuICAgIH1cbiAgICBpZiAoT2JqZWN0LmtleXMoJGNhdGVnb3J5Q2hlY2tlZFRyYWNrKS5sZW5ndGggIT09IDApIHtcbiAgICAgIHNlYXJjaFBhcmFtcy5zZXQoXG4gICAgICAgICd0YWJ3aXNlX2NhdGVnb3JpZXMnLFxuICAgICAgICBKU09OLnN0cmluZ2lmeSgkY2F0ZWdvcnlDaGVja2VkVHJhY2spLFxuICAgICAgKTtcbiAgICB9XG4gICAgY29uc3QgdXJsID0gYCR7T1JJR0lOX1VSTH0vZHJ1cGFsLW9yZy1wcm94eS9wcm9qZWN0PyR7c2VhcmNoUGFyYW1zLnRvU3RyaW5nKCl9YDtcblxuICAgIGNvbnN0IHJlcyA9IGF3YWl0IGZldGNoKHVybCk7XG4gICAgaWYgKHJlcy5vaykge1xuICAgICAgZGF0YSA9IGF3YWl0IHJlcy5qc29uKCk7XG4gICAgICAvLyBBIGxpc3Qgb2YgdGhlIGF2YWlsYWJsZSBzb3VyY2VzIHRvIGdldCBwcm9qZWN0IGRhdGEuXG4gICAgICBzb3VyY2VzID0gT2JqZWN0LmtleXMoZGF0YSk7XG4gICAgICBkYXRhQXJyYXkgPSBPYmplY3QudmFsdWVzKGRhdGEpO1xuICAgICAgcm93cyA9IGRhdGFbJGFjdGl2ZVRhYl0ubGlzdDtcbiAgICAgICRyb3dzQ291bnQgPSBkYXRhWyRhY3RpdmVUYWJdLnRvdGFsUmVzdWx0cztcbiAgICB9IGVsc2Uge1xuICAgICAgcm93cyA9IFtdO1xuICAgICAgJHJvd3NDb3VudCA9IDA7XG4gICAgfVxuICAgIGxvYWRpbmcgPSBmYWxzZTtcbiAgfVxuXG4gIGFzeW5jIGZ1bmN0aW9uIGZpbHRlclJlY29tbWVuZGVkKCkge1xuICAgIC8vIFNob3cgcmVjb21tZW5kZWQgcHJvamVjdHMgb24gaW5pdGlhbCBwYWdlIGxvYWQgb25seSB3aGVuIG5vIGZpbHRlcnMgYXJlIGFwcGxpZWQuXG4gICAgaWYgKFxuICAgICAgJGZpbHRlcnMuZGV2ZWxvcG1lbnRTdGF0dXMubGVuZ3RoID09PSAwICYmXG4gICAgICAkZmlsdGVycy5tYWludGVuYW5jZVN0YXR1cy5sZW5ndGggPT09IDAgJiZcbiAgICAgICRmaWx0ZXJzLnNlY3VyaXR5Q292ZXJhZ2UubGVuZ3RoID09PSAwXG4gICAgKSB7XG4gICAgICAkZmlsdGVycy5tYWludGVuYW5jZVN0YXR1cyA9IEFDVElWRUxZX01BSU5UQUlORURfSUQ7XG4gICAgICAkZmlsdGVycy5zZWN1cml0eUNvdmVyYWdlID0gQ09WRVJFRF9JRDtcbiAgICAgICRmaWx0ZXJzLmRldmVsb3BtZW50U3RhdHVzID0gQUxMX1ZBTFVFU19JRDtcbiAgICB9XG4gICAgaXNGaXJzdExvYWQuc2V0KGZhbHNlKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBMb2FkIHJlbW90ZSBkYXRhIHdoZW4gdGhlIFN2ZWx0ZSBjb21wb25lbnQgaXMgbW91bnRlZC5cbiAgICovXG4gIG9uTW91bnQoYXN5bmMgKCkgPT4ge1xuICAgIC8vIElmIGN1cnJlbnQgYWN0aXZlIHBsdWdpbiBpcyBkaXNhYmxlZCwgcmVtb3ZlIHN0b3JhZ2Uga2V5cyBhbmQgcmVsb2FkIHBhZ2UuXG4gICAgY29uc3Qgc2V0dGluZ3NBY3RpdmVUYWIgPSBKU09OLnN0cmluZ2lmeShERUZBVUxUX1NPVVJDRV9JRCk7XG4gICAgaWYgKFxuICAgICAgJGFjdGl2ZVRhYiAhPT0gc2V0dGluZ3NBY3RpdmVUYWIgJiZcbiAgICAgIENVUlJFTlRfU09VUkNFU19LRVlTLmluZGV4T2YoJGFjdGl2ZVRhYikgPT09IC0xXG4gICAgKSB7XG4gICAgICBzZXNzaW9uU3RvcmFnZS5yZW1vdmVJdGVtKCdhY3RpdmVUYWInKTtcbiAgICAgIHNlc3Npb25TdG9yYWdlLnJlbW92ZUl0ZW0oJ2NhdGVnb3J5RmlsdGVyJyk7XG4gICAgICBzZXNzaW9uU3RvcmFnZS5yZW1vdmVJdGVtKCdjYXRlZ29yeUNoZWNrZWRUcmFjaycpO1xuICAgICAgc2Vzc2lvblN0b3JhZ2Uuc2V0SXRlbSgnYWN0aXZlVGFiJywgc2V0dGluZ3NBY3RpdmVUYWIpO1xuICAgICAgd2luZG93LmxvY2F0aW9uLnJlbG9hZCgpO1xuICAgIH1cbiAgICAvLyBPbmx5IGZpbHRlciBieSByZWNvbW1lbmRlZCBvbiBmaXJzdCBwYWdlIGxvYWQuXG4gICAgaWYgKCRpc0ZpcnN0TG9hZCkge1xuICAgICAgYXdhaXQgZmlsdGVyUmVjb21tZW5kZWQoKTtcbiAgICB9XG5cbiAgICBhd2FpdCBsb2FkKCRwYWdlKTtcbiAgICBjb25zdCBmb2N1cyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGVsZW1lbnQpO1xuICAgIGlmIChmb2N1cykge1xuICAgICAgZm9jdXMuZm9jdXMoKTtcbiAgICAgICRmb2N1c2VkRWxlbWVudCA9ICcnO1xuICAgIH1cbiAgfSk7XG5cbiAgZnVuY3Rpb24gb25QYWdlQ2hhbmdlKGV2ZW50KSB7XG4gICAgcGFnZS5zZXQoZXZlbnQuZGV0YWlsLnBhZ2UpO1xuICAgIGxvYWQoJHBhZ2UpO1xuICB9XG5cbiAgZnVuY3Rpb24gb25QYWdlU2l6ZUNoYW5nZSgpIHtcbiAgICBwYWdlLnNldCgwKTtcbiAgICBsb2FkKCRwYWdlKTtcbiAgfVxuXG4gIGFzeW5jIGZ1bmN0aW9uIG9uU2VhcmNoKGV2ZW50KSB7XG4gICAgc2VhcmNoVGV4dCA9IGV2ZW50LmRldGFpbC5zZWFyY2hUZXh0O1xuICAgIGF3YWl0IGxvYWQoMCk7XG4gICAgcGFnZS5zZXQoMCk7XG4gIH1cblxuICBhc3luYyBmdW5jdGlvbiBvblNlbGVjdENhdGVnb3J5KGV2ZW50KSB7XG4gICAgbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIuc2V0KGV2ZW50LmRldGFpbC5jYXRlZ29yeSk7XG4gICAgYXdhaXQgbG9hZCgwKTtcbiAgICBwYWdlLnNldCgwKTtcbiAgfVxuICBhc3luYyBmdW5jdGlvbiBvblNvcnQoZXZlbnQpIHtcbiAgICBzb3J0LnNldChldmVudC5kZXRhaWwuc29ydCk7XG4gICAgYXdhaXQgbG9hZCgwKTtcbiAgICBwYWdlLnNldCgwKTtcbiAgfVxuICBhc3luYyBmdW5jdGlvbiBvbkFkdmFuY2VkRmlsdGVyKGV2ZW50KSB7XG4gICAgJGZpbHRlcnMuZGV2ZWxvcG1lbnRTdGF0dXMgPSBldmVudC5kZXRhaWwuZGV2ZWxvcG1lbnRTdGF0dXM7XG4gICAgJGZpbHRlcnMubWFpbnRlbmFuY2VTdGF0dXMgPSBldmVudC5kZXRhaWwubWFpbnRlbmFuY2VTdGF0dXM7XG4gICAgJGZpbHRlcnMuc2VjdXJpdHlDb3ZlcmFnZSA9IGV2ZW50LmRldGFpbC5zZWN1cml0eUNvdmVyYWdlO1xuXG4gICAgYXdhaXQgbG9hZCgwKTtcbiAgICBwYWdlLnNldCgwKTtcbiAgfVxuXG4gIGFzeW5jIGZ1bmN0aW9uIG9uVG9nZ2xlKHZhbCkge1xuICAgIGlmICh2YWwgIT09IHRvZ2dsZVZpZXcpIHRvZ2dsZVZpZXcgPSB2YWw7XG4gICAgcHJlZmVycmVkVmlldy5zZXQodmFsKTtcbiAgfVxuXG4gIGFzeW5jIGZ1bmN0aW9uIHRvZ2dsZVJvd3ModmFsKSB7XG4gICAgZmlsdGVyQ29tcG9uZW50LnNldE1vZHVsZUNhdGVnb3J5Vm9jYWJ1bGFyeSgpO1xuICAgICRjYXRlZ29yeUNoZWNrZWRUcmFja1skYWN0aXZlVGFiXSA9ICRtb2R1bGVDYXRlZ29yeUZpbHRlcjtcbiAgICAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIgPSBbXTtcbiAgICAkYWN0aXZlVGFiID0gdmFsO1xuICAgICRtb2R1bGVDYXRlZ29yeUZpbHRlciA9XG4gICAgICB0eXBlb2YgJGNhdGVnb3J5Q2hlY2tlZFRyYWNrWyRhY3RpdmVUYWJdICE9PSAndW5kZWZpbmVkJ1xuICAgICAgICA/ICRjYXRlZ29yeUNoZWNrZWRUcmFja1skYWN0aXZlVGFiXVxuICAgICAgICA6IFtdO1xuXG4gICAgJHNvcnRDcml0ZXJpYSA9IFNPUlRfT1BUSU9OU1skYWN0aXZlVGFiXTtcbiAgICBjb25zdCBzb3J0TWF0Y2ggPSAkc29ydENyaXRlcmlhLmZpbmQoKG9wdGlvbikgPT4gb3B0aW9uLmlkID09PSAkc29ydCk7XG4gICAgaWYgKHR5cGVvZiBzb3J0TWF0Y2ggPT09ICd1bmRlZmluZWQnKSB7XG4gICAgICAkc29ydCA9ICRzb3J0Q3JpdGVyaWFbMF0uaWQ7XG4gICAgfVxuXG4gICAgLy8gTW92ZSB0byBwYWdlIDAgd2hlbiBzd2l0Y2hpbmcgc291cmNlcyBhcyB0aGVyZSdzIG5vIGd1YXJhbnRlZSB0aGUgbmV3XG4gICAgLy8gc291cmNlIGhhcyBlbm91Z2ggcmVzdWx0cyB0byByZWFjaCB3aGF0ZXZlciB0aGUgY3VycmVudCBwYWdlIGlzLlxuICAgIHBhZ2Uuc2V0KDApO1xuICAgIGF3YWl0IGxvYWQoMCk7XG4gIH1cblxuICBkb2N1bWVudC5vbm1vdXNlb3ZlciA9IGZ1bmN0aW9uIHNldElubmVyRG9jQ2xpY2tUcnVlKCkge1xuICAgIHdpbmRvdy5pbm5lckRvY0NsaWNrID0gdHJ1ZTtcbiAgfTtcblxuICBkb2N1bWVudC5vbm1vdXNlbGVhdmUgPSBmdW5jdGlvbiBzZXRJbm5lckRvY0NsaWNrRmFsc2UoKSB7XG4gICAgd2luZG93LmlubmVyRG9jQ2xpY2sgPSBmYWxzZTtcbiAgfTtcblxuICAvLyBIYW5kbGVzIGJhY2sgYnV0dG9uIGZ1bmN0aW9uYWxpdHkgdG8gZ28gYmFjayB0byB0aGUgcHJldmlvdXMgcGFnZSB0aGUgdXNlciB3YXMgb24gYmVmb3JlLlxuICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcigncG9wc3RhdGUnLCAoKSA9PiB7XG4gICAgLy8gQ29uZmlybSB0aGUgcG9wc3RhdGUgZXZlbnQgd2FzIGEgYmFjayBidXR0b24gYWN0aW9uIGJ5IGNoZWNraW5nIHRoYXRcbiAgICAvLyB0aGUgdXNlciBjbGlja2VkIG91dCBvZiB0aGUgZG9jdW1lbnQuXG4gICAgaWYgKCF3aW5kb3cuaW5uZXJEb2NDbGljaykge1xuICAgICAgcGFnZS5zZXQoJHByZXZpb3VzUGFnZSk7XG4gICAgICBsb2FkKCRwYWdlKTtcbiAgICB9XG4gIH0pO1xuXG4gIHdpbmRvdy5vbmxvYWQgPSB7IG9uU2VhcmNoIH07XG4gIC8vIFJlbW92ZXMgaW5pdGlhbCBsb2FkZXIgaWYgaXQgZXhpc3RzLlxuICBjb25zdCBpbml0aWFsTG9hZGVyID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2luaXRpYWwtbG9hZGVyJyk7XG4gIGlmIChpbml0aWFsTG9hZGVyKSB7XG4gICAgaW5pdGlhbExvYWRlci5yZW1vdmUoKTtcbiAgfVxuPC9zY3JpcHQ+XG5cbjxNZWRpYVF1ZXJ5IHF1ZXJ5PVwiKG1pbi13aWR0aDogMTIwMHB4KVwiIGxldDptYXRjaGVzPlxuICA8UHJvamVjdEdyaWQge2xvYWRpbmd9IHtyb3dzfSB7cGFnZUluZGV4fSB7JHBhZ2VTaXplfSBsZXQ6cm93cz5cbiAgICA8ZGl2IHNsb3Q9XCJ0b3BcIj5cbiAgICAgIDxTZWFyY2hcbiAgICAgICAgb246c2VhcmNoPXtvblNlYXJjaH1cbiAgICAgICAgb246c29ydD17b25Tb3J0fVxuICAgICAgICBvbjphZHZhbmNlZEZpbHRlcj17b25BZHZhbmNlZEZpbHRlcn1cbiAgICAgICAgb246c2VsZWN0Q2F0ZWdvcnk9e29uU2VsZWN0Q2F0ZWdvcnl9XG4gICAgICAgIHtzZWFyY2hUZXh0fVxuICAgICAgLz5cbiAgICAgIHsjaWYgbWF0Y2hlc31cbiAgICAgICAgPGRpdiBjbGFzcz1cInByb2plY3QtYnJvd3Nlcl9fdG9nZ2xlLWJ1dHRvbnNcIj5cbiAgICAgICAgICA8YnV0dG9uXG4gICAgICAgICAgICBjbGFzczpwcm9qZWN0LWJyb3dzZXJfX3NlbGVjdGVkLXRhYj17dG9nZ2xlVmlldyA9PT0gJ0xpc3QnfVxuICAgICAgICAgICAgY2xhc3M9XCJwcm9qZWN0LWJyb3dzZXJfX3RvZ2dsZSBwcm9qZWN0LWJyb3dzZXJfX2xpc3QtYnV0dG9uXCJcbiAgICAgICAgICAgIHZhbHVlPVwiTGlzdFwiXG4gICAgICAgICAgICBvbjpjbGljaz17KGUpID0+IHtcbiAgICAgICAgICAgICAgdG9nZ2xlVmlldyA9ICdMaXN0JztcbiAgICAgICAgICAgICAgb25Ub2dnbGUoZS50YXJnZXQudmFsdWUpO1xuICAgICAgICAgICAgfX1cbiAgICAgICAgICA+XG4gICAgICAgICAgICA8aW1nXG4gICAgICAgICAgICAgIGNsYXNzPVwicHJvamVjdC1icm93c2VyX19saXN0LWljb25cIlxuICAgICAgICAgICAgICBzcmM9XCJ7RlVMTF9NT0RVTEVfUEFUSH0vaW1hZ2VzL2xpc3Quc3ZnXCJcbiAgICAgICAgICAgICAgYWx0PVwiXCJcbiAgICAgICAgICAgIC8+XG4gICAgICAgICAgICB7RHJ1cGFsLnQoJ0xpc3QnKX1cbiAgICAgICAgICA8L2J1dHRvbj5cbiAgICAgICAgICA8YnV0dG9uXG4gICAgICAgICAgICBjbGFzczpwcm9qZWN0LWJyb3dzZXJfX3NlbGVjdGVkLXRhYj17dG9nZ2xlVmlldyA9PT0gJ0dyaWQnfVxuICAgICAgICAgICAgY2xhc3M9XCJwcm9qZWN0LWJyb3dzZXJfX3RvZ2dsZSBwcm9qZWN0LWJyb3dzZXJfX2dyaWQtYnV0dG9uXCJcbiAgICAgICAgICAgIHZhbHVlPVwiR3JpZFwiXG4gICAgICAgICAgICBvbjpjbGljaz17KGUpID0+IHtcbiAgICAgICAgICAgICAgdG9nZ2xlVmlldyA9ICdHcmlkJztcbiAgICAgICAgICAgICAgb25Ub2dnbGUoZS50YXJnZXQudmFsdWUpO1xuICAgICAgICAgICAgfX1cbiAgICAgICAgICA+XG4gICAgICAgICAgICA8aW1nXG4gICAgICAgICAgICAgIGNsYXNzPVwicHJvamVjdC1icm93c2VyX19ncmlkLWljb25cIlxuICAgICAgICAgICAgICBzcmM9XCJ7RlVMTF9NT0RVTEVfUEFUSH0vaW1hZ2VzL2dyaWQtZmlsbC5zdmdcIlxuICAgICAgICAgICAgICBhbHQ9XCJcIlxuICAgICAgICAgICAgLz5cbiAgICAgICAgICAgIHtEcnVwYWwudCgnR3JpZCcpfVxuICAgICAgICAgIDwvYnV0dG9uPlxuICAgICAgICA8L2Rpdj5cbiAgICAgIHsvaWZ9XG4gICAgICB7I2lmIGRhdGFBcnJheS5sZW5ndGggPj0gMn1cbiAgICAgICAgPG5hdiBhcmlhLWxhYmVsPXtEcnVwYWwudCgnUGx1Z2luIHRhYnMnKX0+XG4gICAgICAgICAgPGRpdiBjbGFzcz1cInByb2plY3QtYnJvd3Nlcl9fcGx1Z2luLXRhYnNcIj5cbiAgICAgICAgICAgIHsjZWFjaCBkYXRhQXJyYXkgYXMgZGF0YVZhbHVlfVxuICAgICAgICAgICAgICA8YnV0dG9uXG4gICAgICAgICAgICAgICAgY2xhc3M6cHJvamVjdC1icm93c2VyX19zZWxlY3RlZC10YWI9eyRhY3RpdmVUYWIgPT09XG4gICAgICAgICAgICAgICAgICBkYXRhVmFsdWUucGx1Z2luSWR9XG4gICAgICAgICAgICAgICAgY2xhc3M9XCJwcm9qZWN0LWJyb3dzZXJfX3RvZ2dsZSBwcm9qZWN0LWJyb3dzZXJfX3BsdWdpbi10YWJcIlxuICAgICAgICAgICAgICAgIHZhbHVlPXtkYXRhVmFsdWUucGx1Z2luSWR9XG4gICAgICAgICAgICAgICAgb246Y2xpY2s9eyhlKSA9PiB7XG4gICAgICAgICAgICAgICAgICB0b2dnbGVSb3dzKGUudGFyZ2V0LnZhbHVlKTtcbiAgICAgICAgICAgICAgICB9fVxuICAgICAgICAgICAgICA+XG4gICAgICAgICAgICAgICAge2RhdGFWYWx1ZS5wbHVnaW5MYWJlbH1cbiAgICAgICAgICAgICAgICB7ZGF0YVZhbHVlLnRvdGFsUmVzdWx0c31cbiAgICAgICAgICAgICAgICB7RHJ1cGFsLnQoJ1Jlc3VsdHMnKX1cbiAgICAgICAgICAgICAgPC9idXR0b24+XG4gICAgICAgICAgICB7L2VhY2h9XG4gICAgICAgICAgPC9kaXY+XG4gICAgICAgIDwvbmF2PlxuICAgICAgey9pZn1cbiAgICAgIDwhLS0gSWYgVUkgaW5zdGFsbHMgYXJlIGVuYWJsZWQsIGJ1dCB0aGUgc2l0ZSBjb25maWd1cmF0aW9uIGRvZXMgbm90IHRoZW0sXG4gICAgICAgICAgIGRpc3BsYXkgYSBtZXNzYWdlIGluZm9ybWluZyB0aGUgdXNlciB3aGF0IG11c3QgYmUgY2hhbmdlZCBmb3IgVUlcbiAgICAgICAgICAgaW5zdGFsbHMgdG8gd29yay4gLS0+XG4gICAgICB7I2lmIFBNX1ZBTElEQVRJT05fRVJST1IgJiYgdHlwZW9mIFBNX1ZBTElEQVRJT05fRVJST1IgPT09ICdzdHJpbmcnICYmIE1PRFVMRV9TVEFUVVMucGFja2FnZV9tYW5hZ2VyICYmIEFMTE9XX1VJX0lOU1RBTEx9XG4gICAgICAgIDxkaXYgY2xhc3M9XCJwcm9qZWN0LWJyb3dzZXJfX2luc3RhbGwtd2FybmluZ1wiPlxuICAgICAgICAgIDxwIGNsYXNzPVwicHJvamVjdC1icm93c2VyX193YXJuaW5nLWhlYWRlclwiPlxuICAgICAgICAgICAgPHN0cm9uZz57RHJ1cGFsLnQoJ1VuYWJsZSB0byBkb3dubG9hZCBtb2R1bGVzIHZpYSB0aGUgVUknKX08L3N0cm9uZz5cbiAgICAgICAgICA8L3A+XG4gICAgICAgICAgPHAgY2xhc3M9XCJwcm9qZWN0LWJyb3dzZXJfX3dhcm5pbmdcIj5cbiAgICAgICAgICAgIDxlbT57QGh0bWwgUE1fVkFMSURBVElPTl9FUlJPUn08L2VtPlxuICAgICAgICAgIDwvcD5cbiAgICAgICAgPC9kaXY+XG4gICAgICB7L2lmfVxuICAgICAgPFBhZ2luYXRpb25cbiAgICAgICAgcGFnZT17JHBhZ2V9XG4gICAgICAgIGNvdW50PXskcm93c0NvdW50fVxuICAgICAgICBvbjpwYWdlQ2hhbmdlPXtvblBhZ2VDaGFuZ2V9XG4gICAgICAgIG9uOnBhZ2VTaXplQ2hhbmdlPXtvblBhZ2VTaXplQ2hhbmdlfVxuICAgICAgLz5cbiAgICA8L2Rpdj5cblxuICAgIDxkaXYgc2xvdD1cImxlZnRcIj5cbiAgICAgIDxGaWx0ZXJcbiAgICAgICAgb246c2VsZWN0Q2F0ZWdvcnk9e29uU2VsZWN0Q2F0ZWdvcnl9XG4gICAgICAgIGJpbmQ6dGhpcz17ZmlsdGVyQ29tcG9uZW50fVxuICAgICAgLz5cbiAgICA8L2Rpdj5cbiAgICB7I2VhY2ggcm93cyBhcyByb3csIGluZGV4IChyb3cpfVxuICAgICAgPFByb2plY3QgdG9nZ2xlVmlldz17IW1hdGNoZXMgPyAnR3JpZCcgOiB0b2dnbGVWaWV3fSBwcm9qZWN0PXtyb3d9IC8+XG4gICAgey9lYWNofVxuICAgIDxkaXYgc2xvdD1cImJvdHRvbVwiPlxuICAgICAgPFBhZ2luYXRpb25cbiAgICAgICAgcGFnZT17JHBhZ2V9XG4gICAgICAgIGNvdW50PXskcm93c0NvdW50fVxuICAgICAgICBvbjpwYWdlQ2hhbmdlPXtvblBhZ2VDaGFuZ2V9XG4gICAgICAgIG9uOnBhZ2VTaXplQ2hhbmdlPXtvblBhZ2VTaXplQ2hhbmdlfVxuICAgICAgLz5cbiAgICA8L2Rpdj5cbiAgPC9Qcm9qZWN0R3JpZD5cbjwvTWVkaWFRdWVyeT5cblxuPHN0eWxlPlxuICAucHJvamVjdC1icm93c2VyX190b2dnbGUge1xuICAgIGRpc3BsYXk6IGZsZXg7XG4gICAganVzdGlmeS1jb250ZW50OiBzcGFjZS1ldmVubHk7XG4gICAgYWxpZ24taXRlbXM6IGNlbnRlcjtcbiAgICBtYXJnaW4tYm90dG9tOiAxLjVlbTtcbiAgICBmb250LWZhbWlseTogaW5oZXJpdDtcbiAgICBjb2xvcjogIzIzMjQyOTtcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjZDNkNGQ5O1xuICAgIHdpZHRoOiA4MC40MXB4O1xuICAgIGhlaWdodDogMzBweDtcbiAgICBib3gtc2hhZG93OiAwIDJweCA0cHggcmdiYSgwLCAwLCAwLCAwLjI1KTtcbiAgICBib3JkZXI6IG5vbmU7XG4gIH1cblxuICAucHJvamVjdC1icm93c2VyX19saXN0LWljb24sXG4gIC5wcm9qZWN0LWJyb3dzZXJfX2dyaWQtaWNvbiB7XG4gICAgcG9pbnRlci1ldmVudHM6IG5vbmU7XG4gIH1cbiAgLnByb2plY3QtYnJvd3Nlcl9fdG9nZ2xlOmZpcnN0LWNoaWxkIHtcbiAgICBtYXJnaW4taW5saW5lLXN0YXJ0OiBhdXRvO1xuICB9XG4gIC5wcm9qZWN0LWJyb3dzZXJfX3RvZ2dsZS1idXR0b25zIHtcbiAgICBkaXNwbGF5OiBmbGV4O1xuICAgIG1hcmdpbi1pbmxpbmUtZW5kOiAyNXB4O1xuICB9XG4gIC5wcm9qZWN0LWJyb3dzZXJfX3RvZ2dsZTpmb2N1cyB7XG4gICAgYm94LXNoYWRvdzogMCAwIDAgMnB4ICNmZmYsIDAgMCAwIDVweCAjMjZhNzY5O1xuICB9XG4gIC5wcm9qZWN0LWJyb3dzZXJfX3RvZ2dsZS5wcm9qZWN0LWJyb3dzZXJfX2xpc3QtYnV0dG9uIHtcbiAgICBtYXJnaW4taW5saW5lLWVuZDogNXB4O1xuICAgIGJvcmRlci1yYWRpdXM6IDJweCAwIDAgMnB4O1xuICAgIGN1cnNvcjogcG9pbnRlcjtcbiAgfVxuICAucHJvamVjdC1icm93c2VyX190b2dnbGUucHJvamVjdC1icm93c2VyX19ncmlkLWJ1dHRvbiB7XG4gICAgYm9yZGVyLXJhZGl1czogMCAycHggMnB4IDA7XG4gICAgY3Vyc29yOiBwb2ludGVyO1xuICAgIG1hcmdpbi1pbmxpbmUtZW5kOiA1cHg7XG4gIH1cbiAgLnByb2plY3QtYnJvd3Nlcl9fc2VsZWN0ZWQtdGFiIHtcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjYWRhZWIzO1xuICB9XG4gIC5wcm9qZWN0LWJyb3dzZXJfX3BsdWdpbi10YWJzIHtcbiAgICBkaXNwbGF5OiBmbGV4O1xuICB9XG4gIC5wcm9qZWN0LWJyb3dzZXJfX3BsdWdpbi10YWIge1xuICAgIG1hcmdpbi1yaWdodDogNXB4O1xuICAgIG1hcmdpbi1sZWZ0OiA1cHg7XG4gICAgd2lkdGg6IDMzJTtcbiAgICBoZWlnaHQ6IGF1dG87XG4gICAgbWluLWhlaWdodDogMzBweDtcbiAgICBjdXJzb3I6IHBvaW50ZXI7XG4gIH1cbiAgLnByb2plY3QtYnJvd3Nlcl9fcGx1Z2luLXRhYnMgLnByb2plY3QtYnJvd3Nlcl9fdG9nZ2xlIHtcbiAgICBtYXJnaW4taW5saW5lLXN0YXJ0OiAwO1xuICB9XG4gIC5wcm9qZWN0LWJyb3dzZXJfX2luc3RhbGwtd2FybmluZyB7XG4gICAgYm9yZGVyOiAxcHggc29saWQgcmVkO1xuICAgIHBhZGRpbmc6IDFlbTtcbiAgfVxuICAucHJvamVjdC1icm93c2VyX193YXJuaW5nIHtcbiAgICBtYXJnaW46IDAuNWVtIDA7XG4gIH1cbiAgLnByb2plY3QtYnJvd3Nlcl9fd2FybmluZy1oZWFkZXIge1xuICAgIGNvbG9yOiByZWQ7XG4gIH1cbiAgQG1lZGlhIChmb3JjZWQtY29sb3JzOiBhY3RpdmUpIHtcbiAgICAucHJvamVjdC1icm93c2VyX190b2dnbGUge1xuICAgICAgYm9yZGVyOiAxcHggc29saWQ7XG4gICAgfVxuICAgIEBtZWRpYSAocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspIHtcbiAgICAgIC5wcm9qZWN0LWJyb3dzZXJfX2xpc3QtaWNvbiB7XG4gICAgICAgIGZpbHRlcjogaW52ZXJ0KDEpO1xuICAgICAgfVxuICAgICAgLnByb2plY3QtYnJvd3Nlcl9fZ3JpZC1pY29uIHtcbiAgICAgICAgZmlsdGVyOiBpbnZlcnQoMSk7XG4gICAgICB9XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQXFXRSw4Q0FBeUIsQ0FDdkIsT0FBTyxDQUFFLElBQUksQ0FDYixlQUFlLENBQUUsWUFBWSxDQUM3QixXQUFXLENBQUUsTUFBTSxDQUNuQixhQUFhLENBQUUsS0FBSyxDQUNwQixXQUFXLENBQUUsT0FBTyxDQUNwQixLQUFLLENBQUUsT0FBTyxDQUNkLGdCQUFnQixDQUFFLE9BQU8sQ0FDekIsS0FBSyxDQUFFLE9BQU8sQ0FDZCxNQUFNLENBQUUsSUFBSSxDQUNaLFVBQVUsQ0FBRSxDQUFDLENBQUMsR0FBRyxDQUFDLEdBQUcsQ0FBQyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLElBQUksQ0FBQyxDQUN6QyxNQUFNLENBQUUsSUFDVixDQUVBLGlEQUEyQixDQUMzQixpREFBNEIsQ0FDMUIsY0FBYyxDQUFFLElBQ2xCLENBQ0EsOENBQXdCLFlBQWEsQ0FDbkMsbUJBQW1CLENBQUUsSUFDdkIsQ0FDQSxzREFBaUMsQ0FDL0IsT0FBTyxDQUFFLElBQUksQ0FDYixpQkFBaUIsQ0FBRSxJQUNyQixDQUNBLDhDQUF3QixNQUFPLENBQzdCLFVBQVUsQ0FBRSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUMsR0FBRyxDQUFDLE9BQ3hDLENBQ0Esd0JBQXdCLG1EQUE4QixDQUNwRCxpQkFBaUIsQ0FBRSxHQUFHLENBQ3RCLGFBQWEsQ0FBRSxHQUFHLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxHQUFHLENBQzFCLE1BQU0sQ0FBRSxPQUNWLENBQ0Esd0JBQXdCLG1EQUE4QixDQUNwRCxhQUFhLENBQUUsQ0FBQyxDQUFDLEdBQUcsQ0FBQyxHQUFHLENBQUMsQ0FBQyxDQUMxQixNQUFNLENBQUUsT0FBTyxDQUNmLGlCQUFpQixDQUFFLEdBQ3JCLENBQ0Esb0RBQStCLENBQzdCLGdCQUFnQixDQUFFLE9BQ3BCLENBQ0EsbURBQThCLENBQzVCLE9BQU8sQ0FBRSxJQUNYLENBQ0Esa0RBQTZCLENBQzNCLFlBQVksQ0FBRSxHQUFHLENBQ2pCLFdBQVcsQ0FBRSxHQUFHLENBQ2hCLEtBQUssQ0FBRSxHQUFHLENBQ1YsTUFBTSxDQUFFLElBQUksQ0FDWixVQUFVLENBQUUsSUFBSSxDQUNoQixNQUFNLENBQUUsT0FDVixDQUNBLHdDQUE2QixDQUFDLG1DQUF5QixDQUNyRCxtQkFBbUIsQ0FBRSxDQUN2QixDQUNBLHVEQUFrQyxDQUNoQyxNQUFNLENBQUUsR0FBRyxDQUFDLEtBQUssQ0FBQyxHQUFHLENBQ3JCLE9BQU8sQ0FBRSxHQUNYLENBQ0EsK0NBQTBCLENBQ3hCLE1BQU0sQ0FBRSxLQUFLLENBQUMsQ0FDaEIsQ0FDQSxzREFBaUMsQ0FDL0IsS0FBSyxDQUFFLEdBQ1QsQ0FDQSxNQUFPLGdCQUFnQixNQUFNLENBQUUsQ0FDN0IsOENBQXlCLENBQ3ZCLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FDZCxDQUNBLE1BQU8sdUJBQXVCLElBQUksQ0FBRSxDQUNsQyxpREFBNEIsQ0FDMUIsTUFBTSxDQUFFLE9BQU8sQ0FBQyxDQUNsQixDQUNBLGlEQUE0QixDQUMxQixNQUFNLENBQUUsT0FBTyxDQUFDLENBQ2xCLENBQ0YsQ0FDRiJ9 */");
}

function get_each_context_1(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[44] = list[i];
	child_ctx[46] = i;
	return child_ctx;
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[41] = list[i];
	return child_ctx;
}

// (343:4) {#each rows as row, index (row)}
function create_each_block_1(key_1, ctx) {
	let first;
	let project;
	let current;

	project = new _Project_Project_svelte__WEBPACK_IMPORTED_MODULE_5__["default"]({
			props: {
				toggleView: !/*matches*/ ctx[40] ? 'Grid' : /*toggleView*/ ctx[4],
				project: /*row*/ ctx[44]
			},
			$$inline: true
		});

	const block = {
		key: key_1,
		first: null,
		c: function create() {
			first = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(project.$$.fragment);
			this.first = first;
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, first, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(project, target, anchor);
			current = true;
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;
			const project_changes = {};
			if (dirty[0] & /*toggleView*/ 16 | dirty[1] & /*matches*/ 512) project_changes.toggleView = !/*matches*/ ctx[40] ? 'Grid' : /*toggleView*/ ctx[4];
			if (dirty[0] & /*rows*/ 512) project_changes.project = /*row*/ ctx[44];
			project.$set(project_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(project.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(project.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(first);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(project, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block_1.name,
		type: "each",
		source: "(343:4) {#each rows as row, index (row)}",
		ctx
	});

	return block;
}

// (250:2) <ProjectGrid {loading} {rows} {pageIndex} {$pageSize} let:rows>
function create_default_slot_1(ctx) {
	let each_blocks = [];
	let each_1_lookup = new Map();
	let each_1_anchor;
	let current;
	let each_value_1 = /*rows*/ ctx[9];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value_1);
	const get_key = ctx => /*row*/ ctx[44];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_keys)(ctx, each_value_1, get_each_context_1, get_key);

	for (let i = 0; i < each_value_1.length; i += 1) {
		let child_ctx = get_each_context_1(ctx, each_value_1, i);
		let key = get_key(child_ctx);
		each_1_lookup.set(key, each_blocks[i] = create_each_block_1(key, child_ctx));
	}

	const block = {
		c: function create() {
			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			each_1_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(target, anchor);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, each_1_anchor, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			if (dirty[0] & /*toggleView, rows*/ 528 | dirty[1] & /*matches*/ 512) {
				each_value_1 = /*rows*/ ctx[9];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value_1);
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_keys)(ctx, each_value_1, get_each_context_1, get_key);
				each_blocks = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_keyed_each)(each_blocks, dirty, get_key, 1, ctx, each_value_1, each_1_lookup, each_1_anchor.parentNode, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.outro_and_destroy_block, create_each_block_1, each_1_anchor, get_each_context_1);
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}
		},
		i: function intro(local) {
			if (current) return;

			for (let i = 0; i < each_value_1.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i]);
			}

			current = true;
		},
		o: function outro(local) {
			for (let i = 0; i < each_blocks.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks[i]);
			}

			current = false;
		},
		d: function destroy(detaching) {
			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].d(detaching);
			}

			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(each_1_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_default_slot_1.name,
		type: "slot",
		source: "(250:2) <ProjectGrid {loading} {rows} {pageIndex} {$pageSize} let:rows>",
		ctx
	});

	return block;
}

// (259:6) {#if matches}
function create_if_block_2(ctx) {
	let div;
	let button0;
	let img0;
	let img0_src_value;
	let t0;
	let t1_value = /*Drupal*/ ctx[10].t('List') + "";
	let t1;
	let t2;
	let button1;
	let img1;
	let img1_src_value;
	let t3;
	let t4_value = /*Drupal*/ ctx[10].t('Grid') + "";
	let t4;
	let mounted;
	let dispose;

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			button0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			img0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t1_value);
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			button1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			img1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t4_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img0, "class", "project-browser__list-icon pb-1l3u22a");
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img0.src, img0_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_8__.FULL_MODULE_PATH + "/images/list.svg"))) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img0, "src", img0_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img0, "alt", "");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img0, file, 269, 12, 7700);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button0, "class", "project-browser__toggle project-browser__list-button pb-1l3u22a");
			button0.value = "List";
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button0, "project-browser__selected-tab", /*toggleView*/ ctx[4] === 'List');
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button0, file, 260, 10, 7377);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img1, "class", "project-browser__grid-icon pb-1l3u22a");
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img1.src, img1_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_8__.FULL_MODULE_PATH + "/images/grid-fill.svg"))) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img1, "src", img1_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img1, "alt", "");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img1, file, 285, 12, 8229);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button1, "class", "project-browser__toggle project-browser__grid-button pb-1l3u22a");
			button1.value = "Grid";
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button1, "project-browser__selected-tab", /*toggleView*/ ctx[4] === 'Grid');
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button1, file, 276, 10, 7906);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "project-browser__toggle-buttons pb-1l3u22a");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 259, 8, 7321);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, button0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button0, img0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button0, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button0, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, button1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button1, img1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button1, t3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button1, t4);

			if (!mounted) {
				dispose = [
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button0, "click", /*click_handler*/ ctx[22], false, false, false, false),
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button1, "click", /*click_handler_1*/ ctx[23], false, false, false, false)
				];

				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			if (dirty[0] & /*toggleView*/ 16) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button0, "project-browser__selected-tab", /*toggleView*/ ctx[4] === 'List');
			}

			if (dirty[0] & /*toggleView*/ 16) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button1, "project-browser__selected-tab", /*toggleView*/ ctx[4] === 'Grid');
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			mounted = false;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.run_all)(dispose);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(259:6) {#if matches}",
		ctx
	});

	return block;
}

// (295:6) {#if dataArray.length >= 2}
function create_if_block_1(ctx) {
	let nav;
	let div;
	let nav_aria_label_value;
	let each_value = /*dataArray*/ ctx[2];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const block = {
		c: function create() {
			nav = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("nav");
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");

			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "project-browser__plugin-tabs pb-1l3u22a");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 296, 10, 8552);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(nav, "aria-label", nav_aria_label_value = /*Drupal*/ ctx[10].t('Plugin tabs'));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(nav, file, 295, 8, 8499);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, nav, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(nav, div);

			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(div, null);
				}
			}
		},
		p: function update(ctx, dirty) {
			if (dirty[0] & /*dataArray, $activeTab, toggleRows, Drupal*/ 1049668) {
				each_value = /*dataArray*/ ctx[2];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						each_blocks[i].m(div, null);
					}
				}

				for (; i < each_blocks.length; i += 1) {
					each_blocks[i].d(1);
				}

				each_blocks.length = each_value.length;
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(nav);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(295:6) {#if dataArray.length >= 2}",
		ctx
	});

	return block;
}

// (298:12) {#each dataArray as dataValue}
function create_each_block(ctx) {
	let button;
	let t0_value = /*dataValue*/ ctx[41].pluginLabel + "";
	let t0;
	let t1;
	let t2_value = /*dataValue*/ ctx[41].totalResults + "";
	let t2;
	let t3;
	let t4_value = /*Drupal*/ ctx[10].t('Results') + "";
	let t4;
	let t5;
	let button_value_value;
	let mounted;
	let dispose;

	const block = {
		c: function create() {
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t2_value);
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t4_value);
			t5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "class", "project-browser__toggle project-browser__plugin-tab pb-1l3u22a");
			button.value = button_value_value = /*dataValue*/ ctx[41].pluginId;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "project-browser__selected-tab", /*$activeTab*/ ctx[6] === /*dataValue*/ ctx[41].pluginId);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 298, 14, 8652);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, button, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t5);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button, "click", /*click_handler_2*/ ctx[24], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			if (dirty[0] & /*dataArray*/ 4 && t0_value !== (t0_value = /*dataValue*/ ctx[41].pluginLabel + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, t0_value);
			if (dirty[0] & /*dataArray*/ 4 && t2_value !== (t2_value = /*dataValue*/ ctx[41].totalResults + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t2, t2_value);

			if (dirty[0] & /*dataArray*/ 4 && button_value_value !== (button_value_value = /*dataValue*/ ctx[41].pluginId)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prop_dev)(button, "value", button_value_value);
			}

			if (dirty[0] & /*$activeTab, dataArray*/ 68) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "project-browser__selected-tab", /*$activeTab*/ ctx[6] === /*dataValue*/ ctx[41].pluginId);
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(button);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(298:12) {#each dataArray as dataValue}",
		ctx
	});

	return block;
}

// (319:6) {#if PM_VALIDATION_ERROR && typeof PM_VALIDATION_ERROR === 'string' && MODULE_STATUS.package_manager && ALLOW_UI_INSTALL}
function create_if_block(ctx) {
	let div;
	let p0;
	let strong;
	let t1;
	let p1;
	let em;

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			p0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			strong = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("strong");
			strong.textContent = `${/*Drupal*/ ctx[10].t('Unable to download modules via the UI')}`;
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			p1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			em = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("em");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(strong, file, 321, 12, 9647);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p0, "class", "project-browser__warning-header pb-1l3u22a");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p0, file, 320, 10, 9591);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(em, file, 324, 12, 9790);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p1, "class", "project-browser__warning pb-1l3u22a");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p1, file, 323, 10, 9741);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "project-browser__install-warning pb-1l3u22a");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 319, 8, 9534);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, p0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p0, strong);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, p1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p1, em);
			em.innerHTML = _constants__WEBPACK_IMPORTED_MODULE_8__.PM_VALIDATION_ERROR;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(319:6) {#if PM_VALIDATION_ERROR && typeof PM_VALIDATION_ERROR === 'string' && MODULE_STATUS.package_manager && ALLOW_UI_INSTALL}",
		ctx
	});

	return block;
}

// (251:4) 
function create_top_slot(ctx) {
	let div;
	let search;
	let t0;
	let t1;
	let t2;
	let t3;
	let pagination;
	let current;

	search = new _ProjectGrid_svelte__WEBPACK_IMPORTED_MODULE_3__.Search({
			props: { searchText: /*searchText*/ ctx[0] },
			$$inline: true
		});

	search.$on("search", /*onSearch*/ ctx[15]);
	search.$on("sort", /*onSort*/ ctx[17]);
	search.$on("advancedFilter", /*onAdvancedFilter*/ ctx[18]);
	search.$on("selectCategory", /*onSelectCategory*/ ctx[16]);
	let if_block0 = /*matches*/ ctx[40] && create_if_block_2(ctx);
	let if_block1 = /*dataArray*/ ctx[2].length >= 2 && create_if_block_1(ctx);
	let if_block2 = _constants__WEBPACK_IMPORTED_MODULE_8__.PM_VALIDATION_ERROR && typeof _constants__WEBPACK_IMPORTED_MODULE_8__.PM_VALIDATION_ERROR === 'string' && _constants__WEBPACK_IMPORTED_MODULE_8__.MODULE_STATUS.package_manager && _constants__WEBPACK_IMPORTED_MODULE_8__.ALLOW_UI_INSTALL && create_if_block(ctx);

	pagination = new _Pagination_svelte__WEBPACK_IMPORTED_MODULE_4__["default"]({
			props: {
				page: /*$page*/ ctx[1],
				count: /*$rowsCount*/ ctx[7]
			},
			$$inline: true
		});

	pagination.$on("pageChange", /*onPageChange*/ ctx[13]);
	pagination.$on("pageSizeChange", /*onPageSizeChange*/ ctx[14]);

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(search.$$.fragment);
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block0) if_block0.c();
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block1) if_block1.c();
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block2) if_block2.c();
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(pagination.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "slot", "top");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 250, 4, 7088);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(search, div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t0);
			if (if_block0) if_block0.m(div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t1);
			if (if_block1) if_block1.m(div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t2);
			if (if_block2) if_block2.m(div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(pagination, div, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			const search_changes = {};
			if (dirty[0] & /*searchText*/ 1) search_changes.searchText = /*searchText*/ ctx[0];
			search.$set(search_changes);

			if (/*matches*/ ctx[40]) {
				if (if_block0) {
					if_block0.p(ctx, dirty);
				} else {
					if_block0 = create_if_block_2(ctx);
					if_block0.c();
					if_block0.m(div, t1);
				}
			} else if (if_block0) {
				if_block0.d(1);
				if_block0 = null;
			}

			if (/*dataArray*/ ctx[2].length >= 2) {
				if (if_block1) {
					if_block1.p(ctx, dirty);
				} else {
					if_block1 = create_if_block_1(ctx);
					if_block1.c();
					if_block1.m(div, t2);
				}
			} else if (if_block1) {
				if_block1.d(1);
				if_block1 = null;
			}

			if (_constants__WEBPACK_IMPORTED_MODULE_8__.PM_VALIDATION_ERROR && typeof _constants__WEBPACK_IMPORTED_MODULE_8__.PM_VALIDATION_ERROR === 'string' && _constants__WEBPACK_IMPORTED_MODULE_8__.MODULE_STATUS.package_manager && _constants__WEBPACK_IMPORTED_MODULE_8__.ALLOW_UI_INSTALL) if_block2.p(ctx, dirty);
			const pagination_changes = {};
			if (dirty[0] & /*$page*/ 2) pagination_changes.page = /*$page*/ ctx[1];
			if (dirty[0] & /*$rowsCount*/ 128) pagination_changes.count = /*$rowsCount*/ ctx[7];
			pagination.$set(pagination_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(search.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(pagination.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(search.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(pagination.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(search);
			if (if_block0) if_block0.d();
			if (if_block1) if_block1.d();
			if (if_block2) if_block2.d();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(pagination);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_top_slot.name,
		type: "slot",
		source: "(251:4) ",
		ctx
	});

	return block;
}

// (337:4) 
function create_left_slot(ctx) {
	let div;
	let filter;
	let current;
	let filter_props = {};
	filter = new _ProjectGrid_svelte__WEBPACK_IMPORTED_MODULE_3__.Filter({ props: filter_props, $$inline: true });
	/*filter_binding*/ ctx[21](filter);
	filter.$on("selectCategory", /*onSelectCategory*/ ctx[16]);

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(filter.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "slot", "left");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 336, 4, 10042);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(filter, div, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			const filter_changes = {};
			filter.$set(filter_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(filter.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(filter.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			/*filter_binding*/ ctx[21](null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(filter);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_left_slot.name,
		type: "slot",
		source: "(337:4) ",
		ctx
	});

	return block;
}

// (346:4) 
function create_bottom_slot(ctx) {
	let div;
	let pagination;
	let current;

	pagination = new _Pagination_svelte__WEBPACK_IMPORTED_MODULE_4__["default"]({
			props: {
				page: /*$page*/ ctx[1],
				count: /*$rowsCount*/ ctx[7]
			},
			$$inline: true
		});

	pagination.$on("pageChange", /*onPageChange*/ ctx[13]);
	pagination.$on("pageSizeChange", /*onPageSizeChange*/ ctx[14]);

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(pagination.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "slot", "bottom");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 345, 4, 10304);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(pagination, div, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			const pagination_changes = {};
			if (dirty[0] & /*$page*/ 2) pagination_changes.page = /*$page*/ ctx[1];
			if (dirty[0] & /*$rowsCount*/ 128) pagination_changes.count = /*$rowsCount*/ ctx[7];
			pagination.$set(pagination_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(pagination.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(pagination.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(pagination);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_bottom_slot.name,
		type: "slot",
		source: "(346:4) ",
		ctx
	});

	return block;
}

// (249:0) <MediaQuery query="(min-width: 1200px)" let:matches>
function create_default_slot(ctx) {
	let projectgrid;
	let current;

	projectgrid = new _ProjectGrid_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: {
				loading: /*loading*/ ctx[3],
				rows: /*rows*/ ctx[9],
				pageIndex,
				$pageSize: /*$pageSize*/ ctx[8],
				$$slots: {
					bottom: [
						create_bottom_slot,
						({ rows }) => ({ 9: rows }),
						({ rows }) => [rows ? 512 : 0]
					],
					left: [
						create_left_slot,
						({ rows }) => ({ 9: rows }),
						({ rows }) => [rows ? 512 : 0]
					],
					top: [
						create_top_slot,
						({ rows }) => ({ 9: rows }),
						({ rows }) => [rows ? 512 : 0]
					],
					default: [
						create_default_slot_1,
						({ rows }) => ({ 9: rows }),
						({ rows }) => [rows ? 512 : 0]
					]
				},
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projectgrid.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projectgrid, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const projectgrid_changes = {};
			if (dirty[0] & /*loading*/ 8) projectgrid_changes.loading = /*loading*/ ctx[3];
			if (dirty[0] & /*rows*/ 512) projectgrid_changes.rows = /*rows*/ ctx[9];
			if (dirty[0] & /*$pageSize*/ 256) projectgrid_changes.$pageSize = /*$pageSize*/ ctx[8];

			if (dirty[0] & /*$page, $rowsCount, filterComponent, dataArray, $activeTab, toggleView, searchText, rows*/ 759 | dirty[1] & /*$$scope, matches*/ 66048) {
				projectgrid_changes.$$scope = { dirty, ctx };
			}

			projectgrid.$set(projectgrid_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projectgrid.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projectgrid.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projectgrid, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_default_slot.name,
		type: "slot",
		source: "(249:0) <MediaQuery query=\\\"(min-width: 1200px)\\\" let:matches>",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let mediaquery;
	let current;

	mediaquery = new _MediaQuery_svelte__WEBPACK_IMPORTED_MODULE_7__["default"]({
			props: {
				query: "(min-width: 1200px)",
				$$slots: {
					default: [
						create_default_slot,
						({ matches }) => ({ 40: matches }),
						({ matches }) => [0, matches ? 512 : 0]
					]
				},
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(mediaquery.$$.fragment);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(mediaquery, target, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			const mediaquery_changes = {};

			if (dirty[0] & /*loading, rows, $pageSize, $page, $rowsCount, filterComponent, dataArray, $activeTab, toggleView, searchText*/ 1023 | dirty[1] & /*$$scope, matches*/ 66048) {
				mediaquery_changes.$$scope = { dirty, ctx };
			}

			mediaquery.$set(mediaquery_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(mediaquery.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(mediaquery.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(mediaquery, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

const pageIndex = 0; // first row

function instance($$self, $$props, $$invalidate) {
	let $page;
	let $previousPage;
	let $sortCriteria;
	let $sort;
	let $activeTab;
	let $categoryCheckedTrack;
	let $moduleCategoryFilter;
	let $filters;
	let $focusedElement;
	let $isFirstLoad;
	let $rowsCount;
	let $pageSize;
	let $currentPage;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.page, 'page');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.page, $$value => $$invalidate(1, $page = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.sortCriteria, 'sortCriteria');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.sortCriteria, $$value => $$invalidate(29, $sortCriteria = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.sort, 'sort');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.sort, $$value => $$invalidate(30, $sort = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.activeTab, 'activeTab');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.activeTab, $$value => $$invalidate(6, $activeTab = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.categoryCheckedTrack, 'categoryCheckedTrack');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.categoryCheckedTrack, $$value => $$invalidate(31, $categoryCheckedTrack = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter, 'moduleCategoryFilter');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter, $$value => $$invalidate(32, $moduleCategoryFilter = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.filters, 'filters');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.filters, $$value => $$invalidate(33, $filters = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.focusedElement, 'focusedElement');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.focusedElement, $$value => $$invalidate(34, $focusedElement = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.isFirstLoad, 'isFirstLoad');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.isFirstLoad, $$value => $$invalidate(35, $isFirstLoad = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.rowsCount, 'rowsCount');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.rowsCount, $$value => $$invalidate(7, $rowsCount = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_6__.pageSize, 'pageSize');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_6__.pageSize, $$value => $$invalidate(8, $pageSize = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ProjectBrowser', slots, []);
	const { Drupal } = window;
	let data;
	let rows = [];
	let sources = [];
	let dataArray = [];
	let loading = true;
	let { searchText } = $$props;

	_stores__WEBPACK_IMPORTED_MODULE_6__.searchString.subscribe(value => {
		$$invalidate(0, searchText = value);
	});

	let toggleView = 'Grid';

	_stores__WEBPACK_IMPORTED_MODULE_6__.preferredView.subscribe(value => {
		$$invalidate(4, toggleView = value);
	});

	const [currentPage, previousPage] = (0,svelte_previous__WEBPACK_IMPORTED_MODULE_2__.withPrevious)(0);
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(currentPage, 'currentPage');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, currentPage, value => $$invalidate(36, $currentPage = value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(previousPage, 'previousPage');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, previousPage, value => $$invalidate(28, $previousPage = value));
	let element = '';

	_stores__WEBPACK_IMPORTED_MODULE_6__.focusedElement.subscribe(value => {
		element = value;
	});

	let filterComponent;

	/**
 * Load data from Drupal.org API.
 *
 * @param {number|string} _page
 *   The page number.
 *
 * @return {Promise<void>}
 *   Empty promise that resolves on content load.*
 */
	async function load(_page) {
		$$invalidate(3, loading = true);

		const searchParams = new URLSearchParams({
				page: _page,
				limit: $pageSize,
				sort: $sort,
				source: $activeTab
			});

		if (searchText) {
			searchParams.set('search', searchText);
		}

		if ($moduleCategoryFilter && $moduleCategoryFilter.length) {
			searchParams.set('categories', $moduleCategoryFilter);
		}

		if ($filters.developmentStatus && $filters.developmentStatus.length) {
			searchParams.set('development_status', $filters.developmentStatus);
		}

		if ($filters.maintenanceStatus && $filters.maintenanceStatus.length) {
			searchParams.set('maintenance_status', $filters.maintenanceStatus);
		}

		if ($filters.securityCoverage && $filters.securityCoverage.length) {
			searchParams.set('security_advisory_coverage', $filters.securityCoverage);
		}

		if (Object.keys($categoryCheckedTrack).length !== 0) {
			searchParams.set('tabwise_categories', JSON.stringify($categoryCheckedTrack));
		}

		const url = `${_constants__WEBPACK_IMPORTED_MODULE_8__.ORIGIN_URL}/drupal-org-proxy/project?${searchParams.toString()}`;
		const res = await fetch(url);

		if (res.ok) {
			data = await res.json();

			// A list of the available sources to get project data.
			sources = Object.keys(data);

			$$invalidate(2, dataArray = Object.values(data));
			$$invalidate(9, rows = data[$activeTab].list);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.rowsCount, $rowsCount = data[$activeTab].totalResults, $rowsCount);
		} else {
			$$invalidate(9, rows = []);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.rowsCount, $rowsCount = 0, $rowsCount);
		}

		$$invalidate(3, loading = false);
	}

	async function filterRecommended() {
		// Show recommended projects on initial page load only when no filters are applied.
		if ($filters.developmentStatus.length === 0 && $filters.maintenanceStatus.length === 0 && $filters.securityCoverage.length === 0) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.filters, $filters.maintenanceStatus = _constants__WEBPACK_IMPORTED_MODULE_8__.ACTIVELY_MAINTAINED_ID, $filters);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.filters, $filters.securityCoverage = _constants__WEBPACK_IMPORTED_MODULE_8__.COVERED_ID, $filters);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.filters, $filters.developmentStatus = _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID, $filters);
		}

		_stores__WEBPACK_IMPORTED_MODULE_6__.isFirstLoad.set(false);
	}

	/**
 * Load remote data when the Svelte component is mounted.
 */
	(0,svelte__WEBPACK_IMPORTED_MODULE_1__.onMount)(async () => {
		// If current active plugin is disabled, remove storage keys and reload page.
		const settingsActiveTab = JSON.stringify(_constants__WEBPACK_IMPORTED_MODULE_8__.DEFAULT_SOURCE_ID);

		if ($activeTab !== settingsActiveTab && _constants__WEBPACK_IMPORTED_MODULE_8__.CURRENT_SOURCES_KEYS.indexOf($activeTab) === -1) {
			sessionStorage.removeItem('activeTab');
			sessionStorage.removeItem('categoryFilter');
			sessionStorage.removeItem('categoryCheckedTrack');
			sessionStorage.setItem('activeTab', settingsActiveTab);
			window.location.reload();
		}

		// Only filter by recommended on first page load.
		if ($isFirstLoad) {
			await filterRecommended();
		}

		await load($page);
		const focus = document.getElementById(element);

		if (focus) {
			focus.focus();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.focusedElement, $focusedElement = '', $focusedElement);
		}
	});

	function onPageChange(event) {
		_stores__WEBPACK_IMPORTED_MODULE_6__.page.set(event.detail.page);
		load($page);
	}

	function onPageSizeChange() {
		_stores__WEBPACK_IMPORTED_MODULE_6__.page.set(0);
		load($page);
	}

	async function onSearch(event) {
		$$invalidate(0, searchText = event.detail.searchText);
		await load(0);
		_stores__WEBPACK_IMPORTED_MODULE_6__.page.set(0);
	}

	async function onSelectCategory(event) {
		_stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter.set(event.detail.category);
		await load(0);
		_stores__WEBPACK_IMPORTED_MODULE_6__.page.set(0);
	}

	async function onSort(event) {
		_stores__WEBPACK_IMPORTED_MODULE_6__.sort.set(event.detail.sort);
		await load(0);
		_stores__WEBPACK_IMPORTED_MODULE_6__.page.set(0);
	}

	async function onAdvancedFilter(event) {
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.filters, $filters.developmentStatus = event.detail.developmentStatus, $filters);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.filters, $filters.maintenanceStatus = event.detail.maintenanceStatus, $filters);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.filters, $filters.securityCoverage = event.detail.securityCoverage, $filters);
		await load(0);
		_stores__WEBPACK_IMPORTED_MODULE_6__.page.set(0);
	}

	async function onToggle(val) {
		if (val !== toggleView) $$invalidate(4, toggleView = val);
		_stores__WEBPACK_IMPORTED_MODULE_6__.preferredView.set(val);
	}

	async function toggleRows(val) {
		filterComponent.setModuleCategoryVocabulary();
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.categoryCheckedTrack, $categoryCheckedTrack[$activeTab] = $moduleCategoryFilter, $categoryCheckedTrack);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter, $moduleCategoryFilter = [], $moduleCategoryFilter);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.activeTab, $activeTab = val, $activeTab);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(
			_stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter,
			$moduleCategoryFilter = typeof $categoryCheckedTrack[$activeTab] !== 'undefined'
			? $categoryCheckedTrack[$activeTab]
			: [],
			$moduleCategoryFilter
		);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.sortCriteria, $sortCriteria = _constants__WEBPACK_IMPORTED_MODULE_8__.SORT_OPTIONS[$activeTab], $sortCriteria);
		const sortMatch = $sortCriteria.find(option => option.id === $sort);

		if (typeof sortMatch === 'undefined') {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_6__.sort, $sort = $sortCriteria[0].id, $sort);
		}

		// Move to page 0 when switching sources as there's no guarantee the new
		// source has enough results to reach whatever the current page is.
		_stores__WEBPACK_IMPORTED_MODULE_6__.page.set(0);

		await load(0);
	}

	document.onmouseover = function setInnerDocClickTrue() {
		window.innerDocClick = true;
	};

	document.onmouseleave = function setInnerDocClickFalse() {
		window.innerDocClick = false;
	};

	// Handles back button functionality to go back to the previous page the user was on before.
	window.addEventListener('popstate', () => {
		// Confirm the popstate event was a back button action by checking that
		// the user clicked out of the document.
		if (!window.innerDocClick) {
			_stores__WEBPACK_IMPORTED_MODULE_6__.page.set($previousPage);
			load($page);
		}
	});

	window.onload = { onSearch };

	// Removes initial loader if it exists.
	const initialLoader = document.getElementById('initial-loader');

	if (initialLoader) {
		initialLoader.remove();
	}

	$$self.$$.on_mount.push(function () {
		if (searchText === undefined && !('searchText' in $$props || $$self.$$.bound[$$self.$$.props['searchText']])) {
			console.warn("<ProjectBrowser> was created without expected prop 'searchText'");
		}
	});

	const writable_props = ['searchText'];

	Object_1.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ProjectBrowser> was created with unknown prop '${key}'`);
	});

	function filter_binding($$value) {
		svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks[$$value ? 'unshift' : 'push'](() => {
			filterComponent = $$value;
			$$invalidate(5, filterComponent);
		});
	}

	const click_handler = e => {
		$$invalidate(4, toggleView = 'List');
		onToggle(e.target.value);
	};

	const click_handler_1 = e => {
		$$invalidate(4, toggleView = 'Grid');
		onToggle(e.target.value);
	};

	const click_handler_2 = e => {
		toggleRows(e.target.value);
	};

	$$self.$$set = $$props => {
		if ('searchText' in $$props) $$invalidate(0, searchText = $$props.searchText);
	};

	$$self.$capture_state = () => ({
		onMount: svelte__WEBPACK_IMPORTED_MODULE_1__.onMount,
		withPrevious: svelte_previous__WEBPACK_IMPORTED_MODULE_2__.withPrevious,
		ProjectGrid: _ProjectGrid_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		Search: _ProjectGrid_svelte__WEBPACK_IMPORTED_MODULE_3__.Search,
		Filter: _ProjectGrid_svelte__WEBPACK_IMPORTED_MODULE_3__.Filter,
		Pagination: _Pagination_svelte__WEBPACK_IMPORTED_MODULE_4__["default"],
		Project: _Project_Project_svelte__WEBPACK_IMPORTED_MODULE_5__["default"],
		filters: _stores__WEBPACK_IMPORTED_MODULE_6__.filters,
		rowsCount: _stores__WEBPACK_IMPORTED_MODULE_6__.rowsCount,
		moduleCategoryFilter: _stores__WEBPACK_IMPORTED_MODULE_6__.moduleCategoryFilter,
		isFirstLoad: _stores__WEBPACK_IMPORTED_MODULE_6__.isFirstLoad,
		page: _stores__WEBPACK_IMPORTED_MODULE_6__.page,
		sort: _stores__WEBPACK_IMPORTED_MODULE_6__.sort,
		focusedElement: _stores__WEBPACK_IMPORTED_MODULE_6__.focusedElement,
		searchString: _stores__WEBPACK_IMPORTED_MODULE_6__.searchString,
		activeTab: _stores__WEBPACK_IMPORTED_MODULE_6__.activeTab,
		categoryCheckedTrack: _stores__WEBPACK_IMPORTED_MODULE_6__.categoryCheckedTrack,
		sortCriteria: _stores__WEBPACK_IMPORTED_MODULE_6__.sortCriteria,
		preferredView: _stores__WEBPACK_IMPORTED_MODULE_6__.preferredView,
		pageSize: _stores__WEBPACK_IMPORTED_MODULE_6__.pageSize,
		MediaQuery: _MediaQuery_svelte__WEBPACK_IMPORTED_MODULE_7__["default"],
		ACTIVELY_MAINTAINED_ID: _constants__WEBPACK_IMPORTED_MODULE_8__.ACTIVELY_MAINTAINED_ID,
		COVERED_ID: _constants__WEBPACK_IMPORTED_MODULE_8__.COVERED_ID,
		ALL_VALUES_ID: _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID,
		DEFAULT_SOURCE_ID: _constants__WEBPACK_IMPORTED_MODULE_8__.DEFAULT_SOURCE_ID,
		CURRENT_SOURCES_KEYS: _constants__WEBPACK_IMPORTED_MODULE_8__.CURRENT_SOURCES_KEYS,
		ORIGIN_URL: _constants__WEBPACK_IMPORTED_MODULE_8__.ORIGIN_URL,
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_8__.FULL_MODULE_PATH,
		SORT_OPTIONS: _constants__WEBPACK_IMPORTED_MODULE_8__.SORT_OPTIONS,
		MODULE_STATUS: _constants__WEBPACK_IMPORTED_MODULE_8__.MODULE_STATUS,
		ALLOW_UI_INSTALL: _constants__WEBPACK_IMPORTED_MODULE_8__.ALLOW_UI_INSTALL,
		PM_VALIDATION_ERROR: _constants__WEBPACK_IMPORTED_MODULE_8__.PM_VALIDATION_ERROR,
		Drupal,
		data,
		rows,
		sources,
		dataArray,
		pageIndex,
		loading,
		searchText,
		toggleView,
		currentPage,
		previousPage,
		element,
		filterComponent,
		load,
		filterRecommended,
		onPageChange,
		onPageSizeChange,
		onSearch,
		onSelectCategory,
		onSort,
		onAdvancedFilter,
		onToggle,
		toggleRows,
		initialLoader,
		$page,
		$previousPage,
		$sortCriteria,
		$sort,
		$activeTab,
		$categoryCheckedTrack,
		$moduleCategoryFilter,
		$filters,
		$focusedElement,
		$isFirstLoad,
		$rowsCount,
		$pageSize,
		$currentPage
	});

	$$self.$inject_state = $$props => {
		if ('data' in $$props) data = $$props.data;
		if ('rows' in $$props) $$invalidate(9, rows = $$props.rows);
		if ('sources' in $$props) sources = $$props.sources;
		if ('dataArray' in $$props) $$invalidate(2, dataArray = $$props.dataArray);
		if ('loading' in $$props) $$invalidate(3, loading = $$props.loading);
		if ('searchText' in $$props) $$invalidate(0, searchText = $$props.searchText);
		if ('toggleView' in $$props) $$invalidate(4, toggleView = $$props.toggleView);
		if ('element' in $$props) element = $$props.element;
		if ('filterComponent' in $$props) $$invalidate(5, filterComponent = $$props.filterComponent);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	$$self.$$.update = () => {
		if ($$self.$$.dirty[0] & /*$page*/ 2) {
			$: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(currentPage, $currentPage = $page, $currentPage);
		}
	};

	return [
		searchText,
		$page,
		dataArray,
		loading,
		toggleView,
		filterComponent,
		$activeTab,
		$rowsCount,
		$pageSize,
		rows,
		Drupal,
		currentPage,
		previousPage,
		onPageChange,
		onPageSizeChange,
		onSearch,
		onSelectCategory,
		onSort,
		onAdvancedFilter,
		onToggle,
		toggleRows,
		filter_binding,
		click_handler,
		click_handler_1,
		click_handler_2
	];
}

class ProjectBrowser extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { searchText: 0 }, add_css, [-1, -1]);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ProjectBrowser",
			options,
			id: create_fragment.name
		});
	}

	get searchText() {
		throw new Error("<ProjectBrowser>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set searchText(value) {
		throw new Error("<ProjectBrowser>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ProjectBrowser);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/ProjectGrid.svelte":
/*!*****************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/ProjectGrid.svelte ***!
  \*****************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Filter": () => (/* reexport safe */ _Filter_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]),
/* harmony export */   "Search": () => (/* reexport safe */ _Search_Search_svelte__WEBPACK_IMPORTED_MODULE_4__["default"]),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _Loading_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Loading.svelte */ "./modules/project_browser/sveltejs/src/Loading.svelte");
/* harmony import */ var _Filter_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./Filter.svelte */ "./modules/project_browser/sveltejs/src/Filter.svelte");
/* harmony import */ var _Search_Search_svelte__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./Search/Search.svelte */ "./modules/project_browser/sveltejs/src/Search/Search.svelte");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/ProjectGrid.svelte generated by Svelte v3.56.0 */







const file = "modules/project_browser/sveltejs/src/ProjectGrid.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-18bqkxf", ".projects-list.pb-18bqkxf{display:flex;flex-wrap:wrap;list-style-type:none;margin:0}.project-browser__aside.pb-18bqkxf{flex:1}.project-browser__main.pb-18bqkxf{flex:4}@media(min-width: 700px){.project-browser__container.pb-18bqkxf{display:flex}}@media screen and (max-width: 900px){.project-browser__container.pb-18bqkxf{flex-direction:column}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiUHJvamVjdEdyaWQuc3ZlbHRlIiwic291cmNlcyI6WyJQcm9qZWN0R3JpZC5zdmVsdGUiXSwic291cmNlc0NvbnRlbnQiOlsiPHNjcmlwdCBjb250ZXh0PVwibW9kdWxlXCI+XG4gIGltcG9ydCBTZWFyY2ggZnJvbSAnLi9TZWFyY2gvU2VhcmNoLnN2ZWx0ZSc7XG4gIGltcG9ydCBGaWx0ZXIgZnJvbSAnLi9GaWx0ZXIuc3ZlbHRlJztcbiAgaW1wb3J0IExvYWRpbmcgZnJvbSAnLi9Mb2FkaW5nLnN2ZWx0ZSc7XG4gIGltcG9ydCB7IHBhZ2VTaXplIH0gZnJvbSAnLi9zdG9yZXMnO1xuXG4gIGV4cG9ydCB7IFNlYXJjaCwgRmlsdGVyIH07XG48L3NjcmlwdD5cblxuPHNjcmlwdD5cbiAgaW1wb3J0IHsgc2V0Q29udGV4dCB9IGZyb20gJ3N2ZWx0ZSc7XG5cbiAgY29uc3QgeyBEcnVwYWwgfSA9IHdpbmRvdztcblxuICBleHBvcnQgbGV0IGxvYWRpbmcgPSBmYWxzZTtcbiAgZXhwb3J0IGxldCBwYWdlID0gMDtcbiAgZXhwb3J0IGxldCBwYWdlSW5kZXggPSAwO1xuICBleHBvcnQgbGV0IHJvd3M7XG4gIGV4cG9ydCBsZXQgbGFiZWxzID0ge1xuICAgIGVtcHR5OiBEcnVwYWwudCgnTm8gbW9kdWxlcyBmb3VuZCcpLFxuICAgIGxvYWRpbmc6IERydXBhbC50KCdMb2FkaW5nIGRhdGEnKSxcbiAgfTtcblxuICAkOiBmaWx0ZXJlZFJvd3MgPSByb3dzO1xuICAkOiB2aXNpYmxlUm93cyA9IGZpbHRlcmVkUm93c1xuICAgID8gZmlsdGVyZWRSb3dzLnNsaWNlKHBhZ2VJbmRleCwgcGFnZUluZGV4ICsgJHBhZ2VTaXplKVxuICAgIDogW107XG5cbiAgc2V0Q29udGV4dCgnc3RhdGUnLCB7XG4gICAgZ2V0U3RhdGU6ICgpID0+ICh7XG4gICAgICBwYWdlLFxuICAgICAgcGFnZUluZGV4LFxuICAgICAgcGFnZVNpemUsXG4gICAgICByb3dzLFxuICAgICAgZmlsdGVyZWRSb3dzLFxuICAgIH0pLFxuICAgIHNldFBhZ2U6IChfcGFnZSwgX3BhZ2VJbmRleCkgPT4ge1xuICAgICAgcGFnZSA9IF9wYWdlO1xuICAgICAgcGFnZUluZGV4ID0gX3BhZ2VJbmRleDtcbiAgICB9LFxuICAgIHNldFJvd3M6IChfcm93cykgPT4ge1xuICAgICAgZmlsdGVyZWRSb3dzID0gX3Jvd3M7XG4gICAgfSxcbiAgfSk7XG48L3NjcmlwdD5cblxuPCEtLUFsaWducyBDYXRlZ29yeSBmaWx0ZXIgYW5kIEdyaWQgY2FyZHMgc2lkZSBieSBzaWRlLS0+XG48ZGl2IGNsYXNzPVwicHJvamVjdC1icm93c2VyX19jb250YWluZXJcIj5cbiAgPGFzaWRlIGNsYXNzPVwicHJvamVjdC1icm93c2VyX19hc2lkZVwiPlxuICAgIDxzbG90IG5hbWU9XCJsZWZ0XCIgLz5cbiAgPC9hc2lkZT5cbiAgPGRpdiBjbGFzcz1cInByb2plY3QtYnJvd3Nlcl9fbWFpblwiPlxuICAgIDxzbG90IG5hbWU9XCJ0b3BcIiAvPlxuICAgIDxkaXYgY2xhc3M9XCJwcm9qZWN0cy1jb250YWluZXJcIj5cbiAgICAgIDxzbG90IG5hbWU9XCJoZWFkXCIgLz5cbiAgICAgIHsjaWYgbG9hZGluZ31cbiAgICAgICAgPExvYWRpbmcgLz5cbiAgICAgIHs6ZWxzZSBpZiB2aXNpYmxlUm93cy5sZW5ndGggPT09IDB9XG4gICAgICAgIDxkaXY+e0BodG1sIGxhYmVscy5lbXB0eX08L2Rpdj5cbiAgICAgIHs6ZWxzZX1cbiAgICAgICAgPHVsIGNsYXNzPVwicHJvamVjdHMtbGlzdFwiPlxuICAgICAgICAgIDxzbG90IHJvd3M9e3Zpc2libGVSb3dzfSAvPlxuICAgICAgICA8L3VsPlxuICAgICAgey9pZn1cbiAgICAgIDxzbG90IG5hbWU9XCJmb290XCIgLz5cbiAgICA8L2Rpdj5cbiAgPC9kaXY+XG48L2Rpdj5cblxuPHNsb3QgbmFtZT1cImJvdHRvbVwiIC8+XG5cbjxzdHlsZT5cbiAgLnByb2plY3RzLWxpc3Qge1xuICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgZmxleC13cmFwOiB3cmFwO1xuICAgIGxpc3Qtc3R5bGUtdHlwZTogbm9uZTtcbiAgICBtYXJnaW46IDA7XG4gIH1cblxuICAucHJvamVjdC1icm93c2VyX19hc2lkZSB7XG4gICAgZmxleDogMTtcbiAgfVxuICAucHJvamVjdC1icm93c2VyX19tYWluIHtcbiAgICBmbGV4OiA0O1xuICB9XG5cbiAgQG1lZGlhIChtaW4td2lkdGg6IDcwMHB4KSB7XG4gICAgLnByb2plY3QtYnJvd3Nlcl9fY29udGFpbmVyIHtcbiAgICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgfVxuICB9XG4gIC8qIFN0YWNrIGNhcmRzIGJlbG93IGNhdGVnb3J5IGZpbHRlciBvbiBzbWFsbGVyIHNjcmVlbnMgKi9cbiAgQG1lZGlhIHNjcmVlbiBhbmQgKG1heC13aWR0aDogOTAwcHgpIHtcbiAgICAucHJvamVjdC1icm93c2VyX19jb250YWluZXIge1xuICAgICAgZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgICB9XG4gIH1cbjwvc3R5bGU+XG4iXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBd0VFLHlCQUFlLENBQ2IsT0FBTyxDQUFFLElBQUksQ0FDYixTQUFTLENBQUUsSUFBSSxDQUNmLGVBQWUsQ0FBRSxJQUFJLENBQ3JCLE1BQU0sQ0FBRSxDQUNWLENBRUEsa0NBQXdCLENBQ3RCLElBQUksQ0FBRSxDQUNSLENBQ0EsaUNBQXVCLENBQ3JCLElBQUksQ0FBRSxDQUNSLENBRUEsTUFBTyxZQUFZLEtBQUssQ0FBRSxDQUN4QixzQ0FBNEIsQ0FDMUIsT0FBTyxDQUFFLElBQ1gsQ0FDRixDQUVBLE9BQU8sTUFBTSxDQUFDLEdBQUcsQ0FBQyxZQUFZLEtBQUssQ0FBRSxDQUNuQyxzQ0FBNEIsQ0FDMUIsY0FBYyxDQUFFLE1BQ2xCLENBQ0YifQ== */");
}

const get_bottom_slot_changes = dirty => ({ rows: dirty & /*visibleRows*/ 4 });
const get_bottom_slot_context = ctx => ({ rows: /*visibleRows*/ ctx[2] });
const get_foot_slot_changes = dirty => ({ rows: dirty & /*visibleRows*/ 4 });
const get_foot_slot_context = ctx => ({ rows: /*visibleRows*/ ctx[2] });
const get_default_slot_changes = dirty => ({ rows: dirty & /*visibleRows*/ 4 });
const get_default_slot_context = ctx => ({ rows: /*visibleRows*/ ctx[2] });
const get_head_slot_changes = dirty => ({ rows: dirty & /*visibleRows*/ 4 });
const get_head_slot_context = ctx => ({ rows: /*visibleRows*/ ctx[2] });
const get_top_slot_changes = dirty => ({ rows: dirty & /*visibleRows*/ 4 });
const get_top_slot_context = ctx => ({ rows: /*visibleRows*/ ctx[2] });
const get_left_slot_changes = dirty => ({ rows: dirty & /*visibleRows*/ 4 });
const get_left_slot_context = ctx => ({ rows: /*visibleRows*/ ctx[2] });

// (60:6) {:else}
function create_else_block(ctx) {
	let ul;
	let current;
	const default_slot_template = /*#slots*/ ctx[9].default;
	const default_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(default_slot_template, ctx, /*$$scope*/ ctx[8], get_default_slot_context);

	const block = {
		c: function create() {
			ul = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("ul");
			if (default_slot) default_slot.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(ul, "class", "projects-list pb-18bqkxf");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(ul, file, 60, 8, 1403);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, ul, anchor);

			if (default_slot) {
				default_slot.m(ul, null);
			}

			current = true;
		},
		p: function update(ctx, dirty) {
			if (default_slot) {
				if (default_slot.p && (!current || dirty & /*$$scope, visibleRows*/ 260)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						default_slot,
						default_slot_template,
						ctx,
						/*$$scope*/ ctx[8],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[8])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(default_slot_template, /*$$scope*/ ctx[8], dirty, get_default_slot_changes),
						get_default_slot_context
					);
				}
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(default_slot, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(default_slot, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(ul);
			if (default_slot) default_slot.d(detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_else_block.name,
		type: "else",
		source: "(60:6) {:else}",
		ctx
	});

	return block;
}

// (58:41) 
function create_if_block_1(ctx) {
	let div;
	let raw_value = /*labels*/ ctx[1].empty + "";

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 58, 8, 1349);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			div.innerHTML = raw_value;
		},
		p: function update(ctx, dirty) {
			if (dirty & /*labels*/ 2 && raw_value !== (raw_value = /*labels*/ ctx[1].empty + "")) div.innerHTML = raw_value;;
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(58:41) ",
		ctx
	});

	return block;
}

// (56:6) {#if loading}
function create_if_block(ctx) {
	let loading_1;
	let current;
	loading_1 = new _Loading_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({ $$inline: true });

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(loading_1.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(loading_1, target, anchor);
			current = true;
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(loading_1.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(loading_1.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(loading_1, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(56:6) {#if loading}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let div2;
	let aside;
	let t0;
	let div1;
	let t1;
	let div0;
	let t2;
	let current_block_type_index;
	let if_block;
	let t3;
	let t4;
	let current;
	const left_slot_template = /*#slots*/ ctx[9].left;
	const left_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(left_slot_template, ctx, /*$$scope*/ ctx[8], get_left_slot_context);
	const top_slot_template = /*#slots*/ ctx[9].top;
	const top_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(top_slot_template, ctx, /*$$scope*/ ctx[8], get_top_slot_context);
	const head_slot_template = /*#slots*/ ctx[9].head;
	const head_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(head_slot_template, ctx, /*$$scope*/ ctx[8], get_head_slot_context);
	const if_block_creators = [create_if_block, create_if_block_1, create_else_block];
	const if_blocks = [];

	function select_block_type(ctx, dirty) {
		if (/*loading*/ ctx[0]) return 0;
		if (/*visibleRows*/ ctx[2].length === 0) return 1;
		return 2;
	}

	current_block_type_index = select_block_type(ctx, -1);
	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
	const foot_slot_template = /*#slots*/ ctx[9].foot;
	const foot_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(foot_slot_template, ctx, /*$$scope*/ ctx[8], get_foot_slot_context);
	const bottom_slot_template = /*#slots*/ ctx[9].bottom;
	const bottom_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(bottom_slot_template, ctx, /*$$scope*/ ctx[8], get_bottom_slot_context);

	const block = {
		c: function create() {
			div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			aside = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("aside");
			if (left_slot) left_slot.c();
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if (top_slot) top_slot.c();
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			if (head_slot) head_slot.c();
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if_block.c();
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (foot_slot) foot_slot.c();
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (bottom_slot) bottom_slot.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(aside, "class", "project-browser__aside pb-18bqkxf");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(aside, file, 48, 2, 1058);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "class", "projects-container");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div0, file, 53, 4, 1199);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "class", "project-browser__main pb-18bqkxf");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div1, file, 51, 2, 1135);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div2, "class", "project-browser__container pb-18bqkxf");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div2, file, 47, 0, 1015);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div2, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, aside);

			if (left_slot) {
				left_slot.m(aside, null);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, div1);

			if (top_slot) {
				top_slot.m(div1, null);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, div0);

			if (head_slot) {
				head_slot.m(div0, null);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div0, t2);
			if_blocks[current_block_type_index].m(div0, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div0, t3);

			if (foot_slot) {
				foot_slot.m(div0, null);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, t4, anchor);

			if (bottom_slot) {
				bottom_slot.m(target, anchor);
			}

			current = true;
		},
		p: function update(ctx, [dirty]) {
			if (left_slot) {
				if (left_slot.p && (!current || dirty & /*$$scope, visibleRows*/ 260)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						left_slot,
						left_slot_template,
						ctx,
						/*$$scope*/ ctx[8],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[8])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(left_slot_template, /*$$scope*/ ctx[8], dirty, get_left_slot_changes),
						get_left_slot_context
					);
				}
			}

			if (top_slot) {
				if (top_slot.p && (!current || dirty & /*$$scope, visibleRows*/ 260)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						top_slot,
						top_slot_template,
						ctx,
						/*$$scope*/ ctx[8],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[8])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(top_slot_template, /*$$scope*/ ctx[8], dirty, get_top_slot_changes),
						get_top_slot_context
					);
				}
			}

			if (head_slot) {
				if (head_slot.p && (!current || dirty & /*$$scope, visibleRows*/ 260)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						head_slot,
						head_slot_template,
						ctx,
						/*$$scope*/ ctx[8],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[8])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(head_slot_template, /*$$scope*/ ctx[8], dirty, get_head_slot_changes),
						get_head_slot_context
					);
				}
			}

			let previous_block_index = current_block_type_index;
			current_block_type_index = select_block_type(ctx, dirty);

			if (current_block_type_index === previous_block_index) {
				if_blocks[current_block_type_index].p(ctx, dirty);
			} else {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_blocks[previous_block_index], 1, 1, () => {
					if_blocks[previous_block_index] = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
				if_block = if_blocks[current_block_type_index];

				if (!if_block) {
					if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
					if_block.c();
				} else {
					if_block.p(ctx, dirty);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
				if_block.m(div0, t3);
			}

			if (foot_slot) {
				if (foot_slot.p && (!current || dirty & /*$$scope, visibleRows*/ 260)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						foot_slot,
						foot_slot_template,
						ctx,
						/*$$scope*/ ctx[8],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[8])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(foot_slot_template, /*$$scope*/ ctx[8], dirty, get_foot_slot_changes),
						get_foot_slot_context
					);
				}
			}

			if (bottom_slot) {
				if (bottom_slot.p && (!current || dirty & /*$$scope, visibleRows*/ 260)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						bottom_slot,
						bottom_slot_template,
						ctx,
						/*$$scope*/ ctx[8],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[8])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(bottom_slot_template, /*$$scope*/ ctx[8], dirty, get_bottom_slot_changes),
						get_bottom_slot_context
					);
				}
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(left_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(top_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(head_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(foot_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(bottom_slot, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(left_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(top_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(head_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(foot_slot, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(bottom_slot, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div2);
			if (left_slot) left_slot.d(detaching);
			if (top_slot) top_slot.d(detaching);
			if (head_slot) head_slot.d(detaching);
			if_blocks[current_block_type_index].d();
			if (foot_slot) foot_slot.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(t4);
			if (bottom_slot) bottom_slot.d(detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let filteredRows;
	let visibleRows;
	let $pageSize;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_1__.pageSize, 'pageSize');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_1__.pageSize, $$value => $$invalidate(7, $pageSize = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('ProjectGrid', slots, ['left','top','head','default','foot','bottom']);
	const { Drupal } = window;
	let { loading = false } = $$props;
	let { page = 0 } = $$props;
	let { pageIndex = 0 } = $$props;
	let { rows } = $$props;

	let { labels = {
		empty: Drupal.t('No modules found'),
		loading: Drupal.t('Loading data')
	} } = $$props;

	(0,svelte__WEBPACK_IMPORTED_MODULE_5__.setContext)('state', {
		getState: () => ({
			page,
			pageIndex,
			pageSize: _stores__WEBPACK_IMPORTED_MODULE_1__.pageSize,
			rows,
			filteredRows
		}),
		setPage: (_page, _pageIndex) => {
			$$invalidate(4, page = _page);
			$$invalidate(3, pageIndex = _pageIndex);
		},
		setRows: _rows => {
			$$invalidate(6, filteredRows = _rows);
		}
	});

	$$self.$$.on_mount.push(function () {
		if (rows === undefined && !('rows' in $$props || $$self.$$.bound[$$self.$$.props['rows']])) {
			console.warn("<ProjectGrid> was created without expected prop 'rows'");
		}
	});

	const writable_props = ['loading', 'page', 'pageIndex', 'rows', 'labels'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ProjectGrid> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('loading' in $$props) $$invalidate(0, loading = $$props.loading);
		if ('page' in $$props) $$invalidate(4, page = $$props.page);
		if ('pageIndex' in $$props) $$invalidate(3, pageIndex = $$props.pageIndex);
		if ('rows' in $$props) $$invalidate(5, rows = $$props.rows);
		if ('labels' in $$props) $$invalidate(1, labels = $$props.labels);
		if ('$$scope' in $$props) $$invalidate(8, $$scope = $$props.$$scope);
	};

	$$self.$capture_state = () => ({
		Search: _Search_Search_svelte__WEBPACK_IMPORTED_MODULE_4__["default"],
		Filter: _Filter_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		Loading: _Loading_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		pageSize: _stores__WEBPACK_IMPORTED_MODULE_1__.pageSize,
		setContext: svelte__WEBPACK_IMPORTED_MODULE_5__.setContext,
		Drupal,
		loading,
		page,
		pageIndex,
		rows,
		labels,
		filteredRows,
		visibleRows,
		$pageSize
	});

	$$self.$inject_state = $$props => {
		if ('loading' in $$props) $$invalidate(0, loading = $$props.loading);
		if ('page' in $$props) $$invalidate(4, page = $$props.page);
		if ('pageIndex' in $$props) $$invalidate(3, pageIndex = $$props.pageIndex);
		if ('rows' in $$props) $$invalidate(5, rows = $$props.rows);
		if ('labels' in $$props) $$invalidate(1, labels = $$props.labels);
		if ('filteredRows' in $$props) $$invalidate(6, filteredRows = $$props.filteredRows);
		if ('visibleRows' in $$props) $$invalidate(2, visibleRows = $$props.visibleRows);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	$$self.$$.update = () => {
		if ($$self.$$.dirty & /*rows*/ 32) {
			$: $$invalidate(6, filteredRows = rows);
		}

		if ($$self.$$.dirty & /*filteredRows, pageIndex, $pageSize*/ 200) {
			$: $$invalidate(2, visibleRows = filteredRows
			? filteredRows.slice(pageIndex, pageIndex + $pageSize)
			: []);
		}
	};

	return [
		loading,
		labels,
		visibleRows,
		pageIndex,
		page,
		rows,
		filteredRows,
		$pageSize,
		$$scope,
		slots
	];
}

class ProjectGrid extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(
			this,
			options,
			instance,
			create_fragment,
			svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal,
			{
				loading: 0,
				page: 4,
				pageIndex: 3,
				rows: 5,
				labels: 1
			},
			add_css
		);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "ProjectGrid",
			options,
			id: create_fragment.name
		});
	}

	get loading() {
		throw new Error("<ProjectGrid>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set loading(value) {
		throw new Error("<ProjectGrid>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get page() {
		throw new Error("<ProjectGrid>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set page(value) {
		throw new Error("<ProjectGrid>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get pageIndex() {
		throw new Error("<ProjectGrid>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set pageIndex(value) {
		throw new Error("<ProjectGrid>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get rows() {
		throw new Error("<ProjectGrid>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set rows(value) {
		throw new Error("<ProjectGrid>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get labels() {
		throw new Error("<ProjectGrid>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set labels(value) {
		throw new Error("<ProjectGrid>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (ProjectGrid);




/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Search/FilterApplied.svelte":
/*!**************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Search/FilterApplied.svelte ***!
  \**************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Search/FilterApplied.svelte generated by Svelte v3.56.0 */



const file = "modules/project_browser/sveltejs/src/Search/FilterApplied.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-qfypfr", ".filter-applied.pb-qfypfr.pb-qfypfr{word-wrap:break-word;font-size:0.875rem;color:white;display:inline-flex;align-items:center;border-radius:25px;background:#013cc5;padding:10px;margin-inline-end:10px;width:auto;height:auto}.filter-applied__button-close.pb-qfypfr.pb-qfypfr{display:inline-flex;padding:0;margin-inline-start:10px;outline:none;box-shadow:none;border:none;background:none;cursor:pointer}.filter-applied__button-close.pb-qfypfr>img.pb-qfypfr{transform:scale(1.4);height:20px;width:20px}@media(forced-colors: active){.filter-applied.pb-qfypfr.pb-qfypfr{border:1px solid}@media(prefers-color-scheme: light){.filter-applied__button-close.pb-qfypfr>img.pb-qfypfr{filter:invert(1)}}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiRmlsdGVyQXBwbGllZC5zdmVsdGUiLCJzb3VyY2VzIjpbIkZpbHRlckFwcGxpZWQuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIGltcG9ydCB7IEFMTF9WQUxVRVNfSUQsIEZVTExfTU9EVUxFX1BBVEggfSBmcm9tICcuLi9jb25zdGFudHMnO1xuXG4gIGV4cG9ydCBsZXQgaWQ7XG4gIGV4cG9ydCBsZXQgbGFiZWw7XG4gIGV4cG9ydCBsZXQgY2xpY2tIYW5kbGVyO1xuXG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG48L3NjcmlwdD5cblxueyNpZiBpZCAhPT0gQUxMX1ZBTFVFU19JRH1cbiAgPHAgY2xhc3M9XCJmaWx0ZXItYXBwbGllZFwiPlxuICAgIDxzcGFuIGNsYXNzPVwiZmlsdGVyLWFwcGxpZWRfX2xhYmVsXCI+e2xhYmVsfTwvc3Bhbj5cbiAgICA8YnV0dG9uXG4gICAgICB0eXBlPVwiYnV0dG9uXCJcbiAgICAgIG9uOmNsaWNrPXtjbGlja0hhbmRsZXJ9XG4gICAgICBjbGFzcz1cImZpbHRlci1hcHBsaWVkX19idXR0b24tY2xvc2VcIlxuICAgID5cbiAgICAgIHsjaWYgbGFiZWx9XG4gICAgICAgIDxzcGFuIGNsYXNzPVwidmlzdWFsbHktaGlkZGVuXCJcbiAgICAgICAgICA+e0RydXBhbC50KCdSZW1vdmUgQGZpbHRlcicsIHsgJ0BmaWx0ZXInOiBsYWJlbCB9KX08L3NwYW5cbiAgICAgICAgPlxuICAgICAgey9pZn1cbiAgICAgIDxpbWcgc3JjPVwie0ZVTExfTU9EVUxFX1BBVEh9L2ltYWdlcy93aGl0ZS1jbG9zZS1pY29uLnN2Z1wiIGFsdD1cIlwiIC8+XG4gICAgPC9idXR0b24+XG4gIDwvcD5cbnsvaWZ9XG5cbjxzdHlsZT5cbiAgLmZpbHRlci1hcHBsaWVkIHtcbiAgICB3b3JkLXdyYXA6IGJyZWFrLXdvcmQ7XG4gICAgZm9udC1zaXplOiAwLjg3NXJlbTtcbiAgICBjb2xvcjogd2hpdGU7XG4gICAgZGlzcGxheTogaW5saW5lLWZsZXg7XG4gICAgYWxpZ24taXRlbXM6IGNlbnRlcjtcbiAgICBib3JkZXItcmFkaXVzOiAyNXB4O1xuICAgIGJhY2tncm91bmQ6ICMwMTNjYzU7XG4gICAgcGFkZGluZzogMTBweDtcbiAgICBtYXJnaW4taW5saW5lLWVuZDogMTBweDtcbiAgICB3aWR0aDogYXV0bztcbiAgICBoZWlnaHQ6IGF1dG87XG4gIH1cbiAgLmZpbHRlci1hcHBsaWVkX19idXR0b24tY2xvc2Uge1xuICAgIGRpc3BsYXk6IGlubGluZS1mbGV4O1xuICAgIHBhZGRpbmc6IDA7XG4gICAgbWFyZ2luLWlubGluZS1zdGFydDogMTBweDtcbiAgICBvdXRsaW5lOiBub25lO1xuICAgIGJveC1zaGFkb3c6IG5vbmU7XG4gICAgYm9yZGVyOiBub25lO1xuICAgIGJhY2tncm91bmQ6IG5vbmU7XG4gICAgY3Vyc29yOiBwb2ludGVyO1xuICB9XG4gIC5maWx0ZXItYXBwbGllZF9fYnV0dG9uLWNsb3NlID4gaW1nIHtcbiAgICB0cmFuc2Zvcm06IHNjYWxlKDEuNCk7XG4gICAgaGVpZ2h0OiAyMHB4O1xuICAgIHdpZHRoOiAyMHB4O1xuICB9XG5cbiAgQG1lZGlhIChmb3JjZWQtY29sb3JzOiBhY3RpdmUpIHtcbiAgICAuZmlsdGVyLWFwcGxpZWQge1xuICAgICAgYm9yZGVyOiAxcHggc29saWQ7XG4gICAgfVxuICAgIEBtZWRpYSAocHJlZmVycy1jb2xvci1zY2hlbWU6IGxpZ2h0KSB7XG4gICAgICAuZmlsdGVyLWFwcGxpZWRfX2J1dHRvbi1jbG9zZSA+IGltZyB7XG4gICAgICAgIGZpbHRlcjogaW52ZXJ0KDEpO1xuICAgICAgfVxuICAgIH1cbiAgfVxuPC9zdHlsZT5cbiJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUE2QkUsbUNBQWdCLENBQ2QsU0FBUyxDQUFFLFVBQVUsQ0FDckIsU0FBUyxDQUFFLFFBQVEsQ0FDbkIsS0FBSyxDQUFFLEtBQUssQ0FDWixPQUFPLENBQUUsV0FBVyxDQUNwQixXQUFXLENBQUUsTUFBTSxDQUNuQixhQUFhLENBQUUsSUFBSSxDQUNuQixVQUFVLENBQUUsT0FBTyxDQUNuQixPQUFPLENBQUUsSUFBSSxDQUNiLGlCQUFpQixDQUFFLElBQUksQ0FDdkIsS0FBSyxDQUFFLElBQUksQ0FDWCxNQUFNLENBQUUsSUFDVixDQUNBLGlEQUE4QixDQUM1QixPQUFPLENBQUUsV0FBVyxDQUNwQixPQUFPLENBQUUsQ0FBQyxDQUNWLG1CQUFtQixDQUFFLElBQUksQ0FDekIsT0FBTyxDQUFFLElBQUksQ0FDYixVQUFVLENBQUUsSUFBSSxDQUNoQixNQUFNLENBQUUsSUFBSSxDQUNaLFVBQVUsQ0FBRSxJQUFJLENBQ2hCLE1BQU0sQ0FBRSxPQUNWLENBQ0EsdUNBQTZCLENBQUcsYUFBSSxDQUNsQyxTQUFTLENBQUUsTUFBTSxHQUFHLENBQUMsQ0FDckIsTUFBTSxDQUFFLElBQUksQ0FDWixLQUFLLENBQUUsSUFDVCxDQUVBLE1BQU8sZ0JBQWdCLE1BQU0sQ0FBRSxDQUM3QixtQ0FBZ0IsQ0FDZCxNQUFNLENBQUUsR0FBRyxDQUFDLEtBQ2QsQ0FDQSxNQUFPLHVCQUF1QixLQUFLLENBQUUsQ0FDbkMsdUNBQTZCLENBQUcsYUFBSSxDQUNsQyxNQUFNLENBQUUsT0FBTyxDQUFDLENBQ2xCLENBQ0YsQ0FDRiJ9 */");
}

// (11:0) {#if id !== ALL_VALUES_ID}
function create_if_block(ctx) {
	let p;
	let span;
	let t0;
	let t1;
	let button;
	let t2;
	let img;
	let img_src_value;
	let mounted;
	let dispose;
	let if_block = /*label*/ ctx[1] && create_if_block_1(ctx);

	const block = {
		c: function create() {
			p = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("p");
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(/*label*/ ctx[1]);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			if (if_block) if_block.c();
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "filter-applied__label");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 12, 4, 241);
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH + "/images/white-close-icon.svg"))) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "alt", "");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", "pb-qfypfr");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 23, 6, 555);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "type", "button");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "class", "filter-applied__button-close pb-qfypfr");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 13, 4, 296);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(p, "class", "filter-applied pb-qfypfr");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(p, file, 11, 2, 210);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, p, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p, span);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(p, button);
			if (if_block) if_block.m(button, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, img);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(
					button,
					"click",
					function () {
						if ((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.is_function)(/*clickHandler*/ ctx[2])) /*clickHandler*/ ctx[2].apply(this, arguments);
					},
					false,
					false,
					false,
					false
				);

				mounted = true;
			}
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;
			if (dirty & /*label*/ 2) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, /*label*/ ctx[1]);

			if (/*label*/ ctx[1]) {
				if (if_block) {
					if_block.p(ctx, dirty);
				} else {
					if_block = create_if_block_1(ctx);
					if_block.c();
					if_block.m(button, t2);
				}
			} else if (if_block) {
				if_block.d(1);
				if_block = null;
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(p);
			if (if_block) if_block.d();
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(11:0) {#if id !== ALL_VALUES_ID}",
		ctx
	});

	return block;
}

// (19:6) {#if label}
function create_if_block_1(ctx) {
	let span;
	let t_value = /*Drupal*/ ctx[3].t('Remove @filter', { '@filter': /*label*/ ctx[1] }) + "";
	let t;

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "visually-hidden");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 19, 8, 429);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span, t);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*label*/ 2 && t_value !== (t_value = /*Drupal*/ ctx[3].t('Remove @filter', { '@filter': /*label*/ ctx[1] }) + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(19:6) {#if label}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let if_block_anchor;
	let if_block = /*id*/ ctx[0] !== _constants__WEBPACK_IMPORTED_MODULE_1__.ALL_VALUES_ID && create_if_block(ctx);

	const block = {
		c: function create() {
			if (if_block) if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			if (if_block) if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
		},
		p: function update(ctx, [dirty]) {
			if (/*id*/ ctx[0] !== _constants__WEBPACK_IMPORTED_MODULE_1__.ALL_VALUES_ID) {
				if (if_block) {
					if_block.p(ctx, dirty);
				} else {
					if_block = create_if_block(ctx);
					if_block.c();
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			} else if (if_block) {
				if_block.d(1);
				if_block = null;
			}
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (if_block) if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('FilterApplied', slots, []);
	let { id } = $$props;
	let { label } = $$props;
	let { clickHandler } = $$props;
	const { Drupal } = window;

	$$self.$$.on_mount.push(function () {
		if (id === undefined && !('id' in $$props || $$self.$$.bound[$$self.$$.props['id']])) {
			console.warn("<FilterApplied> was created without expected prop 'id'");
		}

		if (label === undefined && !('label' in $$props || $$self.$$.bound[$$self.$$.props['label']])) {
			console.warn("<FilterApplied> was created without expected prop 'label'");
		}

		if (clickHandler === undefined && !('clickHandler' in $$props || $$self.$$.bound[$$self.$$.props['clickHandler']])) {
			console.warn("<FilterApplied> was created without expected prop 'clickHandler'");
		}
	});

	const writable_props = ['id', 'label', 'clickHandler'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<FilterApplied> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('id' in $$props) $$invalidate(0, id = $$props.id);
		if ('label' in $$props) $$invalidate(1, label = $$props.label);
		if ('clickHandler' in $$props) $$invalidate(2, clickHandler = $$props.clickHandler);
	};

	$$self.$capture_state = () => ({
		ALL_VALUES_ID: _constants__WEBPACK_IMPORTED_MODULE_1__.ALL_VALUES_ID,
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH,
		id,
		label,
		clickHandler,
		Drupal
	});

	$$self.$inject_state = $$props => {
		if ('id' in $$props) $$invalidate(0, id = $$props.id);
		if ('label' in $$props) $$invalidate(1, label = $$props.label);
		if ('clickHandler' in $$props) $$invalidate(2, clickHandler = $$props.clickHandler);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [id, label, clickHandler, Drupal];
}

class FilterApplied extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { id: 0, label: 1, clickHandler: 2 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "FilterApplied",
			options,
			id: create_fragment.name
		});
	}

	get id() {
		throw new Error("<FilterApplied>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set id(value) {
		throw new Error("<FilterApplied>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get label() {
		throw new Error("<FilterApplied>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set label(value) {
		throw new Error("<FilterApplied>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get clickHandler() {
		throw new Error("<FilterApplied>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set clickHandler(value) {
		throw new Error("<FilterApplied>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FilterApplied);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Search/FilterGroup.svelte":
/*!************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Search/FilterGroup.svelte ***!
  \************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Search/FilterGroup.svelte generated by Svelte v3.56.0 */


const { Object: Object_1 } = svelte_internal__WEBPACK_IMPORTED_MODULE_0__.globals;

const file = "modules/project_browser/sveltejs/src/Search/FilterGroup.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-wu1rzn", ".filter-group__radio.pb-wu1rzn{width:20px;height:20px;position:absolute;top:2px;inset-inline-start:5px}.filter-group__radio.pb-wu1rzn:hover{cursor:pointer}.filter-group.pb-wu1rzn{flex-wrap:wrap;flex-direction:column;display:inline-grid;position:relative;margin-bottom:20px;margin-top:15px;padding-inline-start:15px;padding-top:15px;justify-content:flex-start}.filter-group__title-wrapper.pb-wu1rzn{font-size:18px;margin-inline-end:10px}.filter-group__filter-options.pb-wu1rzn{display:flex;flex-wrap:wrap;margin-top:10px;flex-direction:column}.filter-group__filter-option.pb-wu1rzn{margin-bottom:10px;line-height:25px;position:relative}@media only screen and (max-width: 72rem){.filter-group.pb-wu1rzn{display:table}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiRmlsdGVyR3JvdXAuc3ZlbHRlIiwic291cmNlcyI6WyJGaWx0ZXJHcm91cC5zdmVsdGUiXSwic291cmNlc0NvbnRlbnQiOlsiPHNjcmlwdD5cbiAgaW1wb3J0IHsgZmlsdGVycyB9IGZyb20gJy4uL3N0b3Jlcyc7XG5cbiAgZXhwb3J0IGxldCBmaWx0ZXJUaXRsZTtcbiAgZXhwb3J0IGxldCBmaWx0ZXJEYXRhO1xuICBleHBvcnQgbGV0IGNoYW5nZUhhbmRsZXI7XG4gIGV4cG9ydCBsZXQgZmlsdGVyVHlwZTtcbjwvc2NyaXB0PlxuXG48ZGl2XG4gIHJvbGU9XCJncm91cFwiXG4gIGFyaWEtbGFiZWxsZWRieT17ZmlsdGVyVGl0bGUucmVwbGFjZSgvXFxzKy9nLCAnJyl9XG4gIGNsYXNzPVwiZmlsdGVyLWdyb3VwXCJcbj5cbiAgPGRpdiBjbGFzcz1cImZpbHRlci1ncm91cF9fdGl0bGUtd3JhcHBlclwiIGlkPXtmaWx0ZXJUaXRsZS5yZXBsYWNlKC9cXHMrL2csICcnKX0+XG4gICAge2ZpbHRlclRpdGxlfTpcbiAgPC9kaXY+XG4gIDxkaXYgY2xhc3M9XCJmaWx0ZXItZ3JvdXBfX2ZpbHRlci1vcHRpb25zLXdyYXBwZXJcIj5cbiAgICA8ZGl2IGNsYXNzPVwiZmlsdGVyLWdyb3VwX19maWx0ZXItb3B0aW9uc1wiPlxuICAgICAgeyNlYWNoIE9iamVjdC5lbnRyaWVzKGZpbHRlckRhdGEpIGFzIFtpZCwgbGFiZWxdfVxuICAgICAgICA8ZGl2IGNsYXNzPVwiZmlsdGVyLWdyb3VwX19maWx0ZXItb3B0aW9uXCI+XG4gICAgICAgICAgPGlucHV0XG4gICAgICAgICAgICB0eXBlPVwicmFkaW9cIlxuICAgICAgICAgICAgbmFtZT17ZmlsdGVyVHlwZX1cbiAgICAgICAgICAgIGlkPXtmaWx0ZXJUeXBlICsgaWR9XG4gICAgICAgICAgICBjbGFzcz1cImZpbHRlci1ncm91cF9fcmFkaW9cIlxuICAgICAgICAgICAgYmluZDpncm91cD17JGZpbHRlcnNbZmlsdGVyVHlwZV19XG4gICAgICAgICAgICBvbjpjaGFuZ2U9e2NoYW5nZUhhbmRsZXJ9XG4gICAgICAgICAgICB2YWx1ZT17aWR9XG4gICAgICAgICAgLz5cbiAgICAgICAgICA8c2xvdCBjbGFzcz1cImZpbHRlci1ncm91cF9fbGFiZWwtc2xvdFwiIG5hbWU9XCJsYWJlbFwiIHtpZH0ge2xhYmVsfT5cbiAgICAgICAgICAgIDxsYWJlbCBjbGFzcz1cImZpbHRlci1ncm91cF9fb3B0aW9uLWxhYmVsXCIgZm9yPXtmaWx0ZXJUeXBlICsgaWR9PlxuICAgICAgICAgICAgICB7bGFiZWx9XG4gICAgICAgICAgICA8L2xhYmVsPlxuICAgICAgICAgIDwvc2xvdD5cbiAgICAgICAgPC9kaXY+XG4gICAgICB7L2VhY2h9XG4gICAgPC9kaXY+XG4gIDwvZGl2PlxuPC9kaXY+XG5cbjxzdHlsZT5cbiAgLmZpbHRlci1ncm91cF9fcmFkaW8ge1xuICAgIHdpZHRoOiAyMHB4O1xuICAgIGhlaWdodDogMjBweDtcbiAgICBwb3NpdGlvbjogYWJzb2x1dGU7XG4gICAgdG9wOiAycHg7XG4gICAgaW5zZXQtaW5saW5lLXN0YXJ0OiA1cHg7XG4gIH1cbiAgLmZpbHRlci1ncm91cF9fcmFkaW86aG92ZXIge1xuICAgIGN1cnNvcjogcG9pbnRlcjtcbiAgfVxuXG4gIC5maWx0ZXItZ3JvdXAge1xuICAgIGZsZXgtd3JhcDogd3JhcDtcbiAgICBmbGV4LWRpcmVjdGlvbjogY29sdW1uO1xuICAgIGRpc3BsYXk6IGlubGluZS1ncmlkO1xuICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgICBtYXJnaW4tYm90dG9tOiAyMHB4O1xuICAgIG1hcmdpbi10b3A6IDE1cHg7XG4gICAgcGFkZGluZy1pbmxpbmUtc3RhcnQ6IDE1cHg7XG4gICAgcGFkZGluZy10b3A6IDE1cHg7XG4gICAganVzdGlmeS1jb250ZW50OiBmbGV4LXN0YXJ0O1xuICB9XG5cbiAgLmZpbHRlci1ncm91cF9fdGl0bGUtd3JhcHBlciB7XG4gICAgZm9udC1zaXplOiAxOHB4O1xuICAgIG1hcmdpbi1pbmxpbmUtZW5kOiAxMHB4O1xuICB9XG5cbiAgLmZpbHRlci1ncm91cF9fZmlsdGVyLW9wdGlvbnMge1xuICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgZmxleC13cmFwOiB3cmFwO1xuICAgIG1hcmdpbi10b3A6IDEwcHg7XG4gICAgZmxleC1kaXJlY3Rpb246IGNvbHVtbjtcbiAgfVxuXG4gIC5maWx0ZXItZ3JvdXBfX2ZpbHRlci1vcHRpb24ge1xuICAgIG1hcmdpbi1ib3R0b206IDEwcHg7XG4gICAgbGluZS1oZWlnaHQ6IDI1cHg7XG4gICAgcG9zaXRpb246IHJlbGF0aXZlO1xuICB9XG5cbiAgQG1lZGlhIG9ubHkgc2NyZWVuIGFuZCAobWF4LXdpZHRoOiA3MnJlbSkge1xuICAgIC5maWx0ZXItZ3JvdXAge1xuICAgICAgZGlzcGxheTogdGFibGU7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQTBDRSw4QkFBcUIsQ0FDbkIsS0FBSyxDQUFFLElBQUksQ0FDWCxNQUFNLENBQUUsSUFBSSxDQUNaLFFBQVEsQ0FBRSxRQUFRLENBQ2xCLEdBQUcsQ0FBRSxHQUFHLENBQ1Isa0JBQWtCLENBQUUsR0FDdEIsQ0FDQSw4QkFBb0IsTUFBTyxDQUN6QixNQUFNLENBQUUsT0FDVixDQUVBLHVCQUFjLENBQ1osU0FBUyxDQUFFLElBQUksQ0FDZixjQUFjLENBQUUsTUFBTSxDQUN0QixPQUFPLENBQUUsV0FBVyxDQUNwQixRQUFRLENBQUUsUUFBUSxDQUNsQixhQUFhLENBQUUsSUFBSSxDQUNuQixVQUFVLENBQUUsSUFBSSxDQUNoQixvQkFBb0IsQ0FBRSxJQUFJLENBQzFCLFdBQVcsQ0FBRSxJQUFJLENBQ2pCLGVBQWUsQ0FBRSxVQUNuQixDQUVBLHNDQUE2QixDQUMzQixTQUFTLENBQUUsSUFBSSxDQUNmLGlCQUFpQixDQUFFLElBQ3JCLENBRUEsdUNBQThCLENBQzVCLE9BQU8sQ0FBRSxJQUFJLENBQ2IsU0FBUyxDQUFFLElBQUksQ0FDZixVQUFVLENBQUUsSUFBSSxDQUNoQixjQUFjLENBQUUsTUFDbEIsQ0FFQSxzQ0FBNkIsQ0FDM0IsYUFBYSxDQUFFLElBQUksQ0FDbkIsV0FBVyxDQUFFLElBQUksQ0FDakIsUUFBUSxDQUFFLFFBQ1osQ0FFQSxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLFlBQVksS0FBSyxDQUFFLENBQ3hDLHVCQUFjLENBQ1osT0FBTyxDQUFFLEtBQ1gsQ0FDRiJ9 */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[9] = list[i][0];
	child_ctx[10] = list[i][1];
	return child_ctx;
}

const get_label_slot_changes = dirty => ({
	id: dirty & /*filterData*/ 2,
	label: dirty & /*filterData*/ 2
});

const get_label_slot_context = ctx => ({
	class: "filter-group__label-slot",
	id: /*id*/ ctx[9],
	label: /*label*/ ctx[10]
});

// (31:75)              
function fallback_block(ctx) {
	let label;
	let t_value = /*label*/ ctx[10] + "";
	let t;
	let label_for_value;

	const block = {
		c: function create() {
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "class", "filter-group__option-label");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value = /*filterType*/ ctx[3] + /*id*/ ctx[9]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 31, 12, 929);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, label, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(label, t);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*filterData*/ 2 && t_value !== (t_value = /*label*/ ctx[10] + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);

			if (dirty & /*filterType, filterData*/ 10 && label_for_value !== (label_for_value = /*filterType*/ ctx[3] + /*id*/ ctx[9])) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value);
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(label);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: fallback_block.name,
		type: "fallback",
		source: "(31:75)              ",
		ctx
	});

	return block;
}

// (20:6) {#each Object.entries(filterData) as [id, label]}
function create_each_block(ctx) {
	let div;
	let input;
	let input_id_value;
	let input_value_value;
	let value_has_changed = false;
	let t0;
	let t1;
	let current;
	let binding_group;
	let mounted;
	let dispose;
	const label_slot_template = /*#slots*/ ctx[6].label;
	const label_slot = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_slot)(label_slot_template, ctx, /*$$scope*/ ctx[5], get_label_slot_context);
	const label_slot_or_fallback = label_slot || fallback_block(ctx);
	binding_group = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init_binding_group)(/*$$binding_groups*/ ctx[8][0]);

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			input = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("input");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (label_slot_or_fallback) label_slot_or_fallback.c();
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "type", "radio");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "name", /*filterType*/ ctx[3]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "id", input_id_value = /*filterType*/ ctx[3] + /*id*/ ctx[9]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "class", "filter-group__radio pb-wu1rzn");
			input.__value = input_value_value = /*id*/ ctx[9];
			input.value = input.__value;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(input, file, 21, 10, 586);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "filter-group__filter-option pb-wu1rzn");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 20, 8, 534);
			binding_group.p(input);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, input);
			input.checked = input.__value === /*$filters*/ ctx[4][/*filterType*/ ctx[3]];
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t0);

			if (label_slot_or_fallback) {
				label_slot_or_fallback.m(div, null);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t1);
			current = true;

			if (!mounted) {
				dispose = [
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(input, "change", /*input_change_handler*/ ctx[7]),
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(
						input,
						"change",
						function () {
							if ((0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.is_function)(/*changeHandler*/ ctx[2])) /*changeHandler*/ ctx[2].apply(this, arguments);
						},
						false,
						false,
						false,
						false
					)
				];

				mounted = true;
			}
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;

			if (!current || dirty & /*filterType*/ 8) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "name", /*filterType*/ ctx[3]);
			}

			if (!current || dirty & /*filterType, filterData*/ 10 && input_id_value !== (input_id_value = /*filterType*/ ctx[3] + /*id*/ ctx[9])) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "id", input_id_value);
			}

			if (!current || dirty & /*filterData*/ 2 && input_value_value !== (input_value_value = /*id*/ ctx[9])) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prop_dev)(input, "__value", input_value_value);
				input.value = input.__value;
				value_has_changed = true;
			}

			if (value_has_changed || dirty & /*$filters, filterType, filterData*/ 26) {
				input.checked = input.__value === /*$filters*/ ctx[4][/*filterType*/ ctx[3]];
			}

			if (label_slot) {
				if (label_slot.p && (!current || dirty & /*$$scope, filterData*/ 34)) {
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.update_slot_base)(
						label_slot,
						label_slot_template,
						ctx,
						/*$$scope*/ ctx[5],
						!current
						? (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_all_dirty_from_scope)(/*$$scope*/ ctx[5])
						: (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.get_slot_changes)(label_slot_template, /*$$scope*/ ctx[5], dirty, get_label_slot_changes),
						get_label_slot_context
					);
				}
			} else {
				if (label_slot_or_fallback && label_slot_or_fallback.p && (!current || dirty & /*filterType, filterData*/ 10)) {
					label_slot_or_fallback.p(ctx, !current ? -1 : dirty);
				}
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(label_slot_or_fallback, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(label_slot_or_fallback, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			if (label_slot_or_fallback) label_slot_or_fallback.d(detaching);
			binding_group.r();
			mounted = false;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.run_all)(dispose);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(20:6) {#each Object.entries(filterData) as [id, label]}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let div3;
	let div0;
	let t0;
	let t1;
	let div0_id_value;
	let t2;
	let div2;
	let div1;
	let div3_aria_labelledby_value;
	let current;
	let each_value = Object.entries(/*filterData*/ ctx[1]);
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const out = i => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks[i], 1, 1, () => {
		each_blocks[i] = null;
	});

	const block = {
		c: function create() {
			div3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(/*filterTitle*/ ctx[0]);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(":");
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");

			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "class", "filter-group__title-wrapper pb-wu1rzn");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "id", div0_id_value = /*filterTitle*/ ctx[0].replace(/\s+/g, ''));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div0, file, 14, 2, 263);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "class", "filter-group__filter-options pb-wu1rzn");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div1, file, 18, 4, 427);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div2, "class", "filter-group__filter-options-wrapper");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div2, file, 17, 2, 372);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div3, "role", "group");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div3, "aria-labelledby", div3_aria_labelledby_value = /*filterTitle*/ ctx[0].replace(/\s+/g, ''));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div3, "class", "filter-group pb-wu1rzn");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div3, file, 9, 0, 164);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div3, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, div0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div0, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div0, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, div2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, div1);

			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(div1, null);
				}
			}

			current = true;
		},
		p: function update(ctx, [dirty]) {
			if (!current || dirty & /*filterTitle*/ 1) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, /*filterTitle*/ ctx[0]);

			if (!current || dirty & /*filterTitle*/ 1 && div0_id_value !== (div0_id_value = /*filterTitle*/ ctx[0].replace(/\s+/g, ''))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "id", div0_id_value);
			}

			if (dirty & /*filterType, Object, filterData, $$scope, $filters, changeHandler*/ 62) {
				each_value = Object.entries(/*filterData*/ ctx[1]);
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i], 1);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i], 1);
						each_blocks[i].m(div1, null);
					}
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				for (i = each_value.length; i < each_blocks.length; i += 1) {
					out(i);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (!current || dirty & /*filterTitle*/ 1 && div3_aria_labelledby_value !== (div3_aria_labelledby_value = /*filterTitle*/ ctx[0].replace(/\s+/g, ''))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div3, "aria-labelledby", div3_aria_labelledby_value);
			}
		},
		i: function intro(local) {
			if (current) return;

			for (let i = 0; i < each_value.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i]);
			}

			current = true;
		},
		o: function outro(local) {
			each_blocks = each_blocks.filter(Boolean);

			for (let i = 0; i < each_blocks.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks[i]);
			}

			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let $filters;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_1__.filters, 'filters');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_1__.filters, $$value => $$invalidate(4, $filters = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('FilterGroup', slots, ['label']);
	let { filterTitle } = $$props;
	let { filterData } = $$props;
	let { changeHandler } = $$props;
	let { filterType } = $$props;

	$$self.$$.on_mount.push(function () {
		if (filterTitle === undefined && !('filterTitle' in $$props || $$self.$$.bound[$$self.$$.props['filterTitle']])) {
			console.warn("<FilterGroup> was created without expected prop 'filterTitle'");
		}

		if (filterData === undefined && !('filterData' in $$props || $$self.$$.bound[$$self.$$.props['filterData']])) {
			console.warn("<FilterGroup> was created without expected prop 'filterData'");
		}

		if (changeHandler === undefined && !('changeHandler' in $$props || $$self.$$.bound[$$self.$$.props['changeHandler']])) {
			console.warn("<FilterGroup> was created without expected prop 'changeHandler'");
		}

		if (filterType === undefined && !('filterType' in $$props || $$self.$$.bound[$$self.$$.props['filterType']])) {
			console.warn("<FilterGroup> was created without expected prop 'filterType'");
		}
	});

	const writable_props = ['filterTitle', 'filterData', 'changeHandler', 'filterType'];

	Object_1.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<FilterGroup> was created with unknown prop '${key}'`);
	});

	const $$binding_groups = [[]];

	function input_change_handler() {
		$filters[filterType] = this.__value;
		_stores__WEBPACK_IMPORTED_MODULE_1__.filters.set($filters);
	}

	$$self.$$set = $$props => {
		if ('filterTitle' in $$props) $$invalidate(0, filterTitle = $$props.filterTitle);
		if ('filterData' in $$props) $$invalidate(1, filterData = $$props.filterData);
		if ('changeHandler' in $$props) $$invalidate(2, changeHandler = $$props.changeHandler);
		if ('filterType' in $$props) $$invalidate(3, filterType = $$props.filterType);
		if ('$$scope' in $$props) $$invalidate(5, $$scope = $$props.$$scope);
	};

	$$self.$capture_state = () => ({
		filters: _stores__WEBPACK_IMPORTED_MODULE_1__.filters,
		filterTitle,
		filterData,
		changeHandler,
		filterType,
		$filters
	});

	$$self.$inject_state = $$props => {
		if ('filterTitle' in $$props) $$invalidate(0, filterTitle = $$props.filterTitle);
		if ('filterData' in $$props) $$invalidate(1, filterData = $$props.filterData);
		if ('changeHandler' in $$props) $$invalidate(2, changeHandler = $$props.changeHandler);
		if ('filterType' in $$props) $$invalidate(3, filterType = $$props.filterType);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [
		filterTitle,
		filterData,
		changeHandler,
		filterType,
		$filters,
		$$scope,
		slots,
		input_change_handler,
		$$binding_groups
	];
}

class FilterGroup extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(
			this,
			options,
			instance,
			create_fragment,
			svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal,
			{
				filterTitle: 0,
				filterData: 1,
				changeHandler: 2,
				filterType: 3
			},
			add_css
		);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "FilterGroup",
			options,
			id: create_fragment.name
		});
	}

	get filterTitle() {
		throw new Error("<FilterGroup>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set filterTitle(value) {
		throw new Error("<FilterGroup>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get filterData() {
		throw new Error("<FilterGroup>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set filterData(value) {
		throw new Error("<FilterGroup>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get changeHandler() {
		throw new Error("<FilterGroup>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set changeHandler(value) {
		throw new Error("<FilterGroup>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get filterType() {
		throw new Error("<FilterGroup>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set filterType(value) {
		throw new Error("<FilterGroup>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (FilterGroup);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Search/Search.svelte":
/*!*******************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Search/Search.svelte ***!
  \*******************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _FilterApplied_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FilterApplied.svelte */ "./modules/project_browser/sveltejs/src/Search/FilterApplied.svelte");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../util */ "./modules/project_browser/sveltejs/src/util.js");
/* harmony import */ var _SearchFilters_svelte__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./SearchFilters.svelte */ "./modules/project_browser/sveltejs/src/Search/SearchFilters.svelte");
/* harmony import */ var _SearchFilterToggle_svelte__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./SearchFilterToggle.svelte */ "./modules/project_browser/sveltejs/src/Search/SearchFilterToggle.svelte");
/* harmony import */ var _SearchSort_svelte__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./SearchSort.svelte */ "./modules/project_browser/sveltejs/src/Search/SearchSort.svelte");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Search/Search.svelte generated by Svelte v3.56.0 */


const { Object: Object_1 } = svelte_internal__WEBPACK_IMPORTED_MODULE_0__.globals;











const file = "modules/project_browser/sveltejs/src/Search/Search.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-tqj3ak", ".search__form-item.pb-tqj3ak.pb-tqj3ak{margin-top:0}.search__form.pb-tqj3ak.pb-tqj3ak{margin-top:2.375rem;display:inherit;flex-wrap:wrap;padding:0 0 1.5rem;border:transparent;border-radius:2px;background-color:#fff}.search__search-bar.pb-tqj3ak .search__searchterm.pb-tqj3ak{width:100%;outline:none;height:50px;position:relative;display:flex}.search__search-bar.pb-tqj3ak.pb-tqj3ak{height:50px;text-align:center;color:#fff;cursor:pointer;font-size:20px;position:relative;border:1px solid #919297;border-radius:2px}.search__search-icon.pb-tqj3ak.pb-tqj3ak{position:absolute;bottom:12px;inset-inline-end:30px}.search__searchterm.pb-tqj3ak.pb-tqj3ak::placeholder{font-family:sans-serif;font-style:normal;font-weight:400;font-size:16px;line-height:150%;display:flex;align-items:center}.dropdown-filters.pb-tqj3ak.pb-tqj3ak{position:relative;border:3px solid #f3f4f9;z-index:1}#output.pb-tqj3ak.pb-tqj3ak{display:inline-block;font-family:sans-serif;font-style:normal;font-weight:700;font-size:14px;line-height:21px;margin-inline-start:20px}.search__grid-container.pb-tqj3ak.pb-tqj3ak{display:grid;height:auto;grid-template-columns:5fr auto auto;grid-gap:20px;background:#f3f4f9;padding:5px;align-items:center;max-width:100%}.search__filter-button.pb-tqj3ak.pb-tqj3ak{padding:0 0.25rem;background:none;border:none;color:#013cc5;text-decoration:underline;cursor:pointer}@media screen and (max-width: 855px){.search__grid-container.pb-tqj3ak.pb-tqj3ak{display:block}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiU2VhcmNoLnN2ZWx0ZSIsInNvdXJjZXMiOlsiU2VhcmNoLnN2ZWx0ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8c2NyaXB0PlxuICBpbXBvcnQgeyBjcmVhdGVFdmVudERpc3BhdGNoZXIsIGdldENvbnRleHQsIG9uTW91bnQgfSBmcm9tICdzdmVsdGUnO1xuICBpbXBvcnQgRmlsdGVyQXBwbGllZCBmcm9tICcuL0ZpbHRlckFwcGxpZWQuc3ZlbHRlJztcbiAgaW1wb3J0IHsgbm9ybWFsaXplT3B0aW9ucywgc2hhbGxvd0NvbXBhcmUgfSBmcm9tICcuLi91dGlsJztcbiAgaW1wb3J0IFNlYXJjaEZpbHRlcnMgZnJvbSAnLi9TZWFyY2hGaWx0ZXJzLnN2ZWx0ZSc7XG4gIGltcG9ydCBTZWFyY2hGaWx0ZXJUb2dnbGUgZnJvbSAnLi9TZWFyY2hGaWx0ZXJUb2dnbGUuc3ZlbHRlJztcbiAgaW1wb3J0IFNlYXJjaFNvcnQgZnJvbSAnLi9TZWFyY2hTb3J0LnN2ZWx0ZSc7XG4gIGltcG9ydCB7XG4gICAgZmlsdGVycyxcbiAgICByb3dzQ291bnQsXG4gICAgZmlsdGVyc1ZvY2FidWxhcmllcyxcbiAgICBtb2R1bGVDYXRlZ29yeUZpbHRlcixcbiAgICBtb2R1bGVDYXRlZ29yeVZvY2FidWxhcmllcyxcbiAgICBzb3J0LFxuICAgIHNlYXJjaFN0cmluZyxcbiAgICBzb3J0Q3JpdGVyaWEsXG4gIH0gZnJvbSAnLi4vc3RvcmVzJztcbiAgaW1wb3J0IHtcbiAgICBDT1ZFUkVEX0lELFxuICAgIEFDVElWRUxZX01BSU5UQUlORURfSUQsXG4gICAgTUFJTlRFTkFOQ0VfT1BUSU9OUyxcbiAgICBERVZFTE9QTUVOVF9PUFRJT05TLFxuICAgIFNFQ1VSSVRZX09QVElPTlMsXG4gICAgQUxMX1ZBTFVFU19JRCxcbiAgICBGVUxMX01PRFVMRV9QQVRILFxuICAgIERBUktfQ09MT1JfU0NIRU1FLFxuICB9IGZyb20gJy4uL2NvbnN0YW50cyc7XG5cbiAgY29uc3QgeyBEcnVwYWwgfSA9IHdpbmRvdztcbiAgY29uc3QgeyBhbm5vdW5jZSB9ID0gRHJ1cGFsO1xuICBjb25zdCBkaXNwYXRjaCA9IGNyZWF0ZUV2ZW50RGlzcGF0Y2hlcigpO1xuICBjb25zdCBzdGF0ZUNvbnRleHQgPSBnZXRDb250ZXh0KCdzdGF0ZScpO1xuXG4gIGV4cG9ydCBjb25zdCBmaWx0ZXIgPSAocm93LCB0ZXh0KSA9PlxuICAgIE9iamVjdC52YWx1ZXMocm93KS5maWx0ZXIoXG4gICAgICAoaXRlbSkgPT5cbiAgICAgICAgaXRlbSAmJiBpdGVtLnRvU3RyaW5nKCkudG9Mb3dlckNhc2UoKS5pbmRleE9mKHRleHQudG9Mb3dlckNhc2UoKSkgPiAxLFxuICAgICkubGVuZ3RoID4gMDtcbiAgZXhwb3J0IGxldCBpbmRleCA9IC0xO1xuICBleHBvcnQgbGV0IHNlYXJjaFRleHQ7XG4gIHNlYXJjaFN0cmluZy5zdWJzY3JpYmUoKHZhbHVlKSA9PiB7XG4gICAgc2VhcmNoVGV4dCA9IHZhbHVlO1xuICB9KTtcbiAgZXhwb3J0IGxldCBsYWJlbHMgPSB7XG4gICAgcGxhY2Vob2xkZXI6IERydXBhbC50KCdNb2R1bGUgTmFtZSwgS2V5d29yZChzKSwgZXRjLicpLFxuICB9O1xuXG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBwcmVmZXItY29uc3RcbiAgbGV0IGZpbHRlcnNPcGVuID0gZmFsc2U7XG4gIGxldCBzb3J0TWF0Y2ggPSAkc29ydENyaXRlcmlhLmZpbmQoKG9wdGlvbikgPT4gb3B0aW9uLmlkID09PSAkc29ydCk7XG4gIGlmICh0eXBlb2Ygc29ydE1hdGNoID09PSAndW5kZWZpbmVkJykge1xuICAgICRzb3J0ID0gJHNvcnRDcml0ZXJpYVswXS5pZDtcbiAgICBzb3J0TWF0Y2ggPSAkc29ydENyaXRlcmlhLmZpbmQoKG9wdGlvbikgPT4gb3B0aW9uLmlkID09PSAkc29ydCk7XG4gIH1cbiAgbGV0IHNvcnRUZXh0ID0gc29ydE1hdGNoLnRleHQ7XG5cbiAgLyoqXG4gICAqIFJlZnJlc2hlcyB0aGUgbGl2ZSByZWdpb24gYWZ0ZXIgYSBmaWx0ZXIgb3Igc2VhcmNoIGNvbXBsZXRlcy5cbiAgICovXG4gIGNvbnN0IHJlZnJlc2hMaXZlUmVnaW9uID0gKCkgPT4ge1xuICAgIGlmICgkcm93c0NvdW50KSB7XG4gICAgICAvLyBTZXQgYW5ub3VuY2UoKSB0byBhbiBlbXB0eSBzdHJpbmcuIFRoaXMgZW5zdXJlcyB0aGUgcmVzdWx0IGNvdW50IHdpbGxcbiAgICAgIC8vIGJlIGFubm91bmNlZCBhZnRlciBmaWx0ZXJpbmcgZXZlbiBpZiB0aGUgY291bnQgaXMgdGhlIHNhbWUuXG4gICAgICBhbm5vdW5jZSgnJyk7XG5cbiAgICAgIC8vIFRoZSBhbm5vdW5jZW1lbnQgaXMgZGVsYXllZCBieSAyMTAgbWlsbGlzZWNvbmRzLCBhIHdhaXQgdGhhdCBpc1xuICAgICAgLy8gc2xpZ2h0bHkgbG9uZ2VyIHRoYW4gdGhlIDIwMCBtaWxsaXNlY29uZCBkZWJvdW5jZSgpIGJ1aWx0IGludG9cbiAgICAgIC8vIGFubm91bmNlKCkuIFRoaXMgZW5zdXJlcyB0aGF0IHRoZSBhYm92ZSBjYWxsIHRvIHJlc2V0IHRoZSBhcmlhIGxpdmVcbiAgICAgIC8vIHJlZ2lvbiB0byBhbiBlbXB0eSBzdHJpbmcgYWN0dWFsbHkgdGFrZXMgcGxhY2UgaW5zdGVhZCBvZiBiZWluZ1xuICAgICAgLy8gZGVib3VuY2VkLlxuICAgICAgc2V0VGltZW91dCgoKSA9PiB7XG4gICAgICAgIGFubm91bmNlKFxuICAgICAgICAgIERydXBhbC50KCdAY291bnQgUmVzdWx0cywgU29ydGVkIGJ5IEBzb3J0VGV4dCcsIHtcbiAgICAgICAgICAgICdAY291bnQnOiAkcm93c0NvdW50XG4gICAgICAgICAgICAgIC50b1N0cmluZygpXG4gICAgICAgICAgICAgIC5yZXBsYWNlKC9cXEIoPz0oXFxkezN9KSsoPyFcXGQpKS9nLCAnLCcpLFxuICAgICAgICAgICAgJ0Bzb3J0VGV4dCc6IHNvcnRUZXh0LFxuICAgICAgICAgIH0pLFxuICAgICAgICApO1xuICAgICAgfSwgMjEwKTtcbiAgICB9XG4gIH07XG5cbiAgY29uc3QgdXBkYXRlVm9jYWJ1bGFyaWVzID0gKHZvY2FidWxhcnksIHZhbHVlKSA9PiB7XG4gICAgY29uc3Qgbm9ybWFsaXplZFZhbHVlID0gbm9ybWFsaXplT3B0aW9ucyh2YWx1ZSk7XG4gICAgY29uc3Qgc3RvcmVkVmFsdWUgPSBKU09OLnBhcnNlKGxvY2FsU3RvcmFnZS5nZXRJdGVtKGBwYi4ke3ZvY2FidWxhcnl9YCkpO1xuICAgIGlmIChzdG9yZWRWYWx1ZSA9PT0gbnVsbCB8fCAhc2hhbGxvd0NvbXBhcmUobm9ybWFsaXplZFZhbHVlLCBzdG9yZWRWYWx1ZSkpIHtcbiAgICAgICRmaWx0ZXJzVm9jYWJ1bGFyaWVzW3ZvY2FidWxhcnldID0gbm9ybWFsaXplZFZhbHVlO1xuICAgICAgbG9jYWxTdG9yYWdlLnNldEl0ZW0oYHBiLiR7dm9jYWJ1bGFyeX1gLCBKU09OLnN0cmluZ2lmeShub3JtYWxpemVkVmFsdWUpKTtcbiAgICB9XG4gIH07XG5cbiAgb25Nb3VudCgoKSA9PiB7XG4gICAgdXBkYXRlVm9jYWJ1bGFyaWVzKCdkZXZlbG9wbWVudFN0YXR1cycsIERFVkVMT1BNRU5UX09QVElPTlMpO1xuICAgIHVwZGF0ZVZvY2FidWxhcmllcygnbWFpbnRlbmFuY2VTdGF0dXMnLCBNQUlOVEVOQU5DRV9PUFRJT05TKTtcbiAgICB1cGRhdGVWb2NhYnVsYXJpZXMoJ3NlY3VyaXR5Q292ZXJhZ2UnLCBTRUNVUklUWV9PUFRJT05TKTtcbiAgfSk7XG5cbiAgYXN5bmMgZnVuY3Rpb24gb25TZWFyY2goZXZlbnQpIHtcbiAgICBjb25zdCBzdGF0ZSA9IHN0YXRlQ29udGV4dC5nZXRTdGF0ZSgpO1xuICAgIGNvbnN0IGRldGFpbCA9IHtcbiAgICAgIG9yaWdpbmFsRXZlbnQ6IGV2ZW50LFxuICAgICAgZmlsdGVyLFxuICAgICAgaW5kZXgsXG4gICAgICBzZWFyY2hUZXh0LFxuICAgICAgcGFnZTogc3RhdGUucGFnZSxcbiAgICAgIHBhZ2VJbmRleDogc3RhdGUucGFnZUluZGV4LFxuICAgICAgcGFnZVNpemU6IHN0YXRlLnBhZ2VTaXplLFxuICAgICAgcm93czogc3RhdGUuZmlsdGVyZWRSb3dzLFxuICAgIH07XG4gICAgZGlzcGF0Y2goJ3NlYXJjaCcsIGRldGFpbCk7XG5cbiAgICBpZiAoZGV0YWlsLnByZXZlbnREZWZhdWx0ICE9PSB0cnVlKSB7XG4gICAgICBpZiAoZGV0YWlsLnNlYXJjaFRleHQubGVuZ3RoID09PSAwKSB7XG4gICAgICAgIHN0YXRlQ29udGV4dC5zZXRSb3dzKHN0YXRlLnJvd3MpO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgc3RhdGVDb250ZXh0LnNldFJvd3MoXG4gICAgICAgICAgZGV0YWlsLnJvd3MuZmlsdGVyKChyKSA9PiBkZXRhaWwuZmlsdGVyKHIsIGRldGFpbC5zZWFyY2hUZXh0LCBpbmRleCkpLFxuICAgICAgICApO1xuICAgICAgfVxuICAgICAgc3RhdGVDb250ZXh0LnNldFBhZ2UoMCwgMCk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHN0YXRlQ29udGV4dC5zZXRSb3dzKGRldGFpbC5yb3dzKTtcbiAgICB9XG4gICAgcmVmcmVzaExpdmVSZWdpb24oKTtcbiAgfVxuXG4gIGNvbnN0IG9uQWR2YW5jZWRGaWx0ZXIgPSBhc3luYyAoZXZlbnQpID0+IHtcbiAgICBjb25zdCBzdGF0ZSA9IHN0YXRlQ29udGV4dC5nZXRTdGF0ZSgpO1xuICAgIGNvbnN0IGRldGFpbCA9IHtcbiAgICAgIG9yaWdpbmFsRXZlbnQ6IGV2ZW50LFxuICAgICAgZGV2ZWxvcG1lbnRTdGF0dXM6ICRmaWx0ZXJzLmRldmVsb3BtZW50U3RhdHVzLFxuICAgICAgbWFpbnRlbmFuY2VTdGF0dXM6ICRmaWx0ZXJzLm1haW50ZW5hbmNlU3RhdHVzLFxuICAgICAgc2VjdXJpdHlDb3ZlcmFnZTogJGZpbHRlcnMuc2VjdXJpdHlDb3ZlcmFnZSxcbiAgICAgIHBhZ2U6IHN0YXRlLnBhZ2UsXG4gICAgICBwYWdlSW5kZXg6IHN0YXRlLnBhZ2VJbmRleCxcbiAgICAgIHBhZ2VTaXplOiBzdGF0ZS5wYWdlU2l6ZSxcbiAgICAgIHJvd3M6IHN0YXRlLmZpbHRlcmVkUm93cyxcbiAgICB9O1xuICAgIGRpc3BhdGNoKCdhZHZhbmNlZEZpbHRlcicsIGRldGFpbCk7XG4gICAgc3RhdGVDb250ZXh0LnNldFBhZ2UoMCwgMCk7XG4gICAgc3RhdGVDb250ZXh0LnNldFJvd3MoZGV0YWlsLnJvd3MpO1xuICAgIHJlZnJlc2hMaXZlUmVnaW9uKCk7XG4gIH07XG5cbiAgZnVuY3Rpb24gb25TZWxlY3RDYXRlZ29yeShldmVudCkge1xuICAgIGNvbnN0IHN0YXRlID0gc3RhdGVDb250ZXh0LmdldFN0YXRlKCk7XG4gICAgY29uc3QgZGV0YWlsID0ge1xuICAgICAgb3JpZ2luYWxFdmVudDogZXZlbnQsXG4gICAgICBjYXRlZ29yeTogJG1vZHVsZUNhdGVnb3J5RmlsdGVyLFxuICAgICAgcGFnZTogc3RhdGUucGFnZSxcbiAgICAgIHBhZ2VJbmRleDogc3RhdGUucGFnZUluZGV4LFxuICAgICAgcGFnZVNpemU6IHN0YXRlLnBhZ2VTaXplLFxuICAgICAgcm93czogc3RhdGUuZmlsdGVyZWRSb3dzLFxuICAgIH07XG4gICAgZGlzcGF0Y2goJ3NlbGVjdENhdGVnb3J5JywgZGV0YWlsKTtcbiAgICBzdGF0ZUNvbnRleHQuc2V0UGFnZSgwLCAwKTtcbiAgICBzdGF0ZUNvbnRleHQuc2V0Um93cyhkZXRhaWwucm93cyk7XG4gIH1cblxuICBmdW5jdGlvbiByZW1vdmVGaWx0ZXIoZmlsdGVyVHlwZSkge1xuICAgICRmaWx0ZXJzW2ZpbHRlclR5cGVdID0gQUxMX1ZBTFVFU19JRDtcbiAgICAkZmlsdGVycyA9ICRmaWx0ZXJzO1xuICAgIG9uQWR2YW5jZWRGaWx0ZXIoKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBBY3Rpb25zIHBlcmZvcm1lZCB3aGVuIGNsaWNraW5nIGZpbHRlciByZXNldHMgc3VjaCBhcyBcInJlY29tbWVuZGVkXCJcbiAgICogQHBhcmFtIHtzdHJpbmd9IG1haW50ZW5hbmNlSWRcbiAgICogICAgSUQgb2YgdGhlIHNlbGVjdGVkIG1haW50ZW5hbmNlIHN0YXR1cy5cbiAgICogQHBhcmFtIHtzdHJpbmd9IGRldmVsb3BtZW50SWRcbiAgICogICBJRCBvZiB0aGUgc2VsZWN0ZWQgZGV2ZWxvcG1lbnQgc3RhdHVzLlxuICAgKiBAcGFyYW0ge3N0cmluZ30gc2VjdXJpdHlJZFxuICAgKiAgIElEIG9mIHRoZSBzZWxlY3RlZCBzZWN1cml0eSBzdGF0dXMuXG4gICAqL1xuICBjb25zdCBmaWx0ZXJSZXNldHMgPSAobWFpbnRlbmFuY2VJZCwgZGV2ZWxvcG1lbnRJZCwgc2VjdXJpdHlJZCkgPT4ge1xuICAgICRmaWx0ZXJzLm1haW50ZW5hbmNlU3RhdHVzID0gbWFpbnRlbmFuY2VJZDtcbiAgICAkZmlsdGVycy5kZXZlbG9wbWVudFN0YXR1cyA9IGRldmVsb3BtZW50SWQ7XG4gICAgJGZpbHRlcnMuc2VjdXJpdHlDb3ZlcmFnZSA9IHNlY3VyaXR5SWQ7XG4gICAgJGZpbHRlcnMgPSAkZmlsdGVycztcbiAgICAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIgPSBbXTtcbiAgICBvbkFkdmFuY2VkRmlsdGVyKCk7XG4gICAgb25TZWxlY3RDYXRlZ29yeSgpO1xuICB9O1xuPC9zY3JpcHQ+XG5cbjxmb3JtIGNsYXNzPVwic2VhcmNoX19mb3JtXCI+XG4gIDxkaXZcbiAgICBjbGFzcz1cInNlYXJjaF9fZm9ybS1pdGVtIGpzLWZvcm0taXRlbSBmb3JtLWl0ZW0ganMtZm9ybS10eXBlLXRleHRmaWVsZCBmb3JtLXR5cGUtLXRleHRmaWVsZFwiXG4gICAgcm9sZT1cInNlYXJjaFwiXG4gID5cbiAgICA8bGFiZWwgZm9yPVwicGItdGV4dFwiIGNsYXNzPVwiZm9ybS1pdGVtX19sYWJlbFwiXG4gICAgICA+e0RydXBhbC50KCdTZWFyY2ggZm9yIG1vZHVsZXMnKX08L2xhYmVsXG4gICAgPlxuICAgIDxkaXYgY2xhc3M9XCJzZWFyY2hfX3NlYXJjaC1iYXJcIj5cbiAgICAgIDxpbnB1dFxuICAgICAgICBjbGFzcz1cInNlYXJjaF9fc2VhcmNodGVybSBmb3JtLXRleHQgZm9ybS1lbGVtZW50IGZvcm0tZWxlbWVudC0tdHlwZS10ZXh0XCJcbiAgICAgICAgdHlwZT1cInNlYXJjaFwiXG4gICAgICAgIHRpdGxlPXtsYWJlbHMucGxhY2Vob2xkZXJ9XG4gICAgICAgIHBsYWNlaG9sZGVyPXtsYWJlbHMucGxhY2Vob2xkZXJ9XG4gICAgICAgIGlkPVwicGItdGV4dFwiXG4gICAgICAgIG5hbWU9XCJ0ZXh0XCJcbiAgICAgICAgYmluZDp2YWx1ZT17JHNlYXJjaFN0cmluZ31cbiAgICAgICAgb246a2V5dXA9e0RydXBhbC5kZWJvdW5jZShvblNlYXJjaCwgMjUwLCBmYWxzZSl9XG4gICAgICAvPlxuICAgICAgPGltZ1xuICAgICAgICBjbGFzcz1cInNlYXJjaF9fc2VhcmNoLWljb25cIlxuICAgICAgICBpZD1cInNlYXJjaC1pY29uXCJcbiAgICAgICAgc3JjPVwie0ZVTExfTU9EVUxFX1BBVEh9L2ltYWdlcy9zZWFyY2gtaWNvbntEQVJLX0NPTE9SX1NDSEVNRVxuICAgICAgICAgID8gJy0tZGFyay1jb2xvci1zY2hlbWUnXG4gICAgICAgICAgOiAnJ30uc3ZnXCJcbiAgICAgICAgYWx0PVwiXCJcbiAgICAgIC8+XG4gICAgPC9kaXY+XG4gIDwvZGl2PlxuICA8ZGl2XG4gICAgY2xhc3M9XCJzZWFyY2hfX2dyaWQtY29udGFpbmVyIGpzLWZvcm0taXRlbSBqcy1mb3JtLXR5cGUtc2VsZWN0IGZvcm0tdHlwZS0tc2VsZWN0IGpzLWZvcm0taXRlbS10eXBlIGZvcm0taXRlbS0tdHlwZVwiXG4gID5cbiAgICA8c2VjdGlvbiBhcmlhLWxhYmVsPXtEcnVwYWwudCgnU2VhcmNoIHJlc3VsdHMnKX0+XG4gICAgICA8ZGl2IGNsYXNzPVwic2VhcmNoX19yZXN1bHRzLWNvdW50XCI+XG4gICAgICAgIDxzcGFuIGlkPVwib3V0cHV0XCI+XG4gICAgICAgICAgeyRyb3dzQ291bnQgJiZcbiAgICAgICAgICAgICRyb3dzQ291bnQudG9TdHJpbmcoKS5yZXBsYWNlKC9cXEIoPz0oXFxkezN9KSsoPyFcXGQpKS9nLCAnLCcpfVxuICAgICAgICAgIHtEcnVwYWwudCgnUmVzdWx0cycpfVxuICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmlzdWFsbHktaGlkZGVuXCJcbiAgICAgICAgICAgID57RHJ1cGFsLnQoJ1NvcnRlZCBieSBAc29ydFRleHQnLCB7ICdAc29ydFRleHQnOiBzb3J0VGV4dCB9KX08L3NwYW5cbiAgICAgICAgICA+XG4gICAgICAgIDwvc3Bhbj5cbiAgICAgICAgeyNlYWNoIFsnZGV2ZWxvcG1lbnRTdGF0dXMnLCAnbWFpbnRlbmFuY2VTdGF0dXMnLCAnc2VjdXJpdHlDb3ZlcmFnZSddIGFzIGZpbHRlclR5cGV9XG4gICAgICAgICAgeyNpZiAkZmlsdGVyc1tmaWx0ZXJUeXBlXX1cbiAgICAgICAgICAgIDxGaWx0ZXJBcHBsaWVkXG4gICAgICAgICAgICAgIGlkPXskZmlsdGVyc1tmaWx0ZXJUeXBlXX1cbiAgICAgICAgICAgICAgbGFiZWw9eyRmaWx0ZXJzVm9jYWJ1bGFyaWVzW2ZpbHRlclR5cGVdWyRmaWx0ZXJzW2ZpbHRlclR5cGVdXX1cbiAgICAgICAgICAgICAgY2xpY2tIYW5kbGVyPXsoKSA9PiByZW1vdmVGaWx0ZXIoZmlsdGVyVHlwZSl9XG4gICAgICAgICAgICAvPlxuICAgICAgICAgIHsvaWZ9XG4gICAgICAgIHsvZWFjaH1cblxuICAgICAgICB7I2VhY2ggJG1vZHVsZUNhdGVnb3J5RmlsdGVyIGFzIGNhdGVnb3J5fVxuICAgICAgICAgIDxGaWx0ZXJBcHBsaWVkXG4gICAgICAgICAgICBpZD17Y2F0ZWdvcnl9XG4gICAgICAgICAgICBsYWJlbD17JG1vZHVsZUNhdGVnb3J5Vm9jYWJ1bGFyaWVzW2NhdGVnb3J5XX1cbiAgICAgICAgICAgIGNsaWNrSGFuZGxlcj17KCkgPT4ge1xuICAgICAgICAgICAgICAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIuc3BsaWNlKFxuICAgICAgICAgICAgICAgICRtb2R1bGVDYXRlZ29yeUZpbHRlci5pbmRleE9mKGNhdGVnb3J5KSxcbiAgICAgICAgICAgICAgICAxLFxuICAgICAgICAgICAgICApO1xuICAgICAgICAgICAgICAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIgPSAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXI7XG4gICAgICAgICAgICAgIG9uU2VsZWN0Q2F0ZWdvcnkoKTtcbiAgICAgICAgICAgIH19XG4gICAgICAgICAgLz5cbiAgICAgICAgey9lYWNofVxuXG4gICAgICAgIHsjaWYgJGZpbHRlcnMuc2VjdXJpdHlDb3ZlcmFnZSAhPT0gQUxMX1ZBTFVFU19JRCB8fCAkZmlsdGVycy5tYWludGVuYW5jZVN0YXR1cyAhPT0gQUxMX1ZBTFVFU19JRCB8fCAkZmlsdGVycy5kZXZlbG9wbWVudFN0YXR1cyAhPT0gQUxMX1ZBTFVFU19JRCB8fCAkbW9kdWxlQ2F0ZWdvcnlGaWx0ZXIubGVuZ3RofVxuICAgICAgICAgIDxidXR0b25cbiAgICAgICAgICAgIGNsYXNzPVwic2VhcmNoX19maWx0ZXItYnV0dG9uXCJcbiAgICAgICAgICAgIG9uOmNsaWNrfHByZXZlbnREZWZhdWx0PXsoKSA9PlxuICAgICAgICAgICAgICBmaWx0ZXJSZXNldHMoQUxMX1ZBTFVFU19JRCwgQUxMX1ZBTFVFU19JRCwgQUxMX1ZBTFVFU19JRCl9XG4gICAgICAgICAgPlxuICAgICAgICAgICAge0RydXBhbC50KCdDbGVhciBmaWx0ZXJzJyl9XG4gICAgICAgICAgPC9idXR0b24+XG4gICAgICAgIHsvaWZ9XG4gICAgICAgIHsjaWYgISgkZmlsdGVycy5tYWludGVuYW5jZVN0YXR1cyA9PT0gQUNUSVZFTFlfTUFJTlRBSU5FRF9JRCAmJiAkZmlsdGVycy5zZWN1cml0eUNvdmVyYWdlID09PSBDT1ZFUkVEX0lEICYmICRmaWx0ZXJzLmRldmVsb3BtZW50U3RhdHVzID09PSBBTExfVkFMVUVTX0lEICYmICRtb2R1bGVDYXRlZ29yeUZpbHRlci5sZW5ndGggPT09IDApfVxuICAgICAgICAgIDxidXR0b25cbiAgICAgICAgICAgIGNsYXNzPVwic2VhcmNoX19maWx0ZXItYnV0dG9uXCJcbiAgICAgICAgICAgIG9uOmNsaWNrfHByZXZlbnREZWZhdWx0PXsoKSA9PlxuICAgICAgICAgICAgICBmaWx0ZXJSZXNldHMoQUNUSVZFTFlfTUFJTlRBSU5FRF9JRCwgQUxMX1ZBTFVFU19JRCwgQ09WRVJFRF9JRCl9XG4gICAgICAgICAgPlxuICAgICAgICAgICAge0RydXBhbC50KCdSZWNvbW1lbmRlZCBmaWx0ZXJzJyl9XG4gICAgICAgICAgPC9idXR0b24+XG4gICAgICAgIHsvaWZ9XG4gICAgICA8L2Rpdj5cbiAgICA8L3NlY3Rpb24+XG4gICAgPFNlYXJjaFNvcnQgb246c29ydCBiaW5kOnNvcnRUZXh0IHJlZnJlc2g9e3JlZnJlc2hMaXZlUmVnaW9ufSAvPlxuICAgIDxTZWFyY2hGaWx0ZXJUb2dnbGUgYmluZDppc09wZW49e2ZpbHRlcnNPcGVufSAvPlxuICA8L2Rpdj5cbiAgPGRpdiBjbGFzcz1cInNlYXJjaF9fZHJvcGRvd24gZHJvcGRvd24tZmlsdGVyc1wiIGlkPVwiZmlsdGVyLWRyb3Bkb3duXCI+XG4gICAgPFNlYXJjaEZpbHRlcnMgYmluZDppc09wZW49e2ZpbHRlcnNPcGVufSB7b25BZHZhbmNlZEZpbHRlcn0gLz5cbiAgPC9kaXY+XG48L2Zvcm0+XG5cbjxzdHlsZT5cbiAgLnNlYXJjaF9fZm9ybS1pdGVtIHtcbiAgICBtYXJnaW4tdG9wOiAwO1xuICB9XG4gIC5zZWFyY2hfX2Zvcm0ge1xuICAgIG1hcmdpbi10b3A6IDIuMzc1cmVtO1xuICAgIGRpc3BsYXk6IGluaGVyaXQ7XG4gICAgZmxleC13cmFwOiB3cmFwO1xuICAgIHBhZGRpbmc6IDAgMCAxLjVyZW07XG4gICAgYm9yZGVyOiB0cmFuc3BhcmVudDtcbiAgICBib3JkZXItcmFkaXVzOiAycHg7XG4gICAgYmFja2dyb3VuZC1jb2xvcjogI2ZmZjtcbiAgfVxuXG4gIC5zZWFyY2hfX3NlYXJjaC1iYXIgLnNlYXJjaF9fc2VhcmNodGVybSB7XG4gICAgd2lkdGg6IDEwMCU7XG4gICAgb3V0bGluZTogbm9uZTtcbiAgICBoZWlnaHQ6IDUwcHg7XG4gICAgcG9zaXRpb246IHJlbGF0aXZlO1xuICAgIGRpc3BsYXk6IGZsZXg7XG4gIH1cblxuICAuc2VhcmNoX19zZWFyY2gtYmFyIHtcbiAgICBoZWlnaHQ6IDUwcHg7XG4gICAgdGV4dC1hbGlnbjogY2VudGVyO1xuICAgIGNvbG9yOiAjZmZmO1xuICAgIGN1cnNvcjogcG9pbnRlcjtcbiAgICBmb250LXNpemU6IDIwcHg7XG4gICAgcG9zaXRpb246IHJlbGF0aXZlO1xuICAgIGJvcmRlcjogMXB4IHNvbGlkICM5MTkyOTc7XG4gICAgYm9yZGVyLXJhZGl1czogMnB4O1xuICB9XG5cbiAgLnNlYXJjaF9fc2VhcmNoLWljb24ge1xuICAgIHBvc2l0aW9uOiBhYnNvbHV0ZTtcbiAgICBib3R0b206IDEycHg7XG4gICAgaW5zZXQtaW5saW5lLWVuZDogMzBweDtcbiAgfVxuXG4gIC5zZWFyY2hfX3NlYXJjaHRlcm06OnBsYWNlaG9sZGVyIHtcbiAgICBmb250LWZhbWlseTogc2Fucy1zZXJpZjtcbiAgICBmb250LXN0eWxlOiBub3JtYWw7XG4gICAgZm9udC13ZWlnaHQ6IDQwMDtcbiAgICBmb250LXNpemU6IDE2cHg7XG4gICAgbGluZS1oZWlnaHQ6IDE1MCU7XG4gICAgZGlzcGxheTogZmxleDtcbiAgICBhbGlnbi1pdGVtczogY2VudGVyO1xuICB9XG5cbiAgLmRyb3Bkb3duLWZpbHRlcnMge1xuICAgIHBvc2l0aW9uOiByZWxhdGl2ZTtcbiAgICBib3JkZXI6IDNweCBzb2xpZCAjZjNmNGY5O1xuICAgIHotaW5kZXg6IDE7XG4gIH1cblxuICAjb3V0cHV0IHtcbiAgICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7XG4gICAgZm9udC1mYW1pbHk6IHNhbnMtc2VyaWY7XG4gICAgZm9udC1zdHlsZTogbm9ybWFsO1xuICAgIGZvbnQtd2VpZ2h0OiA3MDA7XG4gICAgZm9udC1zaXplOiAxNHB4O1xuICAgIGxpbmUtaGVpZ2h0OiAyMXB4O1xuICAgIG1hcmdpbi1pbmxpbmUtc3RhcnQ6IDIwcHg7XG4gIH1cblxuICAuc2VhcmNoX19ncmlkLWNvbnRhaW5lciB7XG4gICAgZGlzcGxheTogZ3JpZDtcbiAgICBoZWlnaHQ6IGF1dG87XG4gICAgZ3JpZC10ZW1wbGF0ZS1jb2x1bW5zOiA1ZnIgYXV0byBhdXRvO1xuICAgIGdyaWQtZ2FwOiAyMHB4O1xuICAgIGJhY2tncm91bmQ6ICNmM2Y0Zjk7XG4gICAgcGFkZGluZzogNXB4O1xuICAgIGFsaWduLWl0ZW1zOiBjZW50ZXI7XG4gICAgbWF4LXdpZHRoOiAxMDAlO1xuICB9XG5cbiAgLnNlYXJjaF9fZmlsdGVyLWJ1dHRvbiB7XG4gICAgcGFkZGluZzogMCAwLjI1cmVtO1xuICAgIGJhY2tncm91bmQ6IG5vbmU7XG4gICAgYm9yZGVyOiBub25lO1xuICAgIGNvbG9yOiAjMDEzY2M1O1xuICAgIHRleHQtZGVjb3JhdGlvbjogdW5kZXJsaW5lO1xuICAgIGN1cnNvcjogcG9pbnRlcjtcbiAgfVxuXG4gIEBtZWRpYSBzY3JlZW4gYW5kIChtYXgtd2lkdGg6IDg1NXB4KSB7XG4gICAgLnNlYXJjaF9fZ3JpZC1jb250YWluZXIge1xuICAgICAgZGlzcGxheTogYmxvY2s7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQTBSRSxzQ0FBbUIsQ0FDakIsVUFBVSxDQUFFLENBQ2QsQ0FDQSxpQ0FBYyxDQUNaLFVBQVUsQ0FBRSxRQUFRLENBQ3BCLE9BQU8sQ0FBRSxPQUFPLENBQ2hCLFNBQVMsQ0FBRSxJQUFJLENBQ2YsT0FBTyxDQUFFLENBQUMsQ0FBQyxDQUFDLENBQUMsTUFBTSxDQUNuQixNQUFNLENBQUUsV0FBVyxDQUNuQixhQUFhLENBQUUsR0FBRyxDQUNsQixnQkFBZ0IsQ0FBRSxJQUNwQixDQUVBLDZCQUFtQixDQUFDLDZCQUFvQixDQUN0QyxLQUFLLENBQUUsSUFBSSxDQUNYLE9BQU8sQ0FBRSxJQUFJLENBQ2IsTUFBTSxDQUFFLElBQUksQ0FDWixRQUFRLENBQUUsUUFBUSxDQUNsQixPQUFPLENBQUUsSUFDWCxDQUVBLHVDQUFvQixDQUNsQixNQUFNLENBQUUsSUFBSSxDQUNaLFVBQVUsQ0FBRSxNQUFNLENBQ2xCLEtBQUssQ0FBRSxJQUFJLENBQ1gsTUFBTSxDQUFFLE9BQU8sQ0FDZixTQUFTLENBQUUsSUFBSSxDQUNmLFFBQVEsQ0FBRSxRQUFRLENBQ2xCLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FBSyxDQUFDLE9BQU8sQ0FDekIsYUFBYSxDQUFFLEdBQ2pCLENBRUEsd0NBQXFCLENBQ25CLFFBQVEsQ0FBRSxRQUFRLENBQ2xCLE1BQU0sQ0FBRSxJQUFJLENBQ1osZ0JBQWdCLENBQUUsSUFDcEIsQ0FFQSx1Q0FBbUIsYUFBYyxDQUMvQixXQUFXLENBQUUsVUFBVSxDQUN2QixVQUFVLENBQUUsTUFBTSxDQUNsQixXQUFXLENBQUUsR0FBRyxDQUNoQixTQUFTLENBQUUsSUFBSSxDQUNmLFdBQVcsQ0FBRSxJQUFJLENBQ2pCLE9BQU8sQ0FBRSxJQUFJLENBQ2IsV0FBVyxDQUFFLE1BQ2YsQ0FFQSxxQ0FBa0IsQ0FDaEIsUUFBUSxDQUFFLFFBQVEsQ0FDbEIsTUFBTSxDQUFFLEdBQUcsQ0FBQyxLQUFLLENBQUMsT0FBTyxDQUN6QixPQUFPLENBQUUsQ0FDWCxDQUVBLDJCQUFRLENBQ04sT0FBTyxDQUFFLFlBQVksQ0FDckIsV0FBVyxDQUFFLFVBQVUsQ0FDdkIsVUFBVSxDQUFFLE1BQU0sQ0FDbEIsV0FBVyxDQUFFLEdBQUcsQ0FDaEIsU0FBUyxDQUFFLElBQUksQ0FDZixXQUFXLENBQUUsSUFBSSxDQUNqQixtQkFBbUIsQ0FBRSxJQUN2QixDQUVBLDJDQUF3QixDQUN0QixPQUFPLENBQUUsSUFBSSxDQUNiLE1BQU0sQ0FBRSxJQUFJLENBQ1oscUJBQXFCLENBQUUsR0FBRyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQ3BDLFFBQVEsQ0FBRSxJQUFJLENBQ2QsVUFBVSxDQUFFLE9BQU8sQ0FDbkIsT0FBTyxDQUFFLEdBQUcsQ0FDWixXQUFXLENBQUUsTUFBTSxDQUNuQixTQUFTLENBQUUsSUFDYixDQUVBLDBDQUF1QixDQUNyQixPQUFPLENBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FDbEIsVUFBVSxDQUFFLElBQUksQ0FDaEIsTUFBTSxDQUFFLElBQUksQ0FDWixLQUFLLENBQUUsT0FBTyxDQUNkLGVBQWUsQ0FBRSxTQUFTLENBQzFCLE1BQU0sQ0FBRSxPQUNWLENBRUEsT0FBTyxNQUFNLENBQUMsR0FBRyxDQUFDLFlBQVksS0FBSyxDQUFFLENBQ25DLDJDQUF3QixDQUN0QixPQUFPLENBQUUsS0FDWCxDQUNGIn0= */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[35] = list[i];
	return child_ctx;
}

function get_each_context_1(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[38] = list[i];
	return child_ctx;
}

// (230:10) {#if $filters[filterType]}
function create_if_block_2(ctx) {
	let filterapplied;
	let current;

	function func() {
		return /*func*/ ctx[20](/*filterType*/ ctx[38]);
	}

	filterapplied = new _FilterApplied_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				id: /*$filters*/ ctx[4][/*filterType*/ ctx[38]],
				label: /*$filtersVocabularies*/ ctx[5][/*filterType*/ ctx[38]][/*$filters*/ ctx[4][/*filterType*/ ctx[38]]],
				clickHandler: func
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(filterapplied.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(filterapplied, target, anchor);
			current = true;
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;
			const filterapplied_changes = {};
			if (dirty[0] & /*$filters*/ 16) filterapplied_changes.id = /*$filters*/ ctx[4][/*filterType*/ ctx[38]];
			if (dirty[0] & /*$filtersVocabularies, $filters*/ 48) filterapplied_changes.label = /*$filtersVocabularies*/ ctx[5][/*filterType*/ ctx[38]][/*$filters*/ ctx[4][/*filterType*/ ctx[38]]];
			filterapplied.$set(filterapplied_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(filterapplied.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(filterapplied.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(filterapplied, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_2.name,
		type: "if",
		source: "(230:10) {#if $filters[filterType]}",
		ctx
	});

	return block;
}

// (229:8) {#each ['developmentStatus', 'maintenanceStatus', 'securityCoverage'] as filterType}
function create_each_block_1(ctx) {
	let if_block_anchor;
	let current;
	let if_block = /*$filters*/ ctx[4][/*filterType*/ ctx[38]] && create_if_block_2(ctx);

	const block = {
		c: function create() {
			if (if_block) if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		m: function mount(target, anchor) {
			if (if_block) if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, dirty) {
			if (/*$filters*/ ctx[4][/*filterType*/ ctx[38]]) {
				if (if_block) {
					if_block.p(ctx, dirty);

					if (dirty[0] & /*$filters*/ 16) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					}
				} else {
					if_block = create_if_block_2(ctx);
					if_block.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			} else if (if_block) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block, 1, 1, () => {
					if_block = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (if_block) if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block_1.name,
		type: "each",
		source: "(229:8) {#each ['developmentStatus', 'maintenanceStatus', 'securityCoverage'] as filterType}",
		ctx
	});

	return block;
}

// (239:8) {#each $moduleCategoryFilter as category}
function create_each_block(ctx) {
	let filterapplied;
	let current;

	function func_1() {
		return /*func_1*/ ctx[21](/*category*/ ctx[35]);
	}

	filterapplied = new _FilterApplied_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				id: /*category*/ ctx[35],
				label: /*$moduleCategoryVocabularies*/ ctx[8][/*category*/ ctx[35]],
				clickHandler: func_1
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(filterapplied.$$.fragment);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(filterapplied, target, anchor);
			current = true;
		},
		p: function update(new_ctx, dirty) {
			ctx = new_ctx;
			const filterapplied_changes = {};
			if (dirty[0] & /*$moduleCategoryFilter*/ 8) filterapplied_changes.id = /*category*/ ctx[35];
			if (dirty[0] & /*$moduleCategoryVocabularies, $moduleCategoryFilter*/ 264) filterapplied_changes.label = /*$moduleCategoryVocabularies*/ ctx[8][/*category*/ ctx[35]];
			if (dirty[0] & /*$moduleCategoryFilter*/ 8) filterapplied_changes.clickHandler = func_1;
			filterapplied.$set(filterapplied_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(filterapplied.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(filterapplied.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(filterapplied, detaching);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(239:8) {#each $moduleCategoryFilter as category}",
		ctx
	});

	return block;
}

// (254:8) {#if $filters.securityCoverage !== ALL_VALUES_ID || $filters.maintenanceStatus !== ALL_VALUES_ID || $filters.developmentStatus !== ALL_VALUES_ID || $moduleCategoryFilter.length}
function create_if_block_1(ctx) {
	let button;
	let mounted;
	let dispose;

	const block = {
		c: function create() {
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			button.textContent = `${/*Drupal*/ ctx[9].t('Clear filters')}`;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "class", "search__filter-button pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 254, 10, 8140);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, button, anchor);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button, "click", (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prevent_default)(/*click_handler*/ ctx[22]), false, true, false, false);
				mounted = true;
			}
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(button);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(254:8) {#if $filters.securityCoverage !== ALL_VALUES_ID || $filters.maintenanceStatus !== ALL_VALUES_ID || $filters.developmentStatus !== ALL_VALUES_ID || $moduleCategoryFilter.length}",
		ctx
	});

	return block;
}

// (263:8) {#if !($filters.maintenanceStatus === ACTIVELY_MAINTAINED_ID && $filters.securityCoverage === COVERED_ID && $filters.developmentStatus === ALL_VALUES_ID && $moduleCategoryFilter.length === 0)}
function create_if_block(ctx) {
	let button;
	let mounted;
	let dispose;

	const block = {
		c: function create() {
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			button.textContent = `${/*Drupal*/ ctx[9].t('Recommended filters')}`;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "class", "search__filter-button pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 263, 10, 8603);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, button, anchor);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button, "click", (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prevent_default)(/*click_handler_1*/ ctx[23]), false, true, false, false);
				mounted = true;
			}
		},
		p: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(button);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(263:8) {#if !($filters.maintenanceStatus === ACTIVELY_MAINTAINED_ID && $filters.securityCoverage === COVERED_ID && $filters.developmentStatus === ALL_VALUES_ID && $moduleCategoryFilter.length === 0)}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let form;
	let div1;
	let label;
	let t1;
	let div0;
	let input;
	let input_title_value;
	let input_placeholder_value;
	let t2;
	let img;
	let img_src_value;
	let t3;
	let div3;
	let section;
	let div2;
	let span1;
	let t4_value = (/*$rowsCount*/ ctx[6] && /*$rowsCount*/ ctx[6].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')) + "";
	let t4;
	let t5;
	let t6_value = /*Drupal*/ ctx[9].t('Results') + "";
	let t6;
	let t7;
	let span0;
	let t8_value = /*Drupal*/ ctx[9].t('Sorted by @sortText', { '@sortText': /*sortText*/ ctx[2] }) + "";
	let t8;
	let t9;
	let t10;
	let t11;
	let t12;
	let section_aria_label_value;
	let t13;
	let searchsort;
	let updating_sortText;
	let t14;
	let searchfiltertoggle;
	let updating_isOpen;
	let t15;
	let div4;
	let searchfilters;
	let updating_isOpen_1;
	let current;
	let mounted;
	let dispose;
	let each_value_1 = ['developmentStatus', 'maintenanceStatus', 'securityCoverage'];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value_1);
	let each_blocks_1 = [];

	for (let i = 0; i < 3; i += 1) {
		each_blocks_1[i] = create_each_block_1(get_each_context_1(ctx, each_value_1, i));
	}

	const out = i => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks_1[i], 1, 1, () => {
		each_blocks_1[i] = null;
	});

	let each_value = /*$moduleCategoryFilter*/ ctx[3];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const out_1 = i => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks[i], 1, 1, () => {
		each_blocks[i] = null;
	});

	let if_block0 = (/*$filters*/ ctx[4].securityCoverage !== _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID || /*$filters*/ ctx[4].maintenanceStatus !== _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID || /*$filters*/ ctx[4].developmentStatus !== _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID || /*$moduleCategoryFilter*/ ctx[3].length) && create_if_block_1(ctx);
	let if_block1 = !(/*$filters*/ ctx[4].maintenanceStatus === _constants__WEBPACK_IMPORTED_MODULE_8__.ACTIVELY_MAINTAINED_ID && /*$filters*/ ctx[4].securityCoverage === _constants__WEBPACK_IMPORTED_MODULE_8__.COVERED_ID && /*$filters*/ ctx[4].developmentStatus === _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID && /*$moduleCategoryFilter*/ ctx[3].length === 0) && create_if_block(ctx);

	function searchsort_sortText_binding(value) {
		/*searchsort_sortText_binding*/ ctx[24](value);
	}

	let searchsort_props = { refresh: /*refreshLiveRegion*/ ctx[10] };

	if (/*sortText*/ ctx[2] !== void 0) {
		searchsort_props.sortText = /*sortText*/ ctx[2];
	}

	searchsort = new _SearchSort_svelte__WEBPACK_IMPORTED_MODULE_6__["default"]({ props: searchsort_props, $$inline: true });
	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(searchsort, 'sortText', searchsort_sortText_binding));
	searchsort.$on("sort", /*sort_handler*/ ctx[25]);

	function searchfiltertoggle_isOpen_binding(value) {
		/*searchfiltertoggle_isOpen_binding*/ ctx[26](value);
	}

	let searchfiltertoggle_props = {};

	if (/*filtersOpen*/ ctx[1] !== void 0) {
		searchfiltertoggle_props.isOpen = /*filtersOpen*/ ctx[1];
	}

	searchfiltertoggle = new _SearchFilterToggle_svelte__WEBPACK_IMPORTED_MODULE_5__["default"]({
			props: searchfiltertoggle_props,
			$$inline: true
		});

	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(searchfiltertoggle, 'isOpen', searchfiltertoggle_isOpen_binding));

	function searchfilters_isOpen_binding(value) {
		/*searchfilters_isOpen_binding*/ ctx[27](value);
	}

	let searchfilters_props = {
		onAdvancedFilter: /*onAdvancedFilter*/ ctx[12]
	};

	if (/*filtersOpen*/ ctx[1] !== void 0) {
		searchfilters_props.isOpen = /*filtersOpen*/ ctx[1];
	}

	searchfilters = new _SearchFilters_svelte__WEBPACK_IMPORTED_MODULE_4__["default"]({
			props: searchfilters_props,
			$$inline: true
		});

	svelte_internal__WEBPACK_IMPORTED_MODULE_0__.binding_callbacks.push(() => (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bind)(searchfilters, 'isOpen', searchfilters_isOpen_binding));

	const block = {
		c: function create() {
			form = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("form");
			div1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			label.textContent = `${/*Drupal*/ ctx[9].t('Search for modules')}`;
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			input = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("input");
			t2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			t3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div3 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			section = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("section");
			div2 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			span1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t4_value);
			t5 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			t6 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t6_value);
			t7 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			span0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			t8 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t8_value);
			t9 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();

			for (let i = 0; i < 3; i += 1) {
				each_blocks_1[i].c();
			}

			t10 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();

			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			t11 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block0) if_block0.c();
			t12 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block1) if_block1.c();
			t13 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(searchsort.$$.fragment);
			t14 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(searchfiltertoggle.$$.fragment);
			t15 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			div4 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(searchfilters.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", "pb-text");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "class", "form-item__label");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 191, 4, 5849);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "class", "search__searchterm form-text form-element form-element--type-text pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "type", "search");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "title", input_title_value = /*labels*/ ctx[0].placeholder);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "placeholder", input_placeholder_value = /*labels*/ ctx[0].placeholder);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "id", "pb-text");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "name", "text");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(input, file, 195, 6, 5991);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", "search__search-icon pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "id", "search-icon");
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_8__.FULL_MODULE_PATH + "/images/search-icon" + (_constants__WEBPACK_IMPORTED_MODULE_8__.DARK_COLOR_SCHEME ? '--dark-color-scheme' : '') + ".svg"))) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "alt", "");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 205, 6, 6326);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div0, "class", "search__search-bar pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div0, file, 194, 4, 5952);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "class", "search__form-item js-form-item form-item js-form-type-textfield form-type--textfield pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div1, "role", "search");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div1, file, 187, 2, 5721);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span0, "class", "visually-hidden");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span0, file, 224, 10, 6954);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span1, "id", "output");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span1, "class", "pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span1, file, 220, 8, 6795);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div2, "class", "search__results-count");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div2, file, 219, 6, 6751);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(section, "aria-label", section_aria_label_value = /*Drupal*/ ctx[9].t('Search results'));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(section, file, 218, 4, 6695);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div3, "class", "search__grid-container js-form-item js-form-type-select form-type--select js-form-item-type form-item--type pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div3, file, 215, 2, 6562);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div4, "class", "search__dropdown dropdown-filters pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div4, "id", "filter-dropdown");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div4, file, 276, 2, 9028);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(form, "class", "search__form pb-tqj3ak");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(form, file, 186, 0, 5691);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, form, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(form, div1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, label);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div1, div0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div0, input);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_input_value)(input, /*$searchString*/ ctx[7]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div0, t2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div0, img);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(form, t3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(form, div3);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, section);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(section, div2);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, span1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, t4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, t5);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, t6);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, t7);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span1, span0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(span0, t8);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t9);

			for (let i = 0; i < 3; i += 1) {
				if (each_blocks_1[i]) {
					each_blocks_1[i].m(div2, null);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t10);

			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(div2, null);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t11);
			if (if_block0) if_block0.m(div2, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div2, t12);
			if (if_block1) if_block1.m(div2, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, t13);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(searchsort, div3, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div3, t14);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(searchfiltertoggle, div3, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(form, t15);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(form, div4);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(searchfilters, div4, null);
			current = true;

			if (!mounted) {
				dispose = [
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(input, "input", /*input_input_handler*/ ctx[19]),
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(input, "keyup", /*Drupal*/ ctx[9].debounce(/*onSearch*/ ctx[11], 250, false), false, false, false, false)
				];

				mounted = true;
			}
		},
		p: function update(ctx, dirty) {
			if (!current || dirty[0] & /*labels*/ 1 && input_title_value !== (input_title_value = /*labels*/ ctx[0].placeholder)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "title", input_title_value);
			}

			if (!current || dirty[0] & /*labels*/ 1 && input_placeholder_value !== (input_placeholder_value = /*labels*/ ctx[0].placeholder)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(input, "placeholder", input_placeholder_value);
			}

			if (dirty[0] & /*$searchString*/ 128 && input.value !== /*$searchString*/ ctx[7]) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_input_value)(input, /*$searchString*/ ctx[7]);
			}

			if ((!current || dirty[0] & /*$rowsCount*/ 64) && t4_value !== (t4_value = (/*$rowsCount*/ ctx[6] && /*$rowsCount*/ ctx[6].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')) + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t4, t4_value);
			if ((!current || dirty[0] & /*sortText*/ 4) && t8_value !== (t8_value = /*Drupal*/ ctx[9].t('Sorted by @sortText', { '@sortText': /*sortText*/ ctx[2] }) + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t8, t8_value);

			if (dirty[0] & /*$filters, $filtersVocabularies, removeFilter*/ 16432) {
				each_value_1 = ['developmentStatus', 'maintenanceStatus', 'securityCoverage'];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value_1);
				let i;

				for (i = 0; i < 3; i += 1) {
					const child_ctx = get_each_context_1(ctx, each_value_1, i);

					if (each_blocks_1[i]) {
						each_blocks_1[i].p(child_ctx, dirty);
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks_1[i], 1);
					} else {
						each_blocks_1[i] = create_each_block_1(child_ctx);
						each_blocks_1[i].c();
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks_1[i], 1);
						each_blocks_1[i].m(div2, t10);
					}
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				for (i = 3; i < 3; i += 1) {
					out(i);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (dirty[0] & /*$moduleCategoryFilter, $moduleCategoryVocabularies, onSelectCategory*/ 8456) {
				each_value = /*$moduleCategoryFilter*/ ctx[3];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i], 1);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i], 1);
						each_blocks[i].m(div2, t11);
					}
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				for (i = each_value.length; i < each_blocks.length; i += 1) {
					out_1(i);
				}

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (/*$filters*/ ctx[4].securityCoverage !== _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID || /*$filters*/ ctx[4].maintenanceStatus !== _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID || /*$filters*/ ctx[4].developmentStatus !== _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID || /*$moduleCategoryFilter*/ ctx[3].length) {
				if (if_block0) {
					if_block0.p(ctx, dirty);
				} else {
					if_block0 = create_if_block_1(ctx);
					if_block0.c();
					if_block0.m(div2, t12);
				}
			} else if (if_block0) {
				if_block0.d(1);
				if_block0 = null;
			}

			if (!(/*$filters*/ ctx[4].maintenanceStatus === _constants__WEBPACK_IMPORTED_MODULE_8__.ACTIVELY_MAINTAINED_ID && /*$filters*/ ctx[4].securityCoverage === _constants__WEBPACK_IMPORTED_MODULE_8__.COVERED_ID && /*$filters*/ ctx[4].developmentStatus === _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID && /*$moduleCategoryFilter*/ ctx[3].length === 0)) {
				if (if_block1) {
					if_block1.p(ctx, dirty);
				} else {
					if_block1 = create_if_block(ctx);
					if_block1.c();
					if_block1.m(div2, null);
				}
			} else if (if_block1) {
				if_block1.d(1);
				if_block1 = null;
			}

			const searchsort_changes = {};

			if (!updating_sortText && dirty[0] & /*sortText*/ 4) {
				updating_sortText = true;
				searchsort_changes.sortText = /*sortText*/ ctx[2];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_sortText = false);
			}

			searchsort.$set(searchsort_changes);
			const searchfiltertoggle_changes = {};

			if (!updating_isOpen && dirty[0] & /*filtersOpen*/ 2) {
				updating_isOpen = true;
				searchfiltertoggle_changes.isOpen = /*filtersOpen*/ ctx[1];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_isOpen = false);
			}

			searchfiltertoggle.$set(searchfiltertoggle_changes);
			const searchfilters_changes = {};

			if (!updating_isOpen_1 && dirty[0] & /*filtersOpen*/ 2) {
				updating_isOpen_1 = true;
				searchfilters_changes.isOpen = /*filtersOpen*/ ctx[1];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_flush_callback)(() => updating_isOpen_1 = false);
			}

			searchfilters.$set(searchfilters_changes);
		},
		i: function intro(local) {
			if (current) return;

			for (let i = 0; i < 3; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks_1[i]);
			}

			for (let i = 0; i < each_value.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(each_blocks[i]);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(searchsort.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(searchfiltertoggle.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(searchfilters.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			each_blocks_1 = each_blocks_1.filter(Boolean);

			for (let i = 0; i < 3; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks_1[i]);
			}

			each_blocks = each_blocks.filter(Boolean);

			for (let i = 0; i < each_blocks.length; i += 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(each_blocks[i]);
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(searchsort.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(searchfiltertoggle.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(searchfilters.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(form);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks_1, detaching);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
			if (if_block0) if_block0.d();
			if (if_block1) if_block1.d();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(searchsort);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(searchfiltertoggle);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(searchfilters);
			mounted = false;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.run_all)(dispose);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let $moduleCategoryFilter;
	let $filters;
	let $filtersVocabularies;
	let $rowsCount;
	let $sort;
	let $sortCriteria;
	let $searchString;
	let $moduleCategoryVocabularies;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryFilter, 'moduleCategoryFilter');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryFilter, $$value => $$invalidate(3, $moduleCategoryFilter = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.filters, 'filters');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.filters, $$value => $$invalidate(4, $filters = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.filtersVocabularies, 'filtersVocabularies');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.filtersVocabularies, $$value => $$invalidate(5, $filtersVocabularies = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.rowsCount, 'rowsCount');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.rowsCount, $$value => $$invalidate(6, $rowsCount = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.sort, 'sort');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.sort, $$value => $$invalidate(29, $sort = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.sortCriteria, 'sortCriteria');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.sortCriteria, $$value => $$invalidate(30, $sortCriteria = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.searchString, 'searchString');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.searchString, $$value => $$invalidate(7, $searchString = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryVocabularies, 'moduleCategoryVocabularies');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryVocabularies, $$value => $$invalidate(8, $moduleCategoryVocabularies = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('Search', slots, []);
	const { Drupal } = window;
	const { announce } = Drupal;
	const dispatch = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher)();
	const stateContext = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.getContext)('state');
	const filter = (row, text) => Object.values(row).filter(item => item && item.toString().toLowerCase().indexOf(text.toLowerCase()) > 1).length > 0;
	let { index = -1 } = $$props;
	let { searchText } = $$props;

	_stores__WEBPACK_IMPORTED_MODULE_7__.searchString.subscribe(value => {
		$$invalidate(16, searchText = value);
	});

	let { labels = {
		placeholder: Drupal.t('Module Name, Keyword(s), etc.')
	} } = $$props;

	// eslint-disable-next-line prefer-const
	let filtersOpen = false;

	let sortMatch = $sortCriteria.find(option => option.id === $sort);

	if (typeof sortMatch === 'undefined') {
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_7__.sort, $sort = $sortCriteria[0].id, $sort);
		sortMatch = $sortCriteria.find(option => option.id === $sort);
	}

	let sortText = sortMatch.text;

	/**
 * Refreshes the live region after a filter or search completes.
 */
	const refreshLiveRegion = () => {
		if ($rowsCount) {
			// Set announce() to an empty string. This ensures the result count will
			// be announced after filtering even if the count is the same.
			announce('');

			// The announcement is delayed by 210 milliseconds, a wait that is
			// slightly longer than the 200 millisecond debounce() built into
			// announce(). This ensures that the above call to reset the aria live
			// region to an empty string actually takes place instead of being
			// debounced.
			setTimeout(
				() => {
					announce(Drupal.t('@count Results, Sorted by @sortText', {
						'@count': $rowsCount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','),
						'@sortText': sortText
					}));
				},
				210
			);
		}
	};

	const updateVocabularies = (vocabulary, value) => {
		const normalizedValue = (0,_util__WEBPACK_IMPORTED_MODULE_3__.normalizeOptions)(value);
		const storedValue = JSON.parse(localStorage.getItem(`pb.${vocabulary}`));

		if (storedValue === null || !(0,_util__WEBPACK_IMPORTED_MODULE_3__.shallowCompare)(normalizedValue, storedValue)) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_7__.filtersVocabularies, $filtersVocabularies[vocabulary] = normalizedValue, $filtersVocabularies);
			localStorage.setItem(`pb.${vocabulary}`, JSON.stringify(normalizedValue));
		}
	};

	(0,svelte__WEBPACK_IMPORTED_MODULE_1__.onMount)(() => {
		updateVocabularies('developmentStatus', _constants__WEBPACK_IMPORTED_MODULE_8__.DEVELOPMENT_OPTIONS);
		updateVocabularies('maintenanceStatus', _constants__WEBPACK_IMPORTED_MODULE_8__.MAINTENANCE_OPTIONS);
		updateVocabularies('securityCoverage', _constants__WEBPACK_IMPORTED_MODULE_8__.SECURITY_OPTIONS);
	});

	async function onSearch(event) {
		const state = stateContext.getState();

		const detail = {
			originalEvent: event,
			filter,
			index,
			searchText,
			page: state.page,
			pageIndex: state.pageIndex,
			pageSize: state.pageSize,
			rows: state.filteredRows
		};

		dispatch('search', detail);

		if (detail.preventDefault !== true) {
			if (detail.searchText.length === 0) {
				stateContext.setRows(state.rows);
			} else {
				stateContext.setRows(detail.rows.filter(r => detail.filter(r, detail.searchText, index)));
			}

			stateContext.setPage(0, 0);
		} else {
			stateContext.setRows(detail.rows);
		}

		refreshLiveRegion();
	}

	const onAdvancedFilter = async event => {
		const state = stateContext.getState();

		const detail = {
			originalEvent: event,
			developmentStatus: $filters.developmentStatus,
			maintenanceStatus: $filters.maintenanceStatus,
			securityCoverage: $filters.securityCoverage,
			page: state.page,
			pageIndex: state.pageIndex,
			pageSize: state.pageSize,
			rows: state.filteredRows
		};

		dispatch('advancedFilter', detail);
		stateContext.setPage(0, 0);
		stateContext.setRows(detail.rows);
		refreshLiveRegion();
	};

	function onSelectCategory(event) {
		const state = stateContext.getState();

		const detail = {
			originalEvent: event,
			category: $moduleCategoryFilter,
			page: state.page,
			pageIndex: state.pageIndex,
			pageSize: state.pageSize,
			rows: state.filteredRows
		};

		dispatch('selectCategory', detail);
		stateContext.setPage(0, 0);
		stateContext.setRows(detail.rows);
	}

	function removeFilter(filterType) {
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_7__.filters, $filters[filterType] = _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID, $filters);
		_stores__WEBPACK_IMPORTED_MODULE_7__.filters.set($filters);
		onAdvancedFilter();
	}

	/**
 * Actions performed when clicking filter resets such as "recommended"
 * @param {string} maintenanceId
 *    ID of the selected maintenance status.
 * @param {string} developmentId
 *   ID of the selected development status.
 * @param {string} securityId
 *   ID of the selected security status.
 */
	const filterResets = (maintenanceId, developmentId, securityId) => {
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_7__.filters, $filters.maintenanceStatus = maintenanceId, $filters);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_7__.filters, $filters.developmentStatus = developmentId, $filters);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_7__.filters, $filters.securityCoverage = securityId, $filters);
		_stores__WEBPACK_IMPORTED_MODULE_7__.filters.set($filters);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_store_value)(_stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryFilter, $moduleCategoryFilter = [], $moduleCategoryFilter);
		onAdvancedFilter();
		onSelectCategory();
	};

	$$self.$$.on_mount.push(function () {
		if (searchText === undefined && !('searchText' in $$props || $$self.$$.bound[$$self.$$.props['searchText']])) {
			console.warn("<Search> was created without expected prop 'searchText'");
		}
	});

	const writable_props = ['index', 'searchText', 'labels'];

	Object_1.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Search> was created with unknown prop '${key}'`);
	});

	function input_input_handler() {
		$searchString = this.value;
		_stores__WEBPACK_IMPORTED_MODULE_7__.searchString.set($searchString);
	}

	const func = filterType => removeFilter(filterType);

	const func_1 = category => {
		$moduleCategoryFilter.splice($moduleCategoryFilter.indexOf(category), 1);
		_stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryFilter.set($moduleCategoryFilter);
		onSelectCategory();
	};

	const click_handler = () => filterResets(_constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID, _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID, _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID);
	const click_handler_1 = () => filterResets(_constants__WEBPACK_IMPORTED_MODULE_8__.ACTIVELY_MAINTAINED_ID, _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID, _constants__WEBPACK_IMPORTED_MODULE_8__.COVERED_ID);

	function searchsort_sortText_binding(value) {
		sortText = value;
		$$invalidate(2, sortText);
	}

	function sort_handler(event) {
		svelte_internal__WEBPACK_IMPORTED_MODULE_0__.bubble.call(this, $$self, event);
	}

	function searchfiltertoggle_isOpen_binding(value) {
		filtersOpen = value;
		$$invalidate(1, filtersOpen);
	}

	function searchfilters_isOpen_binding(value) {
		filtersOpen = value;
		$$invalidate(1, filtersOpen);
	}

	$$self.$$set = $$props => {
		if ('index' in $$props) $$invalidate(18, index = $$props.index);
		if ('searchText' in $$props) $$invalidate(16, searchText = $$props.searchText);
		if ('labels' in $$props) $$invalidate(0, labels = $$props.labels);
	};

	$$self.$capture_state = () => ({
		createEventDispatcher: svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher,
		getContext: svelte__WEBPACK_IMPORTED_MODULE_1__.getContext,
		onMount: svelte__WEBPACK_IMPORTED_MODULE_1__.onMount,
		FilterApplied: _FilterApplied_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		normalizeOptions: _util__WEBPACK_IMPORTED_MODULE_3__.normalizeOptions,
		shallowCompare: _util__WEBPACK_IMPORTED_MODULE_3__.shallowCompare,
		SearchFilters: _SearchFilters_svelte__WEBPACK_IMPORTED_MODULE_4__["default"],
		SearchFilterToggle: _SearchFilterToggle_svelte__WEBPACK_IMPORTED_MODULE_5__["default"],
		SearchSort: _SearchSort_svelte__WEBPACK_IMPORTED_MODULE_6__["default"],
		filters: _stores__WEBPACK_IMPORTED_MODULE_7__.filters,
		rowsCount: _stores__WEBPACK_IMPORTED_MODULE_7__.rowsCount,
		filtersVocabularies: _stores__WEBPACK_IMPORTED_MODULE_7__.filtersVocabularies,
		moduleCategoryFilter: _stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryFilter,
		moduleCategoryVocabularies: _stores__WEBPACK_IMPORTED_MODULE_7__.moduleCategoryVocabularies,
		sort: _stores__WEBPACK_IMPORTED_MODULE_7__.sort,
		searchString: _stores__WEBPACK_IMPORTED_MODULE_7__.searchString,
		sortCriteria: _stores__WEBPACK_IMPORTED_MODULE_7__.sortCriteria,
		COVERED_ID: _constants__WEBPACK_IMPORTED_MODULE_8__.COVERED_ID,
		ACTIVELY_MAINTAINED_ID: _constants__WEBPACK_IMPORTED_MODULE_8__.ACTIVELY_MAINTAINED_ID,
		MAINTENANCE_OPTIONS: _constants__WEBPACK_IMPORTED_MODULE_8__.MAINTENANCE_OPTIONS,
		DEVELOPMENT_OPTIONS: _constants__WEBPACK_IMPORTED_MODULE_8__.DEVELOPMENT_OPTIONS,
		SECURITY_OPTIONS: _constants__WEBPACK_IMPORTED_MODULE_8__.SECURITY_OPTIONS,
		ALL_VALUES_ID: _constants__WEBPACK_IMPORTED_MODULE_8__.ALL_VALUES_ID,
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_8__.FULL_MODULE_PATH,
		DARK_COLOR_SCHEME: _constants__WEBPACK_IMPORTED_MODULE_8__.DARK_COLOR_SCHEME,
		Drupal,
		announce,
		dispatch,
		stateContext,
		filter,
		index,
		searchText,
		labels,
		filtersOpen,
		sortMatch,
		sortText,
		refreshLiveRegion,
		updateVocabularies,
		onSearch,
		onAdvancedFilter,
		onSelectCategory,
		removeFilter,
		filterResets,
		$moduleCategoryFilter,
		$filters,
		$filtersVocabularies,
		$rowsCount,
		$sort,
		$sortCriteria,
		$searchString,
		$moduleCategoryVocabularies
	});

	$$self.$inject_state = $$props => {
		if ('index' in $$props) $$invalidate(18, index = $$props.index);
		if ('searchText' in $$props) $$invalidate(16, searchText = $$props.searchText);
		if ('labels' in $$props) $$invalidate(0, labels = $$props.labels);
		if ('filtersOpen' in $$props) $$invalidate(1, filtersOpen = $$props.filtersOpen);
		if ('sortMatch' in $$props) sortMatch = $$props.sortMatch;
		if ('sortText' in $$props) $$invalidate(2, sortText = $$props.sortText);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [
		labels,
		filtersOpen,
		sortText,
		$moduleCategoryFilter,
		$filters,
		$filtersVocabularies,
		$rowsCount,
		$searchString,
		$moduleCategoryVocabularies,
		Drupal,
		refreshLiveRegion,
		onSearch,
		onAdvancedFilter,
		onSelectCategory,
		removeFilter,
		filterResets,
		searchText,
		filter,
		index,
		input_input_handler,
		func,
		func_1,
		click_handler,
		click_handler_1,
		searchsort_sortText_binding,
		sort_handler,
		searchfiltertoggle_isOpen_binding,
		searchfilters_isOpen_binding
	];
}

class Search extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(
			this,
			options,
			instance,
			create_fragment,
			svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal,
			{
				filter: 17,
				index: 18,
				searchText: 16,
				labels: 0
			},
			add_css,
			[-1, -1]
		);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "Search",
			options,
			id: create_fragment.name
		});
	}

	get filter() {
		return this.$$.ctx[17];
	}

	set filter(value) {
		throw new Error("<Search>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get index() {
		throw new Error("<Search>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set index(value) {
		throw new Error("<Search>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get searchText() {
		throw new Error("<Search>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set searchText(value) {
		throw new Error("<Search>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get labels() {
		throw new Error("<Search>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set labels(value) {
		throw new Error("<Search>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Search);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Search/SearchFilterToggle.svelte":
/*!*******************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Search/SearchFilterToggle.svelte ***!
  \*******************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Search/SearchFilterToggle.svelte generated by Svelte v3.56.0 */



const file = "modules/project_browser/sveltejs/src/Search/SearchFilterToggle.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-yurui9", ".search__filter__toggle-container.pb-yurui9{display:flex;margin-inline-end:1em}.search__filter__toggle.pb-yurui9{background-color:#d3d4d9;color:black;padding-left:16px;padding-right:16px;font-size:16px;border-radius:2px;width:fit-content;margin-inline-start:15px;text-decoration:underline}.search__filter__toggle.is_open.pb-yurui9{background-color:#adaeb3}.search__filter__toggle.pb-yurui9:hover{cursor:pointer}.search__filter__toggle-img.pb-yurui9{width:20px;height:14px;padding:0 0.25rem;margin-bottom:-2px;margin-inline-end:4px}@media(forced-colors: active){.search__filter__toggle.pb-yurui9{border:1px solid}@media(prefers-color-scheme: dark){.search__filter__toggle-img.pb-yurui9{filter:invert(1)}}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiU2VhcmNoRmlsdGVyVG9nZ2xlLnN2ZWx0ZSIsInNvdXJjZXMiOlsiU2VhcmNoRmlsdGVyVG9nZ2xlLnN2ZWx0ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8c2NyaXB0PlxuICBpbXBvcnQgeyBGVUxMX01PRFVMRV9QQVRIIH0gZnJvbSAnLi4vY29uc3RhbnRzJztcblxuICBjb25zdCB7IERydXBhbCB9ID0gd2luZG93O1xuXG4gIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBpbXBvcnQvcHJlZmVyLWRlZmF1bHQtZXhwb3J0XG4gIGV4cG9ydCBsZXQgaXNPcGVuO1xuXG4gIC8qIFdoZW4gdGhlIHVzZXIgY2xpY2tzIG9uIHRoZSBidXR0b24sXG4gdG9nZ2xlIGJldHdlZW4gaGlkaW5nIGFuZCBzaG93aW5nIHRoZSBkcm9wZG93biBjb250ZW50ICovXG4gIGZ1bmN0aW9uIG9wZW5Ecm9wZG93bigpIHtcbiAgICBpc09wZW4gPSAhaXNPcGVuO1xuICB9XG48L3NjcmlwdD5cblxuPGRpdiBjbGFzcz1cInNlYXJjaF9fZmlsdGVyX190b2dnbGUtY29udGFpbmVyXCI+XG4gIDxzZWN0aW9uIGFyaWEtbGFiZWw9e0RydXBhbC50KCdGaWx0ZXIgc2V0dGluZ3MnKX0+XG4gICAgPGJ1dHRvblxuICAgICAgdHlwZT1cImJ1dHRvblwiXG4gICAgICBjbGFzcz1cInNlYXJjaF9fZmlsdGVyX190b2dnbGUgZm9ybS1lbGVtZW50XCJcbiAgICAgIGNsYXNzOmlzX29wZW49e2lzT3Blbn1cbiAgICAgIGFyaWEtY29udHJvbHM9XCJmaWx0ZXItZHJvcGRvd25cIlxuICAgICAgYXJpYS1sYWJlbD17aXNPcGVuID8gRHJ1cGFsLnQoJ0Nsb3NlIEZpbHRlcicpIDogRHJ1cGFsLnQoJ09wZW4gRmlsdGVyJyl9XG4gICAgICBhcmlhLWV4cGFuZGVkPXtpc09wZW4udG9TdHJpbmcoKX1cbiAgICAgIG9uOmNsaWNrPXsoKSA9PiBvcGVuRHJvcGRvd24oKX1cbiAgICAgID48aW1nXG4gICAgICAgIHNyYz1cIntGVUxMX01PRFVMRV9QQVRIfS9pbWFnZXMvYWR2YW5jZWQtZmlsdGVyLWljb24uc3ZnXCJcbiAgICAgICAgYWx0PVwiYWR2YW5jZWQgZmlsdGVyIGljb25cIlxuICAgICAgICBjbGFzcz1cInNlYXJjaF9fZmlsdGVyX190b2dnbGUtaW1nXCJcbiAgICAgIC8+RmlsdGVyc1xuICAgIDwvYnV0dG9uPlxuICA8L3NlY3Rpb24+XG48L2Rpdj5cblxuPHN0eWxlPlxuICAuc2VhcmNoX19maWx0ZXJfX3RvZ2dsZS1jb250YWluZXIge1xuICAgIGRpc3BsYXk6IGZsZXg7XG4gICAgbWFyZ2luLWlubGluZS1lbmQ6IDFlbTtcbiAgfVxuXG4gIC5zZWFyY2hfX2ZpbHRlcl9fdG9nZ2xlIHtcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjZDNkNGQ5O1xuICAgIGNvbG9yOiBibGFjaztcbiAgICBwYWRkaW5nLWxlZnQ6IDE2cHg7XG4gICAgcGFkZGluZy1yaWdodDogMTZweDtcbiAgICBmb250LXNpemU6IDE2cHg7XG4gICAgYm9yZGVyLXJhZGl1czogMnB4O1xuICAgIHdpZHRoOiBmaXQtY29udGVudDtcbiAgICBtYXJnaW4taW5saW5lLXN0YXJ0OiAxNXB4O1xuICAgIHRleHQtZGVjb3JhdGlvbjogdW5kZXJsaW5lO1xuICB9XG4gIC5zZWFyY2hfX2ZpbHRlcl9fdG9nZ2xlLmlzX29wZW4ge1xuICAgIGJhY2tncm91bmQtY29sb3I6ICNhZGFlYjM7XG4gIH1cbiAgLnNlYXJjaF9fZmlsdGVyX190b2dnbGU6aG92ZXIge1xuICAgIGN1cnNvcjogcG9pbnRlcjtcbiAgfVxuXG4gIC5zZWFyY2hfX2ZpbHRlcl9fdG9nZ2xlLWltZyB7XG4gICAgd2lkdGg6IDIwcHg7XG4gICAgaGVpZ2h0OiAxNHB4O1xuICAgIHBhZGRpbmc6IDAgMC4yNXJlbTtcbiAgICBtYXJnaW4tYm90dG9tOiAtMnB4O1xuICAgIG1hcmdpbi1pbmxpbmUtZW5kOiA0cHg7XG4gIH1cbiAgQG1lZGlhIChmb3JjZWQtY29sb3JzOiBhY3RpdmUpIHtcbiAgICAuc2VhcmNoX19maWx0ZXJfX3RvZ2dsZSB7XG4gICAgICBib3JkZXI6IDFweCBzb2xpZDtcbiAgICB9XG4gICAgQG1lZGlhIChwcmVmZXJzLWNvbG9yLXNjaGVtZTogZGFyaykge1xuICAgICAgLnNlYXJjaF9fZmlsdGVyX190b2dnbGUtaW1nIHtcbiAgICAgICAgZmlsdGVyOiBpbnZlcnQoMSk7XG4gICAgICB9XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQW1DRSwyQ0FBa0MsQ0FDaEMsT0FBTyxDQUFFLElBQUksQ0FDYixpQkFBaUIsQ0FBRSxHQUNyQixDQUVBLGlDQUF3QixDQUN0QixnQkFBZ0IsQ0FBRSxPQUFPLENBQ3pCLEtBQUssQ0FBRSxLQUFLLENBQ1osWUFBWSxDQUFFLElBQUksQ0FDbEIsYUFBYSxDQUFFLElBQUksQ0FDbkIsU0FBUyxDQUFFLElBQUksQ0FDZixhQUFhLENBQUUsR0FBRyxDQUNsQixLQUFLLENBQUUsV0FBVyxDQUNsQixtQkFBbUIsQ0FBRSxJQUFJLENBQ3pCLGVBQWUsQ0FBRSxTQUNuQixDQUNBLHVCQUF1QixrQkFBUyxDQUM5QixnQkFBZ0IsQ0FBRSxPQUNwQixDQUNBLGlDQUF1QixNQUFPLENBQzVCLE1BQU0sQ0FBRSxPQUNWLENBRUEscUNBQTRCLENBQzFCLEtBQUssQ0FBRSxJQUFJLENBQ1gsTUFBTSxDQUFFLElBQUksQ0FDWixPQUFPLENBQUUsQ0FBQyxDQUFDLE9BQU8sQ0FDbEIsYUFBYSxDQUFFLElBQUksQ0FDbkIsaUJBQWlCLENBQUUsR0FDckIsQ0FDQSxNQUFPLGdCQUFnQixNQUFNLENBQUUsQ0FDN0IsaUNBQXdCLENBQ3RCLE1BQU0sQ0FBRSxHQUFHLENBQUMsS0FDZCxDQUNBLE1BQU8sdUJBQXVCLElBQUksQ0FBRSxDQUNsQyxxQ0FBNEIsQ0FDMUIsTUFBTSxDQUFFLE9BQU8sQ0FBQyxDQUNsQixDQUNGLENBQ0YifQ== */");
}

function create_fragment(ctx) {
	let div;
	let section;
	let button;
	let img;
	let img_src_value;
	let t;
	let button_aria_label_value;
	let button_aria_expanded_value;
	let section_aria_label_value;
	let mounted;
	let dispose;

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			section = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("section");
			button = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("button");
			img = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("img");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)("Filters");
			if (!(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.src_url_equal)(img.src, img_src_value = "" + (_constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH + "/images/advanced-filter-icon.svg"))) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "src", img_src_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "alt", "advanced filter icon");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(img, "class", "search__filter__toggle-img pb-yurui9");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(img, file, 25, 7, 750);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "type", "button");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "class", "search__filter__toggle form-element pb-yurui9");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "aria-controls", "filter-dropdown");

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "aria-label", button_aria_label_value = /*isOpen*/ ctx[0]
			? /*Drupal*/ ctx[1].t('Close Filter')
			: /*Drupal*/ ctx[1].t('Open Filter'));

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "aria-expanded", button_aria_expanded_value = /*isOpen*/ ctx[0].toString());
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "is_open", /*isOpen*/ ctx[0]);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(button, file, 17, 4, 441);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(section, "aria-label", section_aria_label_value = /*Drupal*/ ctx[1].t('Filter settings'));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(section, file, 16, 2, 386);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "search__filter__toggle-container pb-yurui9");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 15, 0, 337);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, section);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(section, button);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, img);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(button, t);

			if (!mounted) {
				dispose = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(button, "click", /*click_handler*/ ctx[3], false, false, false, false);
				mounted = true;
			}
		},
		p: function update(ctx, [dirty]) {
			if (dirty & /*isOpen*/ 1 && button_aria_label_value !== (button_aria_label_value = /*isOpen*/ ctx[0]
			? /*Drupal*/ ctx[1].t('Close Filter')
			: /*Drupal*/ ctx[1].t('Open Filter'))) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "aria-label", button_aria_label_value);
			}

			if (dirty & /*isOpen*/ 1 && button_aria_expanded_value !== (button_aria_expanded_value = /*isOpen*/ ctx[0].toString())) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(button, "aria-expanded", button_aria_expanded_value);
			}

			if (dirty & /*isOpen*/ 1) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.toggle_class)(button, "is_open", /*isOpen*/ ctx[0]);
			}
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			mounted = false;
			dispose();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('SearchFilterToggle', slots, []);
	const { Drupal } = window;
	let { isOpen } = $$props;

	/* When the user clicks on the button,
 toggle between hiding and showing the dropdown content */
	function openDropdown() {
		$$invalidate(0, isOpen = !isOpen);
	}

	$$self.$$.on_mount.push(function () {
		if (isOpen === undefined && !('isOpen' in $$props || $$self.$$.bound[$$self.$$.props['isOpen']])) {
			console.warn("<SearchFilterToggle> was created without expected prop 'isOpen'");
		}
	});

	const writable_props = ['isOpen'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SearchFilterToggle> was created with unknown prop '${key}'`);
	});

	const click_handler = () => openDropdown();

	$$self.$$set = $$props => {
		if ('isOpen' in $$props) $$invalidate(0, isOpen = $$props.isOpen);
	};

	$$self.$capture_state = () => ({
		FULL_MODULE_PATH: _constants__WEBPACK_IMPORTED_MODULE_1__.FULL_MODULE_PATH,
		Drupal,
		isOpen,
		openDropdown
	});

	$$self.$inject_state = $$props => {
		if ('isOpen' in $$props) $$invalidate(0, isOpen = $$props.isOpen);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [isOpen, Drupal, openDropdown, click_handler];
}

class SearchFilterToggle extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { isOpen: 0 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "SearchFilterToggle",
			options,
			id: create_fragment.name
		});
	}

	get isOpen() {
		throw new Error("<SearchFilterToggle>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set isOpen(value) {
		throw new Error("<SearchFilterToggle>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SearchFilterToggle);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Search/SearchFilters.svelte":
/*!**************************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Search/SearchFilters.svelte ***!
  \**************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte_transition__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte/transition */ "./node_modules/svelte/transition/index.mjs");
/* harmony import */ var _FilterGroup_svelte__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FilterGroup.svelte */ "./modules/project_browser/sveltejs/src/Search/FilterGroup.svelte");
/* harmony import */ var _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../Project/ProjectIcon.svelte */ "./modules/project_browser/sveltejs/src/Project/ProjectIcon.svelte");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../constants */ "./modules/project_browser/sveltejs/src/constants.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Search/SearchFilters.svelte generated by Svelte v3.56.0 */








const file = "modules/project_browser/sveltejs/src/Search/SearchFilters.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1j99bzb", "@media only screen and (min-width: 1200px){.small-icons.pb-1j99bzb{margin-top:-3px;margin-inline-end:1px}.search__checkbox-label.pb-1j99bzb{font-weight:normal;padding-inline-start:35px;display:flex}.search__checkbox-label.pb-1j99bzb{font-weight:normal;padding-inline-start:35px;display:flex;margin-right:unset}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiU2VhcmNoRmlsdGVycy5zdmVsdGUiLCJzb3VyY2VzIjpbIlNlYXJjaEZpbHRlcnMuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIGltcG9ydCB7IHNsaWRlIH0gZnJvbSAnc3ZlbHRlL3RyYW5zaXRpb24nO1xuICBpbXBvcnQgRmlsdGVyR3JvdXAgZnJvbSAnLi9GaWx0ZXJHcm91cC5zdmVsdGUnO1xuICBpbXBvcnQgUHJvamVjdEljb24gZnJvbSAnLi4vUHJvamVjdC9Qcm9qZWN0SWNvbi5zdmVsdGUnO1xuICBpbXBvcnQge1xuICAgIENPVkVSRURfSUQsXG4gICAgTUFJTlRFTkFOQ0VfT1BUSU9OUyxcbiAgICBERVZFTE9QTUVOVF9PUFRJT05TLFxuICAgIFNFQ1VSSVRZX09QVElPTlMsXG4gIH0gZnJvbSAnLi4vY29uc3RhbnRzJztcblxuICBjb25zdCB7IERydXBhbCB9ID0gd2luZG93O1xuXG4gIGV4cG9ydCBsZXQgb25BZHZhbmNlZEZpbHRlcjtcbiAgZXhwb3J0IGxldCBpc09wZW47XG48L3NjcmlwdD5cblxueyNpZiBpc09wZW59XG4gIDxkaXYgY2xhc3M9XCJzZWFyY2hfX2ZpbHRlcnNcIiB0cmFuc2l0aW9uOnNsaWRlPlxuICAgIDxGaWx0ZXJHcm91cFxuICAgICAgZmlsdGVyVGl0bGU9e0RydXBhbC50KCdEZXZlbG9wbWVudCBTdGF0dXMnKX1cbiAgICAgIGZpbHRlckRhdGE9e0RFVkVMT1BNRU5UX09QVElPTlN9XG4gICAgICBmaWx0ZXJUeXBlPVwiZGV2ZWxvcG1lbnRTdGF0dXNcIlxuICAgICAgY2hhbmdlSGFuZGxlcj17b25BZHZhbmNlZEZpbHRlcn1cbiAgICAgIGxldDppZFxuICAgICAgbGV0OmxhYmVsXG4gICAgPlxuICAgICAgPGxhYmVsXG4gICAgICAgIHNsb3Q9XCJsYWJlbFwiXG4gICAgICAgIGNsYXNzPVwic2VhcmNoX19jaGVja2JveC1sYWJlbFwiXG4gICAgICAgIGZvcj17YGRldmVsb3BtZW50U3RhdHVzJHtpZH1gfVxuICAgICAgPlxuICAgICAgICB7bGFiZWx9XG4gICAgICA8L2xhYmVsPlxuICAgIDwvRmlsdGVyR3JvdXA+XG4gICAgPEZpbHRlckdyb3VwXG4gICAgICBmaWx0ZXJUaXRsZT17RHJ1cGFsLnQoJ01haW50ZW5hbmNlIFN0YXR1cycpfVxuICAgICAgZmlsdGVyRGF0YT17TUFJTlRFTkFOQ0VfT1BUSU9OU31cbiAgICAgIGZpbHRlclR5cGU9XCJtYWludGVuYW5jZVN0YXR1c1wiXG4gICAgICBjaGFuZ2VIYW5kbGVyPXtvbkFkdmFuY2VkRmlsdGVyfVxuICAgICAgbGV0OmlkXG4gICAgICBsZXQ6bGFiZWxcbiAgICA+XG4gICAgICA8bGFiZWxcbiAgICAgICAgc2xvdD1cImxhYmVsXCJcbiAgICAgICAgY2xhc3M9XCJzZWFyY2hfX2NoZWNrYm94LWxhYmVsXCJcbiAgICAgICAgZm9yPXtgbWFpbnRlbmFuY2VTdGF0dXMke2lkfWB9XG4gICAgICA+XG4gICAgICAgIHtsYWJlbH1cbiAgICAgIDwvbGFiZWw+XG4gICAgPC9GaWx0ZXJHcm91cD5cbiAgICA8RmlsdGVyR3JvdXBcbiAgICAgIGZpbHRlclRpdGxlPXtEcnVwYWwudCgnU2VjdXJpdHkgQWR2aXNvcnkgQ292ZXJhZ2UnKX1cbiAgICAgIGZpbHRlckRhdGE9e1NFQ1VSSVRZX09QVElPTlN9XG4gICAgICBmaWx0ZXJUeXBlPVwic2VjdXJpdHlDb3ZlcmFnZVwiXG4gICAgICBjaGFuZ2VIYW5kbGVyPXtvbkFkdmFuY2VkRmlsdGVyfVxuICAgICAgbGV0OmlkXG4gICAgICBsZXQ6bGFiZWxcbiAgICA+XG4gICAgICA8bGFiZWxcbiAgICAgICAgc2xvdD1cImxhYmVsXCJcbiAgICAgICAgY2xhc3M9XCJzZWFyY2hfX2NoZWNrYm94LWxhYmVsXCJcbiAgICAgICAgZm9yPXtgc2VjdXJpdHlDb3ZlcmFnZSR7aWR9YH1cbiAgICAgID5cbiAgICAgICAge2xhYmVsfVxuICAgICAgICB7I2lmIGlkID09PSBDT1ZFUkVEX0lEfVxuICAgICAgICAgIDxzcGFuIGNsYXNzPVwic21hbGwtaWNvbnNcIj5cbiAgICAgICAgICAgIDxQcm9qZWN0SWNvbiB0eXBlPVwic3RhdHVzXCIgLz5cbiAgICAgICAgICA8L3NwYW4+XG4gICAgICAgIHsvaWZ9XG4gICAgICA8L2xhYmVsPlxuICAgIDwvRmlsdGVyR3JvdXA+XG4gIDwvZGl2Plxuey9pZn1cblxuPHN0eWxlPlxuICBAbWVkaWEgb25seSBzY3JlZW4gYW5kIChtaW4td2lkdGg6IDEyMDBweCkge1xuICAgIC5zbWFsbC1pY29ucyB7XG4gICAgICBtYXJnaW4tdG9wOiAtM3B4O1xuICAgICAgbWFyZ2luLWlubGluZS1lbmQ6IDFweDtcbiAgICB9XG4gICAgLnNlYXJjaF9fY2hlY2tib3gtbGFiZWwge1xuICAgICAgZm9udC13ZWlnaHQ6IG5vcm1hbDtcbiAgICAgIHBhZGRpbmctaW5saW5lLXN0YXJ0OiAzNXB4O1xuICAgICAgZGlzcGxheTogZmxleDtcbiAgICB9XG4gICAgLnNlYXJjaF9fY2hlY2tib3gtbGFiZWwge1xuICAgICAgZm9udC13ZWlnaHQ6IG5vcm1hbDtcbiAgICAgIHBhZGRpbmctaW5saW5lLXN0YXJ0OiAzNXB4O1xuICAgICAgZGlzcGxheTogZmxleDtcbiAgICAgIG1hcmdpbi1yaWdodDogdW5zZXQ7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQTRFRSxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLFlBQVksTUFBTSxDQUFFLENBQ3pDLHVCQUFhLENBQ1gsVUFBVSxDQUFFLElBQUksQ0FDaEIsaUJBQWlCLENBQUUsR0FDckIsQ0FDQSxrQ0FBd0IsQ0FDdEIsV0FBVyxDQUFFLE1BQU0sQ0FDbkIsb0JBQW9CLENBQUUsSUFBSSxDQUMxQixPQUFPLENBQUUsSUFDWCxDQUNBLGtDQUF3QixDQUN0QixXQUFXLENBQUUsTUFBTSxDQUNuQixvQkFBb0IsQ0FBRSxJQUFJLENBQzFCLE9BQU8sQ0FBRSxJQUFJLENBQ2IsWUFBWSxDQUFFLEtBQ2hCLENBQ0YifQ== */");
}

// (18:0) {#if isOpen}
function create_if_block(ctx) {
	let div;
	let filtergroup0;
	let t0;
	let filtergroup1;
	let t1;
	let filtergroup2;
	let div_transition;
	let current;

	filtergroup0 = new _FilterGroup_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				filterTitle: /*Drupal*/ ctx[2].t('Development Status'),
				filterData: _constants__WEBPACK_IMPORTED_MODULE_4__.DEVELOPMENT_OPTIONS,
				filterType: "developmentStatus",
				changeHandler: /*onAdvancedFilter*/ ctx[0],
				$$slots: {
					label: [
						create_label_slot_2,
						({ id, label }) => ({ 3: id, 4: label }),
						({ id, label }) => (id ? 8 : 0) | (label ? 16 : 0)
					]
				},
				$$scope: { ctx }
			},
			$$inline: true
		});

	filtergroup1 = new _FilterGroup_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				filterTitle: /*Drupal*/ ctx[2].t('Maintenance Status'),
				filterData: _constants__WEBPACK_IMPORTED_MODULE_4__.MAINTENANCE_OPTIONS,
				filterType: "maintenanceStatus",
				changeHandler: /*onAdvancedFilter*/ ctx[0],
				$$slots: {
					label: [
						create_label_slot_1,
						({ id, label }) => ({ 3: id, 4: label }),
						({ id, label }) => (id ? 8 : 0) | (label ? 16 : 0)
					]
				},
				$$scope: { ctx }
			},
			$$inline: true
		});

	filtergroup2 = new _FilterGroup_svelte__WEBPACK_IMPORTED_MODULE_2__["default"]({
			props: {
				filterTitle: /*Drupal*/ ctx[2].t('Security Advisory Coverage'),
				filterData: _constants__WEBPACK_IMPORTED_MODULE_4__.SECURITY_OPTIONS,
				filterType: "securityCoverage",
				changeHandler: /*onAdvancedFilter*/ ctx[0],
				$$slots: {
					label: [
						create_label_slot,
						({ id, label }) => ({ 3: id, 4: label }),
						({ id, label }) => (id ? 8 : 0) | (label ? 16 : 0)
					]
				},
				$$scope: { ctx }
			},
			$$inline: true
		});

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(filtergroup0.$$.fragment);
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(filtergroup1.$$.fragment);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(filtergroup2.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "search__filters");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 18, 2, 396);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(filtergroup0, div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(filtergroup1, div, null);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(filtergroup2, div, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			const filtergroup0_changes = {};
			if (dirty & /*onAdvancedFilter*/ 1) filtergroup0_changes.changeHandler = /*onAdvancedFilter*/ ctx[0];

			if (dirty & /*$$scope, id, label*/ 56) {
				filtergroup0_changes.$$scope = { dirty, ctx };
			}

			filtergroup0.$set(filtergroup0_changes);
			const filtergroup1_changes = {};
			if (dirty & /*onAdvancedFilter*/ 1) filtergroup1_changes.changeHandler = /*onAdvancedFilter*/ ctx[0];

			if (dirty & /*$$scope, id, label*/ 56) {
				filtergroup1_changes.$$scope = { dirty, ctx };
			}

			filtergroup1.$set(filtergroup1_changes);
			const filtergroup2_changes = {};
			if (dirty & /*onAdvancedFilter*/ 1) filtergroup2_changes.changeHandler = /*onAdvancedFilter*/ ctx[0];

			if (dirty & /*$$scope, id, label*/ 56) {
				filtergroup2_changes.$$scope = { dirty, ctx };
			}

			filtergroup2.$set(filtergroup2_changes);
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(filtergroup0.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(filtergroup1.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(filtergroup2.$$.fragment, local);

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_render_callback)(() => {
				if (!div_transition) div_transition = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_bidirectional_transition)(div, svelte_transition__WEBPACK_IMPORTED_MODULE_1__.slide, {}, true);
				div_transition.run(1);
			});

			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(filtergroup0.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(filtergroup1.$$.fragment, local);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(filtergroup2.$$.fragment, local);
			if (!div_transition) div_transition = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_bidirectional_transition)(div, svelte_transition__WEBPACK_IMPORTED_MODULE_1__.slide, {}, false);
			div_transition.run(0);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(filtergroup0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(filtergroup1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(filtergroup2);
			if (detaching && div_transition) div_transition.end();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block.name,
		type: "if",
		source: "(18:0) {#if isOpen}",
		ctx
	});

	return block;
}

// (28:6) 
function create_label_slot_2(ctx) {
	let label;
	let t_value = /*label*/ ctx[4] + "";
	let t;
	let label_for_value;

	const block = {
		c: function create() {
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "slot", "label");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "class", "search__checkbox-label pb-1j99bzb");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value = `developmentStatus${/*id*/ ctx[3]}`);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 27, 6, 667);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, label, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(label, t);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*label*/ 16 && t_value !== (t_value = /*label*/ ctx[4] + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);

			if (dirty & /*id*/ 8 && label_for_value !== (label_for_value = `developmentStatus${/*id*/ ctx[3]}`)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value);
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(label);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_label_slot_2.name,
		type: "slot",
		source: "(28:6) ",
		ctx
	});

	return block;
}

// (44:6) 
function create_label_slot_1(ctx) {
	let label;
	let t_value = /*label*/ ctx[4] + "";
	let t;
	let label_for_value;

	const block = {
		c: function create() {
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			t = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t_value);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "slot", "label");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "class", "search__checkbox-label pb-1j99bzb");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value = `maintenanceStatus${/*id*/ ctx[3]}`);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 43, 6, 1055);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, label, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(label, t);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*label*/ 16 && t_value !== (t_value = /*label*/ ctx[4] + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t, t_value);

			if (dirty & /*id*/ 8 && label_for_value !== (label_for_value = `maintenanceStatus${/*id*/ ctx[3]}`)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value);
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(label);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_label_slot_1.name,
		type: "slot",
		source: "(44:6) ",
		ctx
	});

	return block;
}

// (66:8) {#if id === COVERED_ID}
function create_if_block_1(ctx) {
	let span;
	let projecticon;
	let current;

	projecticon = new _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_3__["default"]({
			props: { type: "status" },
			$$inline: true
		});

	const block = {
		c: function create() {
			span = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("span");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.create_component)(projecticon.$$.fragment);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(span, "class", "small-icons pb-1j99bzb");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(span, file, 66, 10, 1618);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, span, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.mount_component)(projecticon, span, null);
			current = true;
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(projecticon.$$.fragment, local);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(projecticon.$$.fragment, local);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(span);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_component)(projecticon);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_if_block_1.name,
		type: "if",
		source: "(66:8) {#if id === COVERED_ID}",
		ctx
	});

	return block;
}

// (60:6) 
function create_label_slot(ctx) {
	let label;
	let t0_value = /*label*/ ctx[4] + "";
	let t0;
	let t1;
	let label_for_value;
	let current;
	let if_block = /*id*/ ctx[3] === _constants__WEBPACK_IMPORTED_MODULE_4__.COVERED_ID && create_if_block_1(ctx);

	const block = {
		c: function create() {
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			if (if_block) if_block.c();
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "slot", "label");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "class", "search__checkbox-label pb-1j99bzb");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value = `securityCoverage${/*id*/ ctx[3]}`);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 59, 6, 1447);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, label, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(label, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(label, t1);
			if (if_block) if_block.m(label, null);
			current = true;
		},
		p: function update(ctx, dirty) {
			if ((!current || dirty & /*label*/ 16) && t0_value !== (t0_value = /*label*/ ctx[4] + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, t0_value);

			if (/*id*/ ctx[3] === _constants__WEBPACK_IMPORTED_MODULE_4__.COVERED_ID) {
				if (if_block) {
					if (dirty & /*id*/ 8) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					}
				} else {
					if_block = create_if_block_1(ctx);
					if_block.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					if_block.m(label, null);
				}
			} else if (if_block) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block, 1, 1, () => {
					if_block = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}

			if (!current || dirty & /*id*/ 8 && label_for_value !== (label_for_value = `securityCoverage${/*id*/ ctx[3]}`)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", label_for_value);
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(label);
			if (if_block) if_block.d();
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_label_slot.name,
		type: "slot",
		source: "(60:6) ",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let if_block_anchor;
	let current;
	let if_block = /*isOpen*/ ctx[1] && create_if_block(ctx);

	const block = {
		c: function create() {
			if (if_block) if_block.c();
			if_block_anchor = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.empty)();
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			if (if_block) if_block.m(target, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, if_block_anchor, anchor);
			current = true;
		},
		p: function update(ctx, [dirty]) {
			if (/*isOpen*/ ctx[1]) {
				if (if_block) {
					if_block.p(ctx, dirty);

					if (dirty & /*isOpen*/ 2) {
						(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					}
				} else {
					if_block = create_if_block(ctx);
					if_block.c();
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block, 1);
					if_block.m(if_block_anchor.parentNode, if_block_anchor);
				}
			} else if (if_block) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.group_outros)();

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block, 1, 1, () => {
					if_block = null;
				});

				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.check_outros)();
			}
		},
		i: function intro(local) {
			if (current) return;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_in)(if_block);
			current = true;
		},
		o: function outro(local) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.transition_out)(if_block);
			current = false;
		},
		d: function destroy(detaching) {
			if (if_block) if_block.d(detaching);
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(if_block_anchor);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('SearchFilters', slots, []);
	const { Drupal } = window;
	let { onAdvancedFilter } = $$props;
	let { isOpen } = $$props;

	$$self.$$.on_mount.push(function () {
		if (onAdvancedFilter === undefined && !('onAdvancedFilter' in $$props || $$self.$$.bound[$$self.$$.props['onAdvancedFilter']])) {
			console.warn("<SearchFilters> was created without expected prop 'onAdvancedFilter'");
		}

		if (isOpen === undefined && !('isOpen' in $$props || $$self.$$.bound[$$self.$$.props['isOpen']])) {
			console.warn("<SearchFilters> was created without expected prop 'isOpen'");
		}
	});

	const writable_props = ['onAdvancedFilter', 'isOpen'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SearchFilters> was created with unknown prop '${key}'`);
	});

	$$self.$$set = $$props => {
		if ('onAdvancedFilter' in $$props) $$invalidate(0, onAdvancedFilter = $$props.onAdvancedFilter);
		if ('isOpen' in $$props) $$invalidate(1, isOpen = $$props.isOpen);
	};

	$$self.$capture_state = () => ({
		slide: svelte_transition__WEBPACK_IMPORTED_MODULE_1__.slide,
		FilterGroup: _FilterGroup_svelte__WEBPACK_IMPORTED_MODULE_2__["default"],
		ProjectIcon: _Project_ProjectIcon_svelte__WEBPACK_IMPORTED_MODULE_3__["default"],
		COVERED_ID: _constants__WEBPACK_IMPORTED_MODULE_4__.COVERED_ID,
		MAINTENANCE_OPTIONS: _constants__WEBPACK_IMPORTED_MODULE_4__.MAINTENANCE_OPTIONS,
		DEVELOPMENT_OPTIONS: _constants__WEBPACK_IMPORTED_MODULE_4__.DEVELOPMENT_OPTIONS,
		SECURITY_OPTIONS: _constants__WEBPACK_IMPORTED_MODULE_4__.SECURITY_OPTIONS,
		Drupal,
		onAdvancedFilter,
		isOpen
	});

	$$self.$inject_state = $$props => {
		if ('onAdvancedFilter' in $$props) $$invalidate(0, onAdvancedFilter = $$props.onAdvancedFilter);
		if ('isOpen' in $$props) $$invalidate(1, isOpen = $$props.isOpen);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [onAdvancedFilter, isOpen, Drupal];
}

class SearchFilters extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { onAdvancedFilter: 0, isOpen: 1 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "SearchFilters",
			options,
			id: create_fragment.name
		});
	}

	get onAdvancedFilter() {
		throw new Error("<SearchFilters>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set onAdvancedFilter(value) {
		throw new Error("<SearchFilters>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get isOpen() {
		throw new Error("<SearchFilters>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set isOpen(value) {
		throw new Error("<SearchFilters>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SearchFilters);



/***/ }),

/***/ "./modules/project_browser/sveltejs/src/Search/SearchSort.svelte":
/*!***********************************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/Search/SearchSort.svelte ***!
  \***********************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var svelte_internal__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/internal */ "./node_modules/svelte/internal/index.mjs");
/* harmony import */ var svelte__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! svelte */ "./node_modules/svelte/index.mjs");
/* harmony import */ var _stores__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../stores */ "./modules/project_browser/sveltejs/src/stores.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_loader_lib_hot_api_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/svelte-loader/lib/hot-api.js */ "./node_modules/svelte-loader/lib/hot-api.js");
/* harmony import */ var _Users_ben_mullins_Sites_d10_core_node_modules_svelte_hmr_runtime_proxy_adapter_dom_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js */ "./node_modules/svelte-hmr/runtime/proxy-adapter-dom.js");
/* module decorator */ module = __webpack_require__.hmd(module);
/* modules/project_browser/sveltejs/src/Search/SearchSort.svelte generated by Svelte v3.56.0 */




const file = "modules/project_browser/sveltejs/src/Search/SearchSort.svelte";

function add_css(target) {
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_styles)(target, "pb-1vq7i0t", ".search__sort.pb-1vq7i0t{z-index:2}.search__sort-select.pb-1vq7i0t{border:none;background-color:#d3d4d9}.search__sort-select.pb-1vq7i0t:hover{cursor:pointer}@media screen and (max-width: 855px){.search__sort.pb-1vq7i0t{margin-bottom:10px;margin-top:10px}}@media(forced-colors: active){#pb-sort.pb-1vq7i0t{border:1px solid}}\n/*# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiU2VhcmNoU29ydC5zdmVsdGUiLCJzb3VyY2VzIjpbIlNlYXJjaFNvcnQuc3ZlbHRlIl0sInNvdXJjZXNDb250ZW50IjpbIjxzY3JpcHQ+XG4gIGltcG9ydCB7IGNyZWF0ZUV2ZW50RGlzcGF0Y2hlciwgZ2V0Q29udGV4dCB9IGZyb20gJ3N2ZWx0ZSc7XG4gIGltcG9ydCB7IHNvcnQsIHNvcnRDcml0ZXJpYSB9IGZyb20gJy4uL3N0b3Jlcyc7XG5cbiAgZXhwb3J0IGxldCBzb3J0VGV4dDtcbiAgZXhwb3J0IGxldCByZWZyZXNoO1xuXG4gIGNvbnN0IHsgRHJ1cGFsIH0gPSB3aW5kb3c7XG4gIGNvbnN0IGRpc3BhdGNoID0gY3JlYXRlRXZlbnREaXNwYXRjaGVyKCk7XG4gIGNvbnN0IHN0YXRlQ29udGV4dCA9IGdldENvbnRleHQoJ3N0YXRlJyk7XG5cbiAgYXN5bmMgZnVuY3Rpb24gb25Tb3J0KGV2ZW50KSB7XG4gICAgY29uc3Qgc3RhdGUgPSBzdGF0ZUNvbnRleHQuZ2V0U3RhdGUoKTtcbiAgICBjb25zdCBkZXRhaWwgPSB7XG4gICAgICBvcmlnaW5hbEV2ZW50OiBldmVudCxcbiAgICAgIHBhZ2U6IHN0YXRlLnBhZ2UsXG4gICAgICBwYWdlSW5kZXg6IHN0YXRlLnBhZ2VJbmRleCxcbiAgICAgIHBhZ2VTaXplOiBzdGF0ZS5wYWdlU2l6ZSxcbiAgICAgIHJvd3M6IHN0YXRlLmZpbHRlcmVkUm93cyxcbiAgICAgIHNvcnQ6ICRzb3J0LFxuICAgIH07XG5cbiAgICBkaXNwYXRjaCgnc29ydCcsIGRldGFpbCk7XG4gICAgc3RhdGVDb250ZXh0LnNldFBhZ2UoMCwgMCk7XG4gICAgc3RhdGVDb250ZXh0LnNldFJvd3MoZGV0YWlsLnJvd3MpO1xuICAgIHNvcnRUZXh0ID0gJHNvcnRDcml0ZXJpYS5maW5kKChvcHRpb24pID0+IG9wdGlvbi5pZCA9PT0gJHNvcnQpLnRleHQ7XG4gICAgcmVmcmVzaCgpO1xuICB9XG48L3NjcmlwdD5cblxuPGRpdiBjbGFzcz1cInNlYXJjaF9fc29ydFwiPlxuICA8bGFiZWwgZm9yPVwicGItc29ydFwiPntEcnVwYWwudCgnU29ydCBieTonKX08L2xhYmVsPlxuICA8c2VsZWN0XG4gICAgbmFtZT1cInBiLXNvcnRcIlxuICAgIGlkPVwicGItc29ydFwiXG4gICAgYmluZDp2YWx1ZT17JHNvcnR9XG4gICAgb246Y2hhbmdlPXtvblNvcnR9XG4gICAgY2xhc3M9XCJzZWFyY2hfX3NvcnQtc2VsZWN0IGZvcm0tc2VsZWN0IGZvcm0tZWxlbWVudCBmb3JtLWVsZW1lbnQtLXR5cGUtc2VsZWN0XCJcbiAgPlxuICAgIHsjZWFjaCAkc29ydENyaXRlcmlhIGFzIG9wdH1cbiAgICAgIDxvcHRpb24gdmFsdWU9e29wdC5pZH0+XG4gICAgICAgIHtvcHQudGV4dH1cbiAgICAgIDwvb3B0aW9uPlxuICAgIHsvZWFjaH1cbiAgPC9zZWxlY3Q+XG48L2Rpdj5cblxuPHN0eWxlPlxuICAuc2VhcmNoX19zb3J0IHtcbiAgICB6LWluZGV4OiAyO1xuICB9XG5cbiAgLnNlYXJjaF9fc29ydC1zZWxlY3Qge1xuICAgIGJvcmRlcjogbm9uZTtcbiAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjZDNkNGQ5O1xuICB9XG4gIC5zZWFyY2hfX3NvcnQtc2VsZWN0OmhvdmVyIHtcbiAgICBjdXJzb3I6IHBvaW50ZXI7XG4gIH1cblxuICBAbWVkaWEgc2NyZWVuIGFuZCAobWF4LXdpZHRoOiA4NTVweCkge1xuICAgIC5zZWFyY2hfX3NvcnQge1xuICAgICAgbWFyZ2luLWJvdHRvbTogMTBweDtcbiAgICAgIG1hcmdpbi10b3A6IDEwcHg7XG4gICAgfVxuICB9XG4gIEBtZWRpYSAoZm9yY2VkLWNvbG9yczogYWN0aXZlKSB7XG4gICAgI3BiLXNvcnQge1xuICAgICAgYm9yZGVyOiAxcHggc29saWQ7XG4gICAgfVxuICB9XG48L3N0eWxlPlxuIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQWdERSx3QkFBYyxDQUNaLE9BQU8sQ0FBRSxDQUNYLENBRUEsK0JBQXFCLENBQ25CLE1BQU0sQ0FBRSxJQUFJLENBQ1osZ0JBQWdCLENBQUUsT0FDcEIsQ0FDQSwrQkFBb0IsTUFBTyxDQUN6QixNQUFNLENBQUUsT0FDVixDQUVBLE9BQU8sTUFBTSxDQUFDLEdBQUcsQ0FBQyxZQUFZLEtBQUssQ0FBRSxDQUNuQyx3QkFBYyxDQUNaLGFBQWEsQ0FBRSxJQUFJLENBQ25CLFVBQVUsQ0FBRSxJQUNkLENBQ0YsQ0FDQSxNQUFPLGdCQUFnQixNQUFNLENBQUUsQ0FDN0IsbUJBQVMsQ0FDUCxNQUFNLENBQUUsR0FBRyxDQUFDLEtBQ2QsQ0FDRiJ9 */");
}

function get_each_context(ctx, list, i) {
	const child_ctx = ctx.slice();
	child_ctx[9] = list[i];
	return child_ctx;
}

// (40:4) {#each $sortCriteria as opt}
function create_each_block(ctx) {
	let option;
	let t0_value = /*opt*/ ctx[9].text + "";
	let t0;
	let t1;
	let option_value_value;

	const block = {
		c: function create() {
			option = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("option");
			t0 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.text)(t0_value);
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			option.__value = option_value_value = /*opt*/ ctx[9].id;
			option.value = option.__value;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(option, file, 40, 6, 1063);
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, option, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(option, t0);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(option, t1);
		},
		p: function update(ctx, dirty) {
			if (dirty & /*$sortCriteria*/ 2 && t0_value !== (t0_value = /*opt*/ ctx[9].text + "")) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.set_data_dev)(t0, t0_value);

			if (dirty & /*$sortCriteria*/ 2 && option_value_value !== (option_value_value = /*opt*/ ctx[9].id)) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.prop_dev)(option, "__value", option_value_value);
				option.value = option.__value;
			}
		},
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(option);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_each_block.name,
		type: "each",
		source: "(40:4) {#each $sortCriteria as opt}",
		ctx
	});

	return block;
}

function create_fragment(ctx) {
	let div;
	let label;
	let t1;
	let select;
	let mounted;
	let dispose;
	let each_value = /*$sortCriteria*/ ctx[1];
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
	let each_blocks = [];

	for (let i = 0; i < each_value.length; i += 1) {
		each_blocks[i] = create_each_block(get_each_context(ctx, each_value, i));
	}

	const block = {
		c: function create() {
			div = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("div");
			label = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("label");
			label.textContent = `${/*Drupal*/ ctx[2].t('Sort by:')}`;
			t1 = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.space)();
			select = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.element)("select");

			for (let i = 0; i < each_blocks.length; i += 1) {
				each_blocks[i].c();
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(label, "for", "pb-sort");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(label, file, 31, 2, 793);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(select, "name", "pb-sort");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(select, "id", "pb-sort");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(select, "class", "search__sort-select form-select form-element form-element--type-select pb-1vq7i0t");
			if (/*$sort*/ ctx[0] === void 0) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_render_callback)(() => /*select_change_handler*/ ctx[6].call(select));
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(select, file, 32, 2, 847);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.attr_dev)(div, "class", "search__sort pb-1vq7i0t");
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.add_location)(div, file, 30, 0, 764);
		},
		l: function claim(nodes) {
			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
		},
		m: function mount(target, anchor) {
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.insert_dev)(target, div, anchor);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, label);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, t1);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.append_dev)(div, select);

			for (let i = 0; i < each_blocks.length; i += 1) {
				if (each_blocks[i]) {
					each_blocks[i].m(select, null);
				}
			}

			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.select_option)(select, /*$sort*/ ctx[0]);

			if (!mounted) {
				dispose = [
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(select, "change", /*select_change_handler*/ ctx[6]),
					(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.listen_dev)(select, "change", /*onSort*/ ctx[3], false, false, false, false)
				];

				mounted = true;
			}
		},
		p: function update(ctx, [dirty]) {
			if (dirty & /*$sortCriteria*/ 2) {
				each_value = /*$sortCriteria*/ ctx[1];
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_each_argument)(each_value);
				let i;

				for (i = 0; i < each_value.length; i += 1) {
					const child_ctx = get_each_context(ctx, each_value, i);

					if (each_blocks[i]) {
						each_blocks[i].p(child_ctx, dirty);
					} else {
						each_blocks[i] = create_each_block(child_ctx);
						each_blocks[i].c();
						each_blocks[i].m(select, null);
					}
				}

				for (; i < each_blocks.length; i += 1) {
					each_blocks[i].d(1);
				}

				each_blocks.length = each_value.length;
			}

			if (dirty & /*$sort, $sortCriteria*/ 3) {
				(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.select_option)(select, /*$sort*/ ctx[0]);
			}
		},
		i: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		o: svelte_internal__WEBPACK_IMPORTED_MODULE_0__.noop,
		d: function destroy(detaching) {
			if (detaching) (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.detach_dev)(div);
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.destroy_each)(each_blocks, detaching);
			mounted = false;
			(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.run_all)(dispose);
		}
	};

	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterBlock", {
		block,
		id: create_fragment.name,
		type: "component",
		source: "",
		ctx
	});

	return block;
}

function instance($$self, $$props, $$invalidate) {
	let $sort;
	let $sortCriteria;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_2__.sort, 'sort');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_2__.sort, $$value => $$invalidate(0, $sort = $$value));
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_store)(_stores__WEBPACK_IMPORTED_MODULE_2__.sortCriteria, 'sortCriteria');
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.component_subscribe)($$self, _stores__WEBPACK_IMPORTED_MODULE_2__.sortCriteria, $$value => $$invalidate(1, $sortCriteria = $$value));
	let { $$slots: slots = {}, $$scope } = $$props;
	(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.validate_slots)('SearchSort', slots, []);
	let { sortText } = $$props;
	let { refresh } = $$props;
	const { Drupal } = window;
	const dispatch = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher)();
	const stateContext = (0,svelte__WEBPACK_IMPORTED_MODULE_1__.getContext)('state');

	async function onSort(event) {
		const state = stateContext.getState();

		const detail = {
			originalEvent: event,
			page: state.page,
			pageIndex: state.pageIndex,
			pageSize: state.pageSize,
			rows: state.filteredRows,
			sort: $sort
		};

		dispatch('sort', detail);
		stateContext.setPage(0, 0);
		stateContext.setRows(detail.rows);
		$$invalidate(4, sortText = $sortCriteria.find(option => option.id === $sort).text);
		refresh();
	}

	$$self.$$.on_mount.push(function () {
		if (sortText === undefined && !('sortText' in $$props || $$self.$$.bound[$$self.$$.props['sortText']])) {
			console.warn("<SearchSort> was created without expected prop 'sortText'");
		}

		if (refresh === undefined && !('refresh' in $$props || $$self.$$.bound[$$self.$$.props['refresh']])) {
			console.warn("<SearchSort> was created without expected prop 'refresh'");
		}
	});

	const writable_props = ['sortText', 'refresh'];

	Object.keys($$props).forEach(key => {
		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SearchSort> was created with unknown prop '${key}'`);
	});

	function select_change_handler() {
		$sort = (0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.select_value)(this);
		_stores__WEBPACK_IMPORTED_MODULE_2__.sort.set($sort);
	}

	$$self.$$set = $$props => {
		if ('sortText' in $$props) $$invalidate(4, sortText = $$props.sortText);
		if ('refresh' in $$props) $$invalidate(5, refresh = $$props.refresh);
	};

	$$self.$capture_state = () => ({
		createEventDispatcher: svelte__WEBPACK_IMPORTED_MODULE_1__.createEventDispatcher,
		getContext: svelte__WEBPACK_IMPORTED_MODULE_1__.getContext,
		sort: _stores__WEBPACK_IMPORTED_MODULE_2__.sort,
		sortCriteria: _stores__WEBPACK_IMPORTED_MODULE_2__.sortCriteria,
		sortText,
		refresh,
		Drupal,
		dispatch,
		stateContext,
		onSort,
		$sort,
		$sortCriteria
	});

	$$self.$inject_state = $$props => {
		if ('sortText' in $$props) $$invalidate(4, sortText = $$props.sortText);
		if ('refresh' in $$props) $$invalidate(5, refresh = $$props.refresh);
	};

	if ($$props && "$$inject" in $$props) {
		$$self.$inject_state($$props.$$inject);
	}

	return [$sort, $sortCriteria, Drupal, onSort, sortText, refresh, select_change_handler];
}

class SearchSort extends svelte_internal__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev {
	constructor(options) {
		super(options);
		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.init)(this, options, instance, create_fragment, svelte_internal__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal, { sortText: 4, refresh: 5 }, add_css);

		(0,svelte_internal__WEBPACK_IMPORTED_MODULE_0__.dispatch_dev)("SvelteRegisterComponent", {
			component: this,
			tagName: "SearchSort",
			options,
			id: create_fragment.name
		});
	}

	get sortText() {
		throw new Error("<SearchSort>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set sortText(value) {
		throw new Error("<SearchSort>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	get refresh() {
		throw new Error("<SearchSort>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}

	set refresh(value) {
		throw new Error("<SearchSort>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
	}
}

if (module && module.hot) {}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SearchSort);



/***/ }),

/***/ "./node_modules/svelte-loader/lib/hot-api.js":
/*!***************************************************!*\
  !*** ./node_modules/svelte-loader/lib/hot-api.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "applyHmr": () => (/* binding */ applyHmr)
/* harmony export */ });
/* harmony import */ var svelte_hmr_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte-hmr/runtime */ "./node_modules/svelte-hmr/runtime/index.js");


// eslint-disable-next-line no-undef
const g = typeof window !== 'undefined' ? window : __webpack_require__.g;

const globalKey =
	typeof Symbol !== 'undefined'
		? Symbol('SVELTE_LOADER_HOT')
		: '__SVELTE_LOADER_HOT';

if (!g[globalKey]) {
	// do updating refs counting to know when a full update has been applied
	let updatingCount = 0;

	const notifyStart = () => {
		updatingCount++;
	};

	const notifyError = reload => err => {
		const errString = (err && err.stack) || err;
		// eslint-disable-next-line no-console
		console.error(
			'[HMR] Failed to accept update (nollup compat mode)',
			errString
		);
		reload();
		notifyEnd();
	};

	const notifyEnd = () => {
		updatingCount--;
		if (updatingCount === 0) {
			// NOTE this message is important for timing in tests
			// eslint-disable-next-line no-console
			console.log('[HMR:Svelte] Up to date');
		}
	};

	g[globalKey] = {
		hotStates: {},
		notifyStart,
		notifyError,
		notifyEnd,
	};
}

const runAcceptHandlers = acceptHandlers => {
	const queue = [...acceptHandlers];
	const next = () => {
		const cur = queue.shift();
		if (cur) {
			return cur(null).then(next);
		} else {
			return Promise.resolve(null);
		}
	};
	return next();
};

const applyHmr = (0,svelte_hmr_runtime__WEBPACK_IMPORTED_MODULE_0__.makeApplyHmr)(args => {
	const { notifyStart, notifyError, notifyEnd } = g[globalKey];
	const { m, reload } = args;

	let acceptHandlers = (m.hot.data && m.hot.data.acceptHandlers) || [];
	let nextAcceptHandlers = [];

	m.hot.dispose(data => {
		data.acceptHandlers = nextAcceptHandlers;
	});

	const dispose = (...args) => m.hot.dispose(...args);

	const accept = handler => {
		if (nextAcceptHandlers.length === 0) {
			m.hot.accept();
		}
		nextAcceptHandlers.push(handler);
	};

	const check = status => {
		if (status === 'ready') {
			notifyStart();
		} else if (status === 'idle') {
			runAcceptHandlers(acceptHandlers)
				.then(notifyEnd)
				.catch(notifyError(reload));
		}
	};

	m.hot.addStatusHandler(check);

	m.hot.dispose(() => {
		m.hot.removeStatusHandler(check);
	});

	const hot = {
		data: m.hot.data,
		dispose,
		accept,
	};

	return { ...args, hot };
});


/***/ }),

/***/ "./node_modules/svelte-previous/dist/index.es.js":
/*!*******************************************************!*\
  !*** ./node_modules/svelte-previous/dist/index.es.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "usePrevious": () => (/* binding */ usePrevious),
/* harmony export */   "withPrevious": () => (/* binding */ withPrevious)
/* harmony export */ });
/* harmony import */ var svelte_store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! svelte/store */ "./node_modules/svelte/store/index.mjs");


/*! *****************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */

function __spreadArray(to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
        if (ar || !(i in from)) {
            if (!ar) ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
        }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
}

function withPrevious(initValue, _a) {
    var _b = _a === void 0 ? {} : _a, _c = _b.numToTrack, numToTrack = _c === void 0 ? 1 : _c, _d = _b.initPrevious, initPrevious = _d === void 0 ? [] : _d, _e = _b.requireChange, requireChange = _e === void 0 ? true : _e, _f = _b.isEqual, isEqual = _f === void 0 ? function (a, b) { return a === b; } : _f;
    if (numToTrack <= 0) {
        throw new Error('Must track at least 1 previous');
    }
    // Generates an array of size numToTrack with the first element set to
    // initValue and all other elements set to ...initPrevious or null.
    var rest = initPrevious.slice(0, numToTrack);
    while (rest.length < numToTrack) {
        rest.push(null);
    }
    var values = (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.writable)(__spreadArray([initValue], rest, true));
    var updateCurrent = function (fn) {
        values.update(function ($values) {
            var newValue = fn($values[0]);
            // Prevent updates if values are equal as defined by an isEqual
            // comparison. By default, use a simple === comparison.
            if (requireChange && isEqual(newValue, $values[0])) {
                return $values;
            }
            // Adds the new value to the front of the array and removes the oldest
            // value from the end.
            return __spreadArray([newValue], $values.slice(0, numToTrack), true);
        });
    };
    var current = {
        subscribe: (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.derived)(values, function ($values) { return $values[0]; }).subscribe,
        update: updateCurrent,
        set: function (newValue) {
            updateCurrent(function () { return newValue; });
        },
    };
    // Create an array of derived stores for every other element in the array.
    var others = __spreadArray([], Array(numToTrack), true).map(function (_, i) {
        return (0,svelte_store__WEBPACK_IMPORTED_MODULE_0__.derived)(values, function ($values) { return $values[i + 1]; });
    });
    return __spreadArray([current], others, true);
}
/**
 * @deprecated Since version 2.0.1. Use `withPrevious` instead.
 */
var usePrevious = withPrevious;




/***/ }),

/***/ "./node_modules/svelte/easing/index.mjs":
/*!**********************************************!*\
  !*** ./node_modules/svelte/easing/index.mjs ***!
  \**********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "backIn": () => (/* binding */ backIn),
/* harmony export */   "backInOut": () => (/* binding */ backInOut),
/* harmony export */   "backOut": () => (/* binding */ backOut),
/* harmony export */   "bounceIn": () => (/* binding */ bounceIn),
/* harmony export */   "bounceInOut": () => (/* binding */ bounceInOut),
/* harmony export */   "bounceOut": () => (/* binding */ bounceOut),
/* harmony export */   "circIn": () => (/* binding */ circIn),
/* harmony export */   "circInOut": () => (/* binding */ circInOut),
/* harmony export */   "circOut": () => (/* binding */ circOut),
/* harmony export */   "cubicIn": () => (/* binding */ cubicIn),
/* harmony export */   "cubicInOut": () => (/* binding */ cubicInOut),
/* harmony export */   "cubicOut": () => (/* binding */ cubicOut),
/* harmony export */   "elasticIn": () => (/* binding */ elasticIn),
/* harmony export */   "elasticInOut": () => (/* binding */ elasticInOut),
/* harmony export */   "elasticOut": () => (/* binding */ elasticOut),
/* harmony export */   "expoIn": () => (/* binding */ expoIn),
/* harmony export */   "expoInOut": () => (/* binding */ expoInOut),
/* harmony export */   "expoOut": () => (/* binding */ expoOut),
/* harmony export */   "linear": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.identity),
/* harmony export */   "quadIn": () => (/* binding */ quadIn),
/* harmony export */   "quadInOut": () => (/* binding */ quadInOut),
/* harmony export */   "quadOut": () => (/* binding */ quadOut),
/* harmony export */   "quartIn": () => (/* binding */ quartIn),
/* harmony export */   "quartInOut": () => (/* binding */ quartInOut),
/* harmony export */   "quartOut": () => (/* binding */ quartOut),
/* harmony export */   "quintIn": () => (/* binding */ quintIn),
/* harmony export */   "quintInOut": () => (/* binding */ quintInOut),
/* harmony export */   "quintOut": () => (/* binding */ quintOut),
/* harmony export */   "sineIn": () => (/* binding */ sineIn),
/* harmony export */   "sineInOut": () => (/* binding */ sineInOut),
/* harmony export */   "sineOut": () => (/* binding */ sineOut)
/* harmony export */ });
/* harmony import */ var _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../internal/index.mjs */ "./node_modules/svelte/internal/index.mjs");


/*
Adapted from https://github.com/mattdesl
Distributed under MIT License https://github.com/mattdesl/eases/blob/master/LICENSE.md
*/
function backInOut(t) {
    const s = 1.70158 * 1.525;
    if ((t *= 2) < 1)
        return 0.5 * (t * t * ((s + 1) * t - s));
    return 0.5 * ((t -= 2) * t * ((s + 1) * t + s) + 2);
}
function backIn(t) {
    const s = 1.70158;
    return t * t * ((s + 1) * t - s);
}
function backOut(t) {
    const s = 1.70158;
    return --t * t * ((s + 1) * t + s) + 1;
}
function bounceOut(t) {
    const a = 4.0 / 11.0;
    const b = 8.0 / 11.0;
    const c = 9.0 / 10.0;
    const ca = 4356.0 / 361.0;
    const cb = 35442.0 / 1805.0;
    const cc = 16061.0 / 1805.0;
    const t2 = t * t;
    return t < a
        ? 7.5625 * t2
        : t < b
            ? 9.075 * t2 - 9.9 * t + 3.4
            : t < c
                ? ca * t2 - cb * t + cc
                : 10.8 * t * t - 20.52 * t + 10.72;
}
function bounceInOut(t) {
    return t < 0.5
        ? 0.5 * (1.0 - bounceOut(1.0 - t * 2.0))
        : 0.5 * bounceOut(t * 2.0 - 1.0) + 0.5;
}
function bounceIn(t) {
    return 1.0 - bounceOut(1.0 - t);
}
function circInOut(t) {
    if ((t *= 2) < 1)
        return -0.5 * (Math.sqrt(1 - t * t) - 1);
    return 0.5 * (Math.sqrt(1 - (t -= 2) * t) + 1);
}
function circIn(t) {
    return 1.0 - Math.sqrt(1.0 - t * t);
}
function circOut(t) {
    return Math.sqrt(1 - --t * t);
}
function cubicInOut(t) {
    return t < 0.5 ? 4.0 * t * t * t : 0.5 * Math.pow(2.0 * t - 2.0, 3.0) + 1.0;
}
function cubicIn(t) {
    return t * t * t;
}
function cubicOut(t) {
    const f = t - 1.0;
    return f * f * f + 1.0;
}
function elasticInOut(t) {
    return t < 0.5
        ? 0.5 *
            Math.sin(((+13.0 * Math.PI) / 2) * 2.0 * t) *
            Math.pow(2.0, 10.0 * (2.0 * t - 1.0))
        : 0.5 *
            Math.sin(((-13.0 * Math.PI) / 2) * (2.0 * t - 1.0 + 1.0)) *
            Math.pow(2.0, -10.0 * (2.0 * t - 1.0)) +
            1.0;
}
function elasticIn(t) {
    return Math.sin((13.0 * t * Math.PI) / 2) * Math.pow(2.0, 10.0 * (t - 1.0));
}
function elasticOut(t) {
    return (Math.sin((-13.0 * (t + 1.0) * Math.PI) / 2) * Math.pow(2.0, -10.0 * t) + 1.0);
}
function expoInOut(t) {
    return t === 0.0 || t === 1.0
        ? t
        : t < 0.5
            ? +0.5 * Math.pow(2.0, 20.0 * t - 10.0)
            : -0.5 * Math.pow(2.0, 10.0 - t * 20.0) + 1.0;
}
function expoIn(t) {
    return t === 0.0 ? t : Math.pow(2.0, 10.0 * (t - 1.0));
}
function expoOut(t) {
    return t === 1.0 ? t : 1.0 - Math.pow(2.0, -10.0 * t);
}
function quadInOut(t) {
    t /= 0.5;
    if (t < 1)
        return 0.5 * t * t;
    t--;
    return -0.5 * (t * (t - 2) - 1);
}
function quadIn(t) {
    return t * t;
}
function quadOut(t) {
    return -t * (t - 2.0);
}
function quartInOut(t) {
    return t < 0.5
        ? +8.0 * Math.pow(t, 4.0)
        : -8.0 * Math.pow(t - 1.0, 4.0) + 1.0;
}
function quartIn(t) {
    return Math.pow(t, 4.0);
}
function quartOut(t) {
    return Math.pow(t - 1.0, 3.0) * (1.0 - t) + 1.0;
}
function quintInOut(t) {
    if ((t *= 2) < 1)
        return 0.5 * t * t * t * t * t;
    return 0.5 * ((t -= 2) * t * t * t * t + 2);
}
function quintIn(t) {
    return t * t * t * t * t;
}
function quintOut(t) {
    return --t * t * t * t * t + 1;
}
function sineInOut(t) {
    return -0.5 * (Math.cos(Math.PI * t) - 1);
}
function sineIn(t) {
    const v = Math.cos(t * Math.PI * 0.5);
    if (Math.abs(v) < 1e-14)
        return 1;
    else
        return 1 - v;
}
function sineOut(t) {
    return Math.sin((t * Math.PI) / 2);
}




/***/ }),

/***/ "./node_modules/svelte/index.mjs":
/*!***************************************!*\
  !*** ./node_modules/svelte/index.mjs ***!
  \***************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "SvelteComponent": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentDev),
/* harmony export */   "SvelteComponentTyped": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.SvelteComponentTyped),
/* harmony export */   "afterUpdate": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.afterUpdate),
/* harmony export */   "beforeUpdate": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.beforeUpdate),
/* harmony export */   "createEventDispatcher": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.createEventDispatcher),
/* harmony export */   "getAllContexts": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.getAllContexts),
/* harmony export */   "getContext": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.getContext),
/* harmony export */   "hasContext": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.hasContext),
/* harmony export */   "onDestroy": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.onDestroy),
/* harmony export */   "onMount": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.onMount),
/* harmony export */   "setContext": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.setContext),
/* harmony export */   "tick": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.tick)
/* harmony export */ });
/* harmony import */ var _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./internal/index.mjs */ "./node_modules/svelte/internal/index.mjs");



/***/ }),

/***/ "./node_modules/svelte/internal/index.mjs":
/*!************************************************!*\
  !*** ./node_modules/svelte/internal/index.mjs ***!
  \************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "HtmlTag": () => (/* binding */ HtmlTag),
/* harmony export */   "HtmlTagHydration": () => (/* binding */ HtmlTagHydration),
/* harmony export */   "SvelteComponent": () => (/* binding */ SvelteComponent),
/* harmony export */   "SvelteComponentDev": () => (/* binding */ SvelteComponentDev),
/* harmony export */   "SvelteComponentTyped": () => (/* binding */ SvelteComponentTyped),
/* harmony export */   "SvelteElement": () => (/* binding */ SvelteElement),
/* harmony export */   "action_destroyer": () => (/* binding */ action_destroyer),
/* harmony export */   "add_attribute": () => (/* binding */ add_attribute),
/* harmony export */   "add_classes": () => (/* binding */ add_classes),
/* harmony export */   "add_flush_callback": () => (/* binding */ add_flush_callback),
/* harmony export */   "add_location": () => (/* binding */ add_location),
/* harmony export */   "add_render_callback": () => (/* binding */ add_render_callback),
/* harmony export */   "add_resize_listener": () => (/* binding */ add_resize_listener),
/* harmony export */   "add_styles": () => (/* binding */ add_styles),
/* harmony export */   "add_transform": () => (/* binding */ add_transform),
/* harmony export */   "afterUpdate": () => (/* binding */ afterUpdate),
/* harmony export */   "append": () => (/* binding */ append),
/* harmony export */   "append_dev": () => (/* binding */ append_dev),
/* harmony export */   "append_empty_stylesheet": () => (/* binding */ append_empty_stylesheet),
/* harmony export */   "append_hydration": () => (/* binding */ append_hydration),
/* harmony export */   "append_hydration_dev": () => (/* binding */ append_hydration_dev),
/* harmony export */   "append_styles": () => (/* binding */ append_styles),
/* harmony export */   "assign": () => (/* binding */ assign),
/* harmony export */   "attr": () => (/* binding */ attr),
/* harmony export */   "attr_dev": () => (/* binding */ attr_dev),
/* harmony export */   "attribute_to_object": () => (/* binding */ attribute_to_object),
/* harmony export */   "beforeUpdate": () => (/* binding */ beforeUpdate),
/* harmony export */   "bind": () => (/* binding */ bind),
/* harmony export */   "binding_callbacks": () => (/* binding */ binding_callbacks),
/* harmony export */   "blank_object": () => (/* binding */ blank_object),
/* harmony export */   "bubble": () => (/* binding */ bubble),
/* harmony export */   "check_outros": () => (/* binding */ check_outros),
/* harmony export */   "children": () => (/* binding */ children),
/* harmony export */   "claim_component": () => (/* binding */ claim_component),
/* harmony export */   "claim_element": () => (/* binding */ claim_element),
/* harmony export */   "claim_html_tag": () => (/* binding */ claim_html_tag),
/* harmony export */   "claim_space": () => (/* binding */ claim_space),
/* harmony export */   "claim_svg_element": () => (/* binding */ claim_svg_element),
/* harmony export */   "claim_text": () => (/* binding */ claim_text),
/* harmony export */   "clear_loops": () => (/* binding */ clear_loops),
/* harmony export */   "component_subscribe": () => (/* binding */ component_subscribe),
/* harmony export */   "compute_rest_props": () => (/* binding */ compute_rest_props),
/* harmony export */   "compute_slots": () => (/* binding */ compute_slots),
/* harmony export */   "construct_svelte_component": () => (/* binding */ construct_svelte_component),
/* harmony export */   "construct_svelte_component_dev": () => (/* binding */ construct_svelte_component_dev),
/* harmony export */   "createEventDispatcher": () => (/* binding */ createEventDispatcher),
/* harmony export */   "create_animation": () => (/* binding */ create_animation),
/* harmony export */   "create_bidirectional_transition": () => (/* binding */ create_bidirectional_transition),
/* harmony export */   "create_component": () => (/* binding */ create_component),
/* harmony export */   "create_in_transition": () => (/* binding */ create_in_transition),
/* harmony export */   "create_out_transition": () => (/* binding */ create_out_transition),
/* harmony export */   "create_slot": () => (/* binding */ create_slot),
/* harmony export */   "create_ssr_component": () => (/* binding */ create_ssr_component),
/* harmony export */   "current_component": () => (/* binding */ current_component),
/* harmony export */   "custom_event": () => (/* binding */ custom_event),
/* harmony export */   "dataset_dev": () => (/* binding */ dataset_dev),
/* harmony export */   "debug": () => (/* binding */ debug),
/* harmony export */   "destroy_block": () => (/* binding */ destroy_block),
/* harmony export */   "destroy_component": () => (/* binding */ destroy_component),
/* harmony export */   "destroy_each": () => (/* binding */ destroy_each),
/* harmony export */   "detach": () => (/* binding */ detach),
/* harmony export */   "detach_after_dev": () => (/* binding */ detach_after_dev),
/* harmony export */   "detach_before_dev": () => (/* binding */ detach_before_dev),
/* harmony export */   "detach_between_dev": () => (/* binding */ detach_between_dev),
/* harmony export */   "detach_dev": () => (/* binding */ detach_dev),
/* harmony export */   "dirty_components": () => (/* binding */ dirty_components),
/* harmony export */   "dispatch_dev": () => (/* binding */ dispatch_dev),
/* harmony export */   "each": () => (/* binding */ each),
/* harmony export */   "element": () => (/* binding */ element),
/* harmony export */   "element_is": () => (/* binding */ element_is),
/* harmony export */   "empty": () => (/* binding */ empty),
/* harmony export */   "end_hydrating": () => (/* binding */ end_hydrating),
/* harmony export */   "escape": () => (/* binding */ escape),
/* harmony export */   "escape_attribute_value": () => (/* binding */ escape_attribute_value),
/* harmony export */   "escape_object": () => (/* binding */ escape_object),
/* harmony export */   "exclude_internal_props": () => (/* binding */ exclude_internal_props),
/* harmony export */   "fix_and_destroy_block": () => (/* binding */ fix_and_destroy_block),
/* harmony export */   "fix_and_outro_and_destroy_block": () => (/* binding */ fix_and_outro_and_destroy_block),
/* harmony export */   "fix_position": () => (/* binding */ fix_position),
/* harmony export */   "flush": () => (/* binding */ flush),
/* harmony export */   "flush_render_callbacks": () => (/* binding */ flush_render_callbacks),
/* harmony export */   "getAllContexts": () => (/* binding */ getAllContexts),
/* harmony export */   "getContext": () => (/* binding */ getContext),
/* harmony export */   "get_all_dirty_from_scope": () => (/* binding */ get_all_dirty_from_scope),
/* harmony export */   "get_binding_group_value": () => (/* binding */ get_binding_group_value),
/* harmony export */   "get_current_component": () => (/* binding */ get_current_component),
/* harmony export */   "get_custom_elements_slots": () => (/* binding */ get_custom_elements_slots),
/* harmony export */   "get_root_for_style": () => (/* binding */ get_root_for_style),
/* harmony export */   "get_slot_changes": () => (/* binding */ get_slot_changes),
/* harmony export */   "get_spread_object": () => (/* binding */ get_spread_object),
/* harmony export */   "get_spread_update": () => (/* binding */ get_spread_update),
/* harmony export */   "get_store_value": () => (/* binding */ get_store_value),
/* harmony export */   "globals": () => (/* binding */ globals),
/* harmony export */   "group_outros": () => (/* binding */ group_outros),
/* harmony export */   "handle_promise": () => (/* binding */ handle_promise),
/* harmony export */   "hasContext": () => (/* binding */ hasContext),
/* harmony export */   "has_prop": () => (/* binding */ has_prop),
/* harmony export */   "head_selector": () => (/* binding */ head_selector),
/* harmony export */   "identity": () => (/* binding */ identity),
/* harmony export */   "init": () => (/* binding */ init),
/* harmony export */   "init_binding_group": () => (/* binding */ init_binding_group),
/* harmony export */   "init_binding_group_dynamic": () => (/* binding */ init_binding_group_dynamic),
/* harmony export */   "insert": () => (/* binding */ insert),
/* harmony export */   "insert_dev": () => (/* binding */ insert_dev),
/* harmony export */   "insert_hydration": () => (/* binding */ insert_hydration),
/* harmony export */   "insert_hydration_dev": () => (/* binding */ insert_hydration_dev),
/* harmony export */   "intros": () => (/* binding */ intros),
/* harmony export */   "invalid_attribute_name_character": () => (/* binding */ invalid_attribute_name_character),
/* harmony export */   "is_client": () => (/* binding */ is_client),
/* harmony export */   "is_crossorigin": () => (/* binding */ is_crossorigin),
/* harmony export */   "is_empty": () => (/* binding */ is_empty),
/* harmony export */   "is_function": () => (/* binding */ is_function),
/* harmony export */   "is_promise": () => (/* binding */ is_promise),
/* harmony export */   "is_void": () => (/* binding */ is_void),
/* harmony export */   "listen": () => (/* binding */ listen),
/* harmony export */   "listen_dev": () => (/* binding */ listen_dev),
/* harmony export */   "loop": () => (/* binding */ loop),
/* harmony export */   "loop_guard": () => (/* binding */ loop_guard),
/* harmony export */   "merge_ssr_styles": () => (/* binding */ merge_ssr_styles),
/* harmony export */   "missing_component": () => (/* binding */ missing_component),
/* harmony export */   "mount_component": () => (/* binding */ mount_component),
/* harmony export */   "noop": () => (/* binding */ noop),
/* harmony export */   "not_equal": () => (/* binding */ not_equal),
/* harmony export */   "now": () => (/* binding */ now),
/* harmony export */   "null_to_empty": () => (/* binding */ null_to_empty),
/* harmony export */   "object_without_properties": () => (/* binding */ object_without_properties),
/* harmony export */   "onDestroy": () => (/* binding */ onDestroy),
/* harmony export */   "onMount": () => (/* binding */ onMount),
/* harmony export */   "once": () => (/* binding */ once),
/* harmony export */   "outro_and_destroy_block": () => (/* binding */ outro_and_destroy_block),
/* harmony export */   "prevent_default": () => (/* binding */ prevent_default),
/* harmony export */   "prop_dev": () => (/* binding */ prop_dev),
/* harmony export */   "query_selector_all": () => (/* binding */ query_selector_all),
/* harmony export */   "raf": () => (/* binding */ raf),
/* harmony export */   "run": () => (/* binding */ run),
/* harmony export */   "run_all": () => (/* binding */ run_all),
/* harmony export */   "safe_not_equal": () => (/* binding */ safe_not_equal),
/* harmony export */   "schedule_update": () => (/* binding */ schedule_update),
/* harmony export */   "select_multiple_value": () => (/* binding */ select_multiple_value),
/* harmony export */   "select_option": () => (/* binding */ select_option),
/* harmony export */   "select_options": () => (/* binding */ select_options),
/* harmony export */   "select_value": () => (/* binding */ select_value),
/* harmony export */   "self": () => (/* binding */ self),
/* harmony export */   "setContext": () => (/* binding */ setContext),
/* harmony export */   "set_attributes": () => (/* binding */ set_attributes),
/* harmony export */   "set_current_component": () => (/* binding */ set_current_component),
/* harmony export */   "set_custom_element_data": () => (/* binding */ set_custom_element_data),
/* harmony export */   "set_custom_element_data_map": () => (/* binding */ set_custom_element_data_map),
/* harmony export */   "set_data": () => (/* binding */ set_data),
/* harmony export */   "set_data_dev": () => (/* binding */ set_data_dev),
/* harmony export */   "set_dynamic_element_data": () => (/* binding */ set_dynamic_element_data),
/* harmony export */   "set_input_type": () => (/* binding */ set_input_type),
/* harmony export */   "set_input_value": () => (/* binding */ set_input_value),
/* harmony export */   "set_now": () => (/* binding */ set_now),
/* harmony export */   "set_raf": () => (/* binding */ set_raf),
/* harmony export */   "set_store_value": () => (/* binding */ set_store_value),
/* harmony export */   "set_style": () => (/* binding */ set_style),
/* harmony export */   "set_svg_attributes": () => (/* binding */ set_svg_attributes),
/* harmony export */   "space": () => (/* binding */ space),
/* harmony export */   "spread": () => (/* binding */ spread),
/* harmony export */   "src_url_equal": () => (/* binding */ src_url_equal),
/* harmony export */   "start_hydrating": () => (/* binding */ start_hydrating),
/* harmony export */   "stop_immediate_propagation": () => (/* binding */ stop_immediate_propagation),
/* harmony export */   "stop_propagation": () => (/* binding */ stop_propagation),
/* harmony export */   "subscribe": () => (/* binding */ subscribe),
/* harmony export */   "svg_element": () => (/* binding */ svg_element),
/* harmony export */   "text": () => (/* binding */ text),
/* harmony export */   "tick": () => (/* binding */ tick),
/* harmony export */   "time_ranges_to_array": () => (/* binding */ time_ranges_to_array),
/* harmony export */   "to_number": () => (/* binding */ to_number),
/* harmony export */   "toggle_class": () => (/* binding */ toggle_class),
/* harmony export */   "transition_in": () => (/* binding */ transition_in),
/* harmony export */   "transition_out": () => (/* binding */ transition_out),
/* harmony export */   "trusted": () => (/* binding */ trusted),
/* harmony export */   "update_await_block_branch": () => (/* binding */ update_await_block_branch),
/* harmony export */   "update_keyed_each": () => (/* binding */ update_keyed_each),
/* harmony export */   "update_slot": () => (/* binding */ update_slot),
/* harmony export */   "update_slot_base": () => (/* binding */ update_slot_base),
/* harmony export */   "validate_component": () => (/* binding */ validate_component),
/* harmony export */   "validate_dynamic_element": () => (/* binding */ validate_dynamic_element),
/* harmony export */   "validate_each_argument": () => (/* binding */ validate_each_argument),
/* harmony export */   "validate_each_keys": () => (/* binding */ validate_each_keys),
/* harmony export */   "validate_slots": () => (/* binding */ validate_slots),
/* harmony export */   "validate_store": () => (/* binding */ validate_store),
/* harmony export */   "validate_void_dynamic_element": () => (/* binding */ validate_void_dynamic_element),
/* harmony export */   "xlink_attr": () => (/* binding */ xlink_attr)
/* harmony export */ });
function noop() { }
const identity = x => x;
function assign(tar, src) {
    // @ts-ignore
    for (const k in src)
        tar[k] = src[k];
    return tar;
}
// Adapted from https://github.com/then/is-promise/blob/master/index.js
// Distributed under MIT License https://github.com/then/is-promise/blob/master/LICENSE
function is_promise(value) {
    return !!value && (typeof value === 'object' || typeof value === 'function') && typeof value.then === 'function';
}
function add_location(element, file, line, column, char) {
    element.__svelte_meta = {
        loc: { file, line, column, char }
    };
}
function run(fn) {
    return fn();
}
function blank_object() {
    return Object.create(null);
}
function run_all(fns) {
    fns.forEach(run);
}
function is_function(thing) {
    return typeof thing === 'function';
}
function safe_not_equal(a, b) {
    return a != a ? b == b : a !== b || ((a && typeof a === 'object') || typeof a === 'function');
}
let src_url_equal_anchor;
function src_url_equal(element_src, url) {
    if (!src_url_equal_anchor) {
        src_url_equal_anchor = document.createElement('a');
    }
    src_url_equal_anchor.href = url;
    return element_src === src_url_equal_anchor.href;
}
function not_equal(a, b) {
    return a != a ? b == b : a !== b;
}
function is_empty(obj) {
    return Object.keys(obj).length === 0;
}
function validate_store(store, name) {
    if (store != null && typeof store.subscribe !== 'function') {
        throw new Error(`'${name}' is not a store with a 'subscribe' method`);
    }
}
function subscribe(store, ...callbacks) {
    if (store == null) {
        return noop;
    }
    const unsub = store.subscribe(...callbacks);
    return unsub.unsubscribe ? () => unsub.unsubscribe() : unsub;
}
function get_store_value(store) {
    let value;
    subscribe(store, _ => value = _)();
    return value;
}
function component_subscribe(component, store, callback) {
    component.$$.on_destroy.push(subscribe(store, callback));
}
function create_slot(definition, ctx, $$scope, fn) {
    if (definition) {
        const slot_ctx = get_slot_context(definition, ctx, $$scope, fn);
        return definition[0](slot_ctx);
    }
}
function get_slot_context(definition, ctx, $$scope, fn) {
    return definition[1] && fn
        ? assign($$scope.ctx.slice(), definition[1](fn(ctx)))
        : $$scope.ctx;
}
function get_slot_changes(definition, $$scope, dirty, fn) {
    if (definition[2] && fn) {
        const lets = definition[2](fn(dirty));
        if ($$scope.dirty === undefined) {
            return lets;
        }
        if (typeof lets === 'object') {
            const merged = [];
            const len = Math.max($$scope.dirty.length, lets.length);
            for (let i = 0; i < len; i += 1) {
                merged[i] = $$scope.dirty[i] | lets[i];
            }
            return merged;
        }
        return $$scope.dirty | lets;
    }
    return $$scope.dirty;
}
function update_slot_base(slot, slot_definition, ctx, $$scope, slot_changes, get_slot_context_fn) {
    if (slot_changes) {
        const slot_context = get_slot_context(slot_definition, ctx, $$scope, get_slot_context_fn);
        slot.p(slot_context, slot_changes);
    }
}
function update_slot(slot, slot_definition, ctx, $$scope, dirty, get_slot_changes_fn, get_slot_context_fn) {
    const slot_changes = get_slot_changes(slot_definition, $$scope, dirty, get_slot_changes_fn);
    update_slot_base(slot, slot_definition, ctx, $$scope, slot_changes, get_slot_context_fn);
}
function get_all_dirty_from_scope($$scope) {
    if ($$scope.ctx.length > 32) {
        const dirty = [];
        const length = $$scope.ctx.length / 32;
        for (let i = 0; i < length; i++) {
            dirty[i] = -1;
        }
        return dirty;
    }
    return -1;
}
function exclude_internal_props(props) {
    const result = {};
    for (const k in props)
        if (k[0] !== '$')
            result[k] = props[k];
    return result;
}
function compute_rest_props(props, keys) {
    const rest = {};
    keys = new Set(keys);
    for (const k in props)
        if (!keys.has(k) && k[0] !== '$')
            rest[k] = props[k];
    return rest;
}
function compute_slots(slots) {
    const result = {};
    for (const key in slots) {
        result[key] = true;
    }
    return result;
}
function once(fn) {
    let ran = false;
    return function (...args) {
        if (ran)
            return;
        ran = true;
        fn.call(this, ...args);
    };
}
function null_to_empty(value) {
    return value == null ? '' : value;
}
function set_store_value(store, ret, value) {
    store.set(value);
    return ret;
}
const has_prop = (obj, prop) => Object.prototype.hasOwnProperty.call(obj, prop);
function action_destroyer(action_result) {
    return action_result && is_function(action_result.destroy) ? action_result.destroy : noop;
}

const is_client = typeof window !== 'undefined';
let now = is_client
    ? () => window.performance.now()
    : () => Date.now();
let raf = is_client ? cb => requestAnimationFrame(cb) : noop;
// used internally for testing
function set_now(fn) {
    now = fn;
}
function set_raf(fn) {
    raf = fn;
}

const tasks = new Set();
function run_tasks(now) {
    tasks.forEach(task => {
        if (!task.c(now)) {
            tasks.delete(task);
            task.f();
        }
    });
    if (tasks.size !== 0)
        raf(run_tasks);
}
/**
 * For testing purposes only!
 */
function clear_loops() {
    tasks.clear();
}
/**
 * Creates a new task that runs on each raf frame
 * until it returns a falsy value or is aborted
 */
function loop(callback) {
    let task;
    if (tasks.size === 0)
        raf(run_tasks);
    return {
        promise: new Promise(fulfill => {
            tasks.add(task = { c: callback, f: fulfill });
        }),
        abort() {
            tasks.delete(task);
        }
    };
}

// Track which nodes are claimed during hydration. Unclaimed nodes can then be removed from the DOM
// at the end of hydration without touching the remaining nodes.
let is_hydrating = false;
function start_hydrating() {
    is_hydrating = true;
}
function end_hydrating() {
    is_hydrating = false;
}
function upper_bound(low, high, key, value) {
    // Return first index of value larger than input value in the range [low, high)
    while (low < high) {
        const mid = low + ((high - low) >> 1);
        if (key(mid) <= value) {
            low = mid + 1;
        }
        else {
            high = mid;
        }
    }
    return low;
}
function init_hydrate(target) {
    if (target.hydrate_init)
        return;
    target.hydrate_init = true;
    // We know that all children have claim_order values since the unclaimed have been detached if target is not <head>
    let children = target.childNodes;
    // If target is <head>, there may be children without claim_order
    if (target.nodeName === 'HEAD') {
        const myChildren = [];
        for (let i = 0; i < children.length; i++) {
            const node = children[i];
            if (node.claim_order !== undefined) {
                myChildren.push(node);
            }
        }
        children = myChildren;
    }
    /*
    * Reorder claimed children optimally.
    * We can reorder claimed children optimally by finding the longest subsequence of
    * nodes that are already claimed in order and only moving the rest. The longest
    * subsequence of nodes that are claimed in order can be found by
    * computing the longest increasing subsequence of .claim_order values.
    *
    * This algorithm is optimal in generating the least amount of reorder operations
    * possible.
    *
    * Proof:
    * We know that, given a set of reordering operations, the nodes that do not move
    * always form an increasing subsequence, since they do not move among each other
    * meaning that they must be already ordered among each other. Thus, the maximal
    * set of nodes that do not move form a longest increasing subsequence.
    */
    // Compute longest increasing subsequence
    // m: subsequence length j => index k of smallest value that ends an increasing subsequence of length j
    const m = new Int32Array(children.length + 1);
    // Predecessor indices + 1
    const p = new Int32Array(children.length);
    m[0] = -1;
    let longest = 0;
    for (let i = 0; i < children.length; i++) {
        const current = children[i].claim_order;
        // Find the largest subsequence length such that it ends in a value less than our current value
        // upper_bound returns first greater value, so we subtract one
        // with fast path for when we are on the current longest subsequence
        const seqLen = ((longest > 0 && children[m[longest]].claim_order <= current) ? longest + 1 : upper_bound(1, longest, idx => children[m[idx]].claim_order, current)) - 1;
        p[i] = m[seqLen] + 1;
        const newLen = seqLen + 1;
        // We can guarantee that current is the smallest value. Otherwise, we would have generated a longer sequence.
        m[newLen] = i;
        longest = Math.max(newLen, longest);
    }
    // The longest increasing subsequence of nodes (initially reversed)
    const lis = [];
    // The rest of the nodes, nodes that will be moved
    const toMove = [];
    let last = children.length - 1;
    for (let cur = m[longest] + 1; cur != 0; cur = p[cur - 1]) {
        lis.push(children[cur - 1]);
        for (; last >= cur; last--) {
            toMove.push(children[last]);
        }
        last--;
    }
    for (; last >= 0; last--) {
        toMove.push(children[last]);
    }
    lis.reverse();
    // We sort the nodes being moved to guarantee that their insertion order matches the claim order
    toMove.sort((a, b) => a.claim_order - b.claim_order);
    // Finally, we move the nodes
    for (let i = 0, j = 0; i < toMove.length; i++) {
        while (j < lis.length && toMove[i].claim_order >= lis[j].claim_order) {
            j++;
        }
        const anchor = j < lis.length ? lis[j] : null;
        target.insertBefore(toMove[i], anchor);
    }
}
function append(target, node) {
    target.appendChild(node);
}
function append_styles(target, style_sheet_id, styles) {
    const append_styles_to = get_root_for_style(target);
    if (!append_styles_to.getElementById(style_sheet_id)) {
        const style = element('style');
        style.id = style_sheet_id;
        style.textContent = styles;
        append_stylesheet(append_styles_to, style);
    }
}
function get_root_for_style(node) {
    if (!node)
        return document;
    const root = node.getRootNode ? node.getRootNode() : node.ownerDocument;
    if (root && root.host) {
        return root;
    }
    return node.ownerDocument;
}
function append_empty_stylesheet(node) {
    const style_element = element('style');
    append_stylesheet(get_root_for_style(node), style_element);
    return style_element.sheet;
}
function append_stylesheet(node, style) {
    append(node.head || node, style);
    return style.sheet;
}
function append_hydration(target, node) {
    if (is_hydrating) {
        init_hydrate(target);
        if ((target.actual_end_child === undefined) || ((target.actual_end_child !== null) && (target.actual_end_child.parentNode !== target))) {
            target.actual_end_child = target.firstChild;
        }
        // Skip nodes of undefined ordering
        while ((target.actual_end_child !== null) && (target.actual_end_child.claim_order === undefined)) {
            target.actual_end_child = target.actual_end_child.nextSibling;
        }
        if (node !== target.actual_end_child) {
            // We only insert if the ordering of this node should be modified or the parent node is not target
            if (node.claim_order !== undefined || node.parentNode !== target) {
                target.insertBefore(node, target.actual_end_child);
            }
        }
        else {
            target.actual_end_child = node.nextSibling;
        }
    }
    else if (node.parentNode !== target || node.nextSibling !== null) {
        target.appendChild(node);
    }
}
function insert(target, node, anchor) {
    target.insertBefore(node, anchor || null);
}
function insert_hydration(target, node, anchor) {
    if (is_hydrating && !anchor) {
        append_hydration(target, node);
    }
    else if (node.parentNode !== target || node.nextSibling != anchor) {
        target.insertBefore(node, anchor || null);
    }
}
function detach(node) {
    if (node.parentNode) {
        node.parentNode.removeChild(node);
    }
}
function destroy_each(iterations, detaching) {
    for (let i = 0; i < iterations.length; i += 1) {
        if (iterations[i])
            iterations[i].d(detaching);
    }
}
function element(name) {
    return document.createElement(name);
}
function element_is(name, is) {
    return document.createElement(name, { is });
}
function object_without_properties(obj, exclude) {
    const target = {};
    for (const k in obj) {
        if (has_prop(obj, k)
            // @ts-ignore
            && exclude.indexOf(k) === -1) {
            // @ts-ignore
            target[k] = obj[k];
        }
    }
    return target;
}
function svg_element(name) {
    return document.createElementNS('http://www.w3.org/2000/svg', name);
}
function text(data) {
    return document.createTextNode(data);
}
function space() {
    return text(' ');
}
function empty() {
    return text('');
}
function listen(node, event, handler, options) {
    node.addEventListener(event, handler, options);
    return () => node.removeEventListener(event, handler, options);
}
function prevent_default(fn) {
    return function (event) {
        event.preventDefault();
        // @ts-ignore
        return fn.call(this, event);
    };
}
function stop_propagation(fn) {
    return function (event) {
        event.stopPropagation();
        // @ts-ignore
        return fn.call(this, event);
    };
}
function stop_immediate_propagation(fn) {
    return function (event) {
        event.stopImmediatePropagation();
        // @ts-ignore
        return fn.call(this, event);
    };
}
function self(fn) {
    return function (event) {
        // @ts-ignore
        if (event.target === this)
            fn.call(this, event);
    };
}
function trusted(fn) {
    return function (event) {
        // @ts-ignore
        if (event.isTrusted)
            fn.call(this, event);
    };
}
function attr(node, attribute, value) {
    if (value == null)
        node.removeAttribute(attribute);
    else if (node.getAttribute(attribute) !== value)
        node.setAttribute(attribute, value);
}
function set_attributes(node, attributes) {
    // @ts-ignore
    const descriptors = Object.getOwnPropertyDescriptors(node.__proto__);
    for (const key in attributes) {
        if (attributes[key] == null) {
            node.removeAttribute(key);
        }
        else if (key === 'style') {
            node.style.cssText = attributes[key];
        }
        else if (key === '__value') {
            node.value = node[key] = attributes[key];
        }
        else if (descriptors[key] && descriptors[key].set) {
            node[key] = attributes[key];
        }
        else {
            attr(node, key, attributes[key]);
        }
    }
}
function set_svg_attributes(node, attributes) {
    for (const key in attributes) {
        attr(node, key, attributes[key]);
    }
}
function set_custom_element_data_map(node, data_map) {
    Object.keys(data_map).forEach((key) => {
        set_custom_element_data(node, key, data_map[key]);
    });
}
function set_custom_element_data(node, prop, value) {
    if (prop in node) {
        node[prop] = typeof node[prop] === 'boolean' && value === '' ? true : value;
    }
    else {
        attr(node, prop, value);
    }
}
function set_dynamic_element_data(tag) {
    return (/-/.test(tag)) ? set_custom_element_data_map : set_attributes;
}
function xlink_attr(node, attribute, value) {
    node.setAttributeNS('http://www.w3.org/1999/xlink', attribute, value);
}
function get_binding_group_value(group, __value, checked) {
    const value = new Set();
    for (let i = 0; i < group.length; i += 1) {
        if (group[i].checked)
            value.add(group[i].__value);
    }
    if (!checked) {
        value.delete(__value);
    }
    return Array.from(value);
}
function init_binding_group(group) {
    let _inputs;
    return {
        /* push */ p(...inputs) {
            _inputs = inputs;
            _inputs.forEach(input => group.push(input));
        },
        /* remove */ r() {
            _inputs.forEach(input => group.splice(group.indexOf(input), 1));
        }
    };
}
function init_binding_group_dynamic(group, indexes) {
    let _group = get_binding_group(group);
    let _inputs;
    function get_binding_group(group) {
        for (let i = 0; i < indexes.length; i++) {
            group = group[indexes[i]] = group[indexes[i]] || [];
        }
        return group;
    }
    function push() {
        _inputs.forEach(input => _group.push(input));
    }
    function remove() {
        _inputs.forEach(input => _group.splice(_group.indexOf(input), 1));
    }
    return {
        /* update */ u(new_indexes) {
            indexes = new_indexes;
            const new_group = get_binding_group(group);
            if (new_group !== _group) {
                remove();
                _group = new_group;
                push();
            }
        },
        /* push */ p(...inputs) {
            _inputs = inputs;
            push();
        },
        /* remove */ r: remove
    };
}
function to_number(value) {
    return value === '' ? null : +value;
}
function time_ranges_to_array(ranges) {
    const array = [];
    for (let i = 0; i < ranges.length; i += 1) {
        array.push({ start: ranges.start(i), end: ranges.end(i) });
    }
    return array;
}
function children(element) {
    return Array.from(element.childNodes);
}
function init_claim_info(nodes) {
    if (nodes.claim_info === undefined) {
        nodes.claim_info = { last_index: 0, total_claimed: 0 };
    }
}
function claim_node(nodes, predicate, processNode, createNode, dontUpdateLastIndex = false) {
    // Try to find nodes in an order such that we lengthen the longest increasing subsequence
    init_claim_info(nodes);
    const resultNode = (() => {
        // We first try to find an element after the previous one
        for (let i = nodes.claim_info.last_index; i < nodes.length; i++) {
            const node = nodes[i];
            if (predicate(node)) {
                const replacement = processNode(node);
                if (replacement === undefined) {
                    nodes.splice(i, 1);
                }
                else {
                    nodes[i] = replacement;
                }
                if (!dontUpdateLastIndex) {
                    nodes.claim_info.last_index = i;
                }
                return node;
            }
        }
        // Otherwise, we try to find one before
        // We iterate in reverse so that we don't go too far back
        for (let i = nodes.claim_info.last_index - 1; i >= 0; i--) {
            const node = nodes[i];
            if (predicate(node)) {
                const replacement = processNode(node);
                if (replacement === undefined) {
                    nodes.splice(i, 1);
                }
                else {
                    nodes[i] = replacement;
                }
                if (!dontUpdateLastIndex) {
                    nodes.claim_info.last_index = i;
                }
                else if (replacement === undefined) {
                    // Since we spliced before the last_index, we decrease it
                    nodes.claim_info.last_index--;
                }
                return node;
            }
        }
        // If we can't find any matching node, we create a new one
        return createNode();
    })();
    resultNode.claim_order = nodes.claim_info.total_claimed;
    nodes.claim_info.total_claimed += 1;
    return resultNode;
}
function claim_element_base(nodes, name, attributes, create_element) {
    return claim_node(nodes, (node) => node.nodeName === name, (node) => {
        const remove = [];
        for (let j = 0; j < node.attributes.length; j++) {
            const attribute = node.attributes[j];
            if (!attributes[attribute.name]) {
                remove.push(attribute.name);
            }
        }
        remove.forEach(v => node.removeAttribute(v));
        return undefined;
    }, () => create_element(name));
}
function claim_element(nodes, name, attributes) {
    return claim_element_base(nodes, name, attributes, element);
}
function claim_svg_element(nodes, name, attributes) {
    return claim_element_base(nodes, name, attributes, svg_element);
}
function claim_text(nodes, data) {
    return claim_node(nodes, (node) => node.nodeType === 3, (node) => {
        const dataStr = '' + data;
        if (node.data.startsWith(dataStr)) {
            if (node.data.length !== dataStr.length) {
                return node.splitText(dataStr.length);
            }
        }
        else {
            node.data = dataStr;
        }
    }, () => text(data), true // Text nodes should not update last index since it is likely not worth it to eliminate an increasing subsequence of actual elements
    );
}
function claim_space(nodes) {
    return claim_text(nodes, ' ');
}
function find_comment(nodes, text, start) {
    for (let i = start; i < nodes.length; i += 1) {
        const node = nodes[i];
        if (node.nodeType === 8 /* comment node */ && node.textContent.trim() === text) {
            return i;
        }
    }
    return nodes.length;
}
function claim_html_tag(nodes, is_svg) {
    // find html opening tag
    const start_index = find_comment(nodes, 'HTML_TAG_START', 0);
    const end_index = find_comment(nodes, 'HTML_TAG_END', start_index);
    if (start_index === end_index) {
        return new HtmlTagHydration(undefined, is_svg);
    }
    init_claim_info(nodes);
    const html_tag_nodes = nodes.splice(start_index, end_index - start_index + 1);
    detach(html_tag_nodes[0]);
    detach(html_tag_nodes[html_tag_nodes.length - 1]);
    const claimed_nodes = html_tag_nodes.slice(1, html_tag_nodes.length - 1);
    for (const n of claimed_nodes) {
        n.claim_order = nodes.claim_info.total_claimed;
        nodes.claim_info.total_claimed += 1;
    }
    return new HtmlTagHydration(claimed_nodes, is_svg);
}
function set_data(text, data) {
    data = '' + data;
    if (text.wholeText !== data)
        text.data = data;
}
function set_input_value(input, value) {
    input.value = value == null ? '' : value;
}
function set_input_type(input, type) {
    try {
        input.type = type;
    }
    catch (e) {
        // do nothing
    }
}
function set_style(node, key, value, important) {
    if (value === null) {
        node.style.removeProperty(key);
    }
    else {
        node.style.setProperty(key, value, important ? 'important' : '');
    }
}
function select_option(select, value) {
    for (let i = 0; i < select.options.length; i += 1) {
        const option = select.options[i];
        if (option.__value === value) {
            option.selected = true;
            return;
        }
    }
    select.selectedIndex = -1; // no option should be selected
}
function select_options(select, value) {
    for (let i = 0; i < select.options.length; i += 1) {
        const option = select.options[i];
        option.selected = ~value.indexOf(option.__value);
    }
}
function first_enabled_option(select) {
    for (const option of select.options) {
        if (!option.disabled) {
            return option;
        }
    }
}
function select_value(select) {
    const selected_option = select.querySelector(':checked') || first_enabled_option(select);
    return selected_option && selected_option.__value;
}
function select_multiple_value(select) {
    return [].map.call(select.querySelectorAll(':checked'), option => option.__value);
}
// unfortunately this can't be a constant as that wouldn't be tree-shakeable
// so we cache the result instead
let crossorigin;
function is_crossorigin() {
    if (crossorigin === undefined) {
        crossorigin = false;
        try {
            if (typeof window !== 'undefined' && window.parent) {
                void window.parent.document;
            }
        }
        catch (error) {
            crossorigin = true;
        }
    }
    return crossorigin;
}
function add_resize_listener(node, fn) {
    const computed_style = getComputedStyle(node);
    if (computed_style.position === 'static') {
        node.style.position = 'relative';
    }
    const iframe = element('iframe');
    iframe.setAttribute('style', 'display: block; position: absolute; top: 0; left: 0; width: 100%; height: 100%; ' +
        'overflow: hidden; border: 0; opacity: 0; pointer-events: none; z-index: -1;');
    iframe.setAttribute('aria-hidden', 'true');
    iframe.tabIndex = -1;
    const crossorigin = is_crossorigin();
    let unsubscribe;
    if (crossorigin) {
        iframe.src = "data:text/html,<script>onresize=function(){parent.postMessage(0,'*')}</script>";
        unsubscribe = listen(window, 'message', (event) => {
            if (event.source === iframe.contentWindow)
                fn();
        });
    }
    else {
        iframe.src = 'about:blank';
        iframe.onload = () => {
            unsubscribe = listen(iframe.contentWindow, 'resize', fn);
            // make sure an initial resize event is fired _after_ the iframe is loaded (which is asynchronous)
            // see https://github.com/sveltejs/svelte/issues/4233
            fn();
        };
    }
    append(node, iframe);
    return () => {
        if (crossorigin) {
            unsubscribe();
        }
        else if (unsubscribe && iframe.contentWindow) {
            unsubscribe();
        }
        detach(iframe);
    };
}
function toggle_class(element, name, toggle) {
    element.classList[toggle ? 'add' : 'remove'](name);
}
function custom_event(type, detail, { bubbles = false, cancelable = false } = {}) {
    const e = document.createEvent('CustomEvent');
    e.initCustomEvent(type, bubbles, cancelable, detail);
    return e;
}
function query_selector_all(selector, parent = document.body) {
    return Array.from(parent.querySelectorAll(selector));
}
function head_selector(nodeId, head) {
    const result = [];
    let started = 0;
    for (const node of head.childNodes) {
        if (node.nodeType === 8 /* comment node */) {
            const comment = node.textContent.trim();
            if (comment === `HEAD_${nodeId}_END`) {
                started -= 1;
                result.push(node);
            }
            else if (comment === `HEAD_${nodeId}_START`) {
                started += 1;
                result.push(node);
            }
        }
        else if (started > 0) {
            result.push(node);
        }
    }
    return result;
}
class HtmlTag {
    constructor(is_svg = false) {
        this.is_svg = false;
        this.is_svg = is_svg;
        this.e = this.n = null;
    }
    c(html) {
        this.h(html);
    }
    m(html, target, anchor = null) {
        if (!this.e) {
            if (this.is_svg)
                this.e = svg_element(target.nodeName);
            /** #7364  target for <template> may be provided as #document-fragment(11) */
            else
                this.e = element((target.nodeType === 11 ? 'TEMPLATE' : target.nodeName));
            this.t = target.tagName !== 'TEMPLATE' ? target : target.content;
            this.c(html);
        }
        this.i(anchor);
    }
    h(html) {
        this.e.innerHTML = html;
        this.n = Array.from(this.e.nodeName === 'TEMPLATE' ? this.e.content.childNodes : this.e.childNodes);
    }
    i(anchor) {
        for (let i = 0; i < this.n.length; i += 1) {
            insert(this.t, this.n[i], anchor);
        }
    }
    p(html) {
        this.d();
        this.h(html);
        this.i(this.a);
    }
    d() {
        this.n.forEach(detach);
    }
}
class HtmlTagHydration extends HtmlTag {
    constructor(claimed_nodes, is_svg = false) {
        super(is_svg);
        this.e = this.n = null;
        this.l = claimed_nodes;
    }
    c(html) {
        if (this.l) {
            this.n = this.l;
        }
        else {
            super.c(html);
        }
    }
    i(anchor) {
        for (let i = 0; i < this.n.length; i += 1) {
            insert_hydration(this.t, this.n[i], anchor);
        }
    }
}
function attribute_to_object(attributes) {
    const result = {};
    for (const attribute of attributes) {
        result[attribute.name] = attribute.value;
    }
    return result;
}
function get_custom_elements_slots(element) {
    const result = {};
    element.childNodes.forEach((node) => {
        result[node.slot || 'default'] = true;
    });
    return result;
}
function construct_svelte_component(component, props) {
    return new component(props);
}

// we need to store the information for multiple documents because a Svelte application could also contain iframes
// https://github.com/sveltejs/svelte/issues/3624
const managed_styles = new Map();
let active = 0;
// https://github.com/darkskyapp/string-hash/blob/master/index.js
function hash(str) {
    let hash = 5381;
    let i = str.length;
    while (i--)
        hash = ((hash << 5) - hash) ^ str.charCodeAt(i);
    return hash >>> 0;
}
function create_style_information(doc, node) {
    const info = { stylesheet: append_empty_stylesheet(node), rules: {} };
    managed_styles.set(doc, info);
    return info;
}
function create_rule(node, a, b, duration, delay, ease, fn, uid = 0) {
    const step = 16.666 / duration;
    let keyframes = '{\n';
    for (let p = 0; p <= 1; p += step) {
        const t = a + (b - a) * ease(p);
        keyframes += p * 100 + `%{${fn(t, 1 - t)}}\n`;
    }
    const rule = keyframes + `100% {${fn(b, 1 - b)}}\n}`;
    const name = `__svelte_${hash(rule)}_${uid}`;
    const doc = get_root_for_style(node);
    const { stylesheet, rules } = managed_styles.get(doc) || create_style_information(doc, node);
    if (!rules[name]) {
        rules[name] = true;
        stylesheet.insertRule(`@keyframes ${name} ${rule}`, stylesheet.cssRules.length);
    }
    const animation = node.style.animation || '';
    node.style.animation = `${animation ? `${animation}, ` : ''}${name} ${duration}ms linear ${delay}ms 1 both`;
    active += 1;
    return name;
}
function delete_rule(node, name) {
    const previous = (node.style.animation || '').split(', ');
    const next = previous.filter(name
        ? anim => anim.indexOf(name) < 0 // remove specific animation
        : anim => anim.indexOf('__svelte') === -1 // remove all Svelte animations
    );
    const deleted = previous.length - next.length;
    if (deleted) {
        node.style.animation = next.join(', ');
        active -= deleted;
        if (!active)
            clear_rules();
    }
}
function clear_rules() {
    raf(() => {
        if (active)
            return;
        managed_styles.forEach(info => {
            const { ownerNode } = info.stylesheet;
            // there is no ownerNode if it runs on jsdom.
            if (ownerNode)
                detach(ownerNode);
        });
        managed_styles.clear();
    });
}

function create_animation(node, from, fn, params) {
    if (!from)
        return noop;
    const to = node.getBoundingClientRect();
    if (from.left === to.left && from.right === to.right && from.top === to.top && from.bottom === to.bottom)
        return noop;
    const { delay = 0, duration = 300, easing = identity, 
    // @ts-ignore todo: should this be separated from destructuring? Or start/end added to public api and documentation?
    start: start_time = now() + delay, 
    // @ts-ignore todo:
    end = start_time + duration, tick = noop, css } = fn(node, { from, to }, params);
    let running = true;
    let started = false;
    let name;
    function start() {
        if (css) {
            name = create_rule(node, 0, 1, duration, delay, easing, css);
        }
        if (!delay) {
            started = true;
        }
    }
    function stop() {
        if (css)
            delete_rule(node, name);
        running = false;
    }
    loop(now => {
        if (!started && now >= start_time) {
            started = true;
        }
        if (started && now >= end) {
            tick(1, 0);
            stop();
        }
        if (!running) {
            return false;
        }
        if (started) {
            const p = now - start_time;
            const t = 0 + 1 * easing(p / duration);
            tick(t, 1 - t);
        }
        return true;
    });
    start();
    tick(0, 1);
    return stop;
}
function fix_position(node) {
    const style = getComputedStyle(node);
    if (style.position !== 'absolute' && style.position !== 'fixed') {
        const { width, height } = style;
        const a = node.getBoundingClientRect();
        node.style.position = 'absolute';
        node.style.width = width;
        node.style.height = height;
        add_transform(node, a);
    }
}
function add_transform(node, a) {
    const b = node.getBoundingClientRect();
    if (a.left !== b.left || a.top !== b.top) {
        const style = getComputedStyle(node);
        const transform = style.transform === 'none' ? '' : style.transform;
        node.style.transform = `${transform} translate(${a.left - b.left}px, ${a.top - b.top}px)`;
    }
}

let current_component;
function set_current_component(component) {
    current_component = component;
}
function get_current_component() {
    if (!current_component)
        throw new Error('Function called outside component initialization');
    return current_component;
}
/**
 * Schedules a callback to run immediately before the component is updated after any state change.
 *
 * The first time the callback runs will be before the initial `onMount`
 *
 * https://svelte.dev/docs#run-time-svelte-beforeupdate
 */
function beforeUpdate(fn) {
    get_current_component().$$.before_update.push(fn);
}
/**
 * The `onMount` function schedules a callback to run as soon as the component has been mounted to the DOM.
 * It must be called during the component's initialisation (but doesn't need to live *inside* the component;
 * it can be called from an external module).
 *
 * `onMount` does not run inside a [server-side component](/docs#run-time-server-side-component-api).
 *
 * https://svelte.dev/docs#run-time-svelte-onmount
 */
function onMount(fn) {
    get_current_component().$$.on_mount.push(fn);
}
/**
 * Schedules a callback to run immediately after the component has been updated.
 *
 * The first time the callback runs will be after the initial `onMount`
 */
function afterUpdate(fn) {
    get_current_component().$$.after_update.push(fn);
}
/**
 * Schedules a callback to run immediately before the component is unmounted.
 *
 * Out of `onMount`, `beforeUpdate`, `afterUpdate` and `onDestroy`, this is the
 * only one that runs inside a server-side component.
 *
 * https://svelte.dev/docs#run-time-svelte-ondestroy
 */
function onDestroy(fn) {
    get_current_component().$$.on_destroy.push(fn);
}
/**
 * Creates an event dispatcher that can be used to dispatch [component events](/docs#template-syntax-component-directives-on-eventname).
 * Event dispatchers are functions that can take two arguments: `name` and `detail`.
 *
 * Component events created with `createEventDispatcher` create a
 * [CustomEvent](https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent).
 * These events do not [bubble](https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Building_blocks/Events#Event_bubbling_and_capture).
 * The `detail` argument corresponds to the [CustomEvent.detail](https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/detail)
 * property and can contain any type of data.
 *
 * https://svelte.dev/docs#run-time-svelte-createeventdispatcher
 */
function createEventDispatcher() {
    const component = get_current_component();
    return (type, detail, { cancelable = false } = {}) => {
        const callbacks = component.$$.callbacks[type];
        if (callbacks) {
            // TODO are there situations where events could be dispatched
            // in a server (non-DOM) environment?
            const event = custom_event(type, detail, { cancelable });
            callbacks.slice().forEach(fn => {
                fn.call(component, event);
            });
            return !event.defaultPrevented;
        }
        return true;
    };
}
/**
 * Associates an arbitrary `context` object with the current component and the specified `key`
 * and returns that object. The context is then available to children of the component
 * (including slotted content) with `getContext`.
 *
 * Like lifecycle functions, this must be called during component initialisation.
 *
 * https://svelte.dev/docs#run-time-svelte-setcontext
 */
function setContext(key, context) {
    get_current_component().$$.context.set(key, context);
    return context;
}
/**
 * Retrieves the context that belongs to the closest parent component with the specified `key`.
 * Must be called during component initialisation.
 *
 * https://svelte.dev/docs#run-time-svelte-getcontext
 */
function getContext(key) {
    return get_current_component().$$.context.get(key);
}
/**
 * Retrieves the whole context map that belongs to the closest parent component.
 * Must be called during component initialisation. Useful, for example, if you
 * programmatically create a component and want to pass the existing context to it.
 *
 * https://svelte.dev/docs#run-time-svelte-getallcontexts
 */
function getAllContexts() {
    return get_current_component().$$.context;
}
/**
 * Checks whether a given `key` has been set in the context of a parent component.
 * Must be called during component initialisation.
 *
 * https://svelte.dev/docs#run-time-svelte-hascontext
 */
function hasContext(key) {
    return get_current_component().$$.context.has(key);
}
// TODO figure out if we still want to support
// shorthand events, or if we want to implement
// a real bubbling mechanism
function bubble(component, event) {
    const callbacks = component.$$.callbacks[event.type];
    if (callbacks) {
        // @ts-ignore
        callbacks.slice().forEach(fn => fn.call(this, event));
    }
}

const dirty_components = [];
const intros = { enabled: false };
const binding_callbacks = [];
let render_callbacks = [];
const flush_callbacks = [];
const resolved_promise = /* @__PURE__ */ Promise.resolve();
let update_scheduled = false;
function schedule_update() {
    if (!update_scheduled) {
        update_scheduled = true;
        resolved_promise.then(flush);
    }
}
function tick() {
    schedule_update();
    return resolved_promise;
}
function add_render_callback(fn) {
    render_callbacks.push(fn);
}
function add_flush_callback(fn) {
    flush_callbacks.push(fn);
}
// flush() calls callbacks in this order:
// 1. All beforeUpdate callbacks, in order: parents before children
// 2. All bind:this callbacks, in reverse order: children before parents.
// 3. All afterUpdate callbacks, in order: parents before children. EXCEPT
//    for afterUpdates called during the initial onMount, which are called in
//    reverse order: children before parents.
// Since callbacks might update component values, which could trigger another
// call to flush(), the following steps guard against this:
// 1. During beforeUpdate, any updated components will be added to the
//    dirty_components array and will cause a reentrant call to flush(). Because
//    the flush index is kept outside the function, the reentrant call will pick
//    up where the earlier call left off and go through all dirty components. The
//    current_component value is saved and restored so that the reentrant call will
//    not interfere with the "parent" flush() call.
// 2. bind:this callbacks cannot trigger new flush() calls.
// 3. During afterUpdate, any updated components will NOT have their afterUpdate
//    callback called a second time; the seen_callbacks set, outside the flush()
//    function, guarantees this behavior.
const seen_callbacks = new Set();
let flushidx = 0; // Do *not* move this inside the flush() function
function flush() {
    // Do not reenter flush while dirty components are updated, as this can
    // result in an infinite loop. Instead, let the inner flush handle it.
    // Reentrancy is ok afterwards for bindings etc.
    if (flushidx !== 0) {
        return;
    }
    const saved_component = current_component;
    do {
        // first, call beforeUpdate functions
        // and update components
        try {
            while (flushidx < dirty_components.length) {
                const component = dirty_components[flushidx];
                flushidx++;
                set_current_component(component);
                update(component.$$);
            }
        }
        catch (e) {
            // reset dirty state to not end up in a deadlocked state and then rethrow
            dirty_components.length = 0;
            flushidx = 0;
            throw e;
        }
        set_current_component(null);
        dirty_components.length = 0;
        flushidx = 0;
        while (binding_callbacks.length)
            binding_callbacks.pop()();
        // then, once components are updated, call
        // afterUpdate functions. This may cause
        // subsequent updates...
        for (let i = 0; i < render_callbacks.length; i += 1) {
            const callback = render_callbacks[i];
            if (!seen_callbacks.has(callback)) {
                // ...so guard against infinite loops
                seen_callbacks.add(callback);
                callback();
            }
        }
        render_callbacks.length = 0;
    } while (dirty_components.length);
    while (flush_callbacks.length) {
        flush_callbacks.pop()();
    }
    update_scheduled = false;
    seen_callbacks.clear();
    set_current_component(saved_component);
}
function update($$) {
    if ($$.fragment !== null) {
        $$.update();
        run_all($$.before_update);
        const dirty = $$.dirty;
        $$.dirty = [-1];
        $$.fragment && $$.fragment.p($$.ctx, dirty);
        $$.after_update.forEach(add_render_callback);
    }
}
/**
 * Useful for example to execute remaining `afterUpdate` callbacks before executing `destroy`.
 */
function flush_render_callbacks(fns) {
    const filtered = [];
    const targets = [];
    render_callbacks.forEach((c) => fns.indexOf(c) === -1 ? filtered.push(c) : targets.push(c));
    targets.forEach((c) => c());
    render_callbacks = filtered;
}

let promise;
function wait() {
    if (!promise) {
        promise = Promise.resolve();
        promise.then(() => {
            promise = null;
        });
    }
    return promise;
}
function dispatch(node, direction, kind) {
    node.dispatchEvent(custom_event(`${direction ? 'intro' : 'outro'}${kind}`));
}
const outroing = new Set();
let outros;
function group_outros() {
    outros = {
        r: 0,
        c: [],
        p: outros // parent group
    };
}
function check_outros() {
    if (!outros.r) {
        run_all(outros.c);
    }
    outros = outros.p;
}
function transition_in(block, local) {
    if (block && block.i) {
        outroing.delete(block);
        block.i(local);
    }
}
function transition_out(block, local, detach, callback) {
    if (block && block.o) {
        if (outroing.has(block))
            return;
        outroing.add(block);
        outros.c.push(() => {
            outroing.delete(block);
            if (callback) {
                if (detach)
                    block.d(1);
                callback();
            }
        });
        block.o(local);
    }
    else if (callback) {
        callback();
    }
}
const null_transition = { duration: 0 };
function create_in_transition(node, fn, params) {
    const options = { direction: 'in' };
    let config = fn(node, params, options);
    let running = false;
    let animation_name;
    let task;
    let uid = 0;
    function cleanup() {
        if (animation_name)
            delete_rule(node, animation_name);
    }
    function go() {
        const { delay = 0, duration = 300, easing = identity, tick = noop, css } = config || null_transition;
        if (css)
            animation_name = create_rule(node, 0, 1, duration, delay, easing, css, uid++);
        tick(0, 1);
        const start_time = now() + delay;
        const end_time = start_time + duration;
        if (task)
            task.abort();
        running = true;
        add_render_callback(() => dispatch(node, true, 'start'));
        task = loop(now => {
            if (running) {
                if (now >= end_time) {
                    tick(1, 0);
                    dispatch(node, true, 'end');
                    cleanup();
                    return running = false;
                }
                if (now >= start_time) {
                    const t = easing((now - start_time) / duration);
                    tick(t, 1 - t);
                }
            }
            return running;
        });
    }
    let started = false;
    return {
        start() {
            if (started)
                return;
            started = true;
            delete_rule(node);
            if (is_function(config)) {
                config = config(options);
                wait().then(go);
            }
            else {
                go();
            }
        },
        invalidate() {
            started = false;
        },
        end() {
            if (running) {
                cleanup();
                running = false;
            }
        }
    };
}
function create_out_transition(node, fn, params) {
    const options = { direction: 'out' };
    let config = fn(node, params, options);
    let running = true;
    let animation_name;
    const group = outros;
    group.r += 1;
    function go() {
        const { delay = 0, duration = 300, easing = identity, tick = noop, css } = config || null_transition;
        if (css)
            animation_name = create_rule(node, 1, 0, duration, delay, easing, css);
        const start_time = now() + delay;
        const end_time = start_time + duration;
        add_render_callback(() => dispatch(node, false, 'start'));
        loop(now => {
            if (running) {
                if (now >= end_time) {
                    tick(0, 1);
                    dispatch(node, false, 'end');
                    if (!--group.r) {
                        // this will result in `end()` being called,
                        // so we don't need to clean up here
                        run_all(group.c);
                    }
                    return false;
                }
                if (now >= start_time) {
                    const t = easing((now - start_time) / duration);
                    tick(1 - t, t);
                }
            }
            return running;
        });
    }
    if (is_function(config)) {
        wait().then(() => {
            // @ts-ignore
            config = config(options);
            go();
        });
    }
    else {
        go();
    }
    return {
        end(reset) {
            if (reset && config.tick) {
                config.tick(1, 0);
            }
            if (running) {
                if (animation_name)
                    delete_rule(node, animation_name);
                running = false;
            }
        }
    };
}
function create_bidirectional_transition(node, fn, params, intro) {
    const options = { direction: 'both' };
    let config = fn(node, params, options);
    let t = intro ? 0 : 1;
    let running_program = null;
    let pending_program = null;
    let animation_name = null;
    function clear_animation() {
        if (animation_name)
            delete_rule(node, animation_name);
    }
    function init(program, duration) {
        const d = (program.b - t);
        duration *= Math.abs(d);
        return {
            a: t,
            b: program.b,
            d,
            duration,
            start: program.start,
            end: program.start + duration,
            group: program.group
        };
    }
    function go(b) {
        const { delay = 0, duration = 300, easing = identity, tick = noop, css } = config || null_transition;
        const program = {
            start: now() + delay,
            b
        };
        if (!b) {
            // @ts-ignore todo: improve typings
            program.group = outros;
            outros.r += 1;
        }
        if (running_program || pending_program) {
            pending_program = program;
        }
        else {
            // if this is an intro, and there's a delay, we need to do
            // an initial tick and/or apply CSS animation immediately
            if (css) {
                clear_animation();
                animation_name = create_rule(node, t, b, duration, delay, easing, css);
            }
            if (b)
                tick(0, 1);
            running_program = init(program, duration);
            add_render_callback(() => dispatch(node, b, 'start'));
            loop(now => {
                if (pending_program && now > pending_program.start) {
                    running_program = init(pending_program, duration);
                    pending_program = null;
                    dispatch(node, running_program.b, 'start');
                    if (css) {
                        clear_animation();
                        animation_name = create_rule(node, t, running_program.b, running_program.duration, 0, easing, config.css);
                    }
                }
                if (running_program) {
                    if (now >= running_program.end) {
                        tick(t = running_program.b, 1 - t);
                        dispatch(node, running_program.b, 'end');
                        if (!pending_program) {
                            // we're done
                            if (running_program.b) {
                                // intro — we can tidy up immediately
                                clear_animation();
                            }
                            else {
                                // outro — needs to be coordinated
                                if (!--running_program.group.r)
                                    run_all(running_program.group.c);
                            }
                        }
                        running_program = null;
                    }
                    else if (now >= running_program.start) {
                        const p = now - running_program.start;
                        t = running_program.a + running_program.d * easing(p / running_program.duration);
                        tick(t, 1 - t);
                    }
                }
                return !!(running_program || pending_program);
            });
        }
    }
    return {
        run(b) {
            if (is_function(config)) {
                wait().then(() => {
                    // @ts-ignore
                    config = config(options);
                    go(b);
                });
            }
            else {
                go(b);
            }
        },
        end() {
            clear_animation();
            running_program = pending_program = null;
        }
    };
}

function handle_promise(promise, info) {
    const token = info.token = {};
    function update(type, index, key, value) {
        if (info.token !== token)
            return;
        info.resolved = value;
        let child_ctx = info.ctx;
        if (key !== undefined) {
            child_ctx = child_ctx.slice();
            child_ctx[key] = value;
        }
        const block = type && (info.current = type)(child_ctx);
        let needs_flush = false;
        if (info.block) {
            if (info.blocks) {
                info.blocks.forEach((block, i) => {
                    if (i !== index && block) {
                        group_outros();
                        transition_out(block, 1, 1, () => {
                            if (info.blocks[i] === block) {
                                info.blocks[i] = null;
                            }
                        });
                        check_outros();
                    }
                });
            }
            else {
                info.block.d(1);
            }
            block.c();
            transition_in(block, 1);
            block.m(info.mount(), info.anchor);
            needs_flush = true;
        }
        info.block = block;
        if (info.blocks)
            info.blocks[index] = block;
        if (needs_flush) {
            flush();
        }
    }
    if (is_promise(promise)) {
        const current_component = get_current_component();
        promise.then(value => {
            set_current_component(current_component);
            update(info.then, 1, info.value, value);
            set_current_component(null);
        }, error => {
            set_current_component(current_component);
            update(info.catch, 2, info.error, error);
            set_current_component(null);
            if (!info.hasCatch) {
                throw error;
            }
        });
        // if we previously had a then/catch block, destroy it
        if (info.current !== info.pending) {
            update(info.pending, 0);
            return true;
        }
    }
    else {
        if (info.current !== info.then) {
            update(info.then, 1, info.value, promise);
            return true;
        }
        info.resolved = promise;
    }
}
function update_await_block_branch(info, ctx, dirty) {
    const child_ctx = ctx.slice();
    const { resolved } = info;
    if (info.current === info.then) {
        child_ctx[info.value] = resolved;
    }
    if (info.current === info.catch) {
        child_ctx[info.error] = resolved;
    }
    info.block.p(child_ctx, dirty);
}

const globals = (typeof window !== 'undefined'
    ? window
    : typeof globalThis !== 'undefined'
        ? globalThis
        : global);

function destroy_block(block, lookup) {
    block.d(1);
    lookup.delete(block.key);
}
function outro_and_destroy_block(block, lookup) {
    transition_out(block, 1, 1, () => {
        lookup.delete(block.key);
    });
}
function fix_and_destroy_block(block, lookup) {
    block.f();
    destroy_block(block, lookup);
}
function fix_and_outro_and_destroy_block(block, lookup) {
    block.f();
    outro_and_destroy_block(block, lookup);
}
function update_keyed_each(old_blocks, dirty, get_key, dynamic, ctx, list, lookup, node, destroy, create_each_block, next, get_context) {
    let o = old_blocks.length;
    let n = list.length;
    let i = o;
    const old_indexes = {};
    while (i--)
        old_indexes[old_blocks[i].key] = i;
    const new_blocks = [];
    const new_lookup = new Map();
    const deltas = new Map();
    const updates = [];
    i = n;
    while (i--) {
        const child_ctx = get_context(ctx, list, i);
        const key = get_key(child_ctx);
        let block = lookup.get(key);
        if (!block) {
            block = create_each_block(key, child_ctx);
            block.c();
        }
        else if (dynamic) {
            // defer updates until all the DOM shuffling is done
            updates.push(() => block.p(child_ctx, dirty));
        }
        new_lookup.set(key, new_blocks[i] = block);
        if (key in old_indexes)
            deltas.set(key, Math.abs(i - old_indexes[key]));
    }
    const will_move = new Set();
    const did_move = new Set();
    function insert(block) {
        transition_in(block, 1);
        block.m(node, next);
        lookup.set(block.key, block);
        next = block.first;
        n--;
    }
    while (o && n) {
        const new_block = new_blocks[n - 1];
        const old_block = old_blocks[o - 1];
        const new_key = new_block.key;
        const old_key = old_block.key;
        if (new_block === old_block) {
            // do nothing
            next = new_block.first;
            o--;
            n--;
        }
        else if (!new_lookup.has(old_key)) {
            // remove old block
            destroy(old_block, lookup);
            o--;
        }
        else if (!lookup.has(new_key) || will_move.has(new_key)) {
            insert(new_block);
        }
        else if (did_move.has(old_key)) {
            o--;
        }
        else if (deltas.get(new_key) > deltas.get(old_key)) {
            did_move.add(new_key);
            insert(new_block);
        }
        else {
            will_move.add(old_key);
            o--;
        }
    }
    while (o--) {
        const old_block = old_blocks[o];
        if (!new_lookup.has(old_block.key))
            destroy(old_block, lookup);
    }
    while (n)
        insert(new_blocks[n - 1]);
    run_all(updates);
    return new_blocks;
}
function validate_each_keys(ctx, list, get_context, get_key) {
    const keys = new Set();
    for (let i = 0; i < list.length; i++) {
        const key = get_key(get_context(ctx, list, i));
        if (keys.has(key)) {
            throw new Error('Cannot have duplicate keys in a keyed each');
        }
        keys.add(key);
    }
}

function get_spread_update(levels, updates) {
    const update = {};
    const to_null_out = {};
    const accounted_for = { $$scope: 1 };
    let i = levels.length;
    while (i--) {
        const o = levels[i];
        const n = updates[i];
        if (n) {
            for (const key in o) {
                if (!(key in n))
                    to_null_out[key] = 1;
            }
            for (const key in n) {
                if (!accounted_for[key]) {
                    update[key] = n[key];
                    accounted_for[key] = 1;
                }
            }
            levels[i] = n;
        }
        else {
            for (const key in o) {
                accounted_for[key] = 1;
            }
        }
    }
    for (const key in to_null_out) {
        if (!(key in update))
            update[key] = undefined;
    }
    return update;
}
function get_spread_object(spread_props) {
    return typeof spread_props === 'object' && spread_props !== null ? spread_props : {};
}

const _boolean_attributes = [
    'allowfullscreen',
    'allowpaymentrequest',
    'async',
    'autofocus',
    'autoplay',
    'checked',
    'controls',
    'default',
    'defer',
    'disabled',
    'formnovalidate',
    'hidden',
    'inert',
    'ismap',
    'itemscope',
    'loop',
    'multiple',
    'muted',
    'nomodule',
    'novalidate',
    'open',
    'playsinline',
    'readonly',
    'required',
    'reversed',
    'selected'
];
/**
 * List of HTML boolean attributes (e.g. `<input disabled>`).
 * Source: https://html.spec.whatwg.org/multipage/indices.html
 */
const boolean_attributes = new Set([..._boolean_attributes]);

/** regex of all html void element names */
const void_element_names = /^(?:area|base|br|col|command|embed|hr|img|input|keygen|link|meta|param|source|track|wbr)$/;
function is_void(name) {
    return void_element_names.test(name) || name.toLowerCase() === '!doctype';
}

const invalid_attribute_name_character = /[\s'">/=\u{FDD0}-\u{FDEF}\u{FFFE}\u{FFFF}\u{1FFFE}\u{1FFFF}\u{2FFFE}\u{2FFFF}\u{3FFFE}\u{3FFFF}\u{4FFFE}\u{4FFFF}\u{5FFFE}\u{5FFFF}\u{6FFFE}\u{6FFFF}\u{7FFFE}\u{7FFFF}\u{8FFFE}\u{8FFFF}\u{9FFFE}\u{9FFFF}\u{AFFFE}\u{AFFFF}\u{BFFFE}\u{BFFFF}\u{CFFFE}\u{CFFFF}\u{DFFFE}\u{DFFFF}\u{EFFFE}\u{EFFFF}\u{FFFFE}\u{FFFFF}\u{10FFFE}\u{10FFFF}]/u;
// https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
// https://infra.spec.whatwg.org/#noncharacter
function spread(args, attrs_to_add) {
    const attributes = Object.assign({}, ...args);
    if (attrs_to_add) {
        const classes_to_add = attrs_to_add.classes;
        const styles_to_add = attrs_to_add.styles;
        if (classes_to_add) {
            if (attributes.class == null) {
                attributes.class = classes_to_add;
            }
            else {
                attributes.class += ' ' + classes_to_add;
            }
        }
        if (styles_to_add) {
            if (attributes.style == null) {
                attributes.style = style_object_to_string(styles_to_add);
            }
            else {
                attributes.style = style_object_to_string(merge_ssr_styles(attributes.style, styles_to_add));
            }
        }
    }
    let str = '';
    Object.keys(attributes).forEach(name => {
        if (invalid_attribute_name_character.test(name))
            return;
        const value = attributes[name];
        if (value === true)
            str += ' ' + name;
        else if (boolean_attributes.has(name.toLowerCase())) {
            if (value)
                str += ' ' + name;
        }
        else if (value != null) {
            str += ` ${name}="${value}"`;
        }
    });
    return str;
}
function merge_ssr_styles(style_attribute, style_directive) {
    const style_object = {};
    for (const individual_style of style_attribute.split(';')) {
        const colon_index = individual_style.indexOf(':');
        const name = individual_style.slice(0, colon_index).trim();
        const value = individual_style.slice(colon_index + 1).trim();
        if (!name)
            continue;
        style_object[name] = value;
    }
    for (const name in style_directive) {
        const value = style_directive[name];
        if (value) {
            style_object[name] = value;
        }
        else {
            delete style_object[name];
        }
    }
    return style_object;
}
const ATTR_REGEX = /[&"]/g;
const CONTENT_REGEX = /[&<]/g;
/**
 * Note: this method is performance sensitive and has been optimized
 * https://github.com/sveltejs/svelte/pull/5701
 */
function escape(value, is_attr = false) {
    const str = String(value);
    const pattern = is_attr ? ATTR_REGEX : CONTENT_REGEX;
    pattern.lastIndex = 0;
    let escaped = '';
    let last = 0;
    while (pattern.test(str)) {
        const i = pattern.lastIndex - 1;
        const ch = str[i];
        escaped += str.substring(last, i) + (ch === '&' ? '&amp;' : (ch === '"' ? '&quot;' : '&lt;'));
        last = i + 1;
    }
    return escaped + str.substring(last);
}
function escape_attribute_value(value) {
    // keep booleans, null, and undefined for the sake of `spread`
    const should_escape = typeof value === 'string' || (value && typeof value === 'object');
    return should_escape ? escape(value, true) : value;
}
function escape_object(obj) {
    const result = {};
    for (const key in obj) {
        result[key] = escape_attribute_value(obj[key]);
    }
    return result;
}
function each(items, fn) {
    let str = '';
    for (let i = 0; i < items.length; i += 1) {
        str += fn(items[i], i);
    }
    return str;
}
const missing_component = {
    $$render: () => ''
};
function validate_component(component, name) {
    if (!component || !component.$$render) {
        if (name === 'svelte:component')
            name += ' this={...}';
        throw new Error(`<${name}> is not a valid SSR component. You may need to review your build config to ensure that dependencies are compiled, rather than imported as pre-compiled modules. Otherwise you may need to fix a <${name}>.`);
    }
    return component;
}
function debug(file, line, column, values) {
    console.log(`{@debug} ${file ? file + ' ' : ''}(${line}:${column})`); // eslint-disable-line no-console
    console.log(values); // eslint-disable-line no-console
    return '';
}
let on_destroy;
function create_ssr_component(fn) {
    function $$render(result, props, bindings, slots, context) {
        const parent_component = current_component;
        const $$ = {
            on_destroy,
            context: new Map(context || (parent_component ? parent_component.$$.context : [])),
            // these will be immediately discarded
            on_mount: [],
            before_update: [],
            after_update: [],
            callbacks: blank_object()
        };
        set_current_component({ $$ });
        const html = fn(result, props, bindings, slots);
        set_current_component(parent_component);
        return html;
    }
    return {
        render: (props = {}, { $$slots = {}, context = new Map() } = {}) => {
            on_destroy = [];
            const result = { title: '', head: '', css: new Set() };
            const html = $$render(result, props, {}, $$slots, context);
            run_all(on_destroy);
            return {
                html,
                css: {
                    code: Array.from(result.css).map(css => css.code).join('\n'),
                    map: null // TODO
                },
                head: result.title + result.head
            };
        },
        $$render
    };
}
function add_attribute(name, value, boolean) {
    if (value == null || (boolean && !value))
        return '';
    const assignment = (boolean && value === true) ? '' : `="${escape(value, true)}"`;
    return ` ${name}${assignment}`;
}
function add_classes(classes) {
    return classes ? ` class="${classes}"` : '';
}
function style_object_to_string(style_object) {
    return Object.keys(style_object)
        .filter(key => style_object[key])
        .map(key => `${key}: ${escape_attribute_value(style_object[key])};`)
        .join(' ');
}
function add_styles(style_object) {
    const styles = style_object_to_string(style_object);
    return styles ? ` style="${styles}"` : '';
}

function bind(component, name, callback) {
    const index = component.$$.props[name];
    if (index !== undefined) {
        component.$$.bound[index] = callback;
        callback(component.$$.ctx[index]);
    }
}
function create_component(block) {
    block && block.c();
}
function claim_component(block, parent_nodes) {
    block && block.l(parent_nodes);
}
function mount_component(component, target, anchor, customElement) {
    const { fragment, after_update } = component.$$;
    fragment && fragment.m(target, anchor);
    if (!customElement) {
        // onMount happens before the initial afterUpdate
        add_render_callback(() => {
            const new_on_destroy = component.$$.on_mount.map(run).filter(is_function);
            // if the component was destroyed immediately
            // it will update the `$$.on_destroy` reference to `null`.
            // the destructured on_destroy may still reference to the old array
            if (component.$$.on_destroy) {
                component.$$.on_destroy.push(...new_on_destroy);
            }
            else {
                // Edge case - component was destroyed immediately,
                // most likely as a result of a binding initialising
                run_all(new_on_destroy);
            }
            component.$$.on_mount = [];
        });
    }
    after_update.forEach(add_render_callback);
}
function destroy_component(component, detaching) {
    const $$ = component.$$;
    if ($$.fragment !== null) {
        flush_render_callbacks($$.after_update);
        run_all($$.on_destroy);
        $$.fragment && $$.fragment.d(detaching);
        // TODO null out other refs, including component.$$ (but need to
        // preserve final state?)
        $$.on_destroy = $$.fragment = null;
        $$.ctx = [];
    }
}
function make_dirty(component, i) {
    if (component.$$.dirty[0] === -1) {
        dirty_components.push(component);
        schedule_update();
        component.$$.dirty.fill(0);
    }
    component.$$.dirty[(i / 31) | 0] |= (1 << (i % 31));
}
function init(component, options, instance, create_fragment, not_equal, props, append_styles, dirty = [-1]) {
    const parent_component = current_component;
    set_current_component(component);
    const $$ = component.$$ = {
        fragment: null,
        ctx: [],
        // state
        props,
        update: noop,
        not_equal,
        bound: blank_object(),
        // lifecycle
        on_mount: [],
        on_destroy: [],
        on_disconnect: [],
        before_update: [],
        after_update: [],
        context: new Map(options.context || (parent_component ? parent_component.$$.context : [])),
        // everything else
        callbacks: blank_object(),
        dirty,
        skip_bound: false,
        root: options.target || parent_component.$$.root
    };
    append_styles && append_styles($$.root);
    let ready = false;
    $$.ctx = instance
        ? instance(component, options.props || {}, (i, ret, ...rest) => {
            const value = rest.length ? rest[0] : ret;
            if ($$.ctx && not_equal($$.ctx[i], $$.ctx[i] = value)) {
                if (!$$.skip_bound && $$.bound[i])
                    $$.bound[i](value);
                if (ready)
                    make_dirty(component, i);
            }
            return ret;
        })
        : [];
    $$.update();
    ready = true;
    run_all($$.before_update);
    // `false` as a special case of no DOM component
    $$.fragment = create_fragment ? create_fragment($$.ctx) : false;
    if (options.target) {
        if (options.hydrate) {
            start_hydrating();
            const nodes = children(options.target);
            // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
            $$.fragment && $$.fragment.l(nodes);
            nodes.forEach(detach);
        }
        else {
            // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
            $$.fragment && $$.fragment.c();
        }
        if (options.intro)
            transition_in(component.$$.fragment);
        mount_component(component, options.target, options.anchor, options.customElement);
        end_hydrating();
        flush();
    }
    set_current_component(parent_component);
}
let SvelteElement;
if (typeof HTMLElement === 'function') {
    SvelteElement = class extends HTMLElement {
        constructor() {
            super();
            this.attachShadow({ mode: 'open' });
        }
        connectedCallback() {
            const { on_mount } = this.$$;
            this.$$.on_disconnect = on_mount.map(run).filter(is_function);
            // @ts-ignore todo: improve typings
            for (const key in this.$$.slotted) {
                // @ts-ignore todo: improve typings
                this.appendChild(this.$$.slotted[key]);
            }
        }
        attributeChangedCallback(attr, _oldValue, newValue) {
            this[attr] = newValue;
        }
        disconnectedCallback() {
            run_all(this.$$.on_disconnect);
        }
        $destroy() {
            destroy_component(this, 1);
            this.$destroy = noop;
        }
        $on(type, callback) {
            // TODO should this delegate to addEventListener?
            if (!is_function(callback)) {
                return noop;
            }
            const callbacks = (this.$$.callbacks[type] || (this.$$.callbacks[type] = []));
            callbacks.push(callback);
            return () => {
                const index = callbacks.indexOf(callback);
                if (index !== -1)
                    callbacks.splice(index, 1);
            };
        }
        $set($$props) {
            if (this.$$set && !is_empty($$props)) {
                this.$$.skip_bound = true;
                this.$$set($$props);
                this.$$.skip_bound = false;
            }
        }
    };
}
/**
 * Base class for Svelte components. Used when dev=false.
 */
class SvelteComponent {
    $destroy() {
        destroy_component(this, 1);
        this.$destroy = noop;
    }
    $on(type, callback) {
        if (!is_function(callback)) {
            return noop;
        }
        const callbacks = (this.$$.callbacks[type] || (this.$$.callbacks[type] = []));
        callbacks.push(callback);
        return () => {
            const index = callbacks.indexOf(callback);
            if (index !== -1)
                callbacks.splice(index, 1);
        };
    }
    $set($$props) {
        if (this.$$set && !is_empty($$props)) {
            this.$$.skip_bound = true;
            this.$$set($$props);
            this.$$.skip_bound = false;
        }
    }
}

function dispatch_dev(type, detail) {
    document.dispatchEvent(custom_event(type, Object.assign({ version: '3.56.0' }, detail), { bubbles: true }));
}
function append_dev(target, node) {
    dispatch_dev('SvelteDOMInsert', { target, node });
    append(target, node);
}
function append_hydration_dev(target, node) {
    dispatch_dev('SvelteDOMInsert', { target, node });
    append_hydration(target, node);
}
function insert_dev(target, node, anchor) {
    dispatch_dev('SvelteDOMInsert', { target, node, anchor });
    insert(target, node, anchor);
}
function insert_hydration_dev(target, node, anchor) {
    dispatch_dev('SvelteDOMInsert', { target, node, anchor });
    insert_hydration(target, node, anchor);
}
function detach_dev(node) {
    dispatch_dev('SvelteDOMRemove', { node });
    detach(node);
}
function detach_between_dev(before, after) {
    while (before.nextSibling && before.nextSibling !== after) {
        detach_dev(before.nextSibling);
    }
}
function detach_before_dev(after) {
    while (after.previousSibling) {
        detach_dev(after.previousSibling);
    }
}
function detach_after_dev(before) {
    while (before.nextSibling) {
        detach_dev(before.nextSibling);
    }
}
function listen_dev(node, event, handler, options, has_prevent_default, has_stop_propagation, has_stop_immediate_propagation) {
    const modifiers = options === true ? ['capture'] : options ? Array.from(Object.keys(options)) : [];
    if (has_prevent_default)
        modifiers.push('preventDefault');
    if (has_stop_propagation)
        modifiers.push('stopPropagation');
    if (has_stop_immediate_propagation)
        modifiers.push('stopImmediatePropagation');
    dispatch_dev('SvelteDOMAddEventListener', { node, event, handler, modifiers });
    const dispose = listen(node, event, handler, options);
    return () => {
        dispatch_dev('SvelteDOMRemoveEventListener', { node, event, handler, modifiers });
        dispose();
    };
}
function attr_dev(node, attribute, value) {
    attr(node, attribute, value);
    if (value == null)
        dispatch_dev('SvelteDOMRemoveAttribute', { node, attribute });
    else
        dispatch_dev('SvelteDOMSetAttribute', { node, attribute, value });
}
function prop_dev(node, property, value) {
    node[property] = value;
    dispatch_dev('SvelteDOMSetProperty', { node, property, value });
}
function dataset_dev(node, property, value) {
    node.dataset[property] = value;
    dispatch_dev('SvelteDOMSetDataset', { node, property, value });
}
function set_data_dev(text, data) {
    data = '' + data;
    if (text.wholeText === data)
        return;
    dispatch_dev('SvelteDOMSetData', { node: text, data });
    text.data = data;
}
function validate_each_argument(arg) {
    if (typeof arg !== 'string' && !(arg && typeof arg === 'object' && 'length' in arg)) {
        let msg = '{#each} only iterates over array-like objects.';
        if (typeof Symbol === 'function' && arg && Symbol.iterator in arg) {
            msg += ' You can use a spread to convert this iterable into an array.';
        }
        throw new Error(msg);
    }
}
function validate_slots(name, slot, keys) {
    for (const slot_key of Object.keys(slot)) {
        if (!~keys.indexOf(slot_key)) {
            console.warn(`<${name}> received an unexpected slot "${slot_key}".`);
        }
    }
}
function validate_dynamic_element(tag) {
    const is_string = typeof tag === 'string';
    if (tag && !is_string) {
        throw new Error('<svelte:element> expects "this" attribute to be a string.');
    }
}
function validate_void_dynamic_element(tag) {
    if (tag && is_void(tag)) {
        console.warn(`<svelte:element this="${tag}"> is self-closing and cannot have content.`);
    }
}
function construct_svelte_component_dev(component, props) {
    const error_message = 'this={...} of <svelte:component> should specify a Svelte component.';
    try {
        const instance = new component(props);
        if (!instance.$$ || !instance.$set || !instance.$on || !instance.$destroy) {
            throw new Error(error_message);
        }
        return instance;
    }
    catch (err) {
        const { message } = err;
        if (typeof message === 'string' && message.indexOf('is not a constructor') !== -1) {
            throw new Error(error_message);
        }
        else {
            throw err;
        }
    }
}
/**
 * Base class for Svelte components with some minor dev-enhancements. Used when dev=true.
 */
class SvelteComponentDev extends SvelteComponent {
    constructor(options) {
        if (!options || (!options.target && !options.$$inline)) {
            throw new Error("'target' is a required option");
        }
        super();
    }
    $destroy() {
        super.$destroy();
        this.$destroy = () => {
            console.warn('Component was already destroyed'); // eslint-disable-line no-console
        };
    }
    $capture_state() { }
    $inject_state() { }
}
/**
 * Base class to create strongly typed Svelte components.
 * This only exists for typing purposes and should be used in `.d.ts` files.
 *
 * ### Example:
 *
 * You have component library on npm called `component-library`, from which
 * you export a component called `MyComponent`. For Svelte+TypeScript users,
 * you want to provide typings. Therefore you create a `index.d.ts`:
 * ```ts
 * import { SvelteComponentTyped } from "svelte";
 * export class MyComponent extends SvelteComponentTyped<{foo: string}> {}
 * ```
 * Typing this makes it possible for IDEs like VS Code with the Svelte extension
 * to provide intellisense and to use the component like this in a Svelte file
 * with TypeScript:
 * ```svelte
 * <script lang="ts">
 * 	import { MyComponent } from "component-library";
 * </script>
 * <MyComponent foo={'bar'} />
 * ```
 *
 * #### Why not make this part of `SvelteComponent(Dev)`?
 * Because
 * ```ts
 * class ASubclassOfSvelteComponent extends SvelteComponent<{foo: string}> {}
 * const component: typeof SvelteComponent = ASubclassOfSvelteComponent;
 * ```
 * will throw a type error, so we need to separate the more strictly typed class.
 */
class SvelteComponentTyped extends SvelteComponentDev {
    constructor(options) {
        super(options);
    }
}
function loop_guard(timeout) {
    const start = Date.now();
    return () => {
        if (Date.now() - start > timeout) {
            throw new Error('Infinite loop detected');
        }
    };
}




/***/ }),

/***/ "./node_modules/svelte/store/index.mjs":
/*!*********************************************!*\
  !*** ./node_modules/svelte/store/index.mjs ***!
  \*********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "derived": () => (/* binding */ derived),
/* harmony export */   "get": () => (/* reexport safe */ _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.get_store_value),
/* harmony export */   "readable": () => (/* binding */ readable),
/* harmony export */   "readonly": () => (/* binding */ readonly),
/* harmony export */   "writable": () => (/* binding */ writable)
/* harmony export */ });
/* harmony import */ var _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../internal/index.mjs */ "./node_modules/svelte/internal/index.mjs");



const subscriber_queue = [];
/**
 * Creates a `Readable` store that allows reading by subscription.
 * @param value initial value
 * @param {StartStopNotifier}start start and stop notifications for subscriptions
 */
function readable(value, start) {
    return {
        subscribe: writable(value, start).subscribe
    };
}
/**
 * Create a `Writable` store that allows both updating and reading by subscription.
 * @param {*=}value initial value
 * @param {StartStopNotifier=}start start and stop notifications for subscriptions
 */
function writable(value, start = _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.noop) {
    let stop;
    const subscribers = new Set();
    function set(new_value) {
        if ((0,_internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.safe_not_equal)(value, new_value)) {
            value = new_value;
            if (stop) { // store is ready
                const run_queue = !subscriber_queue.length;
                for (const subscriber of subscribers) {
                    subscriber[1]();
                    subscriber_queue.push(subscriber, value);
                }
                if (run_queue) {
                    for (let i = 0; i < subscriber_queue.length; i += 2) {
                        subscriber_queue[i][0](subscriber_queue[i + 1]);
                    }
                    subscriber_queue.length = 0;
                }
            }
        }
    }
    function update(fn) {
        set(fn(value));
    }
    function subscribe(run, invalidate = _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.noop) {
        const subscriber = [run, invalidate];
        subscribers.add(subscriber);
        if (subscribers.size === 1) {
            stop = start(set) || _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.noop;
        }
        run(value);
        return () => {
            subscribers.delete(subscriber);
            if (subscribers.size === 0 && stop) {
                stop();
                stop = null;
            }
        };
    }
    return { set, update, subscribe };
}
function derived(stores, fn, initial_value) {
    const single = !Array.isArray(stores);
    const stores_array = single
        ? [stores]
        : stores;
    const auto = fn.length < 2;
    return readable(initial_value, (set) => {
        let inited = false;
        const values = [];
        let pending = 0;
        let cleanup = _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.noop;
        const sync = () => {
            if (pending) {
                return;
            }
            cleanup();
            const result = fn(single ? values[0] : values, set);
            if (auto) {
                set(result);
            }
            else {
                cleanup = (0,_internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.is_function)(result) ? result : _internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.noop;
            }
        };
        const unsubscribers = stores_array.map((store, i) => (0,_internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.subscribe)(store, (value) => {
            values[i] = value;
            pending &= ~(1 << i);
            if (inited) {
                sync();
            }
        }, () => {
            pending |= (1 << i);
        }));
        inited = true;
        sync();
        return function stop() {
            (0,_internal_index_mjs__WEBPACK_IMPORTED_MODULE_0__.run_all)(unsubscribers);
            cleanup();
        };
    });
}
/**
 * Takes a store and returns a new one derived from the old one that is readable.
 *
 * @param store - store to make readonly
 */
function readonly(store) {
    return {
        subscribe: store.subscribe.bind(store)
    };
}




/***/ }),

/***/ "./node_modules/svelte/transition/index.mjs":
/*!**************************************************!*\
  !*** ./node_modules/svelte/transition/index.mjs ***!
  \**************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "blur": () => (/* binding */ blur),
/* harmony export */   "crossfade": () => (/* binding */ crossfade),
/* harmony export */   "draw": () => (/* binding */ draw),
/* harmony export */   "fade": () => (/* binding */ fade),
/* harmony export */   "fly": () => (/* binding */ fly),
/* harmony export */   "scale": () => (/* binding */ scale),
/* harmony export */   "slide": () => (/* binding */ slide)
/* harmony export */ });
/* harmony import */ var _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../easing/index.mjs */ "./node_modules/svelte/easing/index.mjs");
/* harmony import */ var _internal_index_mjs__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../internal/index.mjs */ "./node_modules/svelte/internal/index.mjs");



/******************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */

function __rest(s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) {
            if (e.indexOf(p[i]) < 0 && Object.prototype.propertyIsEnumerable.call(s, p[i]))
                t[p[i]] = s[p[i]];
        }
    return t;
}

function blur(node, { delay = 0, duration = 400, easing = _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__.cubicInOut, amount = 5, opacity = 0 } = {}) {
    const style = getComputedStyle(node);
    const target_opacity = +style.opacity;
    const f = style.filter === 'none' ? '' : style.filter;
    const od = target_opacity * (1 - opacity);
    return {
        delay,
        duration,
        easing,
        css: (_t, u) => `opacity: ${target_opacity - (od * u)}; filter: ${f} blur(${u * amount}px);`
    };
}
function fade(node, { delay = 0, duration = 400, easing = _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__.linear } = {}) {
    const o = +getComputedStyle(node).opacity;
    return {
        delay,
        duration,
        easing,
        css: t => `opacity: ${t * o}`
    };
}
function fly(node, { delay = 0, duration = 400, easing = _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__.cubicOut, x = 0, y = 0, opacity = 0 } = {}) {
    const style = getComputedStyle(node);
    const target_opacity = +style.opacity;
    const transform = style.transform === 'none' ? '' : style.transform;
    const od = target_opacity * (1 - opacity);
    return {
        delay,
        duration,
        easing,
        css: (t, u) => `
			transform: ${transform} translate(${(1 - t) * x}px, ${(1 - t) * y}px);
			opacity: ${target_opacity - (od * u)}`
    };
}
function slide(node, { delay = 0, duration = 400, easing = _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__.cubicOut, axis = 'y' } = {}) {
    const style = getComputedStyle(node);
    const opacity = +style.opacity;
    const primary_property = axis === 'y' ? 'height' : 'width';
    const primary_property_value = parseFloat(style[primary_property]);
    const secondary_properties = axis === 'y' ? ['top', 'bottom'] : ['left', 'right'];
    const capitalized_secondary_properties = secondary_properties.map((e) => `${e[0].toUpperCase()}${e.slice(1)}`);
    const padding_start_value = parseFloat(style[`padding${capitalized_secondary_properties[0]}`]);
    const padding_end_value = parseFloat(style[`padding${capitalized_secondary_properties[1]}`]);
    const margin_start_value = parseFloat(style[`margin${capitalized_secondary_properties[0]}`]);
    const margin_end_value = parseFloat(style[`margin${capitalized_secondary_properties[1]}`]);
    const border_width_start_value = parseFloat(style[`border${capitalized_secondary_properties[0]}Width`]);
    const border_width_end_value = parseFloat(style[`border${capitalized_secondary_properties[1]}Width`]);
    return {
        delay,
        duration,
        easing,
        css: t => 'overflow: hidden;' +
            `opacity: ${Math.min(t * 20, 1) * opacity};` +
            `${primary_property}: ${t * primary_property_value}px;` +
            `padding-${secondary_properties[0]}: ${t * padding_start_value}px;` +
            `padding-${secondary_properties[1]}: ${t * padding_end_value}px;` +
            `margin-${secondary_properties[0]}: ${t * margin_start_value}px;` +
            `margin-${secondary_properties[1]}: ${t * margin_end_value}px;` +
            `border-${secondary_properties[0]}-width: ${t * border_width_start_value}px;` +
            `border-${secondary_properties[1]}-width: ${t * border_width_end_value}px;`
    };
}
function scale(node, { delay = 0, duration = 400, easing = _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__.cubicOut, start = 0, opacity = 0 } = {}) {
    const style = getComputedStyle(node);
    const target_opacity = +style.opacity;
    const transform = style.transform === 'none' ? '' : style.transform;
    const sd = 1 - start;
    const od = target_opacity * (1 - opacity);
    return {
        delay,
        duration,
        easing,
        css: (_t, u) => `
			transform: ${transform} scale(${1 - (sd * u)});
			opacity: ${target_opacity - (od * u)}
		`
    };
}
function draw(node, { delay = 0, speed, duration, easing = _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__.cubicInOut } = {}) {
    let len = node.getTotalLength();
    const style = getComputedStyle(node);
    if (style.strokeLinecap !== 'butt') {
        len += parseInt(style.strokeWidth);
    }
    if (duration === undefined) {
        if (speed === undefined) {
            duration = 800;
        }
        else {
            duration = len / speed;
        }
    }
    else if (typeof duration === 'function') {
        duration = duration(len);
    }
    return {
        delay,
        duration,
        easing,
        css: (_, u) => `
			stroke-dasharray: ${len};
			stroke-dashoffset: ${u * len};
		`
    };
}
function crossfade(_a) {
    var { fallback } = _a, defaults = __rest(_a, ["fallback"]);
    const to_receive = new Map();
    const to_send = new Map();
    function crossfade(from_node, node, params) {
        const { delay = 0, duration = d => Math.sqrt(d) * 30, easing = _easing_index_mjs__WEBPACK_IMPORTED_MODULE_0__.cubicOut } = (0,_internal_index_mjs__WEBPACK_IMPORTED_MODULE_1__.assign)((0,_internal_index_mjs__WEBPACK_IMPORTED_MODULE_1__.assign)({}, defaults), params);
        const from = from_node.getBoundingClientRect();
        const to = node.getBoundingClientRect();
        const dx = from.left - to.left;
        const dy = from.top - to.top;
        const dw = from.width / to.width;
        const dh = from.height / to.height;
        const d = Math.sqrt(dx * dx + dy * dy);
        const style = getComputedStyle(node);
        const transform = style.transform === 'none' ? '' : style.transform;
        const opacity = +style.opacity;
        return {
            delay,
            duration: (0,_internal_index_mjs__WEBPACK_IMPORTED_MODULE_1__.is_function)(duration) ? duration(d) : duration,
            easing,
            css: (t, u) => `
				opacity: ${t * opacity};
				transform-origin: top left;
				transform: ${transform} translate(${u * dx}px,${u * dy}px) scale(${t + (1 - t) * dw}, ${t + (1 - t) * dh});
			`
        };
    }
    function transition(items, counterparts, intro) {
        return (node, params) => {
            items.set(params.key, node);
            return () => {
                if (counterparts.has(params.key)) {
                    const other_node = counterparts.get(params.key);
                    counterparts.delete(params.key);
                    return crossfade(other_node, node, params);
                }
                // if the node is disappearing altogether
                // (i.e. wasn't claimed by the other list)
                // then we need to supply an outro
                items.delete(params.key);
                return fallback && fallback(node, params, intro);
            };
        };
    }
    return [
        transition(to_send, to_receive, false),
        transition(to_receive, to_send, true)
    ];
}




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
/******/ 			id: moduleId,
/******/ 			loaded: false,
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	(() => {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/harmony module decorator */
/******/ 	(() => {
/******/ 		__webpack_require__.hmd = (module) => {
/******/ 			module = Object.create(module);
/******/ 			if (!module.children) module.children = [];
/******/ 			Object.defineProperty(module, 'exports', {
/******/ 				enumerable: true,
/******/ 				set: () => {
/******/ 					throw new Error('ES Modules may not assign module.exports or exports.*, Use ESM export syntax, instead: ' + module.id);
/******/ 				}
/******/ 			});
/******/ 			return module;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!******************************************************!*\
  !*** ./modules/project_browser/sveltejs/src/main.js ***!
  \******************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _App_svelte__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./App.svelte */ "./modules/project_browser/sveltejs/src/App.svelte");


const app = new _App_svelte__WEBPACK_IMPORTED_MODULE_0__["default"]({
  // The #project-browser markup is returned by the project_browser.browse Drupal route.
  target: document.querySelector('#project-browser'),
  props: {},
});

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (app);

})();

/******/ })()
;
//# sourceMappingURL=bundle.js.map