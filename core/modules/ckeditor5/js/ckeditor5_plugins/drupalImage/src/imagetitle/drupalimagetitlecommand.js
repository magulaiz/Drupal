/* eslint-disable import/no-extraneous-dependencies */
import { Command } from 'ckeditor5/src/core';

/**
 * The image title command. It is used to change the `title` attribute of `<imageBlock>` and `<imageInline>` model elements.
 */
export default class DrupalImageTitleCommand extends Command {
  /**
   * The command value: `false` if there is no `title` attribute, otherwise the value of the `title` attribute.
   */
  requires() {
    return ['ImageUtils'];
  }
  /**
   * @inheritDoc
   */
  refresh() {
    const editor = this.editor;
    const imageUtils = editor.plugins.get( 'ImageUtils' );
    const element = imageUtils.getClosestSelectedImageElement( this.editor.model.document.selection );

    this.isEnabled = !!element;

    if ( this.isEnabled && element.hasAttribute( 'title' ) ) {
      this.value = element.getAttribute( 'title' );
    } else {
      this.value = false;
    }
  }

  /**
   * Executes the command.
   *
   * @param options
   * @param options.newValue The new value of the `title` attribute to set.
   */
  execute(options) {
    const editor = this.editor;
    const imageUtils = editor.plugins.get( 'ImageUtils' );
    const model = editor.model;
    const imageElement = imageUtils.getClosestSelectedImageElement( model.document.selection );
    console.log("EXECUTING DrupalImageTitleCommand");
    console.log(options.newValue);
    console.log(imageElement);
    model.change( writer => {
      writer.setAttribute( 'title', options.newValue, imageElement );
    } );
  }
}
