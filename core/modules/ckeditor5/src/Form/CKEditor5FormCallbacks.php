<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Form;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

class CKEditor5FormCallbacks {

  /**
   * Form submission handler for filter format forms.
   */
  #[TrustedCallback]
  public static function filterFormatEditFormSubmit(array $form, FormStateInterface $form_state) {
    $limit_allowed_html_tags = isset($form['filters']['settings']['filter_html']['allowed_html']);
    $manually_editable_tags = $form_state->getValue(['editor', 'settings', 'plugins', 'ckeditor5_sourceEditing', 'allowed_tags']);
    $styles = $form_state->getValue(['editor', 'settings', 'plugins', 'ckeditor5_style', 'styles']);
    if ($limit_allowed_html_tags && is_array($manually_editable_tags) || is_array($styles)) {
      // When "Manually editable tags", "Style" and "limit allowed HTML tags" are
      // all configured, the latter is dependent on the others. This dependent
      // value is typically updated via AJAX, but it's possible for "Manually
      // editable tags" to update without triggering the AJAX rebuild. That value
      // is recalculated here on save to ensure it happens even if the AJAX
      // rebuild doesn't happen.
      $manually_editable_tags_restrictions = HTMLRestrictions::fromString(implode($manually_editable_tags ?? []));
      $styles_restrictions = HTMLRestrictions::fromString(implode($styles ? array_column($styles, 'element') : []));
      $format = $form_state->get('ckeditor5_validated_pair')->getFilterFormat();
      $allowed_html = HTMLRestrictions::fromTextFormat($format);
      $combined_tags_string = $allowed_html
        ->merge($manually_editable_tags_restrictions)
        ->merge($styles_restrictions)
        ->toFilterHtmlAllowedTagsString();
      $form_state->setValue(['filters', 'filter_html', 'settings', 'allowed_html'], $combined_tags_string);
    }
  }

}
