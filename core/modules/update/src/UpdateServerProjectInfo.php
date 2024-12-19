<?php

declare(strict_types=1);

namespace Drupal\update;

/**
 * Update server project information.
 */
class UpdateServerProjectInfo {

  /**
   * Constructs a UpdateServerProjectInfo object.
   */
  private function __construct(
    private readonly ?string $status = NULL,
    private readonly array $supportedBranches = [],
    private readonly array $releases = [],
  ) {}

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
    $data['supported_branches'] ??= [];
    return new UpdateServerProjectInfo(
      $data['project_status'] ?? NULL,
        $data['supported_branches'] ? explode(',', $data['supported_branches']) : [],
        $data['releases'] ?? []
    );
  }

  /**
   * Gets the project status.
   *
   * @return string|null
   *   The project status if available, otherwise NULL.
   */
  public function getStatus(): ?string {
    return $this->status;
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
    return $this->supportedBranches;
  }

  /**
   * Gets the project releases.
   *
   * @return array[]
   *   The project releases.
   *   @link https://www.drupal.org/drupalorg/docs/apis/update-status-xml#s-releases-element
   *   Drupal.org Update XML documentation @endlink for element information. The
   *   releases are ordered by version number descending.
   */
  public function getReleases(): array {
    return $this->releases;
  }

  /**
   * Determines if we can recommend any release in the project.
   *
   * @return bool
   *   TRUE if the project can have any release recommended, otherwise false.
   */
  public function isProjectRecommendable(): bool {
    $unusable_project_statuses = [
      'insecure',
      'unpublished',
      'revoked',
      'unsupported',
      'not-fetched',
    ];
    return in_array($this->getStatus(), $unusable_project_statuses, TRUE);
  }

}
