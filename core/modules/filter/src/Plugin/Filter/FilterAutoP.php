<?php

namespace Drupal\filter\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to convert line breaks to HTML.
 *
 * Based on: http://photomatt.net/scripts/autop
 *
 * @Filter(
 *   id = "filter_autop",
 *   title = @Translation("Convert line breaks into HTML (i.e. <code>&lt;br&gt;</code> and <code>&lt;p&gt;</code>)"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE
 * )
 */
class FilterAutoP extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    // All block level tags
    $block = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|option|form|map|area|blockquote|address|math|input|p|h[1-6]|fieldset|legend|hr|article|aside|details|figcaption|figure|footer|header|hgroup|menu|nav|section|summary)';

    // Split at opening and closing PRE, SCRIPT, STYLE, OBJECT, IFRAME tags
    // and comments. We don't apply any processing to the contents of these tags
    // to avoid messing up code. We look for matched pairs and allow basic
    // nesting. For example:
    // "processed <pre> ignored <script> ignored </script> ignored </pre> processed"
    $chunks = preg_split('@(<!--.*?-->|</?(?:pre|script|style|object|iframe|drupal-media|!--)[^>]*>)@i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    // Note: PHP ensures the array consists of alternating delimiters and literals
    // and begins and ends with a literal (inserting NULL as required).
    $ignore = FALSE;
    $ignoretag = '';
    $output = '';
    foreach ($chunks as $i => $chunk) {
      if ($i % 2) {
        $comment = (substr($chunk, 0, 4) == '<!--');
        if ($comment) {
          // Nothing to do, this is a comment.
          $output .= $chunk;
          continue;
        }
        // Opening or closing tag?
        $open = ($chunk[1] != '/');
        list($tag) = preg_split('/[ >]/', substr($chunk, 2 - $open), 2);
        if (!$ignore) {
          if ($open) {
            $ignore = TRUE;
            $ignoretag = $tag;
          }
        }
        // Only allow a matching tag to close it.
        elseif (!$open && $ignoretag == $tag) {
          $ignore = FALSE;
          $ignoretag = '';
        }
      }
      elseif (!$ignore) {
        // just to make things a little easier, pad the end
        $chunk = preg_replace('|\n*$|', '', $chunk) . "\n\n";
        $chunk = preg_replace('|<br />\s*<br />|', "\n\n", $chunk);
        // Space things out a little
        $chunk = preg_replace('!(<' . $block . '[^>]*>)!', "\n$1", $chunk);
        // Space things out a little
        $chunk = preg_replace('!(</' . $block . '>)!', "$1\n\n", $chunk);
        // take care of duplicates
        $chunk = preg_replace("/\n\n+/", "\n\n", $chunk);
        $chunk = preg_replace('/^\n|\n\s*\n$/', '', $chunk);
        // make paragraphs, including one at the end
        $chunk = '<p>' . preg_replace('/\n\s*\n\n?(.)/', "</p>\n<p>$1", $chunk) . "</p>\n";
        // problem with nested lists
        $chunk = preg_replace("|<p>(<li.+?)</p>|", "$1", $chunk);
        $chunk = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $chunk);
        $chunk = str_replace('</blockquote></p>', '</p></blockquote>', $chunk);
        // under certain strange conditions it could create a P of entirely whitespace
        $chunk = preg_replace('|<p>\s*</p>\n?|', '', $chunk);
        $chunk = preg_replace('!<p>\s*(</?' . $block . '[^>]*>)!', "$1", $chunk);
        $chunk = preg_replace('!(</?' . $block . '[^>]*>)\s*</p>!', "$1", $chunk);
        // make line breaks
        $chunk = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $chunk);
        $chunk = preg_replace('!(</?' . $block . '[^>]*>)\s*<br />!', "$1", $chunk);
        $chunk = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)!', '$1', $chunk);
        $chunk = preg_replace('/&([^#])(?![A-Za-z0-9]{1,8};)/', '&amp;$1', $chunk);
      }
      $output .= $chunk;
    }

    return new FilterProcessResult($output);
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t('Lines and paragraphs are automatically recognized. The &lt;br /&gt; line break, &lt;p&gt; paragraph and &lt;/p&gt; close paragraph tags are inserted automatically. If paragraphs are not recognized simply add a couple of blank lines.');
    }
    else {
      return $this->t('Lines and paragraphs break automatically.');
    }
  }

}
