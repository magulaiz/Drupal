/* eslint-disable import/no-extraneous-dependencies, no-throw-literal, prefer-template */
// cspell:ignore linksuggestionediting

import { Plugin } from 'ckeditor5/src/core';
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
    this._enableLinkAutocomplete();
    this._handleDataLoadingIntoExtraFormField();
  }

  _enableLinkAutocomplete() {
    const editor = this.editor;
    const hostEntityTypeId = editor.sourceElement.getAttribute(
      'data-ckeditor5-host-entity-type',
    );
    const hostEntityLangcode = editor.sourceElement.getAttribute(
      'data-ckeditor5-host-entity-langcode',
    );
    let wasAutocompleteAdded = false;

    editor.plugins.get('ContextualBalloon')._rotatorView.content.on(
      'add',
      (evt, view) => {
        // The linkFormView is lazily instantiated. Modify it when it is created. It's reused for the lifetime of the editor.
        // @todo Once LinkUI makes _isFormInPanel public (or an alternative), use that instead of checking this class
        const isLinkFormView =
          view.template.attributes.class.includes('ck-link-form');
        if (!isLinkFormView || wasAutocompleteAdded) {
          return;
        }

        // This is the earliest known time for the LinkFormView to exist: extends its template + submit event listener.
        const linkFormView = view;
        linkFormView.extendTemplate({
          attributes: {
            class: ['ck-vertical-form', 'ck-link-form_layout-vertical'],
          },
        });
        this._handleExtraFormFieldSubmit();

        /**
         * Used to know if a selection was made from the autocomplete results.
         *
         * @type {boolean}
         */
        let selected;

        initializeAutocomplete(linkFormView.urlInputView.fieldView.element, {
          autocompleteUrl: Drupal.url(
            `ckeditor5/entity-link-suggestions/${hostEntityTypeId}/${hostEntityLangcode}`,
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
            } else {
              this.set('entityType', null);
              this.set('entityUuid', null);
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
            }
            selected = false;
          },
        });

        wasAutocompleteAdded = true;
      },
      { priority: 'highest' },
    );
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
