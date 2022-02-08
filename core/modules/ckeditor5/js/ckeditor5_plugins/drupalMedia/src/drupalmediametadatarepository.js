/* eslint-disable import/no-extraneous-dependencies */

import { Plugin } from 'ckeditor5/src/core';

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

  return {};
};

/**
 * @internal
 */
export default class DrupalMediaMetadataRepository extends Plugin {
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
    if (this._data.get(modelElement)) {
      return new Promise((resolve) => {
        resolve(this._data.get(modelElement));
      });
    }

    const reject = new Promise((resolve, reject) => {
      reject();
    });

    const options = this.editor.config.get('drupalMedia');
    if (!options) {
      return reject;
    }

    if (!modelElement.hasAttribute('drupalMediaEntityUuid')) {
      return reject;
    }

    const { mediaEntityMetadataUrl } = options;
    const query = new URLSearchParams({
      uuid: modelElement.getAttribute('drupalMediaEntityUuid'),
    });
    // The `mediaEntityMetadataUrl` received from the server already includes a
    // a query string (for the CSRF token).
    // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\Media::getDynamicPluginConfig()
    const url = `${mediaEntityMetadataUrl}&${query}`;

    // @todo how to handle errors?
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
