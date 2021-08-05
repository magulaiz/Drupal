<?php

namespace Drupal\filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to convert URLs into links.
 *
 * @Filter(
 *   id = "filter_url",
 *   title = @Translation("Convert URLs into links"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *     "filter_url_length" = 72
 *   }
 * )
 */
class FilterUrl extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['filter_url_length'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum link text length'),
      '#default_value' => $this->settings['filter_url_length'],
      '#min' => 1,
      '#field_suffix' => $this->t('characters'),
      '#description' => $this->t('URLs longer than this number of characters will be truncated to prevent long strings that break formatting. The link itself will be retained; just the text portion of the link will be truncated.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    // Tags to skip and not recurse into.
    $ignore_tags = 'a|script|style|code|pre';

    // Pass length to regexp callback.
    $this->trimUrl(NULL, $this->settings['filter_url_length']);

    // Create an array which contains the regexps for each type of link.
    // The key to the regexp is the name of a function that is used as
    // callback function to process matches of the regexp. The callback function
    // is to return the replacement for the match. The array is used and
    // matching/replacement done below inside some loops.
    $tasks = [];

    // Prepare protocols pattern for absolute URLs.
    // \Drupal\Component\Utility\UrlHelper::stripDangerousProtocols() will replace
    // any bad protocols with HTTP, so we need to support the identical list.
    // While '//' is technically optional for MAILTO only, we cannot cleanly
    // differ between protocols here without hard-coding MAILTO, so '//' is
    // optional for all protocols.
    // @see \Drupal\Component\Utility\UrlHelper::stripDangerousProtocols()
    $protocols = \Drupal::getContainer()->getParameter('filter_protocols');
    $protocols = implode(':(?://)?|', $protocols) . ':(?://)?';

    $valid_url_path_characters = "[\p{L}\p{M}\p{N}!\*\';:=\+,\.\$\/%#\[\]\-_~@&]";

    // Allow URL paths to contain balanced parens
    // 1. Used in Wikipedia URLs like /Primer_(film)
    // 2. Used in IIS sessions like /S(dfd346)/
    $valid_url_balanced_parens = '\(' . $valid_url_path_characters . '+\)';

    // Valid end-of-path characters (so /foo. does not gobble the period).
    // 1. Allow =&# for empty URL parameters and other URL-join artifacts
    $valid_url_ending_characters = '[\p{L}\p{M}\p{N}:_+~#=/]|(?:' . $valid_url_balanced_parens . ')';

    $valid_url_query_chars = '[a-zA-Z0-9!?\*\'@\(\);:&=\+\$\/%#\[\]\-_\.,~|]';
    $valid_url_query_ending_chars = '[a-zA-Z0-9_&=#\/]';

    // full path
    // and allow @ in a url, but only in the middle. Catch things like http://example.com/@user/
    $valid_url_path = '(?:(?:' . $valid_url_path_characters . '*(?:' . $valid_url_balanced_parens . $valid_url_path_characters . '*)*' . $valid_url_ending_characters . ')|(?:@' . $valid_url_path_characters . '+\/))';

    // Prepare domain name pattern.
    // The ICANN seems to be on track towards accepting more diverse top level
    // domains, so this pattern has been "future-proofed" to allow for TLDs
    // of length 2-64.
    $domain = '(?:[\p{L}\p{M}\p{N}._+-]+\.)?[\p{L}\p{M}]{2,64}\b';
    $ip = '(?:[0-9]{1,3}\.){3}[0-9]{1,3}';
    $auth = '[\p{L}\p{M}\p{N}:%_+*~#?&=.,/;-]+@';
    $trail = '(' . $valid_url_path . '*)?(\\?' . $valid_url_query_chars . '*' . $valid_url_query_ending_chars . ')?';

    // Match absolute URLs.
    $url_pattern = "(?:$auth)?(?:$domain|$ip)/?(?:$trail)?";
    $pattern = "`((?:$protocols)(?:$url_pattern))`u";
    $tasks['parseFullLinks'] = $pattern;

    // Match email addresses.
    $url_pattern = "[\p{L}\p{M}\p{N}._+-]{1,254}@(?:$domain)";
    $pattern = "`($url_pattern)`u";
    $tasks['parseEmailLinks'] = $pattern;

    // Match www domains.
    $url_pattern = "www\.(?:$domain)/?(?:$trail)?";
    $pattern = "`($url_pattern)`u";
    $tasks['parsePartialLinks'] = $pattern;

    // Each type of URL needs to be processed separately. The text is joined and
    // re-split after each task, since all injected HTML tags must be correctly
    // protected before the next task.
    foreach ($tasks as $task => $pattern) {
      // HTML comments need to be handled separately, as they may contain HTML
      // markup, especially a '>'. Therefore, remove all comment contents and add
      // them back later.
      $this->escapeComments('', TRUE);
      $text = preg_replace_callback('`<!--(.*?)-->`s', [$this, 'escapeComments'], $text);

      // Split at all tags; ensures that no tags or attributes are processed.
      $chunks = preg_split('/(<.+?>)/is', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
      // PHP ensures that the array consists of alternating delimiters and
      // literals, and begins and ends with a literal (inserting NULL as
      // required). Therefore, the first chunk is always text:
      $chunk_type = 'text';
      // If a tag of $ignore_tags is found, it is stored in $open_tag and only
      // removed when the closing tag is found. Until the closing tag is found,
      // no replacements are made.
      $open_tag = '';

      for ($i = 0; $i < count($chunks); $i++) {
        if ($chunk_type == 'text') {
          // Only process this text if there are no unclosed $ignore_tags.
          if ($open_tag == '') {
            // If there is a match, inject a link into this chunk via the callback
            // function contained in $task.
            $chunks[$i] = preg_replace_callback($pattern, [$this, $task], $chunks[$i]);
          }
          // Text chunk is done, so next chunk must be a tag.
          $chunk_type = 'tag';
        }
        else {
          // Only process this tag if there are no unclosed $ignore_tags.
          if ($open_tag == '') {
            // Check whether this tag is contained in $ignore_tags.
            if (preg_match("`<($ignore_tags)(?:\s|>)`i", $chunks[$i], $matches)) {
              $open_tag = $matches[1];
            }
          }
          // Otherwise, check whether this is the closing tag for $open_tag.
          else {
            if (preg_match("`<\/$open_tag>`i", $chunks[$i], $matches)) {
              $open_tag = '';
            }
          }
          // Tag chunk is done, so next chunk must be text.
          $chunk_type = 'text';
        }
      }

      $text = implode($chunks);
      // Revert to the original comment contents
      $this->escapeComments('', FALSE);
      $text = preg_replace_callback('`<!--(.*?)-->`', [$this, 'escapeComments'], $text);
    }

    return new FilterProcessResult($text);
  }

  /**
   * Makes links out of absolute URLs.
   *
   * Callback for preg_replace_callback() within ::process().
   */
  public function parseFullLinks($match) {
    // The $i:th parenthesis in the regexp contains the URL.
    $i = 1;

    $match[$i] = Html::decodeEntities($match[$i]);
    $caption = Html::escape($this->trimUrl($match[$i]));
    $match[$i] = Html::escape($match[$i]);
    return '<a href="' . $match[$i] . '">' . $caption . '</a>';
  }

  /**
   * Makes links out of email addresses.
   *
   * Callback for preg_replace_callback() within ::process().
   */
  public function parseEmailLinks($match) {
    // The $i:th parenthesis in the regexp contains the URL.
    $i = 0;

    $match[$i] = Html::decodeEntities($match[$i]);
    $caption = Html::escape($this->trimUrl($match[$i]));
    $match[$i] = Html::escape($match[$i]);
    return '<a href="mailto:' . $match[$i] . '">' . $caption . '</a>';
  }

  /**
   * Makes links out of domain names starting with "www.".
   *
   * Callback for preg_replace_callback() within ::process().
   */
  public function parsePartialLinks($match) {
    // The $i:th parenthesis in the regexp contains the URL.
    $i = 1;

    $match[$i] = Html::decodeEntities($match[$i]);
    $caption = Html::escape($this->trimUrl($match[$i]));
    $match[$i] = Html::escape($match[$i]);
    return '<a href="http://' . $match[$i] . '">' . $caption . '</a>';
  }

  /**
   * Escapes the contents of HTML comments.
   *
   * Callback for preg_replace_callback() within ::process().
   *
   * @param array $match
   *   An array containing matches to replace from preg_replace_callback(),
   *   whereas $match[1] is expected to contain the content to be filtered.
   * @param bool|null $escape
   *   (optional) A Boolean indicating whether to escape (TRUE) or unescape
   *   comments (FALSE). Defaults to NULL, indicating neither. If TRUE, statically
   *   cached $comments are reset.
   */
  public function escapeComments($match, $escape = NULL) {
    static $mode, $comments = [];

    if (isset($escape)) {
      $mode = $escape;
      if ($escape) {
        $comments = [];
      }
      return;
    }

    // Replace all HTML comments with a '<!-- [hash] -->' placeholder.
    if ($mode) {
      $content = $match[1];
      $hash = hash('sha256', $content);
      $comments[$hash] = $content;
      return "<!-- $hash -->";
    }
    // Or replace placeholders with actual comment contents.
    else {
      $hash = $match[1];
      $hash = trim($hash);
      $content = $comments[$hash];
      return "<!--$content-->";
    }
  }

  /**
   * Shortens a long URL to a given length ending with an ellipsis.
   */
  public function trimUrl($text, $length = NULL) {
    static $_length;
    if ($length !== NULL) {
      $_length = $length;
    }

    if (isset($_length)) {
      $text = Unicode::truncate($text, $_length, FALSE, TRUE);
    }

    return $text;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Web page addresses and email addresses turn into links automatically.');
  }

}
