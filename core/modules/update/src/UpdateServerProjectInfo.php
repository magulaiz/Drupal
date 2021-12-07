<?php

namespace Drupal\update;

/**
 * Update server project information.
 */
class UpdateServerProjectInfo {


  /**
   * The project data from the server.
   *
   * @var array[]
   */
  protected $data;

  /**
   * Constructs a UpdateServerProjectInfo object.
   *
   * @param array $data
   *   The project data from the Update XML.
   */
  private function __construct(array $data) {
    $this->data = $data;
  }

  /**
   * Creates a UpdateServerProjectInfo object.
   *
   * @param array $available
   *   The project data from the Update XML.
   *
   * @return \Drupal\update\UpdateServerProjectInfo
   *   The UpdateServerProjectInfo instances.
   */
  public static function createFromArray(array $available): UpdateServerProjectInfo {
    return new UpdateServerProjectInfo($available);
  }

  /**
   * Gets the project status.
   *
   * @return string|null
   *   The project status if available, otherwise NULL.
   */
  public function getProjectStatus(): ?string {
    return $available['project_status'] ?? NULL;
  }

  /**
   * Gets the supported branches.
   *
   * @return string[]
   *   The supported branches.
   */
  public function getSupportBranches(): array {
    if (isset($this->data['supported_branches'])) {
      return explode(',', $this->data['supported_branches']);
    }
    return [];
  }

  /**
   * Gets the project releases.
   *
   * @return array[]
   *   The project releases.
   */
  public function getReleases(): array {
    return $this->data['releases'] ?? [];
  }

}
