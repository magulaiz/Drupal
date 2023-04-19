<?php

namespace Drupal\announcements_feed;

/**
 * Object containing a single announcement from the feed.
 *
 * @internal
 */
final class Announcement {

  /**
   * Construct an Announcement object.
   *
   * @param string $id
   *   Unique identifier of the announcement.
   * @param string $title
   *   Title of the announcement.
   * @param string $url
   *   URL where the announcement can be seen.
   * @param string $date_modified
   *   When was the announcement last modified.
   * @param string $date_published
   *   When was the announcement published.
   * @param string $content_html
   *   HTML content of the announcement.
   * @param string $version
   *   Target Drupal version of the announcement.
   * @param bool $featured
   *   Whether this announcement is featured or not.
   * @param bool $new
   *   Indicates if the announcement is new to the user. Defaults to FALSE.
   */
  public function __construct(
    public readonly string $id,
    public readonly string $title,
    public readonly string $url,
    public readonly string $date_modified,
    public readonly string $date_published,
    public readonly string $content_html,
    public readonly string $version,
    public readonly bool $featured,
    public bool $new = FALSE
  ) {
  }

  /**
   * Normalizes the value object.
   *
   * @return array
   *   The normalized value object.
   */
  public function normalize(): array {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'url' => $this->url,
      'date_modified' => $this->date_modified,
      'date_published' => $this->date_published,
      'content_html' => $this->content_html,
      'version' => $this->version,
      'featured' => $this->featured,
      'new' => $this->new,
    ];
  }

}
