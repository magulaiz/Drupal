<?php

namespace Drupal\layout_builder;

/**
 * Defines the interface for an object that stores layout sections.
 *
 * @see \Drupal\layout_builder\Section
 */
interface SectionListInterface extends \Countable {

  /**
   * Gets the layout sections.
   *
   * @param bool $key_by_uuid
   *   (optional) Whether to return the section keyed by UUID. Defaults to FALSE.
   *
   * @return Section[]
   *   An array of section objects.
   */
  public function getSections(bool $key_by_uuid = FALSE);

  /**
   * Gets a domain object for the layout section.
   *
   * @param int|string $uuid
   *   The UUID of the section.
   *
   * @return \Drupal\layout_builder\Section
   *   The layout section.
   */
  public function getSection($uuid);

  /**
   * Appends a new section to the end of the list.
   *
   * @param \Drupal\layout_builder\Section $section
   *   The section to append.
   *
   * @return $this
   */
  public function appendSection(Section $section);

  /**
   * Inserts a new section at a given delta.
   *
   * If a section exists at the given index, the section at that position and
   * others after it are shifted backward.
   *
   * @param int $delta
   *   The delta of the section.
   * @param \Drupal\layout_builder\Section $section
   *   The section to insert.
   *
   * @return $this
   */
  public function insertSection($delta, Section $section);

  /**
   * Removes the section at the given UUID.
   *
   * As sections are stored sequentially this will re-key every
   * subsequent section, shifting them forward.
   *
   * @param int|string $uuid
   *   The UUID of the section.
   *
   * @return $this
   */
  public function removeSection($uuid);

  /**
   * Removes all of the sections.
   *
   * @param bool $set_blank
   *   (optional) The default implementation of section lists differentiates
   *   between a list that has never contained any sections and a list that has
   *   purposefully had all sections removed in order to remain blank. Passing
   *   TRUE will mirror ::removeSection() by tracking this as a blank list.
   *   Passing FALSE will reset the list as though it had never contained any
   *   sections at all. Defaults to FALSE.
   *
   * @return $this
   */
  public function removeAllSections($set_blank = FALSE);

}
