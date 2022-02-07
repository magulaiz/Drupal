import { Plugin } from 'ckeditor5/src/core';

export default class DrupalMediaMetadataRepository extends Plugin {

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
