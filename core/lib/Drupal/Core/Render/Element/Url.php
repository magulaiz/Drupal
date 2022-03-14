<?php

namespace Drupal\Core\Render\Element;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Provides a form element for input of a URL.
 *
 * Properties:
 * - #default_value: A valid URL string.
 * - #size: The size of the input element in characters.
 * - #pattern: A string for the native HTML5 pattern attribute.
 *
 * Usage example:
 * @code
 * $form['homepage'] = array(
 *   '#type' => 'url',
 *   '#title' => $this->t('Home Page'),
 *   '#size' => 30,
 *   '#pattern' => '*.example.com',
 *   ...
 * );
 * @endcode
 *
 * @see \Drupal\Core\Render\Element\Textfield
 *
 * @FormElement("url")
 */
class Url extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#maxlength' => 255,
      '#autocomplete_route_name' => FALSE,
      '#process' => [
        [$class, 'processAutocomplete'],
        [$class, 'processAjaxForm'],
        [$class, 'processPattern'],
      ],
      '#element_validate' => [
        [$class, 'validateUrl'],
      ],
      '#pre_render' => [
        [$class, 'preRenderUrl'],
      ],
      '#theme' => 'input__url',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Form element validation handler for #type 'url'.
   *
   * Note that #maxlength and #required is validated by _form_validate() already.
   */
  public static function validateUrl(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    $form_state->setValueForElement($element, $value);

    if ($value !== '') {
      if ($element['#require_url'] && !UrlHelper::isValid($value, TRUE)) {
        $form_state->setError($element, t('The URL %url is not valid.', ['%url' => $value]));
      }
      elseif (!self::validateUri($value)) {
        $form_state->setError($element, t('The URI %uri is not valid.', ['%uri' => $value]));
      }
    }
  }

  /**
   * Validate a URI.
   *
   * URI validation regular expression used with permission.
   * @see https://github.com/bradjones1/regex-uri-validation#why-this-repository
   *
   * @param string $value
   *   URI to validate.
   *
   * @return bool
   *   Whether the validation regex validates the URI.
   */
  protected static function validateUri(string $value): bool {

    $regex = "/^(?:
        [A-Za-z][A-Za-z0-9+\-.]* :
        (?: \/\/
          (?: (?:[A-Za-z0-9\-._~!$&'()*+,;=:]|%[0-9A-Fa-f]{2})* @)?
          (?:
            \[
            (?:
              (?:
                (?:                                                    (?:[0-9A-Fa-f]{1,4}:){6}
                |                                                   :: (?:[0-9A-Fa-f]{1,4}:){5}
                | (?:                            [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){4}
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,1} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){3}
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,2} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){2}
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,3} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}:
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,4} [0-9A-Fa-f]{1,4})? ::
                ) (?:
                    [0-9A-Fa-f]{1,4} : [0-9A-Fa-f]{1,4}
                  | (?: (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?) \.){3}
                        (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
                  )
              |   (?: (?:[0-9A-Fa-f]{1,4}:){0,5} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}
              |   (?: (?:[0-9A-Fa-f]{1,4}:){0,6} [0-9A-Fa-f]{1,4})? ::
              )
            | [Vv][0-9A-Fa-f]+\.[A-Za-z0-9\-._~!$&'()*+,;=:]+
            )
            \]
          | (?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
               (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
          | (?:[A-Za-z0-9\-._~!$&'()*+,;=]|%[0-9A-Fa-f]{2})*
          )
          (?: : [0-9]* )?
          (?:\/ (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
        | \/
          (?:    (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})+
            (?:\/ (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
          )?
        |        (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})+
            (?:\/ (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
        |
        )
        (?:\? (?:[A-Za-z0-9\-._~!$&'()*+,;=:@\/?]|%[0-9A-Fa-f]{2})* )?
        (?:\# (?:[A-Za-z0-9\-._~!$&'()*+,;=:@\/?]|%[0-9A-Fa-f]{2})* )?
      | (?: \/\/
          (?: (?:[A-Za-z0-9\-._~!$&'()*+,;=:]|%[0-9A-Fa-f]{2})* @)?
          (?:
            \[
            (?:
              (?:
                (?:                                                    (?:[0-9A-Fa-f]{1,4}:){6}
                |                                                   :: (?:[0-9A-Fa-f]{1,4}:){5}
                | (?:                            [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){4}
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,1} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){3}
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,2} [0-9A-Fa-f]{1,4})? :: (?:[0-9A-Fa-f]{1,4}:){2}
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,3} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}:
                | (?: (?:[0-9A-Fa-f]{1,4}:){0,4} [0-9A-Fa-f]{1,4})? ::
                ) (?:
                    [0-9A-Fa-f]{1,4} : [0-9A-Fa-f]{1,4}
                  | (?: (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?) \.){3}
                        (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
                  )
              |   (?: (?:[0-9A-Fa-f]{1,4}:){0,5} [0-9A-Fa-f]{1,4})? ::    [0-9A-Fa-f]{1,4}
              |   (?: (?:[0-9A-Fa-f]{1,4}:){0,6} [0-9A-Fa-f]{1,4})? ::
              )
            | [Vv][0-9A-Fa-f]+\.[A-Za-z0-9\-._~!$&'()*+,;=:]+
            )
            \]
          | (?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
               (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
          | (?:[A-Za-z0-9\-._~!$&'()*+,;=]|%[0-9A-Fa-f]{2})*
          )
          (?: : [0-9]* )?
          (?:\/ (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
        | \/
          (?:    (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})+
            (?:\/ (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
          )?
        |        (?:[A-Za-z0-9\-._~!$&'()*+,;=@] |%[0-9A-Fa-f]{2})+
            (?:\/ (?:[A-Za-z0-9\-._~!$&'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*
        |
        )
        (?:\? (?:[A-Za-z0-9\-._~!$&'()*+,;=:@\/?]|%[0-9A-Fa-f]{2})* )?
        (?:\# (?:[A-Za-z0-9\-._~!$&'()*+,;=:@\/?]|%[0-9A-Fa-f]{2})* )?
      )$/x";
    return preg_match($regex, $value);
  }

  /**
   * Prepares a #type 'url' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderUrl($element) {
    $element['#attributes']['type'] = 'url';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-url']);

    return $element;
  }

}
