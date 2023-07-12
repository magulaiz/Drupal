/* eslint-disable import/no-extraneous-dependencies */
// cspell:ignore mediaimagetextalternative medialinktextalternative drupalmediacaption

import DrupalMedia from './drupalmedia';

// cspell:ignore drupallinkmedia
import DrupalLinkMedia from './drupallinkmedia/drupallinkmedia';

// cspell:ignore drupalelementstyle
import DrupalElementStyle from './drupalelementstyle';

import DrupalMediaCaption from './drupalmediacaption';

// cspell:ignore mediaimagetextalternative medialinktextalternative medialinktextalternativeui medialinktextalternativeediting
import MediaImageTextAlternative from './mediaimagetextalternative';
import MediaImageTextAlternativeEditing from './mediaimagetextalternative/mediaimagetextalternativeediting';
import MediaImageTextAlternativeUi from './mediaimagetextalternative/mediaimagetextalternativeui';
import MediaLinkTextAlternativeEditing from './medialinktextalternative/medialinktextalternativeediting';
import MediaLinkTextAlternativeUi from './medialinktextalternative/medialinktextalternativeui';

/**
 * @private
 */
export default {
  DrupalMedia,
  MediaImageTextAlternative,
  MediaImageTextAlternativeEditing,
  MediaImageTextAlternativeUi,
  MediaLinkTextAlternativeEditing,
  MediaLinkTextAlternativeUi,
  DrupalLinkMedia,
  DrupalMediaCaption,
  DrupalElementStyle,
};
