/* eslint-disable import/no-extraneous-dependencies, no-throw-literal, prefer-template */
// cspell:ignore linksuggestionediting focusables

import { Plugin } from 'ckeditor5/src/core';
import { SwitchButtonView, View, ViewCollection } from 'ckeditor5/src/ui';
import DrupalEntityLinkSuggestionsEditing from './linksuggestionediting';
import initializeAutocomplete from './autocomplete';

class DrupalEntityLinkSuggestions extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [DrupalEntityLinkSuggestionsEditing];
  }

  init() {
    this._state = {};
    const editor = this.editor;
    // TRICKY: Work-around until the CKEditor team offers a better solution: force the ContextualBalloon to get instantiated early thanks to DrupalImage not yet being optimized like https://github.com/ckeditor/ckeditor5/commit/c276c45a934e4ad7c2a8ccd0bd9a01f6442d4cd3#diff-1753317a1a0b947ca8b66581b533616a5309f6d4236a527b9d21ba03e13a78d8.
    editor.plugins.get('LinkUI')._createViews();

    // Some of the attributes supported by this plugin are exposed in the UI.
    const attrs = editor.plugins.get(
      'DrupalEntityLinkSuggestionsEditing',
    ).attrs;
    const exposedAttributes = {
      download: {
        label: Drupal.t('Download link'),
        viewName: 'download',
        modelName: 'drupalEntityLinkDownload',
      },
    };

    this._buttonViews = new ViewCollection();
    attrs.reverse().forEach((attrName) => {
      if (!exposedAttributes.hasOwnProperty(attrName)) {
        return;
      }
      this._createExtraButtonView(
        exposedAttributes[attrName].modelName,
        exposedAttributes[attrName],
      );
    });
    this._enableLinkAutocomplete();
    this._handleExtraFormFieldSubmit();
    this._handleDataLoadingIntoExtraFormField();
  }

  _createExtraButtonView(modelName, options) {
    const editor = this.editor;
    const locale = editor.locale;
    const linkCommand = editor.commands.get('link');
    const linkFormView = editor.plugins.get('LinkUI').formView;

    const buttonView = new SwitchButtonView(editor.locale);
    buttonView.set({
      name: modelName,
      label: options.label,
      withText: true,
    });
    buttonView.on('execute', () => {
      this.set(modelName, !buttonView.isOn);
    });
    this.on(`change:${modelName}`, (evt, propertyName, newValue, oldValue) => {
      buttonView.isOn = newValue === true;
      buttonView.isVisible = typeof newValue === 'boolean';
    });

    linkFormView.on('render', () => {
      linkFormView._focusables.add(buttonView, 1);
      linkFormView.focusTracker.add(buttonView.element);
    });

    this._buttonViews.add(buttonView);
    linkFormView[modelName] = buttonView;
  }

  _enableLinkAutocomplete() {
    const editor = this.editor;
    const hostEntityTypeId = editor.sourceElement.getAttribute(
      'data-ckeditor5-host-entity-type',
    );
    const hostEntityLangcode = editor.sourceElement.getAttribute(
      'data-ckeditor5-host-entity-langcode',
    );
    const linkFormView = editor.plugins.get('LinkUI').formView;
    let wasAutocompleteAdded = false;

    linkFormView.extendTemplate({
      attributes: {
        class: ['ck-vertical-form', 'ck-link-form_layout-vertical'],
      },
    });

    const additionalButtonsView = new View();
    additionalButtonsView.setTemplate({
      tag: 'ul',
      children: this._buttonViews.map((buttonView) => ({
        tag: 'li',
        children: [buttonView],
        attributes: {
          class: ['ck', 'ck-list__item'],
        },
      })),
      attributes: {
        class: ['ck', 'ck-reset', 'ck-list'],
      },
    });
    linkFormView.children.add(additionalButtonsView, 1);

    editor.plugins
      .get('ContextualBalloon')
      .on('set:visibleView', (evt, propertyName, newValue, oldValue) => {
        if (newValue === linkFormView) {
          // Ensure the visibility is computed for the `download` switch button when it's not explicitly set (
          // editing an existing link).
          if (
            this.entityType &&
            this.entityUuid &&
            typeof this.drupalEntityLinkDownload !== 'boolean'
          ) {
            // Use the local cache if it is primed.
            const cached = window.sessionStorage.getItem(
              `ckeditor5:drupal-entity-link-suggestions:download:${this.entityType}:${this.entityUuid}`,
            );
            if (cached === null) {
              console.error('TODO IMPLEMENT FETCHING OF THIS METADATA');
            }
            const isDownloadable = cached === 'true';
            if (isDownloadable) {
              this.set('drupalEntityLinkDownload', false);
            }
          }
        }

        if (newValue !== linkFormView || wasAutocompleteAdded) {
          return;
        }

        /**
         * Used to know if a selection was made from the autocomplete results.
         *
         * @type {boolean}
         */
        let selected;

        initializeAutocomplete(linkFormView.urlInputView.fieldView.element, {
          // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\EntityLinkSuggestions::getDynamicPluginConfig()
          autocompleteUrl: this.editor.config
            .get('drupalEntityLinkSuggestions')
            .suggestionsUrl.replace(
              '/_/_',
              `/${hostEntityTypeId}/${hostEntityLangcode}`,
            ),
          selectHandler: (event, { item }) => {
            if (!item.path) {
              throw 'Missing path param.' + JSON.stringify(item);
            }

            if (item.entity_type_id || item.entity_uuid) {
              if (!item.entity_type_id || !item.entity_uuid) {
                throw 'Missing path param.' + JSON.stringify(item);
              }

              this.set('entityType', item.entity_type_id);
              this.set('entityUuid', item.entity_uuid);
              if (item.exposed_attributes.download === true) {
                this.set(
                  'drupalEntityLinkDownload',
                  typeof this.drupalEntityLinkDownload === 'boolean'
                    ? this.drupalEntityLinkDownload // Keep current state if it is already specified.
                    : true, // Otherwise default downloadable linked entities to have the `download` attribute by default.
                );
              } else {
                this.set('drupalEntityLinkDownload', null);
              }
              // Prime local cache for when this link gets edited again during the same session.
              window.sessionStorage.setItem(
                `ckeditor5:drupal-entity-link-suggestions:download:${item.entity_type_id}:${item.entity_uuid}`,
                item.exposed_attributes.download,
              );
            } else {
              this.set('entityType', null);
              this.set('entityUuid', null);
              this.set('drupalEntityLinkDownload', null);
            }

            event.target.value = item.path;
            selected = true;
            return false;
          },
          openHandler: (event) => {
            selected = false;
          },
          closeHandler: (event) => {
            if (!selected) {
              this.set('entityType', null);
              this.set('entityUuid', null);
              this.set('drupalEntityLinkDownload', null);
            }
            selected = false;
          },
        });

        wasAutocompleteAdded = true;
      });
  }

  _handleExtraFormFieldSubmit() {
    const editor = this.editor;
    const linkFormView = editor.plugins.get('LinkUI').formView;
    const linkCommand = editor.commands.get('link');

    this.listenTo(
      linkFormView,
      'submit',
      () => {
        const values = {
          'data-entity-type': this.entityType,
          'data-entity-uuid': this.entityUuid,
          download: this.drupalEntityLinkDownload,
        };
        // Stop the execution of the link command caused by closing the form.
        // Inject the extra attribute value. The highest priority listener here
        // injects the argument (here below 👇).
        // - The high priority listener in
        //   _addExtraAttributeOnLinkCommandExecute() gets that argument and sets
        //   the extra attribute.
        // - The normal (default) priority listener in ckeditor5-link sets
        //   (creates) the actual link.
        linkCommand.once(
          'execute',
          (evt, args) => {
            if (args.length < 3) {
              args.push(values);
            } else if (args.length === 3) {
              Object.assign(args[2], values);
            } else {
              throw Error('The link command has more than 3 arguments.');
            }
          },
          { priority: 'highest' },
        );
      },
      { priority: 'high' },
    );
  }

  _handleDataLoadingIntoExtraFormField() {
    const editor = this.editor;
    const linkCommand = editor.commands.get('link');

    this.bind('entityType').to(linkCommand, 'data-entity-type');
    this.bind('entityUuid').to(linkCommand, 'data-entity-uuid');
    this.bind('drupalEntityLinkDownload').to(linkCommand, 'download');
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'DrupalEntityLinkSuggestions';
  }
}

export default {
  DrupalEntityLinkSuggestions,
};
