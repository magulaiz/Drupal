<?php

namespace Drupal\update;

/**
 * Update server project information.
 */
class UpdateServerProjectInfo {

  /**
   * The project data from the update server.
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
   * @param array $data
   *   The project data from the Update XML.
   *
   * @return \Drupal\update\UpdateServerProjectInfo
   *   The UpdateServerProjectInfo instances.
   */
  public static function createFromArray(array $data): UpdateServerProjectInfo {
    return new UpdateServerProjectInfo($data);
  }

  /**
   * Gets the project status.
   *
   * @return string|null
   *   The project status if available, otherwise NULL.
   */
  public function getStatus(): ?string {
    return $this->data['project_status'] ?? NULL;
  }

  /**
   * Gets the supported branches.
   *
   * @link https://www.drupal.org/drupalorg/docs/apis/update-status-xml#s-top-level-project-element
   *   Drupal.org Update XML documentation @endlink for format information.
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
   *   @link https://www.drupal.org/drupalorg/docs/apis/update-status-xml#s-releases-element
   *   Drupal.org Update XML documentation @endlink for element information.
   */
  public function getReleases(): array {
    return $this->data['releases'] ?? [];
  }

}
