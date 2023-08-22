<?php

namespace Drupal\Core\Extension\Requirement;

/**
 * Interface for Requirements.
 */
interface RequirementInterface extends \ArrayAccess {

  /**
   * The update phase.
   */
  const PHASE_UPDATE = 'update';

  /**
   * The runtime phase.
   */
  const PHASE_RUNTIME = 'runtime';

  /**
   * The install phase.
   */
  const PHASE_INSTALL = 'install';

  /**
   * The OK severity.
   */
  const SEVERITY_OK = 0;

  /**
   * The info severity.
   */
  const SEVERITY_INFO = -1;

  /**
   * The error severity.
   */
  const SEVERITY_ERROR = 2;

  /**
   * The warning severity.
   */
  const SEVERITY_WARNING = 1;

  /**
   * Gets the severity.
   *
   * @return int
   *   The severity.
   */
  public function getSeverity();

  /**
   * Gets the title.
   *
   * @return string
   *   The title.
   */
  public function getTitle();

  /**
   * Sets the title.
   *
   * @param string $title
   *   The title.
   *
   * @return $this
   */
  public function setTitle($title);

  /**
   * Gets the value.
   *
   * @return string
   *   The value.
   */
  public function getValue();

  /**
   * Sets the value.
   *
   * @param string $value
   *   The value.
   *
   * @return $this
   */
  public function setValue($value);

  /**
   * Gets the description.
   *
   * @return string
   *   The description.
   */
  public function getDescription();

  /**
   * Sets the description.
   *
   * @param string $description
   *   The description.
   *
   * @return $this
   */
  public function setDescription($description);

}
